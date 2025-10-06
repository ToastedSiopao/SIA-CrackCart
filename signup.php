<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CrackCart - Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #FFD500;
      --secondary-color: #333;
      --light-color: #f8f9fa;
      --dark-color: #212529;
      --border-radius: 12px;
      --box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    body.login-page {
      background-color: var(--light-color);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }
    .header-logo {
      position: absolute;
      top: 20px;
      left: 20px;
    }
    .logo-img {
      width: 150px;
    }
    .login-container {
      background-color: #fff;
      padding: 40px;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      width: 100%;
      max-width: 450px;
    }
    .truck-logo {
      width: 80px;
      margin-bottom: 20px;
    }
    .login-title {
      font-weight: 700;
      color: var(--dark-color);
    }
    .carousel-container {
      background-color: var(--dark-color);
      color: #fff;
      padding: 40px;
      border-radius: var(--border-radius);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
    }
    .carousel-content {
      text-align: center;
    }
    .carousel-content i {
      font-size: 3rem;
      color: var(--primary-color);
    }
    .carousel-content h3 {
      font-size: 2rem;
      margin-top: 20px;
    }
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      color: var(--dark-color);
      font-weight: 600;
    }
    .btn-primary:hover {
      background-color: #ffc107;
      border-color: #ffc107;
    }
  </style>
</head>
<body class="login-page">
  <!-- Header Logo -->
  <div class="header-logo">
    <a href="index.php"><img src="assets/Logo.png" alt="CrackCart Logo" class="logo-img"></a>
  </div>

  <div class="container-fluid h-100">
    <div class="row h-100 align-items-center justify-content-center">
      <!-- Carousel Section -->
      <div class="col-lg-5 col-md-6 mb-4 mb-md-0 d-none d-md-block">
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

      <!-- Signup Section (Styled like Login) -->
      <div class="col-lg-4 col-md-5">
        <div class="login-container"> <!-- Using login-container for consistent styling -->
          <div class="text-center mb-4">
            <a href="index.php"><img src="assets/Truck.png" alt="Truck Logo" class="truck-logo"></a>
            <h2 class="login-title">Create Account</h2> <!-- Changed title -->
            <p class="text-muted">Fill in your details to get started</p>
          </div>

          <!-- Error/Success Messages -->
          <div id="formFeedback"></div>

          <form id="signupForm" novalidate>
            <!-- Name Fields -->
            <div class="mb-2">
              <label class="form-label visually-hidden">First Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" name="firstName" placeholder="First name" required>
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label visually-hidden">Middle Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" name="middleName" placeholder="Middle name">
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label visually-hidden">Last Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" name="lastName" placeholder="Last name" required>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label visually-hidden">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label visually-hidden">Phone</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="tel" class="form-control" name="phone" placeholder="Phone" required>
              </div>
            </div>
            
            <div class="mb-2">
              <label class="form-label visually-hidden">House No.</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-home"></i></span>
                <input type="text" class="form-control" name="houseNo" placeholder="House No." required>
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label visually-hidden">Street</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-road"></i></span>
                <input type="text" class="form-control" name="streetName" placeholder="Street" required>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label visually-hidden">Barangay</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" name="barangay" placeholder="Barangay" required>
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label visually-hidden">City</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-city"></i></span>
                <input type="text" class="form-control" name="city" placeholder="City" required>
              </div>
            </div>

            <div class="mb-2">
               <label class="form-label visually-hidden">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="signupPassword" name="password" placeholder="Create a password" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label visually-hidden">Confirm Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
              </div>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="agreeTerms" name="agreeTerms" required>
              <label class="form-check-label" for="agreeTerms">
                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> & <a href="terms.php" target="_blank">Privacy Policy</a>
              </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
              <span class="loading-spinner"></span>
              <span class="button-text">CREATE ACCOUNT</span>
            </button>

            <hr class="my-4">

            <div class="text-center">
               <p class="mb-2">Already have an account?</p>
               <a href="login.php" class="btn btn-outline-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>SIGN IN
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
