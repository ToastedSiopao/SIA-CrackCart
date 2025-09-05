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
  <title>CrackCart</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: url("assets/eggBG.png") no-repeat center center/cover; 
      color: #333;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 50px;
      background: rgba(255, 255, 255, 0.8);
    }

    header img {
      height: 40px;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 20px;
      margin: 0;
      padding: 0;
      align-items: center;
    }

    nav ul li {
      display: inline;
    }

    nav ul li a {
      text-decoration: none;
      color: #333;
      font-weight: bold;
    }

    nav .signup {
      border: 1px solid #333;
      padding: 5px 15px;
      border-radius: 5px;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 213, 0, 0.2);
      padding: 8px 15px;
      border-radius: 20px;
    }

    .user-info i {
      color: #555;
    }

    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 100px 50px;
    }

    .hero-text {
      max-width: 500px;
    }

    .hero-text h1 {
      font-size: 2.5rem;
      margin-bottom: 20px;
    }

    .hero-text p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }

    .buttons {
      display: flex;
      gap: 15px;
    }

    .btn {
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }

    .btn-primary {
      background: #FFD500;
      color: #000;
      font-weight: bold;
    }

    .btn-secondary {
      background: #eee;
      color: #333;
    }

    .hero img {
      max-width: 400px;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <img src="assets/Logo.png" alt="CrackCart Logo"> 
    <nav>
      <ul>
        <li><a href="#">About</a></li>
        <li><a href="#">Features</a></li>
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
    <div class="hero-text">
      <h1>Shop Smarter with CrackCart</h1>
      <p>Your one-stop cart for fast and reliable shopping.</p>
      <div class="buttons">
        <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'login.php'; ?>" class="btn btn-primary">Get Started</a>
        <a href="#" class="btn btn-secondary">Learn More</a>
      </div>
    </div>
    <div>
      <img src="assets/shoppingCart.png" alt="Shopping Cart"> 
    </div>
  </section>
</body>
</html>