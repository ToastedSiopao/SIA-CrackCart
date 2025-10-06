<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = $_GET['order_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="dashboard-styles.css?v=3.1" rel="stylesheet">
    <style>
        .star-rating {
            font-size: 2.5rem;
            cursor: pointer;
        }
        .star-rating > i {
            color: #ccc;
            transition: color 0.2s;
        }
        .star-rating > i:hover,
        .star-rating > i:hover ~ i {
            color: #ccc;
        }
        .star-rating > i.selected,
        .star-rating > i.hovered {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <?php include("navbar.php"); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include("sidebar.php"); ?>
            <?php include("offcanvas_sidebar.php"); ?>

            <main class="col ps-md-2 pt-2">
                <div class="container">
                    <div class="page-header">
                        <h1 class="text-center">Order Details</h1>
                    </div>
                    <div id="order-details-alert-container"></div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div id="order-details-container"><div class="text-center"><div class="spinner-border"></div></div></div>
                                    <div class="mt-3"><a href="my_orders.php" class="btn btn-secondary">Go Back</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <input type="hidden" id="reviewOrderItemId" name="order_item_id">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="star-rating" id="starRatingContainer">
                                <i class="bi bi-star" data-value="1"></i><i class="bi bi-star" data-value="2"></i><i class="bi bi-star" data-value="3"></i><i class="bi bi-star" data-value="4"></i><i class="bi bi-star" data-value="5"></i>
                            </div>
                            <input type="hidden" id="rating" name="rating" value="0">
                            <div class="invalid-feedback">Please select a rating.</div>
                        </div>
                        <div class="mb-3">
                            <label for="reviewText" class="form-label">Review</label>
                            <textarea id="reviewText" name="review_text" class="form-control" rows="4"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderDetailsContainer = document.getElementById('order-details-container');
        const alertContainer = document.getElementById('order-details-alert-container');
        const orderId = new URLSearchParams(window.location.search).get('order_id');
        const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
        const reviewForm = document.getElementById('reviewForm');
        const reviewOrderItemIdInput = document.getElementById('reviewOrderItemId');
        const starRatingContainer = document.getElementById('starRatingContainer');
        const stars = starRatingContainer.querySelectorAll('i');
        const ratingInput = document.getElementById('rating');

        const showAlert = (type, message) => {
            alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        };

        const fetchOrderDetails = async () => {
            if (!orderId) { showAlert('danger', 'Order ID is missing.'); return; }
            try {
                const response = await fetch(`api/order_details.php?order_id=${orderId}`);
                const result = await response.json();
                if (result.status === 'success') { renderOrderDetails(result.data); } 
                else { orderDetailsContainer.innerHTML = `<div class="alert alert-danger">${result.message || 'Could not load order details.'}</div>`; }
            } catch (error) { showAlert('danger', 'Could not connect to the server to get order details.'); }
        };

        const renderOrderDetails = (order) => {
            const itemsHtml = order.items.map(item => {
                let itemActions = '';
                const isDelivered = order.status.toLowerCase() === 'delivered';
                if (item.return_status) {
                    itemActions = `<span class="badge bg-secondary">Return ${item.return_status}</span>`;
                } else if (isDelivered && !item.is_reviewed) {
                    itemActions = `
                        <button class="btn btn-outline-primary btn-sm review-btn" data-order-item-id="${item.order_item_id}">Leave a Review</button>
                        <a href="request_return.php?order_item_id=${item.order_item_id}" class="btn btn-outline-secondary btn-sm">Request Return</a>
                    `;
                } else if (isDelivered && item.is_reviewed) {
                    itemActions = `<span class="badge bg-success">Reviewed</span>`;
                }
                return `<li class="list-group-item d-flex justify-content-between align-items-center"><div><p class="mb-0">${item.product_type}</p><small class="text-muted">₱${parseFloat(item.price_per_item).toFixed(2)} x ${item.quantity}</small></div><div class="d-flex gap-2">${itemActions}</div></li>`;
            }).join('');

            orderDetailsContainer.innerHTML = `<div><p><strong>Order ID:</strong> ${order.order_id}</p><p><strong>Order Date:</strong> ${new Date(order.order_date).toLocaleDateString()}</p><p><strong>Total Amount:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p><p><strong>Status:</strong> <span class="badge bg-${getStatusClass(order.status)}">${order.status}</span></p><h5 class="mt-4">Items</h5><ul class="list-group">${itemsHtml}</ul></div>`;
            
            document.querySelectorAll('.review-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    reviewOrderItemIdInput.value = e.target.dataset.orderItemId;
                    resetStars();
                    reviewModal.show();
                });
            });
        };
        
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (ratingInput.value === '0') { 
                ratingInput.classList.add('is-invalid');
                ratingInput.previousElementSibling.querySelector('.invalid-feedback').style.display = 'block';
                 showAlert('danger', 'Please select a star rating.');
                return;
            }
            ratingInput.classList.remove('is-invalid');

            const formData = new FormData(reviewForm);
            const submitButton = reviewForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            try {
                const response = await fetch('api/submit_review.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status === 'success') {
                    showAlert('success', result.message);
                    reviewModal.hide();
                    fetchOrderDetails();
                } else { showAlert('danger', result.message || 'Failed to submit review.'); }
            } catch (error) { showAlert('danger', 'Could not connect to the server.');
            } finally { submitButton.disabled = false; }
        });

        const resetStars = () => {
            stars.forEach(s => s.classList.remove('selected', 'hovered'));
            ratingInput.value = 0;
        }

        stars.forEach(star => {
            star.addEventListener('mouseover', () => {
                const value = star.dataset.value;
                stars.forEach(s => {
                    s.classList.toggle('hovered', s.dataset.value <= value);
                });
            });
            star.addEventListener('mouseout', () => stars.forEach(s => s.classList.remove('hovered')));
            star.addEventListener('click', () => {
                const value = star.dataset.value;
                ratingInput.value = value;
                stars.forEach(s => {
                    s.classList.toggle('selected', s.dataset.value <= value);
                });
            });
        });

        reviewModal._element.addEventListener('hidden.bs.modal', () => {
            reviewForm.reset();
            resetStars();
        });

        const getStatusClass = (status) => {
            const s = status.toLowerCase();
            if (s === 'delivered' || s === 'completed') return 'success';
            if (s === 'pending' || s === 'requested') return 'warning';
            if (s === 'shipped' || s === 'approved' || s === 'processing') return 'info';
            if (s === 'cancelled') return 'secondary';
            if (s === 'failed' || s === 'rejected') return 'danger';
            return 'light';
        };

        fetchOrderDetails();
    });
    </script>
</body>
</html>