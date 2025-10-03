 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crack Cart - Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">

</head>
<body>
  <!-- Header -->
  <a href="index.php" class="header-logo">
    <img src="assets/Logo.png" alt="Crack Cart Logo">
  </a>
  <!-- Content -->
  <div class="content-container container">
    <!-- Left text box -->
    <div class="text-card">
      <div class="arrow-btn left"><i class="fas fa-chevron-left"></i></div>
      <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis.</p>
      <p>Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas.</p>
      <div class="arrow-btn right"><i class="fas fa-chevron-right"></i></div>
    </div>

    <!-- Right signup form card -->
    <div class="signup-card">
      <div class="text-center mb-4">
        <i class="fas fa-truck fa-3x text-warning mb-3"></i>
        <h2 class="signup-title">Create Account</h2>
        <p class="text-muted">Fill in your details to get started</p>
      </div>

      <div id="formFeedback"></div>

      <form id="signupForm" novalidate>
        <!-- Name Fields -->
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">First Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input type="text" class="form-control" name="firstName" placeholder="First name" required>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Middle Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input type="text" class="form-control" name="middleName" placeholder="Middle name">
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Last Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input type="text" class="form-control" name="lastName" placeholder="Last name" required>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Phone Number (Optional)</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-phone"></i></span>
            <input type="tel" class="form-control" name="phone" placeholder="Enter your phone number">
          </div>
        </div>

        <!-- Address Fields -->
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">House No.</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-home"></i></span>
              <input type="text" class="form-control" name="houseNo" placeholder="House number">
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Street Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-road"></i></span>
              <input type="text" class="form-control" name="streetName" placeholder="Street name">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Barangay</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-map-marker"></i></span>
              <input type="text" class="form-control" name="barangay" placeholder="Barangay">
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">City</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-city"></i></span>
              <input type="text" class="form-control" name="city" placeholder="City" required>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="signupPassword" name="password" placeholder="Create a password" required>
          </div>
          <div class="form-text">Must be at least 8 characters</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
          </div>
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="agreeTerms" required>
          <label class="form-check-label" for="agreeTerms">
            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
          </label>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
          <span class="loading-spinner"></span>
          <span class="button-text">CREATE ACCOUNT</span>
        </button>
      </form>

      <div class="text-center">
        <p class="mb-0">Already have an account? <a href="login.php" class="text-primary">Sign in here</a></p>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>