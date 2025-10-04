<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="dashboard-styles.css?v=2.7" rel="stylesheet">
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
                        <h1 class="text-center">My Orders</h1>
                    </div>
                    <div id="orders-alert-container"></div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div id="orders-container" class="table-responsive">
                                        <div class="text-center"><div class="spinner-border"></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ordersContainer = document.getElementById('orders-container');
        const alertContainer = document.getElementById('orders-alert-container');

        const showAlert = (type, message) => {
            alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        };

        const fetchOrders = async () => {
            try {
                const response = await fetch('api/get_orders.php');
                const result = await response.json();

                if (result.status === 'success') {
                    renderOrders(result.data);
                } else {
                    ordersContainer.innerHTML = `<div class="alert alert-danger">${result.message || 'Could not load your orders.'}</div>`;
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server to get your orders.');
            }
        };

        const renderOrders = (orders) => {
            if (orders.length === 0) {
                ordersContainer.innerHTML = '<p class="text-center">You have not placed any orders yet.</p>';
                return;
            }

            const tableHtml = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orders.map(order => `
                            <tr>
                                <td>${order.order_id}</td>
                                <td>${new Date(order.order_date).toLocaleDateString()}</td>
                                <td>₱${parseFloat(order.total_amount).toFixed(2)}</td>
                                <td><span class="badge bg-${getStatusClass(order.status)}">${order.status}</span></td>
                                <td>
                                    ${order.status.toLowerCase() === 'processing' || order.status.toLowerCase() === 'paid' ?
                                    `<button class="btn btn-sm btn-danger cancel-btn" data-order-id="${order.order_id}">Cancel</button>` :
                                    ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>`;
            
            ordersContainer.innerHTML = tableHtml;
        };

        const getStatusClass = (status) => {
            switch (status.toLowerCase()) {
                case 'paid':
                case 'processing':
                    return 'info';
                case 'shipped':
                    return 'primary';
                case 'delivered':
                    return 'success';
                case 'cancelled':
                    return 'secondary';
                case 'failed':
                    return 'danger';
                default:
                    return 'light';
            }
        };

        ordersContainer.addEventListener('click', async (event) => {
            if (event.target.classList.contains('cancel-btn')) {
                const orderId = event.target.dataset.orderId;
                if (confirm('Are you sure you want to cancel this order?')) {
                    await cancelOrder(orderId);
                }
            }
        });

        const cancelOrder = async (orderId) => {
            try {
                const response = await fetch('api/cancel_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showAlert('success', 'Your order has been cancelled.');
                    fetchOrders();
                } else {
                    showAlert('danger', result.message || 'There was an issue cancelling your order.');
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server to cancel the order.');
            }
        };

        fetchOrders();
    });
    </script>
</body>
</html>
