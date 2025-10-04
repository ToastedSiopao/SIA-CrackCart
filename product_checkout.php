<?php
session_start();
include("api/paypal_config.php"); // Include PayPal config for Client ID
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
  <title>Checkout - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.5" rel="stylesheet">
  <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=PHP"></script>
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col ps-md-2 pt-2">
        <div class="container">
          <h2 class="text-center mb-4">Checkout</h2>
          <div class="row g-5">
            <div class="col-md-7">
              <h4 class="mb-3">Shipping Information</h4>
              <div id="address-selection">
                  <div class="text-center"><div class="spinner-border"></div></div>
              </div>
              <a href="addresses.php" class="btn btn-secondary mt-3">Manage Addresses</a>
            </div>

            <div class="col-md-5">
              <div class="card sticky-top" style="top: 20px;">
                <div class="card-body" id="order-summary">
                    <div class="text-center"><div class="spinner-border"></div></div>
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
      const orderSummaryContainer = document.getElementById('order-summary');
      const addressSelectionContainer = document.getElementById('address-selection');
      let selectedAddressId = null;
      let cartData = null;

      const fetchCartAndAddresses = async () => {
          const [cartResponse, addressResponse] = await Promise.all([
              fetch('api/cart.php'),
              fetch('api/addresses.php')
          ]);
          const cartResult = await cartResponse.json();
          const addressResult = await addressResponse.json();

          if (cartResult.status === 'success') {
              cartData = cartResult.data;
              renderOrderSummary(cartData);
          }
          if (addressResult.status === 'success') {
              renderAddressSelection(addressResult.data);
          }
      };

      const renderAddressSelection = (addresses) => {
          if(addresses.length === 0) {
              addressSelectionContainer.innerHTML = `
                <div class="alert alert-warning"> 
                    You have no saved addresses. Please add an address before proceeding.
                </div>`;
              return;
          }
          const addressHtml = addresses.map((addr, index) => `
            <div class="form-check card card-body mb-2">
                <input class="form-check-input" type="radio" name="shippingAddress" id="addr-${addr.address_id}" value="${addr.address_id}" ${index === 0 ? 'checked' : ''}>
                <label class="form-check-label w-100" for="addr-${addr.address_id}">
                    <strong>Address ${index + 1}</strong><br>
                    ${addr.street}, ${addr.city}, ${addr.state}, ${addr.zip_code}, ${addr.country}
                </label>
            </div>
          `).join('');
          addressSelectionContainer.innerHTML = addressHtml;
          selectedAddressId = addresses[0].address_id;
      };

      const renderOrderSummary = (data) => {
          if (!data || data.items.length === 0) {
              orderSummaryContainer.innerHTML = '<p class="text-center">Your cart is empty.</p>';
              return;
          }
          const itemsHtml = data.items.map(item => `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  ${item.product_type} (x${item.quantity})
                  <span>₱${(item.price * item.quantity).toFixed(2)}</span>
              </li>`).join('');

          orderSummaryContainer.innerHTML = `
              <h5 class="card-title">Order Summary</h5>
              <ul class="list-group list-group-flush">
                  ${itemsHtml}
                  <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
                      Subtotal
                      <span>₱${data.subtotal.toFixed(2)}</span>
                  </li>
              </ul>
              <h5 class="mt-4">Payment</h5>
              <div id="paypal-button-container" class="mt-3"></div>
              <div id="payment-message" class="mt-2"></div>
          `;
          renderPayPalButton();
      };
      
      addressSelectionContainer.addEventListener('change', (e) => {
        if(e.target.name === 'shippingAddress') {
            selectedAddressId = e.target.value;
        }
      });

      function renderPayPalButton() {
          const paymentMessage = document.getElementById('payment-message');

          paypal.Buttons({
              createOrder: function(data, actions) {
                  if (!selectedAddressId) {
                      paymentMessage.innerHTML = '<div class="alert alert-danger">Please select a shipping address first.</div>';
                      return Promise.reject(new Error("No shipping address selected"));
                  }
                  paymentMessage.innerHTML = ''; 
                  return fetch('api/paypal_create_order.php', { method: 'POST' })
                      .then(res => res.json())
                      .then(data => data.id); 
              },
              onApprove: function(data, actions) {
                  paymentMessage.innerHTML = '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border spinner-border-sm"></div><span class="ms-2">Processing payment...</span></div>';
                  return fetch('api/paypal_capture_order.php', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ 
                          orderID: data.orderID,
                          shipping_address_id: selectedAddressId
                      })
                  })
                  .then(res => res.json())
                  .then(result => {
                      if (result.status === 'success') {
                          window.location.href = `order_confirmation.php?order_id=${result.order_id}`;
                      } else {
                          paymentMessage.innerHTML = `<div class="alert alert-danger">Error: ${result.message || 'Payment failed.'}</div>`;
                      }
                  });
              },
              onError: function(err) {
                paymentMessage.innerHTML = '<div class="alert alert-danger">An error occurred with the payment process. Please try again.</div>';
              }
          }).render('#paypal-button-container');
      }

      fetchCartAndAddresses();
  });
  </script>
</body>
</html>