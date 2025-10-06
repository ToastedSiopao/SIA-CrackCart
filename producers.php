<?php
require_once 'session_handler.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

require_once 'db_connect.php';
$categories = [];
$min_price = 0;    // Always start at 0
$max_price = 5000; // Use a fixed, large number for the maximum

try {
    // Fetch product categories (types) for the dropdown
    $category_sql = "SELECT DISTINCT TYPE FROM PRICE WHERE STATUS = 'active' ORDER BY TYPE ASC";
    $category_result = $conn->query($category_sql);
    if ($category_result) {
        while ($row = $category_result->fetch_assoc()) {
            $categories[] = $row['TYPE'];
        }
    }

} catch (Exception $e) {
    error_log('Error fetching page data: ' . $e->getMessage());
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="dashboard-styles.css?v=3.9" rel="stylesheet"> 
  <style>
    .product-card { transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
    .star-rating .bi-star-fill, .star-rating .bi-star-half { color: #ffc107; }
    .star-rating .bi-star { color: #e4e5e9; }
    .badge.bg-success-light { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .badge.bg-danger-light { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .noUi-connect { background: #ffc107; }
    .noUi-handle { border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
  </style>
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col p-4">
        <header class="mb-4">
          <h2 class="text-dark fw-bold">Our Products</h2>
          <p class="text-muted">Fresh eggs from trusted local farms.</p>
        </header>

        <div class="card card-body mb-4 p-3 shadow-sm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="categoryFilter" class="form-label fw-bold">Category</label>
                    <select id="categoryFilter" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Price Range</label>
                    <div id="price-slider"></div>
                    <div id="price-slider-values" class="mt-1 text-center"></div>
                </div>
                <div class="col-md-2">
                    <label for="sizeFilter" class="form-label fw-bold">Tray Size</label>
                    <div id="sizeFilter">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="size12" value="12">
                            <label class="form-check-label" for="size12">12</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="size30" value="30">
                            <label class="form-check-label" for="size30">30</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="sortBy" class="form-label fw-bold">Sort By</label>
                    <select id="sortBy" class="form-select">
                        <option value="popularity">Popularity</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="name_asc">Name: A to Z</option>
                        <option value="name_desc">Name: Z to A</option>
                        <option value="newness">Newest</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="productsContainer" class="row g-4"></div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const productsContainer = document.getElementById('productsContainer');
      const categoryFilter = document.getElementById('categoryFilter');
      const sizeCheckboxes = document.querySelectorAll('#sizeFilter input[type="checkbox"]');
      const sortBy = document.getElementById('sortBy');
      const priceSlider = document.getElementById('price-slider');
      const priceSliderValues = document.getElementById('price-slider-values');
      const minPrice = <?php echo $min_price; ?>;
      const maxPrice = <?php echo $max_price; ?>;

      noUiSlider.create(priceSlider, {
          start: [minPrice, maxPrice],
          connect: true,
          range: { 'min': minPrice, 'max': maxPrice },
          step: 50,
          format: {
              to: value => '₱' + Math.round(value),
              from: value => Number(value.replace('₱', ''))
          }
      });

      priceSlider.noUiSlider.on('update', (values) => {
          priceSliderValues.innerHTML = `${values[0]} - ${values[1]}`;
      });

      const fetchAndRenderProducts = async () => {
        const category = categoryFilter.value;
        const sortByValue = sortBy.value;
        const [minPriceVal, maxPriceVal] = priceSlider.noUiSlider.get(true);
        const selectedSizes = Array.from(sizeCheckboxes).filter(cb => cb.checked).map(cb => cb.value);

        let query = new URLSearchParams();
        if (category) query.append('category', category);
        if (sortByValue) query.append('sort', sortByValue);
        query.append('min_price', minPriceVal);
        query.append('max_price', maxPriceVal);
        if (selectedSizes.length > 0) query.append('sizes', selectedSizes.join(','));
        
        const url = `api/producers.php?${query.toString()}`;
        
        productsContainer.innerHTML = '<div class="col-12 text-center p-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        try {
            const response = await fetch(url);
            const result = await response.json();

            if (result.status === 'success' && result.data.length > 0) {
                productsContainer.innerHTML = result.data.map(createProductCard).join('');
            } else {
                productsContainer.innerHTML = '<div class="col-12"><p class="text-center text-muted">No products found matching your criteria.</p></div>';
            }
        } catch (error) {
            productsContainer.innerHTML = '<div class="col-12"><p class="text-center text-danger">Failed to load products. Please try again later.</p></div>';
            console.error('Fetch Error:', error);
        }
      };

      const createProductCard = (product) => {
        const starRatingHtml = getStarRating(product.avg_rating);
        const correctedImagePath = product.image_url.replace('assets/images', 'assets');

        return `
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 shadow-sm border-0 product-card">
                    <img src="${correctedImagePath}" class="card-img-top" alt="${product.product_type}" style="height: 180px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${product.product_type}</h5>
                        <p class="card-text text-muted small">Sold by: ${product.producer.name}</p>
                        <div class="mt-auto">
                             <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="card-text fw-bold fs-5 mb-0 text-success">₱${product.price.toFixed(2)}</p>
                                <div class="star-rating small" title="${product.avg_rating.toFixed(1)} out of 5 stars">
                                    ${starRatingHtml} <span class="text-muted">(${product.total_reviews})</span>
                                </div>
                            </div>
                            ${product.stock > 0 ? `<span class="badge bg-success-light text-success">In Stock: ${product.stock}</span>` : '<span class="badge bg-danger-light text-danger">Out of Stock</span>'}
                            <a href="order.php?producer_id=${product.producer.id}&product_type=${encodeURIComponent(product.product_type)}" class="btn btn-warning w-100 fw-bold mt-2 ${product.stock <= 0 ? 'disabled' : ''}">Order Now</a>
                        </div>
                    </div>
                </div>
            </div>`;
      };
      
      const getStarRating = (rating) => {
        let stars = '';
        const fullStars = Math.floor(rating);
        const halfStar = rating % 1 >= 0.5 ? 1 : 0;
        const emptyStars = 5 - fullStars - halfStar;
        for (let i = 0; i < fullStars; i++) stars += '<i class="bi bi-star-fill"></i>';
        if (halfStar) stars += '<i class="bi bi-star-half"></i>';
        for (let i = 0; i < emptyStars; i++) stars += '<i class="bi bi-star"></i>';
        return stars;
      };

      // Event Listeners for filters
      categoryFilter.addEventListener('change', fetchAndRenderProducts);
      sortBy.addEventListener('change', fetchAndRenderProducts);
      sizeCheckboxes.forEach(cb => cb.addEventListener('change', fetchAndRenderProducts));
      priceSlider.noUiSlider.on('end', fetchAndRenderProducts);
      
      // Initial load
      fetchAndRenderProducts();
    });
  </script>
</body>
</html>
