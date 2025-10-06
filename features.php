
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Features - CrackCart</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="features-styles.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #FFD500; 
      --dark-color: #333;
      --light-color: #f4f4f4;
      --white-color: #fff;
    }
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: url('assets/eggBG.png') no-repeat center center/cover;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .navbar {
        background: var(--white-color);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    .navbar .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .logo {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark-color);
        text-decoration: none;
    }
    .nav-links {
        display: flex;
        align-items: center;
    }
    .nav-links a {
        color: var(--dark-color);
        text-decoration: none;
        margin: 0 1rem;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    .nav-links a:hover {
        color: var(--primary-color);
    }
    .signup-button {
        background: var(--primary-color);
        color: var(--white-color);
        padding: 0.5rem 1rem;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.3s ease;
    }
    .signup-button:hover {
        background: #e6c300;
    }
    .nav-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .nav-links {
            display: none;
            flex-direction: column;
            width: 100%;
            background: var(--white-color);
            position: absolute;
            top: 60px;
            left: 0;
            padding: 1rem 0;
        }
        .nav-links.active {
            display: flex;
        }
        .nav-links a {
            margin: 0.5rem 0;
            text-align: center;
        }
        .nav-toggle {
            display: block;
        }
    }
  </style>
</head>
<body>
  <?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
  ?>
  <header class="navbar">
    <div class="container">
      <a href="index.php" class="logo">CrackCart.</a>
      <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="features.php">Features</a>
        <?php if(isset($_SESSION['user_id'])): ?>
          <a href="dashboard.php">Dashboard</a>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a>
          <a href="signup.php" class="signup-button">Sign Up</a>
        <?php endif; ?>
      </nav>
      <button class="nav-toggle">
          <span></span>
          <span></span>
          <span></span>
      </button>
    </div>
  </header>

  <main class="container">
    <section class="features-section">
      <h2>What CrackCart Offers</h2>
      <div class="feature-list">
        <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-egg"></i></div>
          <h3>Online Egg Ordering</h3>
          <p>Order fresh eggs from local producers online, anytime, anywhere. No more last-minute trips to the store.</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-truck"></i></div>
          <h3>Direct Home Delivery</h3>
          <p>Get your eggs delivered straight to your doorstep with our dedicated and reliable transport system.</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-tasks"></i></div>
          <h3>Fleet Management</h3>
          <p>Our advanced system tracks our delivery fleet to ensure timely and efficient deliveries.</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-box-open"></i></div>
          <h3>Order Processing & Tracking</h3>
          <p>Place orders easily, receive instant confirmation, and track your delivery in real-time from our platform.</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-route"></i></div>
          <h3>Delivery Coordination</h3>
          <p>We plan the safest and fastest delivery routes to ensure your eggs arrive fresh and on time.</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-credit-card"></i></div>
          <h3>Secure Payments</h3>
          <p>Pay for your orders with confidence through our secure online payment system, integrated with trusted gateways.</p>
        </div>
         <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-user"></i></div>
          <h3>User Profiles</h3>
          <p>Manage your delivery addresses, view your order history, and set your preferences in your personal account.</p>
        </div>
         <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-store"></i></div>
          <h3>Producer Support</h3>
          <p>We provide a platform for egg producers to reach a wider customer base and grow their business.</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon"><i class="fas fa-star"></i></div>
          <h3>Reviews and Ratings</h3>
          <p>Leave reviews and ratings for products you've purchased to help others make informed decisions.</p>
        </div>
      </div>
    </section>
  </main>

  <script>
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');

    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
    });
  </script>
</body>
</html>
