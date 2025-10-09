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
  <link href="dashboard-styles.css?v=3.5" rel="stylesheet"> 
  <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=PHP"></script>
  <style>
    body {
        background-color: #f8f9fa;
    }
    .checkout-container {
        max-width: 1200px;
    }
    .card-header {
        background-color: #fff;
        border-bottom: 2px solid #ffb703;
    }
    .card-header h4 {
        color: #023047;
    }
    .order-summary-card {
        position: sticky;
        top: 20px;
    }
    .list-group-item.summary-total {
        font-size: 1.2rem;
        font-weight: bold;
    }
    #main-checkout-container.empty-cart {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
    }
    .payment-method-card .form-check {
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        transition: border-color 0.2s;
    }
    .payment-method-card .form-check:hover {
        border-color: #ffb703;
    }
    .payment-method-card .form-check-input:checked + .form-check-label {
        color: #023047;
    }
  </style>
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col ps-md-2 pt-4">
        <div class="container checkout-container" id="main-checkout-container">
          <div class="text-center mb-5">
            <h2>Secure Checkout</h2>
            <p class="lead text-muted">Complete your purchase with confidence.</p>
          </div>
          <div id="checkout-alert-container"></div>
          <div class="row g-4" id="checkout-content">
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-2">Loading your checkout details...</p>
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
      const alertContainer = document.getElementById('checkout-alert-container');
      let selectedAddressId = null;
      let cartData = null;
      let selectedPaymentMethod = 'paypal';
      let isUpdatingCart = false;

      const showAlert = (type, message, isDismissible = true) => {
          const dismissBtn = isDismissible ? `<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>` : '';
          alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}${dismissBtn}</div>`;
      };

      const checkUserStatus = async () => {
        try {
            const response = await fetch('api/user_status_check.php');
            if (response.status === 403) {
                const result = await response.json();
                mainContainer.innerHTML = `<div class="alert alert-warning"><h3>Account Locked</h3><p>${result.message}</p></div>`;
                return false;
            }
            return true;
        } catch (error) {
            showAlert('danger', 'Could not verify your account status.', false);
            return false;
        }
      };

      if (!await checkUserStatus()) {
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
                renderCheckoutPage(cartData, addressResult.data);
            } else if (cartResult.data && cartResult.data.items.length === 0) {
                renderEmptyCart();
            } else {
                showAlert('danger', cartResult.message || 'Error loading cart.');
            }
        } catch (error) {
            showAlert('danger', 'There was a problem loading your checkout details. Please try again.');
        }
      };

      const renderEmptyCart = () => {
        mainContainer.classList.add('empty-cart');
        mainContainer.innerHTML = `
            <div class="card text-center shadow-sm" style="max-width: 500px;">
                <div class="card-body p-5">
                    <i class="bi bi-cart-x" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h3 class="card-title mt-4">Your Cart is Empty</h3>
                    <p class="card-text text-muted">Looks like you haven\'t added anything to your cart. Let\'s find something for you!</p>
                    <a href="producers.php" class="btn btn-primary mt-3">
                        <i class="bi bi-bag-check-fill me-2"></i>Start Shopping
                    </a>
                </div>
            </div>`;
      };

      const renderCheckoutPage = (cart, addresses) => {
        if (!cart || !cart.items || cart.items.length === 0) {
            renderEmptyCart();
            return;
        }

        const itemsHtml = cart.items.map(item => {
            const traySizeOptions = [12, 30];
            const selectHtml = `<select class="form-select form-select-sm tray-size-select" data-cart-item-key="${item.cart_item_key}" style="width: auto;">${traySizeOptions.map(size => `<option value="${size}" ${item.tray_size == size ? 'selected' : ''}>${size}</option>`).join('')}</select>`;
            
            return `
            <li class="list-group-item d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="my-0">${item.product_type}</h6>
                    <small class="text-muted">Tray Size: ${selectHtml}</small>
                    <div class="d-flex align-items-center mt-2">
                        <div class="input-group input-group-sm" style="width: 110px;">
                            <button class="btn btn-outline-secondary quantity-btn" data-action="decrease" data-cart-item-key="${item.cart_item_key}">-</button>
                            <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                            <button class="btn btn-outline-secondary quantity-btn" data-action="increase" data-cart-item-key="${item.cart_item_key}">+</button>
                        </div>
                        <button class="btn btn-sm btn-outline-danger ms-2 remove-item-btn" data-cart-item-key="${item.cart_item_key}"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <div class="text-end">
                    <span class="fw-bold">₱${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
                    <small class="d-block text-muted">@ ₱${parseFloat(item.price).toFixed(2)}</small>
                </div>
            </li>`;
        }).join('');

        let defaultAddress = (addresses || []).find(addr => addr.is_default) || (addresses || [])[0];
        selectedAddressId = defaultAddress ? defaultAddress.address_id : null;
        
        const addressHtml = (addresses && addresses.length > 0) ? addresses.map(addr => `
            <div class="form-check card card-body mb-2 p-3">
                <input class="form-check-input" type="radio" name="shippingAddress" id="addr-${addr.address_id}" value="${addr.address_id}" ${defaultAddress && addr.address_id === defaultAddress.address_id ? 'checked' : ''}>
                <label class="form-check-label w-100" for="addr-${addr.address_id}">
                    <strong>${addr.is_default ? 'Default:' : 'Address:'}</strong> ${addr.address_line1}, ${addr.city}
                    <span class="d-block text-muted">${addr.state}, ${addr.country} - ${addr.zip_code}</span>
                </label>
            </div>
        `).join('') : '<div class="alert alert-warning">You have no saved addresses. <a href="addresses.php">Please add an address</a>.</div>';

        const deliveryFee = parseFloat(cart.delivery_fee || 0);
        const discount = cart.applied_coupon ? parseFloat(cart.applied_coupon.discount_value) : 0;
        const grandTotal = (cart.subtotal + deliveryFee) - discount;

        const couponHtml = cart.applied_coupon 
            ? `<div class="d-flex justify-content-between align-items-center"><p class="mb-0 text-success">Coupon Applied: <strong>${cart.applied_coupon.coupon_code}</strong></p><button class="btn btn-sm btn-outline-danger" id="remove-coupon-btn">Remove</button></div>`
            : `<div class="input-group"><input type="text" class="form-control" id="coupon-code" placeholder="Enter coupon code"><button class="btn btn-secondary" type="button" id="apply-coupon-btn">Apply</button></div>`;

        checkoutContent.innerHTML = `
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><h4><i class="bi bi-truck me-2"></i>Shipping Information</h4></div>
                    <div class="card-body">
                        ${addressHtml}
                        <a href="addresses.php" class="btn btn-sm btn-outline-primary mt-2">Manage Addresses</a>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header"><h4><i class="bi bi-credit-card me-2"></i>Payment Method</h4></div>
                    <div class="card-body payment-method-card">
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="radio" name="paymentMethod" id="paypalRadio" value="paypal" checked>
                          <label class="form-check-label" for="paypalRadio"><i class="bi bi-paypal me-2"></i>PayPal</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="paymentMethod" id="codRadio" value="cod">
                          <label class="form-check-label" for="codRadio"><i class="bi bi-cash-coin me-2"></i>Cash on Delivery</label>
                        </div>
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="agreeToTerms">
                            <label class="form-check-label small" for="agreeToTerms">
                              I have read and agree to the <a href="return-policy.php" target="_blank">Return & Refund Policy</a>.
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm order-summary-card">
                    <div class="card-header"><h4><i class="bi bi-cart3 me-2"></i>Order Summary</h4></div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">${itemsHtml}</ul>
                        <div class="card-body">
                            <h6 class="card-title">Coupon Code</h6>
                            ${couponHtml}
                        </div>
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item d-flex justify-content-between"><span>Subtotal</span> <span>₱${cart.subtotal.toFixed(2)}</span></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Delivery Fee</span> <span>₱${deliveryFee.toFixed(2)}</span></li>
                            ${discount > 0 ? `<li class="list-group-item d-flex justify-content-between text-danger"><span>Discount</span> <span>-₱${discount.toFixed(2)}</span></li>` : ''}
                            <li class="list-group-item d-flex justify-content-between summary-total"><span>Grand Total</span> <span>₱${grandTotal.toFixed(2)}</span></li>
                        </ul>
                        <div id="payment-container" class="mt-4 d-grid"></div>
                    </div>
                </div>
            </div>
        `;
        setupEventListeners();
        renderPaymentMethod();
      };
      
      const setupEventListeners = () => {
        checkoutContent.addEventListener('click', (event) => {
            const target = event.target;
            const removeBtn = target.closest('.remove-item-btn');
            const quantityBtn = target.closest('.quantity-btn');
            if (removeBtn) handleRemoveItem(removeBtn.dataset.cartItemKey);
            else if (quantityBtn) handleQuantityChange(quantityBtn.dataset.cartItemKey, quantityBtn.dataset.action);
            else if (target.id === 'apply-coupon-btn') handleApplyCoupon();
            else if (target.id === 'remove-coupon-btn') handleRemoveCoupon();
        });

        checkoutContent.addEventListener('change', (event) => {
            const target = event.target;
            if (target.classList.contains('tray-size-select')) handleTraySizeChange(target.dataset.cartItemKey, target.value);
            else if (target.name === 'shippingAddress') selectedAddressId = target.value;
            else if (target.name === 'paymentMethod') {
                selectedPaymentMethod = target.value;
                renderPaymentMethod();
            }
        });
      };

      const handleApiAction = async (url, body, successMessage) => {
        if (isUpdatingCart) {
            return; 
        }
        isUpdatingCart = true;

        let response;
        try {
            response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            const resultText = await response.text();
            let result;
            try {
                result = JSON.parse(resultText);
            } catch (e) {
                // If parsing fails, it's an unexpected server error (like an HTML error page)
                showAlert('danger', 'An unexpected error occurred. The server response was not valid.');
                isUpdatingCart = false;
                return;
            }

            if (result.status === 'success') {
                if(successMessage) showAlert('success', result.message || successMessage);
                cartData = result.data;
                const addressResult = await (await fetch('api/addresses.php')).json();
                renderCheckoutPage(cartData, addressResult.data);
            } else {
                showAlert('danger', result.message || 'An unknown error occurred.');
            }
        } catch (error) {
            showAlert('danger', 'Could not connect to the server. Please check your internet connection.');
        } finally {
            isUpdatingCart = false;
        }
      };
      
      const handleRemoveItem = (cartItemKey) => {
          if (confirm('Are you sure you want to remove this item?')) {
              handleApiAction('api/cart.php', { action: 'delete', cart_item_key: cartItemKey }, 'Item removed.');
          }
      };

      const handleQuantityChange = (cartItemKey, action) => {
          const item = cartData.items.find(i => i.cart_item_key === cartItemKey);
          if (!item) return;
          let quantity = parseInt(item.quantity, 10);
          quantity = action === 'increase' ? quantity + 1 : quantity - 1;

          if (quantity > 0) {
              handleApiAction('api/cart.php', { action: 'update', cart_item_key: cartItemKey, quantity: quantity }, 'Quantity updated.');
          } else {
              handleRemoveItem(cartItemKey);
          }
      };

      const handleTraySizeChange = (cartItemKey, newTraySize) => {
          handleApiAction('api/cart.php', { action: 'update_tray_size', cart_item_key: cartItemKey, tray_size: newTraySize }, 'Tray size updated.');
      };

      const handleApplyCoupon = () => {
          const couponCode = document.getElementById('coupon-code').value.trim();
          if (couponCode) {
              handleApiAction('api/apply_coupon.php', { coupon_code: couponCode }, 'Coupon applied!');
          } else {
              showAlert('warning', 'Please enter a coupon code.');
          }
      };

      const handleRemoveCoupon = () => {
          handleApiAction('api/remove_coupon.php', {}, 'Coupon removed.');
      };

      function renderPaymentMethod() {
        const paymentContainer = document.getElementById('payment-container');
        if (!paymentContainer) return;
        
        if (selectedPaymentMethod === 'paypal') {
            paymentContainer.innerHTML = '<div id="paypal-button-container"></div>';
            renderPayPalButton();
        } else { 
            paymentContainer.innerHTML = '<button id="cod-place-order" class="btn btn-primary btn-lg w-100">Place Order</button>';
            const btn = document.getElementById('cod-place-order');
            if(btn) btn.addEventListener('click', handleCodOrder);
        }
      }

      async function handleCodOrder() {
        if (!document.getElementById('agreeToTerms').checked) { showAlert('warning', 'Please agree to the Return & Refund Policy.'); return; }
        if (!selectedAddressId) { showAlert('warning', 'Please select a shipping address.'); return; }
        
        const btn = document.getElementById('cod-place-order');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Placing Order...';

        try {
            const response = await fetch('api/cod_create_order.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ shipping_address_id: selectedAddressId, payment_method: 'cod', vehicle_type: cartData.meta.vehicle_type }) });
            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = 'order_confirmation.php';
            } else {
                showAlert('danger', result.message || 'Could not place order.');
                btn.disabled = false; btn.innerText = 'Place Order';
            }
        } catch(e) {
            showAlert('danger', 'Could not connect to place order.');
            btn.disabled = false; btn.innerText = 'Place Order';
        }
      }

      function renderPayPalButton() {
          if (typeof paypal === 'undefined') return;
          if (paypal.Buttons.instances) paypal.Buttons.instances.forEach(i => i.close());
          
          paypal.Buttons({
              onClick: (data, actions) => {
                  if (!document.getElementById('agreeToTerms').checked) {
                      showAlert('warning', 'Please agree to the Return & Refund Policy first.');
                      return actions.reject();
                  }
                  if (!selectedAddressId) {
                      showAlert('warning', 'Please select a shipping address.');
                      return actions.reject();
                  }
              },
              createOrder: () => fetch('api/paypal_create_order.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ vehicle_type: cartData.meta.vehicle_type, shipping_address_id: selectedAddressId }) }).then(res => res.json()).then(order => order.id),
              onApprove: (data) => {
                  document.getElementById('payment-container').innerHTML = '<div class="text-center"><span class="spinner-border"></span> Processing...</div>';
                  return fetch('api/paypal_capture_order.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ orderID: data.orderID, shipping_address_id: selectedAddressId }) })
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
              onError: () => { showAlert('danger', 'An error occurred with PayPal. Please try another method.'); renderPaymentMethod(); }
          }).render('#paypal-button-container').catch(() => {
              showAlert('warning', 'PayPal is currently unavailable. Please try another payment method.');
          });
      }

      fetchCartAndAddresses();
  });
  </script>
</body>
</html>
