<?php
if (isset($_GET['reason']) && $_GET['reason'] === 'inactive') {
    echo "<script>alert('You have been logged out due to inactivity.');</script>";
}
?>
 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CrackCart - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
  <!-- Header Logo -->
  <div class="header-logo">
    <a href="index.php"><img src="assets/Logo.png" alt="CrackCart Logo" class="logo-img"></a>
  </div>

  <div class="container-fluid h-100">
    <div class="row h-100 align-items-center justify-content-center">
      <!-- Carousel Section -->
      <div class="col-lg-5 col-md-6 mb-4 mb-md-0">
        <div class="carousel-container">
          <div id="promoCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="carousel-content">
                  <i class="fas fa-shopping-cart mb-3"></i>
                  <h3>Welcome to CrackCart</h3>
                  <p>Your one-stop destination for all your shopping needs. Discover amazing products at unbeatable prices.</p>
                </div>
              </div>
              <div class="carousel-item">
                <div class="carousel-content">
                  <i class="fas fa-truck mb-3"></i>
                  <h3>Fast Delivery</h3>
                  <p>Get your orders delivered quickly and safely. We partner with the best logistics providers for your convenience.</p>
                </div>
              </div>
              <div class="carousel-item">
                <div class="carousel-content">
                  <i class="fas fa-shield-alt mb-3"></i>
                  <h3>Secure Shopping</h3>
                  <p>Shop with confidence knowing your data and transactions are protected with industry-leading security measures.</p>
                </div>
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
            </button>
          </div>
        </div>
      </div>

      <!-- Login Section -->
      <div class="col-lg-4 col-md-5">
        <div class="login-container">
          <div class="text-center mb-4">
            <a href="index.php"><img src="assets/Truck.png" alt="Truck Logo" class="truck-logo"></a>
            <h2 class="login-title">Welcome Back</h2>
            <p class="text-muted">Sign in to your account</p>
          </div>

          <!-- Error/Success Messages -->
          <div id="formFeedback"></div>

          <form id="loginForm" novalidate>
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Enter your email" required>
              </div>
            </div>

            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter your password" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
              <label class="form-check-label" for="rememberMe">
                Remember me
              </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
              <span class="loading-spinner"></span>
              <span class="button-text">LOGIN</span>
            </button>

            <div class="text-center">
              <a href="#" class="forgot-link">Forgot your password?</a>
            </div>

            <hr class="my-4">

            <div class="text-center">
              <p class="mb-2">Don't have an account?</p>
              <a href="signup.php" class="btn btn-outline-primary w-100">
                <i class="fas fa-user-plus me-2"></i>CREATE ACCOUNT
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>