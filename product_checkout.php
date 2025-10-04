<?php
session_start();
include("api/paypal_config.php"); 
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
      let selectedPaymentMethod = 'paypal'; 

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
             showAlert('danger', 'Could not process your request. Please try again later.');
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
              return;
          }
          let defaultAddress = addresses.find(addr => addr.is_default);
          if (!defaultAddress && addresses.length > 0) defaultAddress = addresses[0];

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
          if (defaultAddress) selectedAddressId = defaultAddress.address_id;
      };

      const renderOrderSummary = (data) => {
          if (!data || !data.items || data.items.length === 0) {
              orderSummaryContainer.innerHTML = '<p class="text-center">Your cart is empty.</p>';
              return;
          }

          const itemsHtml = data.items.map(item => `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                      ${item.product_type}
                      <p class="mb-0">₱${parseFloat(item.price).toFixed(2)} each</p>
                  </div>
                  <div class="d-flex align-items-center">
                      <div class="input-group input-group-sm" style="width: 120px;">
                          <button class="btn btn-outline-secondary quantity-btn" data-action="decrease" data-cart-item-key="${item.cart_item_key}">-</button>
                          <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                          <button class="btn btn-outline-secondary quantity-btn" data-action="increase" data-cart-item-key="${item.cart_item_key}">+</button>
                      </div>
                      <span class="me-3 ms-3">₱${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
                      <button class="btn btn-sm btn-outline-danger remove-item-btn" data-cart-item-key="${item.cart_item_key}">
                          <i class="fas fa-trash"></i>
                      </button>
                  </div>
              </li>`).join('');

          orderSummaryContainer.innerHTML = `
              <h5 class="card-title">Order Summary</h5>
              <ul class="list-group list-group-flush">${itemsHtml}
                  <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
                      Subtotal <span>₱${parseFloat(data.subtotal).toFixed(2)}</span>
                  </li>
              </ul>
              <h5 class="mt-4">Payment Method</h5>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="paymentMethod" id="paypalRadio" value="paypal" checked>
                <label class="form-check-label" for="paypalRadio">PayPal</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="paymentMethod" id="codRadio" value="cod">
                <label class="form-check-label" for="codRadio">Cash on Delivery</label>
              </div>
              <div id="payment-container" class="mt-3"></div>
              <div id="payment-message" class="mt-2"></div>
          `;
          setupPaymentListeners();
          renderPaymentMethod();
      };

      const handleRemoveItem = async (event) => {
          const button = event.target.closest('.remove-item-btn');
          if (!button) return;

          const cartItemKey = button.dataset.cartItemKey;
          if (!cartItemKey) return;
          
          if (!confirm('Are you sure you want to remove this item?')) {
              return;
          }

          try {
              const response = await fetch('api/cart.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ 
                      _method: 'DELETE',
                      cart_item_key: cartItemKey 
                  })
              });
              const result = await response.json();
              if (result.status === 'success') {
                  showAlert('success', 'Item removed successfully.');
                  fetchCartAndAddresses();
              } else {
                  showAlert('danger', result.message || 'Could not remove item.');
              }
          } catch (error) {
              showAlert('danger', 'Could not connect to the server to remove item.');
          }
      };

      const handleQuantityChange = async (event) => {
          const button = event.target.closest('.quantity-btn');
          if (!button) return;

          const cartItemKey = button.dataset.cartItemKey;
          const action = button.dataset.action;
          
          const item = cartData.items.find(i => i.cart_item_key === cartItemKey);
          if (!item) return;

          let newQuantity = parseInt(item.quantity, 10);

          if (action === 'increase') {
              newQuantity++;
          } else if (action === 'decrease') {
              newQuantity--;
          }
          
          if (newQuantity <= 0) {
              if (confirm('Do you want to remove this item from the cart?')) {
                  await updateCartQuantity(cartItemKey, 0);
              }
          } else {
              await updateCartQuantity(cartItemKey, newQuantity);
          }
      };

      const updateCartQuantity = async (cartItemKey, quantity) => {
          try {
              const response = await fetch('api/cart.php', {
                  method: 'POST', // Changed from PUT to POST
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({
                      _method: 'PUT', // Method override
                      cart_item_key: cartItemKey,
                      quantity: quantity
                  })
              });
              const result = await response.json();
              if (result.status === 'success') {
                  await fetchCartAndAddresses();
              } else {
                  showAlert('danger', result.message || 'Could not update cart.');
              }
          } catch (error) {
              showAlert('danger', 'Could not connect to the server to update cart.');
          }
      };
      
      orderSummaryContainer.addEventListener('click', (event) => {
          const removeButton = event.target.closest('.remove-item-btn');
          const quantityButton = event.target.closest('.quantity-btn');

          if (removeButton) {
              handleRemoveItem(event);
          } else if (quantityButton) {
              handleQuantityChange(event);
          }
      });

      function setupPaymentListeners() {
        document.querySelectorAll('input[name="paymentMethod"]').forEach(elem => {
            elem.addEventListener('change', (event) => {
                selectedPaymentMethod = event.target.value;
                renderPaymentMethod();
            });
        });
      }

      function renderPaymentMethod() {
        const paymentContainer = document.getElementById('payment-container');
        if (selectedPaymentMethod === 'paypal') {
            paymentContainer.innerHTML = '<div id="paypal-button-container"></div>';
            renderPayPalButton();
        } else { // Cash on Delivery
            paymentContainer.innerHTML = '<button id="cod-place-order" class="btn btn-primary w-100">Place Order</button>';
            document.getElementById('cod-place-order').addEventListener('click', handleCodOrder);
        }
      }

      async function handleCodOrder() {
        if (!selectedAddressId) {
            showAlert('warning', 'Please select a shipping address first.');
            return;
        }
        const placeOrderBtn = document.getElementById('cod-place-order');
        placeOrderBtn.disabled = true;
        placeOrderBtn.innerHTML = '<div class="spinner-border spinner-border-sm"></div><span class="ms-2">Placing Order...</span>';
        
        try {
            const response = await fetch('api/cod_create_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    address_id: selectedAddressId,
                    cart: cartData.items
                })
            });
            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = `order_confirmation.php?order_id=${result.order_id}`;
            } else {
                showAlert('danger', result.message || 'Could not place order.');
                placeOrderBtn.disabled = false;
                placeOrderBtn.innerText = 'Place Order';
            }
        } catch(e) {
            showAlert('danger', 'Could not connect to the server to place order.');
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerText = 'Place Order';
        }
      }

      addressSelectionContainer.addEventListener('change', (e) => {
        if(e.target.name === 'shippingAddress') selectedAddressId = e.target.value;
      });

      function renderPayPalButton() {
          if (paypal.Buttons.instances) {
              paypal.Buttons.instances.forEach(instance => instance.close());
          }
          paypal.Buttons({
              createOrder: (data, actions) => {
                  if (!selectedAddressId) {
                      showAlert('warning', 'Please select a shipping address first.');
                      return Promise.reject(new Error("No shipping address selected"));
                  }
                  return fetch('api/paypal_create_order.php', { method: 'POST' })
                      .then(res => res.json())
                      .then(data => {
                          if(data.id) return data.id;
                          throw new Error(data.error || 'Could not create PayPal order.');
                      });
              },
              onApprove: (data, actions) => {
                  document.getElementById('payment-message').innerHTML = '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border spinner-border-sm"></div><span class="ms-2">Processing...</span></div>';
                  return fetch('api/paypal_capture_order.php', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ orderID: data.orderID, shipping_address_id: selectedAddressId })
                  })
                  .then(res => res.json())
                  .then(result => {
                      if (result.status === 'success') {
                          window.location.href = `order_confirmation.php?order_id=${result.order_id}`;
                      } else {
                          showAlert('danger', result.message || 'Payment failed.');
                      }
                  });
              },
              onError: (err) => showAlert('danger', 'An error occurred with the payment gateway.')
          }).render('#paypal-button-container');
      }

      fetchCartAndAddresses();
  });
  </script>
</body>
</html>