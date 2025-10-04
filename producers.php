<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// User info from session - updated to match your new session variables
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Connect DB
include("db_connect.php");

// Producer data with prices (you can later move this to a database)
$producers = [
    [
        'name' => 'San Miguel Egg Farm',
        'location' => 'Bulacan, Philippines',
        'logo' => 'https://scontent.fmnl3-2.fna.fbcdn.net/v/t39.30808-6/309197041_397533832570608_2852504124934330080_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeHQAPfsIN59elLsZq6GgMkGtqGVNb0xigq2oZU1vTGKCjQEN2IV6VJS3bcuZzVX_1vGNoFrIf-yEPiyv_e-s-WU&_nc_ohc=Kjracd8B_ZsQ7kNvwEIM2bV&_nc_oc=Adlo_7JOBJAxRwQ-675ShEefHtRiSs0g6L2VYML5UKnDjJ1aBvmJ4HNnXv2bRf-zsr0&_nc_zt=23&_nc_ht=scontent.fmnl3-2.fna&_nc_gid=dntFvBv9oa901C-sCTD3yA&oh=00_AfYBGO7xemNLZmUoZFlxkurMV50-2iS9OPq8hKWMq-0lzw&oe=68C06DE4',
        'url' => 'https://www.facebook.com/sanmiguelgamefarm',
        'prices' => [
            ['type' => 'Regular Eggs', 'price' => '₱7.50', 'per' => 'per piece'],
            ['type' => 'Free-range Eggs', 'price' => '₱12.00', 'per' => 'per piece'],
            ['type' => 'Jumbo Eggs', 'price' => '₱9.00', 'per' => 'per piece']
        ]
    ],
    // ... (rest of the producers array) ...
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Producers</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="dashboard-styles.css?v=2.5" rel="stylesheet">
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <!-- Main Content -->
      <div class="col p-4">
        <h3 class="mb-4 text-warning fw-bold">Producers</h3>
        
        <!-- Search and Filter -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="search-box">
              <input type="text" class="form-control" id="searchInput" placeholder="Search producers...">
            </div>
          </div>
          <div class="col-md-6">
            <div class="filter-buttons">
              <button class="btn btn-outline-primary filter-btn" data-filter="all">All</button>
              <button class="btn btn-outline-secondary filter-btn" data-filter="cheap">Budget (Under ₱8)</button>
              <button class="btn btn-outline-secondary filter-btn" data-filter="premium">Premium (₱10+)</button>
              <button class="btn btn-outline-secondary filter-btn" data-filter="organic">Organic</button>
            </div>
          </div>
        </div>

        <div class="row g-4" id="producersContainer">
          <?php foreach ($producers as $producer): ?>
          <div class="col-12 col-md-4 col-lg-3 producer-item">
            <div class="producer-card">
              <img src="<?php echo htmlspecialchars($producer['logo']); ?>" class="producer-logo" alt="<?php echo htmlspecialchars($producer['name']); ?>">
              <h5 class="fw-bold"><?php echo htmlspecialchars($producer['name']); ?></h5>
              <p class="text-muted"><?php echo htmlspecialchars($producer['location']); ?></p>
              
              <!-- Price List -->
              <div class="price-list">
                <h6 class="fw-bold mb-2">Prices:</h6>
                <?php foreach ($producer['prices'] as $price): ?>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small"><?php echo htmlspecialchars($price['type']); ?></span>
                  <span class="price-tag"><?php echo htmlspecialchars($price['price']); ?></span>
                </div>
                <div class="text-end mb-2">
                  <small class="text-muted"><?php echo htmlspecialchars($price['per']); ?></small>
                </div>
                <?php endforeach; ?>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="<?php echo htmlspecialchars($producer['url']); ?>" target="_blank" class="btn btn-producer">
                  <i class="bi bi-link me-1"></i>Visit Page
                </a>
                <button class="btn btn-success btn-sm order-btn" data-producer="<?php echo htmlspecialchars($producer['name']); ?>">
                  <i class="bi bi-cart me-1"></i>Order
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const producerItems = document.querySelectorAll('.producer-item');
      
      producerItems.forEach(item => {
        const producerName = item.querySelector('h5').textContent.toLowerCase();
        if (producerName.includes(searchTerm)) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });

    // Filter functionality
    document.querySelectorAll('.filter-btn').forEach(button => {
      button.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        const producerItems = document.querySelectorAll('.producer-item');
        
        producerItems.forEach(item => {
          const prices = item.querySelectorAll('.price-tag');
          let showItem = false;
          
          switch(filter) {
            case 'all':
              showItem = true;
              break;
            case 'cheap':
              prices.forEach(price => {
                const priceValue = parseFloat(price.textContent.replace('₱', ''));
                if (priceValue < 8) showItem = true;
              });
              break;
            case 'premium':
              prices.forEach(price => {
                const priceValue = parseFloat(price.textContent.replace('₱', ''));
                if (priceValue >= 10) showItem = true;
              });
              break;
            case 'organic':
              const producerText = item.textContent.toLowerCase();
              if (producerText.includes('organic') || producerText.includes('native')) showItem = true;
              break;
          }
          
          item.style.display = showItem ? 'block' : 'none';
        });
        
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
      });
    });

    // Order button functionality
    document.querySelectorAll('.order-btn').forEach(button => {
      button.addEventListener('click', function() {
        const producerName = this.getAttribute('data-producer');
        alert('Ordering from: ' + producerName + '\nRedirecting to order page...');
        // You can redirect to order page with producer parameter
        window.location.href = 'order.php?producer=' + encodeURIComponent(producerName);
      });
    });
  </script>
</body>
</html>