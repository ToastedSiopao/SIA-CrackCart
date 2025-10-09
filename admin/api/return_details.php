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
$return_statuses = ['Requested', 'Approved', 'Rejected', 'Processing', 'Completed'];

if ($return_id <= 0) {
    $error_message = "Invalid Return ID specified.";
} else {
    // Handle form submissions for status updates or refund processing
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $admin_id = $_SESSION['user_id'];

        // Logic for the main status update form
        if (isset($_POST['update_status'])) {
            $new_status = $_POST['return_status'];
            if (in_array($new_status, $return_statuses)) {
                $stmt = $conn->prepare("UPDATE returns SET status = ? WHERE return_id = ?");
                $stmt->bind_param("si", $new_status, $return_id);
                if ($stmt->execute()) {
                    log_action('Return Status Update', "Admin ID: {$admin_id} changed return #{$return_id} to {$new_status}");
                }
                $stmt->close();
                header("Location: return_details.php?return_id=" . $return_id); // Redirect to prevent resubmission
                exit;
            }
        }

        // Logic for the 'Process Refund' button
        if (isset($_POST['process_refund'])) {
            $stmt_verify = $conn->prepare("SELECT status FROM returns WHERE return_id = ?");
            $stmt_verify->bind_param("i", $return_id);
            $stmt_verify->execute();
            $current_status = $stmt_verify->get_result()->fetch_assoc()['status'];
            $stmt_verify->close();

            if ($current_status === 'Approved') {
                $stmt = $conn->prepare("UPDATE returns SET status = 'Completed' WHERE return_id = ?");
                $stmt->bind_param("i", $return_id);
                if ($stmt->execute()) {
                    log_action('Refund Processed', "Admin ID: {$admin_id} marked return #{$return_id} as 'Completed' and processed refund.");
                }
                $stmt->close();
                header("Location: return_details.php?return_id=" . $return_id); // Redirect
                exit;
            }
        }
    }

    // CORRECTED QUERY: Using correct uppercase table 'USER' and column names 'USER_ID', 'FIRST_NAME', 'LAST_NAME', 'EMAIL'
    $query = "SELECT r.*, poi.product_type, poi.quantity, poi.price_per_item, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, u.EMAIL as customer_email FROM returns r JOIN product_order_items poi ON r.order_item_id = poi.order_item_id JOIN product_orders po ON r.order_id = po.order_id JOIN `USER` u ON po.user_id = u.USER_ID WHERE r.return_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $return_id);
    $stmt->execute();
    $return_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$return_details) {
        $error_message = "Return request not found.";
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

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($return_details): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card mb-4">
                                    <div class="card-header">Return #<?php echo htmlspecialchars($return_details['return_id']); ?></div>
                                    <div class="card-body">
                                        <h5>Item to be Returned</h5>
                                        <div class="alert alert-light">
                                            <p><strong>Product:</strong> <?php echo htmlspecialchars($return_details['product_type']); ?></p>
                                            <p><strong>Quantity:</strong> <?php echo htmlspecialchars($return_details['quantity']); ?></p>
                                            <p><strong>Price:</strong> ₱<?php echo number_format($return_details['price_per_item'], 2); ?></p>
                                        </div>
                                        <hr>
                                        <h5>Customer Information</h5>
                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($return_details['customer_name']); ?> (<?php echo htmlspecialchars($return_details['customer_email']); ?>)</p>
                                        <p><strong>Order ID:</strong> <a href="order_details.php?order_id=<?php echo $return_details['order_id']; ?>"><?php echo htmlspecialchars($return_details['order_id']); ?></a></p>
                                        <hr>
                                        <h5>Return Details</h5>
                                        <p><strong>Reason:</strong> <?php echo htmlspecialchars(ucfirst($return_details['reason'])); ?></p>
                                        <p><strong>Date Requested:</strong> <?php echo date("M d, Y, h:i A", strtotime($return_details['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card position-sticky" style="top: 20px;">
                                    <div class="card-header">Return Actions</div>
                                    <div class="card-body">
                                        <p>Current Status: <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($return_details['status']); ?></span></p>
                                        <hr>
                                        <?php if ($return_details['status'] === 'Approved'): ?>
                                            <div class="d-grid gap-2">
                                                <p>This return is approved and ready for refund processing.</p>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to mark this return as completed and refunded?');">
                                                    <button class="btn btn-success w-100" type="submit" name="process_refund">Process Refund & Complete</button>
                                                </form>
                                            </div>
                                        <?php elseif ($return_details['status'] !== 'Completed' && $return_details['status'] !== 'Rejected'): ?>
                                            <form method="POST">
                                                <label for="return_status" class="form-label"><strong>Update Status</strong></label>
                                                <div class="input-group">
                                                    <select class="form-select" id="return_status" name="return_status">
                                                        <?php foreach ($return_statuses as $status): ?>
                                                            <option value="<?php echo $status; ?>" <?php echo ($return_details['status'] == $status) ? 'selected' : ''; ?>><?php echo $status; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button class="btn btn-primary" type="submit" name="update_status">Update</button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <p>This is a closed return and no further actions are needed.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>