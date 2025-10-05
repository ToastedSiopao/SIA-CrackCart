<?php
require_once 'session_handler.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Producers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.9" rel="stylesheet">
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const producersContainer = document.getElementById('producersContainer');

      // Fetch producers from the API
      fetch('api/producers.php')
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            producersContainer.innerHTML = '' // Clear existing content
            data.data.forEach(producer => {
              const areAllProductsOutOfStock = producer.products.every(p => p.stock <= 0);
              const producerCard = `
                <div class="col-12 col-md-4 col-lg-3 producer-item">
                  <div class="producer-card">
                    <img src="${producer.logo}" class="producer-logo" alt="${producer.name}">
                    <h5 class="fw-bold">${producer.name}</h5>
                    <p class="text-muted">${producer.location}</p>
                    <div class="price-list mb-3">
                      <h6 class="fw-bold mb-2">Available Products:</h6>
                      ${producer.products.map(product => `
                        <div class="d-flex justify-content-between ${product.stock > 0 ? '' : 'text-muted'}">
                          <span class="small">${product.type}</span>
                           <div class="d-flex flex-column align-items-end">
                                <span class="price-tag small">₱${product.price.toFixed(2)} / 30-pc tray</span>
                                ${product.stock > 0 ? `<span class="small text-success">In Stock: ${product.stock}</span>` : '<span class="small text-danger">Out of Stock</span>'}
                            </div>
                        </div>
                      `).join('')}
                    </div>
                    <button class="btn btn-warning order-btn" 
                            data-producer-id="${producer.producer_id}"
                            ${areAllProductsOutOfStock ? 'disabled' : ''}>
                      ${areAllProductsOutOfStock ? 'Out of Stock' : 'Order From Here'}
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
          const producerId = e.target.dataset.producerId;
          window.location.href = `order.php?producer_id=${producerId}`;
        }
      });
    });
  </script>
</body>
</html>
