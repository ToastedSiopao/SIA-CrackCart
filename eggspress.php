<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, you can redirect them to the login page
    // For now, we'll just use placeholder values
    $user_name = "Guest";
} else {
    $user_name = $_SESSION['user_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eggspress: Secure Egg Transport</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      background-color: #fff;
      min-height: 100vh;
      border-right: 1px solid #eee;
    }
    .sidebar .nav-link {
      color: #333;
      font-weight: 500;
      margin-bottom: .3rem;
    }
    .sidebar .nav-link.active {
      background-color: #ffb703;
      color: #fff;
      border-radius: 8px;
    }
    .navbar-yellow {
      background-color: #ffeb3b;
    }
    .hero-section {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSUILGauMpq_eRsL7_h1Nv86l6uxmh2pSoQqg&s') no-repeat center center;
      background-size: cover;
      color: #fff;
      padding: 6rem 0;
      text-align: center;
    }
    .hero-section h1 {
      font-weight: bold;
      font-size: 3.5rem;
    }
    .service-tier {
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 2rem;
      margin-bottom: 2rem;
      background-color: #fff;
      transition: box-shadow 0.3s ease;
      height: 100%;
    }
    .service-tier:hover {
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .service-tier h3 {
      color: #ffb703;
      border-bottom: 2px solid #ffb703;
      padding-bottom: 0.5rem;
      margin-bottom: 1.5rem;
    }
    .popular-badge {
      background-color: #ffb703;
      color: #fff;
      padding: 0.3rem 0.8rem;
      border-radius: 50rem;
      font-size: 0.8rem;
      margin-left: 0.5rem;
    }
    .key-features .feature {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    .key-features .feature i {
      font-size: 2.5rem;
      color: #ffb703;
      margin-right: 1.5rem;
    }
    .how-it-works {
      background-color: #fff;
      padding: 4rem 0;
    }
    .testimonial {
      font-style: italic;
    }
    .cta-section {
      background-color: #ffb703;
      color: #fff;
      padding: 4rem 0;
      text-align: center;
    }
    .cta-section .btn {
      font-size: 1.25rem;
      padding: 0.8rem 2.5rem;
      background-color: #fff;
      color: #ffb703;
      border: 2px solid #fff;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-yellow shadow-sm px-3">
    <div class="container-fluid">
      <button class="btn btn-outline-dark d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <i class="bi bi-list"></i>
      </button>
      <a class="navbar-brand fw-bold" href="dashboard.php">CrackCart.</a>
      <div class="ms-auto d-flex align-items-center gap-4">
        <div class="dropdown">
            <a class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="me-2"><?php echo htmlspecialchars($user_name); ?></span>
                <i class="bi bi-person-circle fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profilePage.php">Profile</a></li>
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </div>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <!-- Sidebar -->
      <div class="col-auto col-md-3 col-lg-2 px-3 sidebar d-none d-md-block">
        <ul class="nav flex-column mb-auto mt-4">
          <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li><a href="orders.php" class="nav-link"><i class="bi bi-cart3 me-2"></i>Make an Order</a></li>
          <li><a href="eggspress.php" class="nav-link active"><i class="bi bi-truck me-2"></i> Eggspress</a></li>
          <li><a href="messages.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
          <li><a href="history.php" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
          <li><a href="bills.php" class="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
          <li><a href="profilePage.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
          <li><a href="producers.php" class="nav-link"><i class="bi bi-egg me-2"></i> Producers</a></li>
        </ul>
      </div>

      <!-- Offcanvas Sidebar for Mobile -->
      <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title">CrackCart.</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="nav flex-column mb-auto">
            <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="orders.php" class="nav-link"><i class="bi bi-cart3 me-2"></i> Order</a></li>
            <li><a href="eggspress.php" class="nav-link active"><i class="bi bi-truck me-2"></i> Eggspress</a></li>
            <li><a href="messages.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
            <li><a href="history.php" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
            <li><a href="bills.php" class="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
            <li><a href="settings.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
          </ul>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col p-0">
        <!-- Hero Section -->
        <section class="hero-section">
          <div class="container">
            <h1>Eggspress: Safe, Secure, and Speedy Egg Transport</h1>
            <p class="lead">From Farm to You, We Handle Your Eggs with Care.</p>
          </div>
        </section>

        <!-- Service Tiers -->
        <section class="container my-5">
          <div class="row">
            <div class="col-md-4 d-flex">
              <div class="service-tier">
                <h3>Standard Hatch</h3>
                <p>Reliable, cost-effective transport for your eggs. Perfect for local deliveries.</p>
                <ul>
                  <li>Climate-controlled transport</li>
                  <li>Standard packaging</li>
                  <li>24-hour delivery window</li>
                </ul>
                <form action="cart_functions.php" method="post">
                    <input type="hidden" name="service_tier" value="Standard Hatch">
                    <input type="hidden" name="price" value="10.00">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" name="quantity" value="1" min="1">
                        <button class="btn btn-primary" type="submit" name="add_to_shipment">Book Shipment</button>
                    </div>
                </form>
              </div>
            </div>
            <div class="col-md-4 d-flex">
              <div class="service-tier">
                <h3>Golden Yolks <span class="popular-badge">Most Popular</span></h3>
                <p>Our most popular option, offering enhanced protection and real-time tracking.</p>
                <ul>
                  <li>Climate-controlled transport</li>
                  <li><strong>Premium, shock-absorbent packaging</strong></li>
                  <li><strong>Real-time GPS tracking</strong></li>
                  <li><strong>12-hour delivery window</strong></li>
                </ul>
                <form action="cart_functions.php" method="post">
                    <input type="hidden" name="service_tier" value="Golden Yolks">
                    <input type="hidden" name="price" value="20.00">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" name="quantity" value="1" min="1">
                        <button class="btn btn-primary" type="submit" name="add_to_shipment">Book Shipment</button>
                    </div>
                </form>
              </div>
            </div>
            <div class="col-md-4 d-flex">
              <div class="service-tier">
                <h3>The Fabergé</h3>
                <p>The ultimate in egg transport. White-glove service for your most valuable shipments.</p>
                <ul>
                  <li><strong>Dedicated, climate-controlled transport</strong></li>
                  <li><strong>Custom-molded, high-impact packaging</strong></li>
                  <li><strong>Real-time GPS tracking with temperature monitoring</strong></li>
                  <li><strong>Guaranteed 4-hour delivery window</strong></li>
                </ul>
                <form action="cart_functions.php" method="post">
                    <input type="hidden" name="service_tier" value="The Fabergé">
                    <input type="hidden" name="price" value="50.00">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" name="quantity" value="1" min="1">
                        <button class="btn btn-primary" type="submit" name="add_to_shipment">Book Shipment</button>
                    </div>
                </form>
              </div>
            </div>
          </div>
        </section>

        <!-- Key Features -->
        <section class="container my-5 key-features">
          <div class="row">
            <div class="col-md-6">
              <div class="feature">
                <i class="fas fa-thermometer-half"></i>
                <div>
                  <h4>Temperature Controlled</h4>
                  <p>Our refrigerated trucks maintain a consistent temperature to ensure your eggs stay fresh from pickup to delivery.</p>
                </div>
              </div>
              <div class="feature">
                <i class="fas fa-egg"></i>
                <div>
                  <h4>Unbeatable Protection</h4>
                  <p>We use specialized, shock-absorbent packaging to minimize the risk of breakage.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="feature">
                <i class="fas fa-map-marked-alt"></i>
                <div>
                  <h4>Real-Time Tracking</h4>
                  <p>Know exactly where your shipment is with our live GPS tracking and delivery notifications.</p>
                </div>
              </div>
              <div class="feature">
                <i class="fas fa-shield-alt"></i>
                <div>
                  <h4>Fully Insured</h4>
                  <p>Your investment is protected. We offer comprehensive insurance on all shipments.</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- How It Works -->
        <section class="how-it-works">
          <div class="container">
            <h2 class="text-center mb-5">A Simple 3-Step Process</h2>
            <div class="row text-center">
              <div class="col-md-4">
                <i class="fas fa-book-reader fa-3x text-secondary mb-3"></i>
                <h4>1. Book Your Shipment</h4>
                <p>Choose your service tier and schedule a pickup time.</p>
              </div>
              <div class="col-md-4">
                <i class="fas fa-truck-loading fa-3x text-secondary mb-3"></i>
                <h4>2. We Handle the Rest</h4>
                <p>Our team will carefully package and load your eggs for transport.</p>
              </div>
              <div class="col-md-4">
                <i class="fas fa-box-check fa-3x text-secondary mb-3"></i>
                <h4>3. Track & Receive</h4>
                <p>Monitor your shipment in real-time and receive a notification upon delivery.</p>
              </div>
            </div>
          </div>
        </section>
        
        <!-- Testimonials -->
        <section class="container my-5">
          <h2 class="text-center mb-5">What Our Customers Are Saying</h2>
          <div class="row">
            <div class="col-md-6">
              <p class="testimonial">"Eggspress has been a game-changer for our farm. We've seen a 90% reduction in breakage since we started using their service."</p>
              <p><strong>- Happy Hen Farms</strong></p>
            </div>
            <div class="col-md-6">
              <p class="testimonial">"The real-time tracking is a lifesaver. I never have to wonder where my shipment is."</p>
              <p><strong>- The Breakfast Club Cafe</strong></p>
            </div>
          </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
          <div class="container">
            <h2>Ready to Ship with Confidence?</h2>
            <p class="lead my-4">Get a free, no-obligation quote for your next egg shipment.</p>
            <a href="#" class="btn">Get a Free Quote</a>
          </div>
        </section>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>