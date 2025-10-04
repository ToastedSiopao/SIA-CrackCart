<?php
require_once 'session_handler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="dashboard-styles.css?v=2.8" rel="stylesheet">
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
                                    <ul class="nav nav-tabs" id="orderTabs">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="#" data-status-filter="all">All</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-status-filter="topay">To Pay</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-status-filter="toship">To Ship</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-status-filter="toreceive">To Receive</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-status-filter="completed">Completed</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-status-filter="cancelled">Cancelled</a>
                                        </li>
                                    </ul>
                                    <div id="orders-container" class="table-responsive mt-3">
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
        const orderTabs = document.getElementById('orderTabs');
        let allOrders = [];

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
                    allOrders = result.data;
                    renderOrders('all');
                } else {
                    ordersContainer.innerHTML = `<div class="alert alert-danger">${result.message || 'Could not load your orders.'}</div>`;
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server to get your orders.');
            }
        };

        const renderOrders = (filter) => {
            let filteredOrders = allOrders;

            if (filter !== 'all') {
                filteredOrders = allOrders.filter(order => {
                    const status = order.status.toLowerCase();
                    if (filter === 'topay') {
                        return status === 'pending';
                    }
                    if (filter === 'toship') {
                        return status === 'processing' || status === 'paid';
                    }
                    if (filter === 'toreceive') {
                        return status === 'shipped';
                    }
                    if (filter === 'completed') {
                        return status === 'delivered';
                    }
                    if (filter === 'cancelled') {
                        return status === 'cancelled' || status === 'failed';
                    }
                    return false;
                });
            }

            if (filteredOrders.length === 0) {
                ordersContainer.innerHTML = '<p class="text-center">No orders found in this category.</p>';
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
                        ${filteredOrders.map(order => `
                            <tr>
                                <td><a href="view_order.php?order_id=${order.order_id}">${order.order_id}</a></td>
                                <td>${new Date(order.order_date).toLocaleDateString()}</td>
                                <td>₱${parseFloat(order.total_amount).toFixed(2)}</td>
                                <td><span class="badge bg-${getStatusClass(order.status)}">${order.status}</span></td>
                                <td>
                                    <a href="view_order.php?order_id=${order.order_id}" class="btn btn-sm btn-info">View</a>
                                    ${order.status.toLowerCase() === 'processing' || order.status.toLowerCase() === 'paid' || order.status.toLowerCase() === 'pending' ?
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
                case 'pending':
                    return 'warning';
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
        
        orderTabs.addEventListener('click', (event) => {
            event.preventDefault();
            if (event.target.tagName === 'A') {
                orderTabs.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
                event.target.classList.add('active');

                const filter = event.target.dataset.statusFilter;
                renderOrders(filter);
            }
        });

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
