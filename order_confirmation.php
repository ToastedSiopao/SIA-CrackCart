<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the Order ID. Prioritize the ID from the session (set after checkout),
// but fall back to the URL query string. This makes the page versatile.
$order_id = 0;
if (isset($_SESSION['last_order_id'])) {
    $order_id = $_SESSION['last_order_id'];
    // Unset session variables to prevent showing the same confirmation on refresh
    unset($_SESSION['last_order_id']);
    unset($_SESSION['last_order_details']);
} elseif (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
}

// If no Order ID is found, redirect to a safe page
if ($order_id === 0) {
    header("Location: producers.php");
    exit();
}

include('api/paypal_helpers.php');
include('api/paypal_config.php');
$access_token = get_paypal_access_token(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);

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
            <td>${item.product_type} (x${item.tray_size})</td>
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
                    <p class="lead">Your payment was successful and your order has been placed.</p>
                    <p>Order ID: <strong>#${order.order_id}</strong></p>
                </div>
            `;
        } else if (order.status === 'processing') {
            headerHtml = `
                <div class="text-center mb-4">
                    <h2 class="text-info">Your Order is Being Processed!</h2>
                    <p class="lead">We have received your order and will begin preparing it for shipment.</p>
                    <p>Payment is due upon delivery (Cash on Delivery).</p>
                    <p>Order ID: <strong>#${order.order_id}</strong></p>
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
        }

        actionButtonsHtml = `
            <div class="text-center mt-4">
                <a href="producers.php" class="btn btn-primary">Continue Shopping</a>
                <a href="my_orders.php" class="btn btn-secondary">View My Orders</a>
            </div>
        `;

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
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
          if (result.status === 'success') {
            renderOrderDetails(result.data);
          } else {
            confirmationContainer.innerHTML = `<div class="alert alert-danger">Error: ${result.message}</div>`;
          }
        })
        .catch(error => {
            confirmationContainer.innerHTML = `<div class="alert alert-danger">Could not load order details. Please check your connection or contact support.</div>`;
            console.error('Fetch error:', error);
        });
    });
  </script>
</body>
</html>