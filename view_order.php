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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="dashboard-styles.css?v=2.9" rel="stylesheet">
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
                                    <div id="order-details-container">
                                        <div class="text-center"><div class="spinner-border"></div></div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="my_orders.php" class="btn btn-secondary">Go Back</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Leave a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <input type="hidden" id="reviewProductId" name="product_id">
                        <input type="hidden" id="reviewOrderId" name="order_id">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <div id="star-rating">
                                <i class="far fa-star" data-value="1"></i>
                                <i class="far fa-star" data-value="2"></i>
                                <i class="far fa-star" data-value="3"></i>
                                <i class="far fa-star" data-value="4"></i>
                                <i class="far fa-star" data-value="5"></i>
                            </div>
                            <input type="hidden" id="rating" name="rating" required>
                        </div>
                        <div class="mb-3">
                            <label for="reviewText" class="form-label">Review</label>
                            <textarea class="form-control" id="reviewText" name="review_text" rows="3"></textarea>
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
        const starRatingContainer = document.getElementById('star-rating');

        const showAlert = (type, message) => {
            alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        };

        const fetchOrderDetails = async () => {
            if (!orderId) {
                showAlert('danger', 'Order ID is missing.');
                return;
            }

            try {
                const response = await fetch(`api/order_details.php?order_id=${orderId}`);
                const result = await response.json();

                if (result.status === 'success') {
                    renderOrderDetails(result.data);
                } else {
                    orderDetailsContainer.innerHTML = `<div class="alert alert-danger">${result.message || 'Could not load order details.'}</div>`;
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server to get order details.');
            }
        };

        const renderOrderDetails = (order) => {
            const isCompleted = order.status.toLowerCase() === 'delivered' || order.status.toLowerCase() === 'completed';
            
            const itemsHtml = order.items.map(item => `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0">${item.product_name}</p>
                        <small class="text-muted">₱${parseFloat(item.price).toFixed(2)} x ${item.quantity}</small>
                    </div>
                    ${isCompleted ? `
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm review-btn" data-product-id="${item.product_id}" data-order-id="${order.order_id}">Leave a Review</button>
                        <a href="request_return.php?order_id=${order.order_id}" class="btn btn-outline-secondary btn-sm">Request Return</a>
                    </div>
                    ` : ''}
                </li>
            `).join('');

            const orderDetailsHtml = `
                <div>
                    <p><strong>Order ID:</strong> ${order.order_id}</p>
                    <p><strong>Order Date:</strong> ${new Date(order.order_date).toLocaleDateString()}</p>
                    <p><strong>Total Amount:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p>
                    <p><strong>Status:</strong> <span class="badge bg-${getStatusClass(order.status)}">${order.status}</span></p>
                    
                    <h5 class="mt-4">Items</h5>
                    <ul class="list-group">
                        ${itemsHtml}
                    </ul>
                </div>`;
            
            orderDetailsContainer.innerHTML = orderDetailsHtml;
        };

        const getStatusClass = (status) => {
            switch (status.toLowerCase()) {
                case 'pending': return 'warning';
                case 'paid':
                case 'processing': return 'info';
                case 'shipped': return 'primary';
                case 'delivered': return 'success';
                case 'completed': return 'success';
                case 'cancelled': return 'secondary';
                case 'failed': return 'danger';
                default: return 'light';
            }
        };

        // Star Rating Logic
        starRatingContainer.addEventListener('mouseover', event => {
            if (event.target.matches('.fa-star')) {
                const stars = [...starRatingContainer.children];
                const rating = event.target.dataset.value;
                stars.forEach((star, index) => {
                    star.className = index < rating ? 'fas fa-star' : 'far fa-star';
                });
            }
        });

        starRatingContainer.addEventListener('click', event => {
            if (event.target.matches('.fa-star')) {
                const rating = event.target.dataset.value;
                document.getElementById('rating').value = rating;
            }
        });

        // Modal Trigger Logic
        orderDetailsContainer.addEventListener('click', event => {
            if (event.target.classList.contains('review-btn')) {
                const productId = event.target.dataset.productId;
                const orderId = event.target.dataset.orderId;
                reviewForm.reset();
                document.getElementById('reviewProductId').value = productId;
                document.getElementById('reviewOrderId').value = orderId;
                document.getElementById('rating').value = '';
                const stars = [...starRatingContainer.children];
                stars.forEach(star => star.className = 'far fa-star');
                reviewModal.show();
            }
        });

        // Review Submission Logic
        reviewForm.addEventListener('submit', async event => {
            event.preventDefault();
            const formData = new FormData(reviewForm);
            const submitButton = reviewForm.querySelector('button[type="submit"]');

            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';

            try {
                const response = await fetch('api/submit_review.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showAlert('success', result.message);
                    reviewModal.hide();
                    fetchOrderDetails(); // Refresh to potentially hide the button
                } else {
                    showAlert('danger', result.message || 'An error occurred.');
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server.');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Submit Review';
            }
        });

        fetchOrderDetails();
    });
    </script>
</body>
</html>
