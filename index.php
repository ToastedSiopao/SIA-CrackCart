<?php
// Start session to check login status
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CrackCart - Your One-Stop Shop</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="index-styles.css" rel="stylesheet">
</head>
<body>
  <!-- Header -->
  <header>
    <img src="assets/Logo.png" alt="CrackCart Logo" class="logo">
    <nav>
      <ul>
        <li><a href="about.php">About</a></li>
        <li><a href="features.php">Features</a></li>
        <?php if($isLoggedIn): ?>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($username); ?></span>
          </li>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php">Login</a></li>
          <li><a href="signup.php" class="signup">Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <div class="hero-text">
        <h1>Shop Smarter with CrackCart</h1>
        <p>Your one-stop cart for fast and reliable shopping.</p>
        <div class="buttons">
          <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'login.php'; ?>" class="btn btn-primary">Get Started</a>
          <a href="about.php" class="btn btn-secondary">Learn More</a>
        </div>
      </div>
      <div class="hero-image">
        <img src="assets/shoppingCart.png" alt="Shopping Cart">
      </div>
    </div>
  </section>
</body>
</html>
