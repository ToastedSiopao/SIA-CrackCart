<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Confirmation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <div class="alert alert-success">
      <h4 class="alert-heading">Thank you for your order!</h4>
      <p>Your shipment has been booked successfully. You will receive an email confirmation shortly.</p>
      <hr>
      <a href="eggspress.php" class="btn btn-primary">Book Another Shipment</a>
    </div>
  </div>
</body>
</html>