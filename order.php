<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// User info from session
$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
include("db_connect.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product = $_POST['product'];
    $quantity = $_POST['quantity'];
    $address = $_POST['address'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO orders (user_id, product, quantity, address, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $user_id, $product, $quantity, $address, $notes);
    $stmt->execute();
    $stmt->close();

    $success_message = "Your order has been placed successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Make an Order - CrackCart</title>
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
      <button class="btn btn-outline-dark d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <i class="bi bi-list"></i>
      </button>
      <a class="navbar-brand fw-bold" href="#">CrackCart.</a>
      <div class="ms-auto d-flex align-items-center gap-4">
        <a href="#" class="text-dark fs-5"><i class="bi bi-bell"></i></a>
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
          <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li><a href="order.php" class="nav-link active"><i class="bi bi-cart3 me-2"></i> Make an Order</a></li>
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
            <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="order.php" class="nav-link active"><i class="bi bi-cart3 me-2"></i> Order</a></li>
            <li><a href="messages.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
            <li><a href="history.php" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
            <li><a href="bills.php" class="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
            <li><a href="profilePage.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
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
          <h6 class="text-warning">Order</h6>
          <h4 class="mb-4">Place Your Order</h4>

          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
          <?php endif; ?>

          <form method="post" action="">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="product" class="form-label">Select Product</label>
                <select class="form-select" id="product" name="product" required>
                  <option value="" disabled selected>Choose...</option>
                  <option value="Egg Tray">Egg Tray</option>
                  <option value="Fresh Eggs">Fresh Eggs</option>
                  <option value="Organic Eggs">Organic Eggs</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
              </div>
              <div class="col-12">
                <label for="address" class="form-label">Delivery Address</label>
                <input type="text" class="form-control" id="address" name="address" placeholder="Enter your delivery address" required>
              </div>
              <div class="col-12">
                <label for="notes" class="form-label">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special instructions?"></textarea>
              </div>
              <div class="col-12 text-center mt-4">
                <button type="submit" class="btn btn-warning btn-lg px-5">
                  <i class="bi bi-check-circle me-2"></i>Submit Order
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
