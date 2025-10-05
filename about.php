<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About CrackCart</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
  <link href="about-styles.css" rel="stylesheet">
</head>
<body>
  <?php include("public_navbar.php"); ?>

  <main class="container">
    <section class="about-section">
      <h2>About CrackCart</h2>
      <div class="about-content">
        <div class="about-text">
          <p>CrackCart is a revolutionary platform designed to connect egg producers directly with consumers. We believe in providing fresh, high-quality eggs with the convenience of online ordering and direct home delivery.</p>
          <p>Our mission is to simplify the supply chain, ensuring that you receive the freshest eggs possible while supporting local producers. We handle everything from order processing and payment to delivery coordination and fleet management, creating a seamless experience for both our customers and our partners.</p>
        </div>
        <div class="about-image">
          <img src="about-image.jpg" alt="Fresh eggs in a basket">
        </div>
      </div>
      <div class="mission">
          <h3>Our Mission</h3>
          <p>To provide a simple, reliable, and fresh egg delivery service that supports local communities and brings the farm-to-table experience to your doorstep.</p>
      </div>
    </section>
  </main>

  <script src="public-script.js"></script>
</body>
</html>
