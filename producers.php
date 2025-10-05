<?php
require_once 'session_handler.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// --- Fetch Categories for Filter ---
require_once 'db_connect.php';
$categories = [];
try {
    $category_sql = "SELECT DISTINCT TYPE FROM PRICE WHERE STATUS = 'active' ORDER BY TYPE ASC";
    $category_result = $conn->query($category_sql);
    if ($category_result) {
        while ($row = $category_result->fetch_assoc()) {
            $categories[] = $row['TYPE'];
        }
    }
} catch (Exception $e) {
    // Log error if needed, but don't crash the page
    error_log('Error fetching categories: ' . $e->getMessage());
}
$conn->close();
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

        <!-- Filter Controls -->
        <div class="row mb-4 g-3">
          <div class="col-md-4">
            <label for="categoryFilter" class="form-label">Category</label>
            <select id="categoryFilter" class="form-select">
              <option value="">All</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label for="priceFilter" class="form-label">Max Price</label>
            <input type="number" id="priceFilter" class="form-control" placeholder="e.g., 500">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button id="filterBtn" class="btn btn-warning">Apply Filters</button>
          </div>
        </div>

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
      const categoryFilter = document.getElementById('categoryFilter');
      const priceFilter = document.getElementById('priceFilter');
      const filterBtn = document.getElementById('filterBtn');

      const fetchProducers = () => {
        const category = categoryFilter.value;
        const maxPrice = priceFilter.value;
        
        let queryString = '';
        if (category) {
            queryString += `category=${encodeURIComponent(category)}`;
        }
        if (maxPrice) {
            queryString += (queryString ? '&' : '') + `max_price=${encodeURIComponent(maxPrice)}`;
        }

        producersContainer.innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        fetch(`api/producers.php?${queryString}`)
            .then(response => response.json())
            .then(data => {
                producersContainer.innerHTML = ''; // Clear loading spinner
                if (data.status === 'success' && data.data.length > 0) {
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
                } else {
                     producersContainer.innerHTML = '<div class="col-12"><p class="text-center p-5 bg-light rounded">No producers match the current filters. Please try a different selection.</p></div>';
                }
            })
            .catch(error => {
                producersContainer.innerHTML = '<div class="col-12"><p class="text-center text-danger p-5 bg-light rounded">An error occurred while fetching data. Please try again later.</p></div>';
                console.error("Fetch Error:", error);
            });
      };

      filterBtn.addEventListener('click', fetchProducers);

      producersContainer.addEventListener('click', function(e) {
          if (e.target.classList.contains('order-btn')) {
              const producerId = e.target.dataset.producerId;
              window.location.href = `order.php?producer_id=${producerId}`;
          }
      });

      // Initial fetch on page load
      fetchProducers();
    });
  </script>
</body>
</html>