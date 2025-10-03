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
  <link href="https.cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https.cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https.fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="dashboard-styles.css?v=2.4" rel="stylesheet">
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

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

  <script src="https.cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>