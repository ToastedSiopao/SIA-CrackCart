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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
                        <h1 class="text-center">My Orders</h1>
                    </div>
                    <div id="orders-alert-container"></div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <ul class="nav nav-tabs" id="orderTabs">
                                        <li class="nav-item"><a class="nav-link active" href="#" data-status-filter="all">All</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#" data-status-filter="topay">To Pay</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#" data-status-filter="toship">To Ship</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#" data-status-filter="toreceive">To Receive</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#" data-status-filter="completed">Completed</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#" data-status-filter="cancelled">Cancelled</a></li>
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
                    // Get the currently active tab and re-render it
                    const activeTab = orderTabs.querySelector('.nav-link.active');
                    const activeFilter = activeTab ? activeTab.dataset.statusFilter : 'all';
                    renderOrders(activeFilter);
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
                    if (filter === 'topay') return status === 'pending';
                    if (filter === 'toship') return ['processing', 'paid'].includes(status);
                    if (filter === 'toreceive') return status === 'shipped';
                    if (filter === 'completed') return status === 'delivered';
                    if (filter === 'cancelled') return ['cancelled', 'failed'].includes(status);
                    return false;
                });
            }

            if (filteredOrders.length === 0) {
                ordersContainer.innerHTML = '<p class="text-center mt-3">No orders found in this category.</p>';
                return;
            }

            const tableHtml = `
                <table class="table table-hover align-middle">
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
                        ${filteredOrders.map(order => {
                            const cancellableStatuses = ['pending', 'processing', 'paid'];
                            return `
                            <tr>
                                <td><a href="view_order.php?order_id=${order.order_id}">#${order.order_id}</a></td>
                                <td>${new Date(order.order_date).toLocaleDateString()}</td>
                                <td>₱${parseFloat(order.total_amount).toFixed(2)}</td>
                                <td><span class="badge bg-${getStatusClass(order.status)}">${order.status}</span></td>
                                <td class="text-center">
                                    <a href="view_order.php?order_id=${order.order_id}" class="btn btn-sm btn-info">View</a>
                                    ${cancellableStatuses.includes(order.status.toLowerCase()) ?
                                    `<button class="btn btn-sm btn-danger cancel-btn ms-1" data-order-id="${order.order_id}">Cancel</button>` :
                                    ''}
                                </td>
                            </tr>
                        `}).join('')}
                    </tbody>
                </table>`;
            
            ordersContainer.innerHTML = tableHtml;
        };

        const getStatusClass = (status) => {
            switch (status.toLowerCase()) {
                case 'pending': return 'warning';
                case 'paid':
                case 'processing': return 'info';
                case 'shipped': return 'primary';
                case 'delivered': return 'success';
                case 'cancelled': return 'secondary';
                case 'failed': return 'danger';
                default: return 'light';
            }
        };
        
        orderTabs.addEventListener('click', (event) => {
            event.preventDefault();
            if (event.target.tagName === 'A') {
                orderTabs.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
                event.target.classList.add('active');
                renderOrders(event.target.dataset.statusFilter);
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
            const button = ordersContainer.querySelector(`[data-order-id="${orderId}"]`);
            button.disabled = true;

            try {
                const response = await fetch('api/cancel_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // If the message from the server contains "locked", show a warning alert.
                    // Otherwise, show a standard success alert.
                    const alertType = result.message.toLowerCase().includes('locked') ? 'warning' : 'success';
                    showAlert(alertType, result.message); // Use the dynamic message from the API
                    await fetchOrders(); // Refresh the order list
                } else {
                    showAlert('danger', result.message || 'There was an issue cancelling your order.');
                    button.disabled = false;
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server to cancel the order.');
                button.disabled = false;
            }
        };

        // Initial load
        fetchOrders();
    });
    </script>
</body>
</html>
