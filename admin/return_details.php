<?php
session_start();
// Security & permission check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include '../db_connect.php';
include '../log_function.php';
include '../notification_function.php'; // Include the notification function

$return_id = isset($_GET['return_id']) ? intval($_GET['return_id']) : 0;
$return_statuses = ['pending', 'approved', 'rejected', 'processing', 'completed'];
$error_message = null;
$return_details = null;

if ($return_id <= 0) {
    $error_message = "Invalid Return ID specified.";
} else {
    // Handle status update POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $admin_id = $_SESSION['user_id'];
        $new_status = $_POST['return_status'];

        if (in_array($new_status, $return_statuses)) {
            // First, get the user_id and order_id for the notification
            $stmt_user = $conn->prepare("SELECT user_id, order_id FROM returns WHERE return_id = ?");
            $stmt_user->bind_param("i", $return_id);
            $user_id_for_notification = null;
            $order_id_for_notification = null;
            if ($stmt_user->execute()) {
                $result = $stmt_user->get_result();
                if ($row = $result->fetch_assoc()) {
                    $user_id_for_notification = $row['user_id'];
                    $order_id_for_notification = $row['order_id'];
                }
            }
            $stmt_user->close();

            // Now, update the return status
            $stmt_update = $conn->prepare("UPDATE returns SET status = ? WHERE return_id = ?");
            $stmt_update->bind_param("si", $new_status, $return_id);

            if ($stmt_update->execute()) {
                log_action('Return Status Update', "Admin ID: {$admin_id} changed return #{$return_id} to {$new_status}");

                // Send notification if we found the user
                if ($user_id_for_notification && $order_id_for_notification) {
                    $message = "Your return request for order #{$order_id_for_notification} has been updated to '{$new_status}'.";
                    create_notification($conn, $user_id_for_notification, $message);
                }
            }
            $stmt_update->close();

            header("Location: return_details.php?return_id=" . $return_id);
            exit;
        }
    }

    // Query to fetch return details for display
    $query = "
        SELECT
            r.return_id,
            r.order_id,
            r.reason,
            r.status,
            r.requested_at,
            poi.product_type,
            poi.quantity,
            r.user_id AS customer_id, // Use user_id from returns table
            CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name,
            u.EMAIL AS customer_email
        FROM
            returns AS r
        LEFT JOIN
            USER AS u ON r.user_id = u.USER_ID
        LEFT JOIN
            product_order_items AS poi ON r.product_id = poi.order_item_id
        WHERE
            r.return_id = ?
    ";
    
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
    <link href="admin-styles.css?v=2.2" rel="stylesheet"> 
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
                                        <h5>Customer Information</h5>
                                        <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($return_details['customer_id'] ?? 'N/A'); ?></p>
                                        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($return_details['customer_name'] ?? 'Unknown User'); ?> (<?php echo htmlspecialchars($return_details['customer_email'] ?? 'N/A'); ?>)</p>
                                        <p><strong>Order ID:</strong> <a href="order_details.php?order_id=<?php echo $return_details['order_id']; ?>"><?php echo htmlspecialchars($return_details['order_id']); ?></a></p>
                                        <hr>
                                        <h5>Return Details</h5>
                                        <p><strong>Product:</strong> <?php echo htmlspecialchars($return_details['product_type'] ?? 'N/A'); ?></p>
                                        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($return_details['quantity'] ?? 'N/A'); ?></p>
                                        <p><strong>Reason:</strong> <?php echo htmlspecialchars(ucfirst($return_details['reason'])); ?></p>
                                        <p><strong>Date Requested:</strong> <?php echo date("M d, Y, h:i A", strtotime($return_details['requested_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card position-sticky" style="top: 20px;">
                                    <div class="card-header">Return Actions</div>
                                    <div class="card-body">
                                        <p>Current Status: <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($return_details['status']); ?></span></p>
                                        <hr>
                                        <form method="POST">
                                            <label for="return_status" class="form-label"><strong>Update Status</strong></label>
                                            <div class="input-group">
                                                <select class="form-select" id="return_status" name="return_status">
                                                    <?php foreach ($return_statuses as $status): ?>
                                                        <option value="<?php echo $status; ?>" <?php echo ($return_details['status'] == $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-primary" type="submit" name="update_status">Update</button>
                                            </div>
                                        </form>
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