<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

include("../db_connect.php");

// This page will now be powered by the API, but we keep the PHP structure.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Producers</title>
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

      <div class="col p-4">
        <h3 class="mb-4 text-warning fw-bold">Producers</h3>

        <div class="row g-4" id="producersContainer">
          <!-- Producers will be loaded here by JavaScript -->
        </div>
      </div>
    </div>
  </div>

  <!-- Toast for notifications -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
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
                    <div class="price-list">
                      <h6 class="fw-bold mb-2">Products:</h6>
                      ${producer.products.map(product => `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <div>
                            <span class="small">${product.type}</span>
                            <span class="price-tag">₱${product.price.toFixed(2)}</span>
                          </div>
                          <button class="btn btn-success btn-sm add-to-cart-btn" 
                                  data-producer-id="${producer.producer_id}"
                                  data-product-type="${product.type}"
                                  data-price="${product.price}">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      `).join('')}
                    </div>
                    <a href="${producer.url}" target="_blank" class="btn btn-producer mt-3">
                      <i class="bi bi-link me-1"></i>Visit Page
                    </a>
                  </div>
                </div>
              `;
              producersContainer.innerHTML += producerCard;
            });
          }
        });

      // Event delegation for add to cart buttons
      producersContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
          const button = e.target.closest('.add-to-cart-btn');
          const producerId = button.dataset.producerId;
          const productType = button.dataset.productType;
          const price = button.dataset.price;

          const cartData = {
            producer_id: producerId,
            product_type: productType,
            price: parseFloat(price),
            quantity: 1 // Default quantity
          };

          fetch('api/cart.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(cartData)
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              cartToast.show(); // Show success notification
              // Optionally, update a cart icon counter here
            } else {
              alert('Error: ' + data.message);
            }
          });
        }
      });
    });
  </script>
</body>
</html>