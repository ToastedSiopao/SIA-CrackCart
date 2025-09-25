<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// User info from session
$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
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
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg shadow-sm px-3">
    <div class="container-fluid">
      <!-- Sidebar toggle (mobile only) -->
      <button class="btn btn-outline-dark d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <i class="bi bi-list"></i>
      </button>

      <!-- Brand -->
      <a class="navbar-brand fw-bold" href="#">CrackCart.</a>

      <!-- Right side -->
      <div class="ms-auto d-flex align-items-center gap-4">
        <!-- Notification Bell -->
        <a href="#" class="text-dark fs-5">
          <i class="bi bi-bell"></i>
        </a>

        <!-- Username + Profile -->
        <div class="dropdown">
          <a class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="me-2"><?php echo htmlspecialchars($user_name); ?></span>
            <i class="bi bi-person-circle fs-4"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="profilePage.php">Profile</a></li>
            <li><a class="dropdown-item" href="#">Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <!-- Sidebar -->
      <div class="col-auto col-md-3 col-lg-2 px-3 sidebar d-none d-md-block">
        <ul class="nav flex-column mb-auto mt-4">
          <li><a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li><a href="order.php" class="nav-link"><i class="bi bi-cart3 me-2"></i>Make an Order</a></li>
          <li><a href="messages.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
          <li><a href="history.php" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
          <li><a href="bills.php" class="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
          <li><a href="profilePage.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
          <li><a href="producers.php" class="nav-link"><i class="bi bi-egg me-2"></i> Producers</a></li>

        </ul>
        <div class="upgrade-box">
          <p>Upgrade your Account to Get Free Voucher</p>
          <button class="btn btn-light btn-sm">Upgrade</button>
        </div>
      </div>

      <!-- Offcanvas Sidebar for Mobile -->
      <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title">CrackCart.</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="nav flex-column mb-auto">
            <li><a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="orders.php" class="nav-link"><i class="bi bi-cart3 me-2"></i> Order</a></li>
            <li><a href="messages.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
            <li><a href="history.php" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
            <li><a href="bills.php" class_="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
            <li><a href="settings.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
          </ul>
          <div class="upgrade-box">
            <p>Upgrade your Account to Get Free Voucher</p>
            <button class="btn btn-light btn-sm">Upgrade</button>
          </div>
        </div>
      </div>

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
</body>
</html>
