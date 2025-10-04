<?php
session_start();
include('api/paypal_helpers.php');
include('api/paypal_config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id === 0) {
    header("Location: producers.php");
    exit();
}

$access_token = get_paypal_access_token(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);

if (!$access_token) {
    die("Could not retrieve PayPal access token.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Status - CrackCart</title>
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
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="card shadow-sm border-0 p-4" id="confirmation-container">
                <div class="text-center">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
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
      const confirmationContainer = document.getElementById('confirmation-container');
      const orderId = <?php echo $order_id; ?>;

      const renderOrderDetails = (order) => {
        const itemsHtml = order.items.map(item => `
          <tr>
            <td>${item.product_type}</td>
            <td class="text-center">${item.quantity}</td>
            <td class="text-end">₱${parseFloat(item.price_per_item).toFixed(2)}</td>
            <td class="text-end">₱${(item.quantity * item.price_per_item).toFixed(2)}</td>
          </tr>
        `).join('');

        let headerHtml = '';
        let actionButtonsHtml = '';

        if (order.status === 'paid') {
            headerHtml = `
                <div class="text-center mb-4">
                    <h2 class="text-success">Thank You For Your Order!</h2>
                    <p class="lead">Your order has been placed successfully.</p>
                    <p>Order ID: <strong>#${order.order_id}</strong></p>
                </div>
            `;
            actionButtonsHtml = `
                <div class="text-center mt-4">
                    <a href="producers.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="order_history.php" class="btn btn-secondary">View Order History</a>
                </div>
            `;
        } else if (order.status === 'failed') {
            headerHtml = `
                <div class="text-center mb-4">
                    <h2 class="text-danger">Payment Failed</h2>
                    <p class="lead">There was a problem with your payment.</p>
                    <p>Order ID: <strong>#${order.order_id}</strong></p>
                </div>
            `;
            actionButtonsHtml = `
                <div class="text-center mt-4">
                    <a href="product_checkout.php" class="btn btn-warning">Retry Payment</a>
                    <a href="order_history.php" class="btn btn-secondary">View Order History</a>
                </div>
            `;
        }

        confirmationContainer.innerHTML = `
            ${headerHtml}
            <div class="card-body">
                <h5>Order Summary</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total Amount:</td>
                                <td class="text-end">₱${parseFloat(order.total_amount).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <h5 class="mt-4">Shipping Details</h5>
                <p>
                    ${order.street}, ${order.city}, ${order.state}<br>
                    ${order.zip_code}, ${order.country}
                </p>
                ${actionButtonsHtml}
            </div>
        `;
      };

      fetch(`api/order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(result => {
          if (result.status === 'success') {
            renderOrderDetails(result.data);
          } else {
            confirmationContainer.innerHTML = `<div class="alert alert-danger">Error: ${result.message}</div>`;
          }
        });
    });
  </script>
</body>
</html>