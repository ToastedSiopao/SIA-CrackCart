<?php
require_once 'session_handler.php';
include('cart_functions.php');
include('db_connect.php'); // Include the database connection

$shipment = isset($_SESSION['shipment']) ? $_SESSION['shipment'] : array();

if (empty($shipment)) {
    header('Location: eggspress.php');
    exit;
}

$subtotal = 0;
foreach ($shipment as $service_tier => $details) {
    $subtotal += $details['quantity'] * $details['price'];
}

$order_status = 'pending'; // Default status

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $payment_success = true; // Simulate a successful payment

    if ($payment_success) {

        // Create the order
        $user_id = $_SESSION['user_id'];
        $pickup_address_id = 1; // Replace with actual address ID
        $delivery_address_id = 2; // Replace with actual address ID
        $tray_quantity = 0;
        $tray_size = 0;

        foreach ($shipment as $service_tier => $details) {
            $tray_quantity += $details['quantity'];
        }

        $sql = "INSERT INTO orders (user_id, pickup_address_id, delivery_address_id, tray_quantity, tray_size, quoted_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiidids", $user_id, $pickup_address_id, $delivery_address_id, $tray_quantity, $tray_size, $subtotal, $order_status);

        if ($stmt->execute()) {
            $order_status = 'confirmed';
            unset($_SESSION['shipment']); // Clear the cart
            header('Location: order_confirmation.php');
            exit;
        } else {
            $order_status = 'failed';
        }

        $stmt->close();
        $conn->close();
    } else {
        $order_status = 'cancelled';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h1 class="mb-4">Checkout</h1>
    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">Shipping Information</div>
          <div class="card-body">
            <form action="checkout.php" method="post">
              <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" required>
              </div>
              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" required>
              </div>
              <button type="submit" name="confirm_order" class="btn btn-primary">Confirm Order</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">Order Summary</div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <?php foreach ($shipment as $service_tier => $details): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <?php echo htmlspecialchars($service_tier); ?> (x<?php echo $details['quantity']; ?>)
                  <span>₱<?php echo number_format($details['quantity'] * $details['price'], 2); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <div class="card-footer text-end">
              <strong>Subtotal: ₱<?php echo number_format($subtotal, 2); ?></strong>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php if ($order_status === 'failed'): ?>
      <div class="alert alert-danger mt-3">Order failed. Please try again.</div>
    <?php elseif ($order_status === 'cancelled'): ?>
      <div class="alert alert-warning mt-3">Order cancelled.</div>
    <?php endif; ?>
  </div>
</body>
</html>