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
  <title>Order History - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.5" rel="stylesheet">
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col ps-md-2 pt-2">
        <div class="container">
          <h2 class="text-center mb-4">Your Order History</h2>
          <div class="card shadow-sm border-0 p-4">
            <div class="card-body" id="order-history-container">
               <div class="text-center">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
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
      const orderHistoryContainer = document.getElementById('order-history-container');
      let orders = [];

      const getStatusBadge = (status) => {
          switch (status) {
              case 'paid': return '<span class="badge bg-success">Paid</span>';
              case 'failed': return '<span class="badge bg-danger">Failed</span>';
              case 'cancelled': return '<span class="badge bg-secondary">Cancelled</span>';
              default: return '<span class="badge bg-light text-dark">Unknown</span>';
          }
      };

      const renderOrderHistory = (orderData) => {
        orders = orderData;
        if (orders.length === 0) {
          orderHistoryContainer.innerHTML = '<div class="alert alert-info">You have no past orders.</div>';
          return;
        }

        const tableHtml = `
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th class="text-end">Total Amount</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                ${orders.map(order => `
                  <tr>
                    <td>#${order.order_id}</td>
                    <td>${new Date(order.order_date).toLocaleDateString()}</td>
                    <td class="text-end">₱${parseFloat(order.total_amount).toFixed(2)}</td>
                    <td class="text-center">${getStatusBadge(order.status)}</td>
                    <td class="text-center">
                      <a href="order_confirmation.php?order_id=${order.order_id}" class="btn btn-sm btn-outline-primary">View</a>
                      ${order.status !== 'cancelled' && order.status !== 'failed' ? 
                        `<button class="btn btn-sm btn-outline-danger ms-1 cancel-btn" data-order-id="${order.order_id}">Cancel</button>` : ''}
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        `;
        orderHistoryContainer.innerHTML = tableHtml;
      };

      const fetchOrders = () => {
          fetch('api/orders.php')
            .then(response => response.json())
            .then(result => {
              if (result.status === 'success') {
                renderOrderHistory(result.data);
              } else {
                orderHistoryContainer.innerHTML = '<div class="alert alert-danger">Error loading order history.</div>';
              }
            });
      }

      orderHistoryContainer.addEventListener('click', async (e) => {
          if (e.target.classList.contains('cancel-btn')) {
              const orderId = e.target.dataset.orderId;
              if (!confirm('Are you sure you want to cancel this order?')) return;

              const response = await fetch('api/cancel_order.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ order_id: orderId })
              });
              const result = await response.json();

              if (result.status === 'success') {
                  fetchOrders(); // Re-fetch orders to show updated status
              } else {
                  alert('Error: ' + result.message);
              }
          }
      });

      fetchOrders();
    });
  </script>
</body>
</html>