<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Features - CrackCart</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
  <link href="features-styles.css" rel="stylesheet">
</head>
<body>
  <?php include("public_navbar.php"); ?>

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
      </div>
    </section>
  </main>

  <script src="public-script.js"></script>
</body>
</html>
