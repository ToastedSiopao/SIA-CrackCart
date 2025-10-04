<?php
require_once 'session_handler.php';

// User info from session
$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_first_name']; 
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

// Connect DB
include("db_connect.php");

// Dummy Data
$total_orders = 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="dashboard-styles.css?v=2.4" rel="stylesheet">
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include 'sidebar.php'; ?>
      <?php include 'offcanvas_sidebar.php'; ?>

      <!-- Main Content -->
      <div class="col p-4">
        <div class="card shadow-sm border-0 p-4">
          <h6 class="text-warning">Overview</h6>
          <h4 class="mb-4">Welcome back, <?php echo htmlspecialchars($user_name); ?> 👋</h4>
          <div class="row g-3">
            <div class="col-6 col-md-3">
              <div class="category-card">
                <i class="bi bi-cart3"></i>
                <p class="mb-0">Orders</p>
                <h5><?php echo $total_orders; ?></h5>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="category-card">
                <i class="bi bi-chat-dots"></i>
                <p class="mb-0">Messages</p>
                <h5>0</h5>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="category-card">
                <i class="bi bi-clock-history"></i>
                <p class="mb-0">Order History</p>
                <h5><?php echo $total_orders; ?></h5>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="category-card active">
                <i class="bi bi-receipt"></i>
                <p class="mb-0">Bills</p>
                <h5>0</h5>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom JS -->
  <script src="script.js?v=1.1"></script>
</body>
</html>