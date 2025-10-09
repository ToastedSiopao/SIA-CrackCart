<?php
session_start();
// Security & permission check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include '../db_connect.php';

$return_id = isset($_GET['return_id']) ? intval($_GET['return_id']) : 0;
$return_statuses = ['pending', 'approved', 'rejected', 'processing', 'completed'];
$error_message = null;
$return_details = null;

if ($return_id <= 0) {
    $error_message = "Invalid Return ID specified.";
} else {
    // Fetch data for displaying the page
    $query = "SELECT 
                r.return_id, r.order_id, r.reason, r.status, r.image_path, 
                r.approved_at, r.restock_processed, r.requested_at, 
                poi.product_type, poi.quantity, poi.price_per_item, poi.tray_size, 
                po.user_id AS customer_id, 
                CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, u.EMAIL AS customer_email
              FROM returns AS r 
              LEFT JOIN product_order_items AS poi ON r.order_item_id = poi.order_item_id
              LEFT JOIN product_orders AS po ON r.order_id = po.order_id 
              LEFT JOIN USER AS u ON po.user_id = u.USER_ID 
              WHERE r.return_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $return_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $return_details = $result->fetch_assoc();
            if (!$return_details) {
                $error_message = "Return request not found.";
            }
        } else {
            $error_message = "Query execution failed: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $error_message = "Query preparation failed: " . htmlspecialchars($conn->error);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="admin-styles.css?v=2.5" rel="stylesheet">
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
                        <a href="manage_returns.php" class="btn btn-outline-secondary">&larr; All Returns</a>
                    </div>

                    <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
                        <div class="alert alert-success alert-dismissible fade show">Status updated successfully. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($return_details): ?>
                        <?php 
                            $return_value = (float)$return_details['price_per_item'] * (int)$return_details['quantity'];
                        ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <span>Return #<?php echo htmlspecialchars($return_details['return_id']); ?></span>
                                        <span class="badge bg-<?php echo strtolower($return_details['status']) == 'approved' ? 'success' : (strtolower($return_details['status']) == 'rejected' ? 'danger' : 'warning text-dark'); ?>"><?php echo htmlspecialchars(ucfirst($return_details['status'])); ?></span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">Item Details</h5>
                                        <p><strong>Product:</strong> <?php echo htmlspecialchars($return_details['product_type']); ?></p>
                                        <p><strong>Quantity Returned:</strong> <?php echo htmlspecialchars($return_details['quantity']); ?> tray(s) of <?php echo htmlspecialchars($return_details['tray_size']); ?></p>
                                        <p><strong>Calculated Return Value:</strong> ₱<?php echo number_format($return_value, 2); ?></p>
                                        <hr>
                                        <h5 class="card-title mt-3">Return Information</h5>
                                        <p><strong>Reason for Return:</strong> <?php echo nl2br(htmlspecialchars($return_details['reason'])); ?></p>
                                        <p><strong>Date Requested:</strong> <?php echo date("M d, Y, h:i A", strtotime($return_details['requested_at'])); ?></p>
                                        <?php if (!empty($return_details['image_path'])): ?>
                                            <p><strong>Attachment:</strong></p>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-image="../<?php echo htmlspecialchars($return_details['image_path']); ?>">
                                                <img src="../<?php echo htmlspecialchars($return_details['image_path']); ?>" alt="Return Attachment" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card position-sticky" style="top: 20px;">
                                    <div class="card-header">Customer Information</div>
                                    <div class="card-body">
                                        <p><strong>Name:</strong> <a href="manage_users.php?user_id=<?php echo $return_details['customer_id']; ?>"><?php echo htmlspecialchars($return_details['customer_name']); ?></a></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($return_details['customer_email']); ?></p>
                                        <p><strong>Order:</strong> <a href="order_details.php?order_id=<?php echo $return_details['order_id']; ?>">View Order #<?php echo htmlspecialchars($return_details['order_id']); ?></a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Return Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid" alt="Return Image">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const imageModal = document.getElementById('imageModal');
    if (imageModal) {
        imageModal.addEventListener('show.bs.modal', event => {
            const triggerElement = event.relatedTarget;
            const imageSrc = triggerElement.getAttribute('data-bs-image');
            document.getElementById('modalImage').src = imageSrc;
        });
    }
    </script>
</body>
</html>