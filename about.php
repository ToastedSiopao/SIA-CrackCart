
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About CrackCart</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="about-styles.css" rel="stylesheet">
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
    .about-section {
        text-align: center; /* Center align the section content */
    }
    .about-image {
        margin-top: 20px; /* Add some space above the image */
    }
    .about-image img {
        max-width: 100%;
        height: auto;
        border-radius: 10px; /* Optional: adds rounded corners */
    }
    .mission {
        margin-top: 30px;
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
    <section class="about-section">
      <h2>About CrackCart</h2>
      <div class="about-content">
        <div class="about-text">
          <p>CrackCart is a revolutionary platform designed to connect egg producers directly with consumers. We believe in providing fresh, high-quality eggs with the convenience of online ordering and direct home delivery.</p>
          <p>Our mission is to simplify the supply chain, ensuring that you receive the freshest eggs possible while supporting local producers. We handle everything from order processing and payment to delivery coordination and fleet management, creating a seamless experience for both our customers and our partners.</p>
        </div>
        <div class="about-image">
          <img src="assets/aboutEgg.jpg" alt="Fresh eggs in a basket">
        </div>
      </div>
      <div class="mission">
          <h3>Our Mission</h3>
          <p>To provide a simple, reliable, and fresh egg delivery service that supports local communities and brings the farm-to-table experience to your doorstep.</p>
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
