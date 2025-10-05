<?php
require_once 'session_handler.php';

// User info from session
$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_first_name']; 

// Connect DB
include("db_connect.php");

// Fetch total orders for the logged-in user
$stmt_orders = $conn->prepare("SELECT COUNT(*) as total_orders FROM product_orders WHERE user_id = ?");
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders_data = $result_orders->fetch_assoc();
$total_orders = $orders_data['total_orders'];
$stmt_orders->close();

// Fetch total pending orders
$stmt_pending = $conn->prepare("SELECT COUNT(*) as pending_orders FROM product_orders WHERE user_id = ? AND status = 'pending'");
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();
$pending_data = $result_pending->fetch_assoc();
$pending_orders = $pending_data['pending_orders'];
$stmt_pending->close();

// Fetch total amount spent by the user on completed orders
$stmt_spent = $conn->prepare("SELECT SUM(total_amount) as total_spent FROM product_orders WHERE user_id = ? AND status IN ('delivered', 'completed', 'paid', 'shipped')");
$stmt_spent->bind_param("i", $user_id);
$stmt_spent->execute();
$result_spent = $stmt_spent->get_result();
$spent_data = $result_spent->fetch_assoc();
$total_spent = $spent_data['total_spent'] ?? 0;
$stmt_spent->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.5" rel="stylesheet"> 
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
                <p class="mb-0">Total Orders</p>
                <h5><?php echo $total_orders; ?></h5>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="category-card">
                <i class="bi bi-box-seam"></i>
                <p class="mb-0">Pending Orders</p>
                <h5><?php echo $pending_orders; ?></h5>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="category-card">
                <i class="bi bi-currency-dollar"></i>
                <p class="mb-0">Total Spent</p>
                <h5>₱<?php echo number_format($total_spent, 2); ?></h5>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js?v=1.1"></script>
</body>
</html>