<?php
require_once '../session_handler.php';
require_once '../db_connect.php'; 
require_once 'admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cancellation Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin-styles.css?v=1.2" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Cancellation Requests</h1>
                </div>
                <div id="requests-alert-container"></div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Username</th>
                                <th>Order Date</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requests-container">
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const requestsContainer = document.getElementById('requests-container');
        const alertContainer = document.getElementById('requests-alert-container');

        const showAlert = (type, message) => {
            alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${message}</div>`;
        };

        const fetchRequests = async () => {
            try {
                const response = await fetch('api/get_cancellation_requests.php');
                const result = await response.json();

                if (result.status === 'success') {
                    renderRequests(result.data);
                } else {
                    showAlert('danger', result.message || 'Could not load cancellation requests.');
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server.');
            }
        };

        const renderRequests = (requests) => {
            if (requests.length === 0) {
                requestsContainer.innerHTML = '<tr><td colspan="5" class="text-center">No pending cancellation requests.</td></tr>';
                return;
            }

            requestsContainer.innerHTML = requests.map(req => `
                <tr>
                    <td><a href="order_details.php?order_id=${req.order_id}">#${req.order_id}</a></td>
                    <td>${req.username}</td>
                    <td>${new Date(req.order_date).toLocaleDateString()}</td>
                    <td>&#8369;${parseFloat(req.total_amount).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-success approve-btn" data-order-id="${req.order_id}">Approve</button>
                        <button class="btn btn-sm btn-danger deny-btn" data-order-id="${req.order_id}">Deny</button>
                    </td>
                </tr>
            `).join('');
        };

        requestsContainer.addEventListener('click', async (event) => {
            const target = event.target;
            if (target.classList.contains('approve-btn') || target.classList.contains('deny-btn')) {
                const orderId = target.dataset.orderId;
                const action = target.classList.contains('approve-btn') ? 'approved' : 'denied';
                
                if (confirm(`Are you sure you want to ${action.slice(0, -1)} this cancellation request?`)) {
                    await handleCancellation(orderId, action);
                }
            }
        });

        const handleCancellation = async (orderId, action) => {
            try {
                const response = await fetch('api/handle_cancellation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId, action: action })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showAlert('success', result.message);
                    fetchRequests();
                } else {
                    showAlert('danger', result.message || 'An error occurred.');
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server.');
            }
        };

        fetchRequests();
    });
    </script>
</body>
</html>
