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
  <link href="dashboard-styles.css?v=2.6" rel="stylesheet">
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
          <div id="checkout-alert-container"></div>
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
      const alertContainer = document.getElementById('checkout-alert-container');
      let selectedAddressId = null;
      let cartData = null;

      const fetchCartAndAddresses = async () => {
          try {
            const [cartResponse, addressResponse] = await Promise.all([
                fetch('api/cart.php'),
                fetch('api/addresses.php')
            ]);
            const cartResult = await cartResponse.json();
            const addressResult = await addressResponse.json();

            if (cartResult.status === 'success') {
                cartData = cartResult.data;
                renderOrderSummary(cartData);
            } else {
                orderSummaryContainer.innerHTML = `<div class="alert alert-danger">${cartResult.message || 'Error loading cart.'}</div>`;
            }
            if (addressResult.status === 'success') {
                renderAddressSelection(addressResult.data);
            } else {
                addressSelectionContainer.innerHTML = `<div class="alert alert-danger">${addressResult.message || 'Error loading addresses.'}</div>`;
            }
          } catch (error) {
             showAlert('danger', 'Could not connect to the server. Please try again later.');
          }
      };

      const showAlert = (type, message) => {
          alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
              ${message}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>`;
      };
      
      const renderAddressSelection = (addresses) => {
          if(addresses.length === 0) {
              addressSelectionContainer.innerHTML = `
                <div class="alert alert-warning"> 
                    You have no saved addresses. <a href="addresses.php">Please add an address</a> before proceeding.
                </div>`;
              if(paypal.Buttons) paypal.Buttons().close();
              return;
          }
          let defaultAddress = addresses.find(addr => addr.is_default);
          // If no default is explicitly set, use the first one.
          if (!defaultAddress && addresses.length > 0) {
              defaultAddress = addresses[0];
          }

          const addressHtml = addresses.map(addr => {
              const isChecked = defaultAddress && addr.address_id === defaultAddress.address_id;
              return `
                <div class="form-check card card-body mb-2">
                    <input class="form-check-input" type="radio" name="shippingAddress" id="addr-${addr.address_id}" value="${addr.address_id}" ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label w-100" for="addr-${addr.address_id}">
                        <strong>${addr.is_default ? 'Default Shipping Address' : 'Shipping Address'}</strong><br>
                        ${addr.address_line1}${addr.address_line2 ? ', ' + addr.address_line2 : ''}<br>
                        ${addr.city}, ${addr.state}, ${addr.country} - ${addr.zip_code}
                    </label>
                </div>
              `;
          }).join('');
          
          addressSelectionContainer.innerHTML = addressHtml;
          
          if (defaultAddress) {
            selectedAddressId = defaultAddress.address_id;
          } else {
            selectedAddressId = null;
          }
      };

      const renderOrderSummary = (data) => {
          if (!data || !data.items || data.items.length === 0) {
              orderSummaryContainer.innerHTML = '<p class="text-center">Your cart is empty.</p>';
              if(paypal.Buttons) paypal.Buttons().close(); // Remove PayPal buttons if cart is empty
              return;
          }
          const itemsHtml = data.items.map(item => `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                      ${item.product_type} (x${item.quantity})
                      <button class="btn btn-sm btn-outline-danger remove-item-btn" 
                              data-producer-id="${item.producer_id}" 
                              data-product-type="${item.product_type}">Remove</button>
                  </div>
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

      orderSummaryContainer.addEventListener('click', e => {
          if (e.target.classList.contains('remove-item-btn')) {
              const button = e.target;
              const producerId = button.dataset.producerId;
              const productType = button.dataset.productType;
              
              button.disabled = true; // Prevent double clicks
              
              fetch('api/remove_from_cart.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ producer_id: producerId, product_type: productType })
              })
              .then(res => res.json())
              .then(result => {
                  if (result.status === 'success') {
                      showAlert('success', 'Item removed from cart.');
                      renderOrderSummary(result.data);
                  } else {
                      showAlert('danger', result.message || 'Error removing item.');
                      button.disabled = false;
                  }
              })
              .catch(() => {
                  showAlert('danger', 'Could not connect to the server.');
                  button.disabled = false;
              });
          }
      });

      function renderPayPalButton() {
          const paymentMessage = document.getElementById('payment-message');
          // Close existing buttons before rendering new ones
          if (paypal.Buttons.instances) {
              paypal.Buttons.instances.forEach(instance => instance.close());
          }

          paypal.Buttons({
              createOrder: function(data, actions) {
                  if (!selectedAddressId) {
                      showAlert('warning', 'Please select or add a shipping address first.');
                      return Promise.reject(new Error("No shipping address selected"));
                  }
                  paymentMessage.innerHTML = ''; 
                  return fetch('api/paypal_create_order.php', { method: 'POST' })
                      .then(res => res.json())
                      .then(data => {
                          if(data.id) return data.id;
                          throw new Error(data.error || 'Could not create PayPal order.');
                      });
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
                          showAlert('danger', result.message || 'Payment failed. Please try again.');
                      }
                  });
              },
              onError: function(err) {
                showAlert('danger', 'An error occurred with the payment gateway. Please try refreshing the page.');
              }
          }).render('#paypal-button-container');
      }

      fetchCartAndAddresses();
  });
  </script>
</body>
</html>