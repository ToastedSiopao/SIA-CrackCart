<?php
require_once 'session_handler.php';
include("api/paypal_config.php"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=3.2" rel="stylesheet">
  <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=PHP"></script>
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col ps-md-2 pt-2">
        <div class="container" id="main-checkout-container">
          <h2 class="text-center mb-4">Checkout</h2>
          <div id="checkout-alert-container"></div>
          <div class="row g-5" id="checkout-content">
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
    document.addEventListener('DOMContentLoaded', async function() {
      const mainContainer = document.getElementById('main-checkout-container');
      const checkoutContent = document.getElementById('checkout-content');
      const orderSummaryContainer = document.getElementById('order-summary');
      const addressSelectionContainer = document.getElementById('address-selection');
      const alertContainer = document.getElementById('checkout-alert-container');
      let selectedAddressId = null;
      let cartData = null;
      let selectedPaymentMethod = 'paypal';

      const checkUserStatus = async () => {
          try {
              const response = await fetch('api/user_status_check.php');
              if (response.status === 403) {
                  const result = await response.json();
                  mainContainer.innerHTML = `<div class="alert alert-warning"><h3>Account Locked</h3><p>${result.message}</p></div>`;
                  return false;
              }
              if (!response.ok) {
                  const result = await response.json();
                  showAlert('danger', result.message || 'Could not verify your account status. Please try again later.');
                  return false;
              }
              return true;
          } catch (error) {
              showAlert('danger', 'Could not connect to verify your account status. Please check your connection and try again.');
              checkoutContent.innerHTML = '';
              return false;
          }
      };

      const isUserActive = await checkUserStatus();
      if (!isUserActive) {
        checkoutContent.innerHTML = '';
        return; 
      }

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
              if (isChecked) selectedAddressId = addr.address_id;
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
          addressSelectionContainer.addEventListener('change', (e) => {
            if(e.target.name === 'shippingAddress') selectedAddressId = e.target.value;
          });
      };

      const renderOrderSummary = (data) => {
          if (!data || !data.items || data.items.length === 0) {
              mainContainer.innerHTML = `
                <div class="card text-center shadow-sm mx-auto" style="max-width: 500px; margin-top: 50px;">
                    <div class="card-body p-5">
                        <i class="bi bi-cart3" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h3 class="card-title mt-4">Your Cart is Empty</h3>
                        <p class="card-text text-muted">It looks like you haven\'t added anything to your cart. Let\'s change that!</p>
                        <a href="products.php" class="btn btn-primary mt-3">
                            <i class="bi bi-bag-check-fill me-2"></i>Start Shopping
                        </a>
                    </div>
                </div>`;
              return;
          }

          const itemsHtml = data.items.map(item => `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                      ${item.product_type} (x${item.tray_size})
                      <p class="mb-0 text-muted" style="font-size: 0.9em;">₱${parseFloat(item.price).toFixed(2)} per tray</p>
                  </div>
                  <div class="d-flex align-items-center">
                      <div class="input-group input-group-sm" style="width: 120px;">
                          <button class="btn btn-outline-secondary quantity-btn" data-action="decrease" data-cart-item-key="${item.cart_item_key}">-</button>
                          <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                          <button class="btn btn-outline-secondary quantity-btn" data-action="increase" data-cart-item-key="${item.cart_item_key}">+</button>
                      </div>
                      <span class="fw-bold me-3 ms-3">₱${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
                      <button class="btn btn-sm btn-outline-danger remove-item-btn" data-cart-item-key="${item.cart_item_key}">
                          <i class="bi bi-trash"></i>
                      </button>
                  </div>
              </li>`).join('');

          orderSummaryContainer.innerHTML = `
              <h5 class="card-title">Order Summary</h5>
              <ul class="list-group list-group-flush">
                  ${itemsHtml}
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                      Subtotal <span>₱${parseFloat(data.subtotal).toFixed(2)}</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                      Delivery Fee (${data.meta.vehicle_type || 'Not Selected'}) <span>₱${parseFloat(data.delivery_fee).toFixed(2)}</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center fw-bold fs-5">
                      Grand Total <span>₱${parseFloat(data.grand_total).toFixed(2)}</span>
                  </li>
              </ul>
              <div class="mt-3 p-3 bg-light border rounded">
                  <h6 class="fw-bold">Return & Refund Policy</h6>
                  <p class="small mb-0">
                      Please inspect your order upon reception. To be eligible for a return, your request must be made on the <strong>same day of delivery</strong>. All items, particularly eggs, must be <strong>intact and in their original packaging</strong>. For more details, please see our full <a href="return-policy.php">return policy</a>.
                  </p>
              </div>
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
      
      orderSummaryContainer.addEventListener('click', (event) => {
          const removeButton = event.target.closest('.remove-item-btn');
          const quantityButton = event.target.closest('.quantity-btn');

          if (removeButton) {
              handleRemoveItem(event);
          } else if (quantityButton) {
              handleQuantityChange(event);
          }
      });

      const handleRemoveItem = async (event) => {
          const button = event.target.closest('.remove-item-btn');
          if (!button || !confirm('Are you sure you want to remove this item?')) return;

          const cartItemKey = button.dataset.cartItemKey;
          try {
            const response = await fetch(`api/cart.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', cart_item_key: cartItemKey })
            });
            const result = await response.json();
            if (result.status === 'success') {
                fetchCartAndAddresses();
            } else {
                showAlert('danger', result.message || 'Could not remove item from cart.');
            }
        } catch (error) {
            showAlert('danger', 'Could not connect to the server to update cart.');
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
          newQuantity = (action === 'increase') ? newQuantity + 1 : newQuantity - 1;
          
          await updateCartQuantity(cartItemKey, newQuantity);
      };

      const updateCartQuantity = async (cartItemKey, quantity) => {
          let action = 'update';
          if (quantity <= 0) {
             if (confirm('Do you want to remove this item from the cart?')) {
                action = 'delete';
             } else {
                 return; // Do nothing if user cancels removal
             }
          }

          try {
              const response = await fetch('api/cart.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({
                      action: action,
                      cart_item_key: cartItemKey,
                      quantity: quantity // Quantity is ignored by server on 'delete' but good to send
                  })
              });
              const result = await response.json();
              if (result.status === 'success') {
                  fetchCartAndAddresses();
              } else {
                  showAlert('danger', result.message || 'Could not update cart.');
              }
          } catch (error) {
              showAlert('danger', 'Could not connect to the server to update cart.');
          }
      };
      
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
            paymentContainer.innerHTML = '<button id="cod-place-order" class="btn btn-primary w-100">Place Order (COD)</button>';
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
                    shipping_address_id: selectedAddressId,
                    payment_method: 'cod',
                    vehicle_type: cartData.meta.vehicle_type
                })
            });
            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = 'order_confirmation.php';
            } else {
                showAlert('danger', result.message || 'Could not place order.');
                placeOrderBtn.disabled = false;
                placeOrderBtn.innerText = 'Place Order (COD)';
            }
        } catch(e) {
            showAlert('danger', 'Could not connect to the server to place order.');
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerText = 'Place Order (COD)';
        }
      }

      function renderPayPalButton() {
          if (paypal.Buttons.instances) {
              paypal.Buttons.instances.forEach(instance => instance.close());
          }
          paypal.Buttons({
              createOrder: (data, a, actions) => {
                  if (!selectedAddressId) {
                      showAlert('warning', 'Please select a shipping address first.');
                      return Promise.reject(new Error("No shipping address selected"));
                  }
                  return fetch('api/paypal_create_order.php', { 
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ 
                          vehicle_type: cartData.meta.vehicle_type,
                          shipping_address_id: selectedAddressId
                      })
                  })
                      .then(res => res.json())
                      .then(data => {
                          if(data.id) return data.id;
                          showAlert('danger', 'Could not create PayPal order. Please try again.');
                          throw new Error(data.error || 'Could not create PayPal order.');
                      });
              },
              onApprove: (data, actions) => {
                  document.getElementById('payment-container').innerHTML = '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border spinner-border-sm"></div><span class="ms-2">Processing Payment...</span></div>';
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
                          window.location.href = 'order_confirmation.php';
                      } else {
                          showAlert('danger', result.message || 'Payment failed.');
                          renderPaymentMethod();
                      }
                  });
              },
              onError: (err) => {
                showAlert('danger', 'An error occurred with the payment gateway. Please try another payment method.');
                renderPaymentMethod();
              }
          }).render('#paypal-button-container');
      }

      fetchCartAndAddresses();
  });
  </script>
</body>
</html>