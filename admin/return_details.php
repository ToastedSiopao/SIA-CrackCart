<?php
session_start();
// Security & permission check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include '../db_connect.php';
include '../log_function.php';

$return_id = isset($_GET['return_id']) ? intval($_GET['return_id']) : 0;
$return_statuses = ['requested', 'approved', 'rejected', 'processing', 'completed'];
$error_message = null;
$return_details = null;

if ($return_id <= 0) {
    $error_message = "Invalid Return ID specified.";
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ... (rest of the POST handling logic is unchanged) ...
    }

    // FINAL CORRECTED QUERY: Based on the provided db.sql schema.
    // - `returns.product_id` joins with `product_order_items.order_item_id`.
    // - `product_orders.user_id` (lowercase) is the correct foreign key.
    // - `returns.requested_at` is the correct date column.
    $query = "SELECT r.*, poi.product_type, poi.quantity, poi.price_per_item, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, u.EMAIL as customer_email FROM returns r JOIN product_order_items poi ON r.product_id = poi.order_item_id JOIN product_orders po ON r.order_id = po.order_id JOIN `USER` u ON po.user_id = u.USER_ID WHERE r.return_id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        $error_message = "Database query preparation failed: " . htmlspecialchars($conn->error);
    } else {
        $stmt->bind_param("i", $return_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $return_details = $result->fetch_assoc();
            if (!$return_details) {
                $error_message = "Return request not found.";
            }
        } else {
            $error_message = "Database query execution failed: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

$user_name = $_SESSION['user_first_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Details - CrackCart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin-styles.css?v=1.2" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="card shadow-sm border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Return Request Details</h4>
                        <a href="returns.php" class="btn btn-outline-secondary">&larr; All Returns</a>
                    </div>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($return_details): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card mb-4">
                                    <div class="card-header">Return #<?php echo htmlspecialchars($return_details['return_id']); ?></div>
                                    <div class="card-body">
                                        <!-- ... (rest of the card body is unchanged) ... -->
                                        <hr>
                                        <h5>Return Details</h5>
                                        <p><strong>Reason:</strong> <?php echo htmlspecialchars(ucfirst($return_details['reason'])); ?></p>
                                        <!-- CORRECTED `created_at` to `requested_at` -->
                                        <p><strong>Date Requested:</strong> <?php echo date("M d, Y, h:i A", strtotime($return_details['requested_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <!-- ... (rest of the file is unchanged) ... -->
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>