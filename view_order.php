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
    <link href="dashboard-styles.css?v=3.1" rel="stylesheet">
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

    <!-- Review Modal (keep this as it is) -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderDetailsContainer = document.getElementById('order-details-container');
        const alertContainer = document.getElementById('order-details-alert-container');
        const orderId = new URLSearchParams(window.location.search).get('order_id');

        const showAlert = (type, message) => {
            alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
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
            const itemsHtml = order.items.map(item => {
                let itemActions = '';
                const isDelivered = order.status.toLowerCase() === 'delivered';

                if (item.return_status) {
                    itemActions = `<span class="badge bg-secondary">Return ${item.return_status}</span>`;
                } else if (isDelivered) {
                    itemActions = `<a href="request_return.php?order_item_id=${item.order_item_id}" class="btn btn-outline-secondary btn-sm">Request Return</a>`;
                }

                return `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0">${item.product_type}</p>
                            <small class="text-muted">₱${parseFloat(item.price_per_item).toFixed(2)} x ${item.quantity}</small>
                        </div>
                        ${itemActions}
                    </li>`;
            }).join('');

            const orderDetailsHtml = `
                <div>
                    <p><strong>Order ID:</strong> ${order.order_id}</p>
                    <p><strong>Order Date:</strong> ${new Date(order.order_date).toLocaleDateString()}</p>
                    <p><strong>Total Amount:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p>
                    <p><strong>Status:</strong> <span class="badge bg-${getStatusClass(order.status)}">${order.status}</span></p>
                    <h5 class="mt-4">Items</h5>
                    <ul class="list-group">${itemsHtml}</ul>
                </div>`;
            
            orderDetailsContainer.innerHTML = orderDetailsHtml;
        };

        const getStatusClass = (status) => {
            switch (status.toLowerCase()) {
                case 'pending': return 'warning';
                case 'processing': return 'info';
                case 'shipped': return 'primary';
                case 'delivered': return 'success';
                case 'cancelled': return 'secondary';
                case 'failed': return 'danger';
                // Return Statuses
                case 'requested': return 'warning';
                case 'approved': return 'info';
                case 'rejected': return 'danger';
                case 'completed': return 'success';
                default: return 'light';
            }
        };

        fetchOrderDetails();
    });
    </script>
</body>
</html>