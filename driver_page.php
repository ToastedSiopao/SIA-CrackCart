<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Deliveries - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="driver-styles.css" rel="stylesheet">
</head>
<body>
    <?php include("driver_header.php"); ?>

    <div class="container mt-4">
        <h1 class="mb-4">My Assigned Deliveries</h1>
        <div id="orders-container"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ordersContainer = document.getElementById('orders-container');

            const fetchAssignedOrders = async () => {
                try {
                    const response = await fetch('api/get_assigned_orders.php');
                    const result = await response.json();

                    if (result.success) {
                        renderOrders(result.data);
                    } else {
                        ordersContainer.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                    }
                } catch (error) {
                    ordersContainer.innerHTML = `<div class="alert alert-danger">Could not connect to the server.</div>`;
                }
            };

            const renderOrders = (orders) => {
                if (orders.length === 0) {
                    ordersContainer.innerHTML = '<div class="alert alert-info">You have no assigned deliveries.</div>';
                    return;
                }

                const ordersHtml = orders.map(order => `
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <span>Order #${order.order_id}</span>
                            <span class="badge bg-info">${order.status}</span>
                        </div>
                        <div class="card-body">
                            <p><strong>Customer:</strong> ${order.user_name}</p>
                            <p><strong>Address:</strong> ${order.shipping_address}</p>
                            <p><strong>Total:</strong> ₱${order.total_amount}</p>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary" onclick="updateDeliveryStatus(${order.order_id}, 'delivered')">Mark as Delivered</button>
                        </div>
                    </div>
                `).join('');

                ordersContainer.innerHTML = ordersHtml;
            };

            window.updateDeliveryStatus = async (orderId, status) => {
                try {
                    const response = await fetch('api/update_delivery_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ order_id: orderId, status: status })
                    });

                    const result = await response.json();

                    if (result.success) {
                        fetchAssignedOrders();
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                } catch (error) {
                    alert('Could not connect to the server.');
                }
            };

            fetchAssignedOrders();
        });
    </script>
</body>
</html>