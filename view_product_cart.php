<?php
session_start();
// Keep the session start for user authentication, but cart logic is now on the client-side.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Product Cart - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.6" rel="stylesheet">
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col ps-md-2 pt-2">
        <div class="container">
          <div class="row">
            <div class="col-12">
              <div class="card shadow-sm border-0 p-4">
                <div class="card-body" id="cartContainer">
                  <h2 class="card-title text-center mb-4">Your Product Cart</h2>
                  <div class="text-center">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                  </div>
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
      const cartContainer = document.getElementById('cartContainer');

      // Main function to render the entire cart view
      const renderCart = (cartData) => {
        if (!cartData || !cartData.items || cartData.items.length === 0) {
          cartContainer.innerHTML = `
            <h2 class="card-title text-center mb-4">Your Product Cart</h2>
            <div class="alert alert-info text-center" role="alert">
              Your product cart is empty. <a href="producers.php" class="alert-link">Browse products</a>.
            </div>
            <div class="text-center mt-3">
                <a href="producers.php" class="btn btn-secondary">Continue Shopping</a>
            </div>`;
          return;
        }

        // HTML for cart items
        const itemsHtml = cartData.items.map(item => `
          <tr>
            <td>${item.product_type}</td>
            <td class="text-center">
              <input type="number" value="${item.quantity}" min="1" class="form-control form-control-sm update-quantity" data-cart-item-key="${item.cart_item_key}" style="width: 70px; margin: auto;">
            </td>
            <td class="text-end">₱${parseFloat(item.price).toFixed(2)}</td>
            <td class="text-end">₱${(item.quantity * item.price).toFixed(2)}</td>
            <td class="text-center">
              <button class="btn btn-danger btn-sm remove-item" data-cart-item-key="${item.cart_item_key}"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        `).join('');

        // HTML for coupon and summary section
        const discountValue = cartData.applied_coupon ? parseFloat(cartData.applied_coupon.discount_value) : 0;
        const total = cartData.subtotal - discountValue;

        const couponHtml = cartData.applied_coupon ? `
          <div class="d-flex justify-content-between align-items-center">
              <p class="mb-0">Coupon Applied: <strong class="text-success">${cartData.applied_coupon.coupon_code}</strong></p>
              <button class="btn btn-sm btn-outline-danger" id="remove-coupon-btn">Remove</button>
          </div>
          <div id="coupon-message" class="mt-2"></div>
        ` : `
          <label for="coupon-code" class="form-label">Have a coupon?</label>
          <div class="input-group">
            <input type="text" class="form-control" id="coupon-code" placeholder="Enter coupon code">
            <button class="btn btn-primary" type="button" id="apply-coupon-btn">Apply</button>
          </div>
          <div id="coupon-message" class="mt-2"></div>
        `;

        cartContainer.innerHTML = `
          <h2 class="card-title text-center mb-4">Your Product Cart</h2>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th class="text-center">Quantity</th>
                  <th class="text-end">Price</th>
                  <th class="text-end">Total</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>${itemsHtml}</tbody>
            </table>
          </div>

          <div class="row justify-content-end mt-4">
            <div class="col-md-6">
              <div class="mb-3">
                ${couponHtml}
              </div>
              <div class="card bg-light p-3">
                <div class="d-flex justify-content-between">
                  <span>Subtotal:</span>
                  <span>₱${cartData.subtotal.toFixed(2)}</span>
                </div>
                ${cartData.applied_coupon ? `
                <div class="d-flex justify-content-between text-danger">
                  <span>Discount:</span>
                  <span>-₱${discountValue.toFixed(2)}</span>
                </div>
                ` : ''}
                <hr>
                <div class="d-flex justify-content-between fw-bold h5">
                  <span>Total:</span>
                  <span>₱${total.toFixed(2)}</span>
                </div>
              </div>
              <div class="d-grid mt-3">
                 <a href="product_checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>
              </div>
            </div>
          </div>
          
          <div class="text-center mt-3">
            <a href="producers.php" class="btn btn-secondary">Continue Shopping</a>
          </div>
        `;
      };

      // Function to fetch cart data from the server
      const fetchCart = () => {
        fetch('api/cart.php')
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              renderCart(data.data);
            } else {
              cartContainer.innerHTML = '<div class="alert alert-danger">Error loading cart. Please try again.</div>';
            }
          }).catch(() => {
            cartContainer.innerHTML = '<div class="alert alert-danger">Could not connect to the server.</div>';
          });
      };

      // --- Event Handlers ---
      const showCouponMessage = (message, isError = false) => {
        const msgDiv = document.getElementById('coupon-message');
        if (msgDiv) {
          msgDiv.textContent = message;
          msgDiv.className = isError ? 'text-danger small' : 'text-success small';
        }
      };

      const handleApplyCoupon = () => {
        const couponCode = document.getElementById('coupon-code').value.trim();
        if (!couponCode) {
          showCouponMessage('Please enter a coupon code.', true);
          return;
        }

        fetch('api/apply_coupon.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ coupon_code: couponCode })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            fetchCart(); // Reload the whole cart to show the discount
          } else {
            showCouponMessage(data.message, true);
          }
        }).catch(() => showCouponMessage('An error occurred. Please try again.', true));
      };

      const handleRemoveCoupon = () => {
        fetch('api/remove_coupon.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            fetchCart(); // Reload the cart to remove the discount
          } else {
            showCouponMessage(data.message || 'Could not remove coupon.', true);
          }
        }).catch(() => showCouponMessage('An error occurred. Please try again.', true));
      };

      // Main event listener for clicks and changes
      cartContainer.addEventListener('click', function(e) {
        if (e.target.id === 'apply-coupon-btn') handleApplyCoupon();
        if (e.target.id === 'remove-coupon-btn') handleRemoveCoupon();

        const removeItemButton = e.target.closest('.remove-item');
        if (removeItemButton) {
          const cartItemKey = removeItemButton.dataset.cartItemKey;
          fetch('api/cart.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_item_key: cartItemKey })
          }).then(() => fetchCart()); // Always refresh after action
        }
      });

      cartContainer.addEventListener('change', function(e) {
        if (e.target.classList.contains('update-quantity')) {
          const cartItemKey = e.target.dataset.cartItemKey;
          const quantity = parseInt(e.target.value);
          if (quantity > 0) {
              fetch('api/cart.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_item_key: cartItemKey, quantity: quantity })
              }).then(() => fetchCart()); // Always refresh after action
          }
        }
      });

      // Initial load
      fetchCart();
    });
  </script>
</body>
</html>
