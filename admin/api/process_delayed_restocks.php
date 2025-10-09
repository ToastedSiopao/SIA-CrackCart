<?php
// Set a higher time limit for the script to run, as it might process many records.
set_time_limit(300); 

// This script should be run by a cron job, not accessed via a web browser.
// A simple check can be done via checking the request method, but a more robust method
// would be to check the IP or use a secret key passed as a command-line argument.
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(403);
    die("Error: This script is designed for automated execution, not direct web access.");
}

include __DIR__ . '../../../db_connect.php';
include __DIR__ . '../../../log_function.php';

$log_prefix = "[Cron Job - Delayed Restock]";
log_action('Cron Execution', "{$log_prefix} Script started.");

// --- Main Logic ---

$processed_count = 0;
$error_count = 0;

try {
    // Begin a transaction for safety. If anything fails, we can roll back.
    $conn->begin_transaction();

    // Find all approved returns that are due for restocking (older than 24 hours)
    // and have not been processed yet.
    $stmt_find_returns = $conn->prepare(
        "SELECT return_id, order_item_id FROM returns 
         WHERE status = 'approved' 
         AND restock_processed = FALSE 
         AND approved_at <= NOW() - INTERVAL 1 DAY"
    );

    if (!$stmt_find_returns) {
        throw new Exception("Failed to prepare statement to find due returns: " . $conn->error);
    }
    
    $stmt_find_returns->execute();
    $returns_to_process = $stmt_find_returns->get_result();

    if ($returns_to_process->num_rows === 0) {
        log_action('Cron Execution', "{$log_prefix} No returns are due for restocking at this time.");
        $conn->commit(); // Commit to finish the transaction even if there's nothing to do.
        $stmt_find_returns->close();
        $conn->close();
        echo "No returns to process.\n";
        exit;
    }

    log_action('Cron Execution', "{$log_prefix} Found {$returns_to_process->num_rows} return(s) to process.");

    // Prepare statements for updating stock and marking returns as processed.
    // Preparing them outside the loop is more efficient.
    $stmt_get_item = $conn->prepare("SELECT producer_id, product_type, quantity, tray_size FROM product_order_items WHERE order_item_id = ?");
    $stmt_update_stock = $conn->prepare("UPDATE PRICE SET STOCK = STOCK + ? WHERE PRODUCER_ID = ? AND TYPE = ?");
    $stmt_mark_processed = $conn->prepare("UPDATE returns SET restock_processed = TRUE WHERE return_id = ?");

    foreach ($returns_to_process as $return) {
        $return_id = $return['return_id'];
        $order_item_id = $return['order_item_id'];

        try {
            if (!$order_item_id) {
                throw new Exception("Skipping return ID {$return_id}: missing order_item_id.");
            }

            // 1. Get the details of the item that was returned.
            $stmt_get_item->bind_param("i", $order_item_id);
            $stmt_get_item->execute();
            $item_result = $stmt_get_item->get_result();

            if ($item = $item_result->fetch_assoc()) {
                // 2. Calculate the exact number of eggs to return to stock.
                $eggs_to_return = (int)$item['quantity'] * (int)$item['tray_size'];

                // 3. Update the stock in the PRICE table.
                if ($eggs_to_return > 0) {
                    $stmt_update_stock->bind_param("iis", $eggs_to_return, $item['producer_id'], $item['product_type']);
                    if (!$stmt_update_stock->execute()) {
                         throw new Exception("Failed to update stock for producer {$item['producer_id']} / type {$item['product_type']}.");
                    }
                    log_action('Stock Update', "{$log_prefix} Restocked {$eggs_to_return} eggs for return ID #{$return_id}.");
                } else {
                    log_action('Stock Update', "{$log_prefix} No stock to replenish for return ID #{$return_id} (0 eggs).");
                }
            } else {
                 throw new Exception("Could not find order item #{$order_item_id} for return ID #{$return_id}.");
            }
            
            // 4. Mark the return as processed to prevent double-counting.
            $stmt_mark_processed->bind_param("i", $return_id);
            if (!$stmt_mark_processed->execute()) {
                throw new Exception("Failed to mark return ID #{$return_id} as processed.");
            }

            $processed_count++;

        } catch (Exception $e) {
            // Log the specific error for this item but don't stop the whole script.
            log_action('Cron Error', "{$log_prefix} CRITICAL: {$e->getMessage()}");
            $error_count++;
        }
    }
    
    // Close prepared statements
    $stmt_get_item->close();
    $stmt_update_stock->close();
    $stmt_mark_processed->close();
    $stmt_find_returns->close();

    // If we reached here without a fatal error, commit the changes.
    $conn->commit();
    log_action('Cron Execution', "{$log_prefix} Script finished. Processed: {$processed_count}. Errors: {$error_count}.");

} catch (Exception $e) {
    // If a major error occurred (e.g., DB connection, initial query), roll back everything.
    $conn->rollback();
    log_action('Cron Error', "{$log_prefix} FATAL ERROR: Transaction rolled back. Reason: {$e->getMessage()}");
    http_response_code(500); // Set an error code for monitoring tools.
}

if ($conn) {
    $conn->close();
}

echo "Cron job finished.\n";
echo "Successfully processed: {$processed_count}\n";
echo "Errors encountered: {$error_count}\n";
?>