<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crack Cart - Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: url("assets/eggBG.png") no-repeat center center fixed; /* your background */
      background-size: cover;
      overflow-x: hidden;
    }

    /* Header */
    .header-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px 0;
    }
    .header-logo img {
      height: 80px;
      margin-right: 10px;
    }
    .header-logo h1 {
      font-weight: bold;
      font-size: 2rem;
      margin: 0;
      color: #222;
    }

    /* Main container */
    .content-container {
      display: flex;
      justify-content: center;
      align-items: stretch;
      padding: 40px;
      gap: 30px;
      flex-wrap: wrap;
    }

    /* Left text card */
    .text-card {
      flex: 1;
      min-width: 320px;
      background: rgba(255,255,255,0.9);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      position: relative;
    }

    .text-card p {
      font-size: 0.95rem;
      color: #333;
    }

    .arrow-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: #ffd800;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    }
    .arrow-btn.left { left: -20px; }
    .arrow-btn.right { right: -20px; }
    .arrow-btn i { color: #222; }

    /* Signup card */
    .signup-card {
      flex: 0.7;
      min-width: 320px;
      background: #fff;
      border-radius: 15px;
      padding: 40px 30px;
      box-shadow: 0 4px 12px rgba(255, 244, 142, 0.15);
    }
    .signup-title {
      color: #020200;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .input-group-text {
      background-color: #f8f9fc;
      border-right: none;
    }
    .form-control {
      border-left: none;
    }
    .form-control:focus {
      box-shadow: none;
      border-color: #ced4da;
    }
    .btn-primary {
      background: linear-gradient(135deg, #f8ff95 0%, #e9ff6f 100%);
      border: none;
      padding: 12px;
      font-weight: 600;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #feffbd 0%, #f8ffbe 100%);
    }
  </style>
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

      <!-- Messages (PHP or JS can inject here) -->
      <div id="messageContainer"></div>

      <form id="signupForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control" name="fullName" placeholder="Enter your full name" required>
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

        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
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
          <i class="fas fa-user-plus me-2"></i>CREATE ACCOUNT
        </button>
      </form>

      <div class="text-center">
        <p class="mb-0">Already have an account? <a href="login.php" class="text-primary">Sign in here</a></p>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('signupForm').addEventListener('submit', async function(event) {
      event.preventDefault();
      event.stopPropagation();

      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      const messageContainer = document.getElementById('messageContainer');

      if (password.length < 8) {
        messageContainer.innerHTML = '<div class="alert alert-danger">Password must be at least 8 characters long.</div>';
        return;
      }

      if (password !== confirmPassword) {
        messageContainer.innerHTML = '<div class="alert alert-danger">Passwords do not match.</div>';
        return;
      }

      if (!this.checkValidity()) {
        this.classList.add('was-validated');
        return;
      }

      const formData = new FormData(this);

      try {
        const response = await fetch('signup_process.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();
        messageContainer.innerHTML = ''; // Clear previous messages

        if (result.error) {
          let errorMessage = '<div class="alert alert-danger">' + result.error.message + '</div>';
          if (result.error.file && result.error.line) {
            errorMessage += '<div class="alert alert-warning">Error in ' + result.error.file + ' on line ' + result.error.line + '</div>';
          }
          messageContainer.innerHTML = errorMessage;
        } else if (result.success) {
          messageContainer.innerHTML = '<div class="alert alert-success">Signup successful! You can now log in.</div>';
          // Optionally, redirect to login page
          // window.location.href = 'login.php';
        }
      } catch (error) {
        messageContainer.innerHTML = '<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>';
      }
    });
  </script>
</body>
</html>
