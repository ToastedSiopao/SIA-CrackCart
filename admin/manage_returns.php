<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

// Correctly fetch product details including the item's price and quantity
$query = "SELECT 
            r.return_id, 
            r.order_id, 
            r.reason, 
            r.status, 
            r.requested_at, 
            r.image_path, 
            CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as customer_name, 
            poi.product_type,
            poi.quantity as item_quantity,
            poi.price_per_item as item_price,
            poi.tray_size
          FROM returns r
          JOIN product_orders po ON r.order_id = po.order_id
          JOIN USER u ON po.user_id = u.USER_ID
          JOIN product_order_items poi ON r.order_item_id = poi.order_item_id
          ORDER BY r.requested_at DESC";

$result = $conn->query($query);
$returns = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $returns[] = $row;
    }
} else {
    // Handle potential query errors
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
    <link href="admin-styles.css?v=1.8" rel="stylesheet"> 
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

                <div id="alert-container-main"></div>

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
                            <?php 
                                $return_value = (float)$return['item_price'] * (int)$return['item_quantity'];
                            ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                        <h6 class="mb-0">Return ID: <?php echo htmlspecialchars($return['return_id']); ?></h6>
                                        <?php
                                            $status = strtolower($return['status']);
                                            $status_badge_class = 'bg-secondary';
                                            switch ($status) {
                                                case 'pending': $status_badge_class = 'bg-warning text-dark'; break;
                                                case 'approved': $status_badge_class = 'bg-success'; break;
                                                case 'rejected': $status_badge_class = 'bg-danger'; break;
                                                case 'refunded': $status_badge_class = 'bg-primary'; break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $status_badge_class; ?>"><?php echo ucfirst(htmlspecialchars($return['status'])); ?></span>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="card-text mb-1">
                                                    <strong>Customer:</strong> <?php echo htmlspecialchars($return['customer_name']); ?><br>
                                                    <strong>Order ID:</strong> <a href="order_details.php?order_id=<?php echo $return['order_id']; ?>"><?php echo htmlspecialchars($return['order_id']); ?></a><br>
                                                    <strong>Product:</strong> <?php echo htmlspecialchars($return['product_type']); ?> (<?php echo htmlspecialchars($return['item_quantity']); ?>x tray of <?php echo htmlspecialchars($return['tray_size']); ?>)
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <h5 class="mb-0">₱<?php echo number_format($return_value, 2); ?></h5>
                                                <small class="text-muted">Return Value</small>
                                            </div>
                                        </div>
                                         <p class="card-text mt-2"><small class="text-muted">Requested: <?php echo date("M j, Y, g:i a", strtotime($return['requested_at'])); ?></small></p>
                                        <hr>
                                        <div class="mb-auto">
                                            <p class="card-text"><strong>Reason:</strong><br><?php echo nl2br(htmlspecialchars($return['reason'])); ?></p>
                                            <?php if (!empty($return['image_path'])): ?>
                                                <div class="mt-2">
                                                    <strong>Attachment:</strong><br>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-image="../<?php echo htmlspecialchars($return['image_path']); ?>">
                                                        <img src="../<?php echo htmlspecialchars($return['image_path']); ?>" alt="Return Attachment" class="img-fluid rounded" style="max-height: 100px; cursor: pointer;">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($status === 'pending'): ?>
                                    <div class="card-footer bg-white text-center">
                                        <button class="btn btn-success btn-sm me-2" onclick="openConfirmationModal(<?php echo $return['return_id']; ?>, 'approved')">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="openConfirmationModal(<?php echo $return['return_id']; ?>, 'rejected')">
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

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage"></p>
                    <div class="form-check" id="restock-option-div">
                        <input class="form-check-input" type="checkbox" id="restock-checkbox" checked>
                        <label class="form-check-label" for="restock-checkbox">
                            Restock item(s) (return to inventory)
                        </label>
                         <small class="text-muted d-block">Uncheck this if the item is damaged and should not be re-added to stock.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-action-btn">Confirm</button>
                </div>
            </div>
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
    let confirmationModal, confirmActionBtn;
    let currentReturnId, currentNewStatus;

    document.addEventListener('DOMContentLoaded', function() {
        confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmActionBtn = document.getElementById('confirm-action-btn');
        confirmActionBtn.addEventListener('click', handleConfirmation);

        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', event => {
                const triggerElement = event.relatedTarget;
                const imageSrc = triggerElement.getAttribute('data-bs-image');
                document.getElementById('modalImage').src = imageSrc;
            });
        }
    });

    function openConfirmationModal(returnId, newStatus) {
        currentReturnId = returnId;
        currentNewStatus = newStatus;

        const msg = document.getElementById('confirmationMessage');
        const restockDiv = document.getElementById('restock-option-div');
        
        msg.textContent = `Are you sure you want to ${newStatus} this return request?`;

        if (newStatus === 'approved') {
            restockDiv.style.display = 'block';
        } else {
            restockDiv.style.display = 'none';
        }

        confirmationModal.show();
    }

    function handleConfirmation() {
        const shouldRestock = document.getElementById('restock-checkbox').checked;
        updateReturnStatus(currentReturnId, currentNewStatus, shouldRestock);
    }

    function updateReturnStatus(returnId, newStatus, restock) {
        confirmationModal.hide();

        let payload = { return_id: returnId, status: newStatus };
        if (newStatus === 'approved') {
            payload.restock = restock;
        }

        fetch('api/update_return_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Server error'); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert(data.message || 'Status updated successfully!', 'success', () => location.reload());
            } else {
                showAlert(data.message || 'Failed to update status.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert(`An error occurred: ${error.message}`, 'danger');
        });
    }

    function showAlert(message, type, onClosedCallback) {
        const alertContainer = document.getElementById('alert-container-main');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
        
        alertContainer.appendChild(alertDiv);
        const bsAlert = new bootstrap.Alert(alertDiv);

        if (onClosedCallback) {
            alertDiv.addEventListener('closed.bs.alert', onClosedCallback, { once: true });
        } else {
            setTimeout(() => bsAlert.close(), 5000);
        }
    }
    </script>
</body>
</html>