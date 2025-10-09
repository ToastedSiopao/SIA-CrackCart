<?php
// --- POOR MAN'S CRON JOB ---
// This logic triggers the delayed restock script based on user traffic.

$cron_last_run_file = __DIR__ . '/admin/api/cron_last_run.txt';
$cron_script_path = __DIR__ . '/admin/api/process_delayed_restocks.php';
$cron_interval = 3600; // 1 hour in seconds

// We only run this on the main page, not on other requests.
if (basename($_SERVER['PHP_SELF']) == 'index.php') {
    $run_cron = false;
    $current_time = time();

    // Check if the timestamp file exists and when the job was last run.
    if (file_exists($cron_last_run_file)) {
        $last_run_time = (int)@file_get_contents($cron_last_run_file);
        if ($current_time - $last_run_time > $cron_interval) {
            $run_cron = true;
        }
    } else {
        // If the file doesn't exist, this is the first run.
        $run_cron = true;
    }

    if ($run_cron) {
        // Update the timestamp immediately to prevent race conditions.
        @file_put_contents($cron_last_run_file, $current_time);

        // To include the script securely, we set the secret key it expects.
        // We save and restore the original $_GET state to avoid side effects.
        $original_get = $_GET;
        $_GET['secret'] = 'CrackCartSecretRestockKey987';

        // Use output buffering to hide any output from the included script.
        ob_start();
        include $cron_script_path;
        ob_end_clean(); // Discard the output.

        // Restore the original $_GET array.
        $_GET = $original_get;
    }
}
// --- END CRON JOB LOGIC ---


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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="index-styles.css?v=1.1" rel="stylesheet">
</head>
<body>
  <!-- Header -->
  <header id="secretButton">
    <div class="logo-container">
        <i class="fa-solid fa-egg logo-icon"></i>
        <span class="logo-text">CrackCart</span>
    </div>
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

  <script>
    const secretButton = document.getElementById('secretButton');
    let clickCount = 0;
    let clickTimer = null;
    let pressTimer = null;

    secretButton.addEventListener('click', () => {
      clickCount++;
      if (clickTimer) {
        clearTimeout(clickTimer);
      }
      if (clickCount >= 5) {
        window.location.href = 'admin/index.php';
        clickCount = 0;
      } else {
        clickTimer = setTimeout(() => {
          clickCount = 0;
        }, 1000); // Reset click count after 1 second of inactivity
      }
    });

    secretButton.addEventListener('mousedown', () => {
      pressTimer = setTimeout(() => {
        window.location.href = 'driver_page.php';
      }, 5000); // 5 seconds
    });

    secretButton.addEventListener('mouseup', () => {
      clearTimeout(pressTimer);
    });

    secretButton.addEventListener('mouseleave', () => {
      clearTimeout(pressTimer);
    });
  </script>
</body>
</html>
