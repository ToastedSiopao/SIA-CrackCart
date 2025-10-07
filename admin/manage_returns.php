<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

$query = "SELECT 
            r.return_id, 
            r.order_id, 
            po.total_amount, 
            r.reason, 
            r.status, 
            r.requested_at, 
            r.image_path, -- Fetch the image path
            CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as customer_name, 
            poi.product_type
          FROM returns r
          JOIN product_orders po ON r.order_id = po.order_id
          JOIN USER u ON po.user_id = u.USER_ID
          JOIN product_order_items poi ON r.order_item_id = poi.order_item_id
          ORDER BY r.requested_at DESC";

$result = $conn->query($query);
$returns = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $returns[] = $row;
    }
}

$conn->close();
$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Returns - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="admin-styles.css?v=1.6" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0 h2">Manage Returns</h1>
                </div>

                <div class="alert alert-info" role="alert">
                    <h5 class="alert-heading">How to Process Refunds</h5>
                    <p>Approving a return request updates the order status to "Refunded" in the system. However, you must manually process the monetary refund based on the customer's original payment method.</p>
                    <hr>
                    <ul>
                        <li><strong>PayPal Orders:</strong> Log in to your PayPal Business account, find the original transaction, and use the "Issue a refund" option.</li>
                        <li><strong>Cash on Delivery (COD) Orders:</strong> No monetary action is needed. Approving the return simply updates your records.</li>
                    </ul>
                </div>

                <div class="row">
                    <?php if (empty($returns)): ?>
                        <div class="col-12">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">No Return Requests</h5>
                                    <p class="card-text">There are currently no pending or processed return requests.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($returns as $return): ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                        <h6 class="mb-0">Return ID: <?php echo htmlspecialchars($return['return_id']); ?></h6>
                                        <?php
                                            $status = strtolower($return['status']);
                                            $status_badge_class = 'bg-secondary';
                                            switch ($status) {
                                                case 'pending':
                                                case 'requested':
                                                    $status_badge_class = 'bg-warning text-dark';
                                                    break;
                                                case 'approved':
                                                    $status_badge_class = 'bg-success';
                                                    break;
                                                case 'rejected':
                                                    $status_badge_class = 'bg-danger';
                                                    break;
                                                case 'completed':
                                                    $status_badge_class = 'bg-primary';
                                                    break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $status_badge_class; ?>"><?php echo ucfirst(htmlspecialchars($return['status'])); ?></span>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <p class="card-text">
                                            <strong>Customer:</strong> <?php echo htmlspecialchars($return['customer_name']); ?><br>
                                            <strong>Order ID:</strong> <a href="order_details.php?order_id=<?php echo $return['order_id']; ?>"><?php echo htmlspecialchars($return['order_id']); ?></a><br>
                                            <strong>Order Total:</strong> $<?php echo number_format($return['total_amount'], 2); ?><br>
                                            <strong>Product:</strong> <?php echo htmlspecialchars($return['product_type']); ?><br>
                                            <strong>Requested:</strong> <?php echo date("M j, Y, g:i a", strtotime($return['requested_at'])); ?>
                                        </p>
                                        <hr>
                                        <div class="mb-auto">
                                            <p class="card-text">
                                                <strong>Reason:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($return['reason'])); ?>
                                            </p>
                                            <?php if (!empty($return['image_path'])): ?>
                                                <div class="mt-2">
                                                    <strong>Attachment:</strong><br>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-image="../<?php echo htmlspecialchars($return['image_path']); ?>">
                                                        <img src="../<?php echo htmlspecialchars($return['image_path']); ?>" alt="Damaged Item" class="img-fluid rounded" style="max-height: 100px; cursor: pointer;">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($status === 'pending' || $status === 'requested'): ?>
                                    <div class="card-footer bg-white text-center">
                                        <button class="btn btn-success btn-sm me-2" onclick="updateReturnStatus(<?php echo $return['return_id']; ?>, 'approved')">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updateReturnStatus(<?php echo $return['return_id']; ?>, 'rejected')">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="imageModalLabel">Return Image</h5>
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
    function updateReturnStatus(returnId, newStatus) {
        if (!confirm(`Are you sure you want to ${newStatus} this return request?`)) {
            return;
        }

        fetch('api/update_return_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ return_id: returnId, status: newStatus })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'HTTP error ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        });
    }

    const imageModal = document.getElementById('imageModal');
    if (imageModal) {
        imageModal.addEventListener('show.bs.modal', event => {
            const triggerElement = event.relatedTarget;
            const imageSrc = triggerElement.getAttribute('data-bs-image');
            const modalImage = imageModal.querySelector('#modalImage');
            modalImage.src = imageSrc;
        });
    }
    </script>
</body>
</html>
