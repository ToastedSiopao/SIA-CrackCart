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
          <div class="row">
            <div class="col-12">
              <div class="card shadow-sm border-0 p-4">
                <div class="card-body" id="cartContainer">
                  <h2 class="card-title text-center mb-4">Your Product Cart</h2>
                  <!-- Cart content will be loaded here by JavaScript -->
                  <div class="text-center">
                    <div class="spinner-border" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
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

      const renderCart = (cartData) => {
        if (!cartData || cartData.items.length === 0) {
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

        const itemsHtml = cartData.items.map(item => `
          <tr>
            <td>${item.product_type}</td>
            <td class="text-center">
              <div class="d-inline-flex align-items-center">
                <input type="number" value="${item.quantity}" min="1" class="form-control form-control-sm update-quantity" data-cart-item-key="${item.cart_item_key}" style="width: 70px;">
              </div>
            </td>
            <td class="text-end">₱${item.price.toFixed(2)}</td>
            <td class="text-end">₱${(item.quantity * item.price).toFixed(2)}</td>
            <td class="text-center">
              <button class="btn btn-danger btn-sm remove-item" data-cart-item-key="${item.cart_item_key}">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        `).join('');

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
          <div class="text-end mt-4">
            <h3>Subtotal: ₱${cartData.subtotal.toFixed(2)}</h3>
            <a href="product_checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>
          </div>
          <div class="text-center mt-3">
            <a href="producers.php" class="btn btn-secondary">Continue Shopping</a>
          </div>
        `;
      };

      const fetchCart = () => {
        fetch('api/cart.php')
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              renderCart(data.data);
            } else {
              cartContainer.innerHTML = '<div class="alert alert-danger">Error loading cart.</div>';
            }
          });
      };

      cartContainer.addEventListener('change', function(e) {
        if (e.target.classList.contains('update-quantity')) {
          const cartItemKey = e.target.dataset.cartItemKey;
          const quantity = parseInt(e.target.value);

          fetch('api/cart.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_item_key: cartItemKey, quantity: quantity })
          }).then(() => fetchCart());
        }
      });

      cartContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
          const button = e.target.closest('.remove-item');
          const cartItemKey = button.dataset.cartItemKey;

          fetch('api/cart.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_item_key: cartItemKey })
          }).then(() => fetchCart());
        }
      });

      fetchCart(); // Initial cart load
    });
  </script>
</body>
</html>