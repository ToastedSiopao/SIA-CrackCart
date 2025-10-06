<?php
require_once 'session_handler.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="dashboard-styles.css?v=3.3" rel="stylesheet">
  <style>
    .producer-card-new {
        background-color: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .producer-card-new:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .producer-banner {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    .producer-info {
        padding: 20px;
    }
    .product-list {
        padding: 0 20px 20px;
    }
    .product-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .product-item:last-child {
        margin-bottom: 0;
        border-bottom: none;
    }
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        margin-right: 15px;
    }
    .product-details {
        flex-grow: 1;
    }
    .star-rating {
        cursor: pointer;
    }
    .star-rating .bi-star-fill {
        color: #ffc107;
    }
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
          <h2 class="text-dark fw-bold">Our Producers</h2>
          <p class="text-muted">Fresh eggs from trusted local farms.</p>
        </header>

        <!-- Filter Controls -->
        <div class="row mb-4 g-3">
          <div class="col-md-4">
            <label for="categoryFilter" class="form-label">Filter by Category</label>
            <select id="categoryFilter" class="form-select">
              <option value="">All Categories</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row g-4" id="producersContainer">
          <!-- Producers will be loaded here -->
        </div>
      </main>
    </div>
  </div>

  <!-- Reviews Modal -->
  <div class="modal fade" id="reviewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reviewsModalTitle">Product Reviews</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="reviewsModalBody">
          <!-- Reviews will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const producersContainer = document.getElementById('producersContainer');
      const categoryFilter = document.getElementById('categoryFilter');
      const reviewsModal = new bootstrap.Modal(document.getElementById('reviewsModal'));

      const fetchAndRenderProducers = async () => {
        const category = categoryFilter.value;
        const url = `api/producers.php${category ? '?category=' + encodeURIComponent(category) : ''}`;
        
        producersContainer.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-warning"></div></div>';

        try {
            const response = await fetch(url);
            const result = await response.json();

            if (result.status === 'success' && result.data.length > 0) {
                producersContainer.innerHTML = '';
                for (const producer of result.data) {
                    const producerCard = await createProducerCard(producer);
                    producersContainer.innerHTML += producerCard;
                }
            } else {
                producersContainer.innerHTML = '<p class="text-center text-muted">No producers found for this category.</p>';
            }
        } catch (error) {
            producersContainer.innerHTML = '<p class="text-center text-danger">Failed to load producers.</p>';
            console.error('Fetch Error:', error);
        }
      };

      const getReviewSummary = async (productType) => {
          try {
              const response = await fetch(`api/get_reviews.php?product_type=${encodeURIComponent(productType)}`);
              const result = await response.json();
              if (result.status === 'success') {
                  return result.data;
              }
          } catch (error) {
              console.error('Error fetching reviews:', error);
          }
          return { average_rating: 0, total_reviews: 0, reviews: [] };
      };

      const createProducerCard = async (producer) => {
        let productsHtml = '';
        for (const product of producer.products) {
            const reviewSummary = await getReviewSummary(product.type);
            const avgRating = reviewSummary.average_rating.toFixed(1);
            const totalReviews = reviewSummary.total_reviews;
            const starRatingHtml = getStarRating(reviewSummary.average_rating);

            productsHtml += `
                <div class="product-item">
                    <img src="${getCorrectedImagePath(product.image_url)}" alt="${product.type}" class="product-image">
                    <div class="product-details">
                        <h6 class="mb-0">${product.type}</h6>
                        <p class="mb-1 small text-muted">₱${product.price.toFixed(2)} / tray</p>
                        <div class="d-flex justify-content-between align-items-center">
                             <div class="star-rating small" title="${avgRating} out of 5 stars" data-product-type="${product.type}" data-bs-toggle="modal" data-bs-target="#reviewsModal">
                                 ${starRatingHtml} <span class="text-muted">(${totalReviews})</span>
                             </div>
                             ${product.stock > 0 ? `<span class="badge bg-success-light text-success">In Stock</span>` : '<span class="badge bg-danger-light text-danger">Out of Stock</span>'}
                        </div>
                    </div>
                </div>
            `;
        }

        const bannerPath = `assets/${producer.name.toLowerCase().replace(/\s+/g, '_')}.jpg`;
        const correctedBannerPath = getCorrectedImagePath(bannerPath);

        return `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="producer-card-new">
                    <img src="${correctedBannerPath}" alt="${producer.name} Banner" class="producer-banner">
                    <div class="producer-info">
                        <h4 class="fw-bold mb-1">${producer.name}</h4>
                        <p class="text-muted small"><i class="bi bi-geo-alt-fill"></i> ${producer.location}</p>
                    </div>
                    <div class="product-list">${productsHtml}</div>
                    <div class="p-3 bg-light">
                         <a href="order.php?producer_id=${producer.producer_id}" class="btn btn-warning w-100 fw-bold">Shop Now</a>
                    </div>
                </div>
            </div>
        `;
      };
      
      const getCorrectedImagePath = (path) => {
          return path.replace('assets/images', 'assets');
      };

      const getStarRating = (rating) => {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) stars += '<i class="bi bi-star-fill"></i>';
            else if (i - 0.5 <= rating) stars += '<i class="bi bi-star-half"></i>';
            else stars += '<i class="bi bi-star"></i>';
        }
        return stars;
      };

      document.getElementById('producersContainer').addEventListener('click', async (e) => {
        const starRatingEl = e.target.closest('.star-rating');
        if (starRatingEl) {
            const productType = starRatingEl.dataset.productType;
            document.getElementById('reviewsModalTitle').innerText = `Reviews for ${productType}`;
            const modalBody = document.getElementById('reviewsModalBody');
            modalBody.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';

            const reviewData = await getReviewSummary(productType);

            if (reviewData.reviews.length > 0) {
                let reviewsHtml = reviewData.reviews.map(review => {
                    const reviewRatingHtml = getStarRating(review.rating);
                    return `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h6 class="card-title">${review.username}</h6>
                                    <small class="text-muted">${new Date(review.created_at).toLocaleDateString()}</small>
                                </div>
                                <div class="mb-2">${reviewRatingHtml}</div>
                                <p class="card-text">${review.review_text}</p>
                            </div>
                        </div>
                    `;
                }).join('');
                modalBody.innerHTML = reviewsHtml;
            } else {
                modalBody.innerHTML = '<p class="text-center text-muted">No reviews yet for this product.</p>';
            }
        }
      });

      categoryFilter.addEventListener('change', fetchAndRenderProducers);
      fetchAndRenderProducers(); // Initial load
    });
  </script>
</body>
</html>
