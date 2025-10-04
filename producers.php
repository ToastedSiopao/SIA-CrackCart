<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

include("../db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Producers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.8" rel="stylesheet">
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <div class="col p-4">
        <h3 class="mb-4 text-warning fw-bold">Choose a Producer</h3>

        <div class="row g-4" id="producersContainer">
          <!-- Producers will be loaded here by JavaScript -->
        </div>
      </div>
    </div>
  </div>

  <!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderModalLabel">Order from [Producer Name]</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="orderForm">
          <input type="hidden" id="producerId" name="producer_id">
          <div class="mb-3">
            <label for="productSelect" class="form-label">Egg Size & Price</label>
            <p class="form-text mt-0 mb-2">Prices are based on a standard 30-piece tray.</p>
            <select class="form-select" id="productSelect" name="product" required>
              <!-- Product options will be populated here -->
            </select>
          </div>
          <div class="mb-3">
            <label for="quantityInput" class="form-label">Quantity of Trays</label>
            <input type="number" class="form-control" id="quantityInput" name="quantity" min="1" value="1" required>
          </div>
           <div class="mb-3">
            <label for="notesInput" class="form-label">Special Instructions (Optional)</label>
            <textarea class="form-control" id="notesInput" name="notes" rows="2" placeholder="e.g., Please select the freshest batch available."></textarea>
          </div>
          <button type="submit" class="btn btn-warning w-100">Add to Cart</button>
        </form>
      </div>
    </div>
  </div>
</div>


  <!-- Toast for notifications -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="cartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto">CrackCart</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        Item added to cart successfully!
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const producersContainer = document.getElementById('producersContainer');
      const cartToast = new bootstrap.Toast(document.getElementById('cartToast'));
      const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
      const orderModalLabel = document.getElementById('orderModalLabel');
      const productSelect = document.getElementById('productSelect');
      const producerIdInput = document.getElementById('producerId');
      const orderForm = document.getElementById('orderForm');

      // Fetch producers from the API
      fetch('api/producers.php')
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            producersContainer.innerHTML = '' // Clear existing content
            data.data.forEach(producer => {
              const producerCard = `
                <div class="col-12 col-md-4 col-lg-3 producer-item">
                  <div class="producer-card">
                    <img src="${producer.logo}" class="producer-logo" alt="${producer.name}">
                    <h5 class="fw-bold">${producer.name}</h5>
                    <p class="text-muted">${producer.location}</p>
                    <div class="price-list mb-3">
                      <h6 class="fw-bold mb-2">Available Products:</h6>
                      ${producer.products.map(product => `
                        <div class="d-flex justify-content-between">
                          <span class="small">${product.type}</span>
                          <span class="price-tag small">₱${product.price.toFixed(2)} / 30-pc tray</span>
                        </div>
                      `).join('')}
                    </div>
                    <button class="btn btn-warning order-btn" 
                            data-producer-id="${producer.producer_id}"
                            data-producer-name="${producer.name}"
                            data-products='${JSON.stringify(producer.products)}'>
                      Order From Here
                    </button>
                  </div>
                </div>
              `;
              producersContainer.innerHTML += producerCard;
            });
          }
        });

      // Event delegation for "Order From Here" buttons
      producersContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('order-btn')) {
          const button = e.target;
          const producerId = button.dataset.producerId;
          const producerName = button.dataset.producerName;
          const products = JSON.parse(button.dataset.products);

          // Populate and show the modal
          orderModalLabel.textContent = `Order from ${producerName}`;
          producerIdInput.value = producerId;
          
          productSelect.innerHTML = ''; // Clear previous options
          products.forEach(product => {
            const option = document.createElement('option');
            option.value = JSON.stringify({ type: product.type, price: product.price });
            option.textContent = `${product.type} Eggs - ₱${product.price.toFixed(2)} / tray`;
            productSelect.appendChild(option);
          });
          
          orderModal.show();
        }
      });

      // Handle form submission inside the modal
      orderForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const producerId = producerIdInput.value;
        const quantity = document.getElementById('quantityInput').value;
        const notes = document.getElementById('notesInput').value;
        const selectedProduct = JSON.parse(productSelect.value);

        const cartData = {
          producer_id: producerId,
          product_type: selectedProduct.type,
          price: selectedProduct.price,
          quantity: parseInt(quantity),
          notes: notes
        };

        fetch('api/cart.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(cartData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            orderModal.hide();
            cartToast.show();
          } else {
            alert('Error: ' + data.message);
          }
        });
      });
    });
  </script>
</body>
</html>