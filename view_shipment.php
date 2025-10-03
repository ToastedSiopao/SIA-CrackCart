<?php
session_start();
include('cart_functions.php');

$shipment = isset($_SESSION['shipment']) ? $_SESSION['shipment'] : array();
$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Shipment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h1 class="mb-4">Your Shipment</h1>
    <?php if (empty($shipment)): ?>
      <p>Your shipment is empty.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Service Tier</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($shipment as $service_tier => $details): ?>
            <?php
            $total = $details['quantity'] * $details['price'];
            $subtotal += $total;
            ?>
            <tr>
              <td><?php echo htmlspecialchars($service_tier); ?></td>
              <td>
                <form action="cart_functions.php" method="post" class="d-inline">
                  <input type="hidden" name="service_tier" value="<?php echo htmlspecialchars($service_tier); ?>">
                  <input type="number" name="quantity" value="<?php echo $details['quantity']; ?>" min="1" class="form-control d-inline" style="width: 80px;">
                  <button type="submit" name="update_shipment" class="btn btn-primary btn-sm">Update</button>
                </form>
              </td>
              <td>$<?php echo number_format($details['price'], 2); ?></td>
              <td>$<?php echo number_format($total, 2); ?></td>
              <td>
                <form action="cart_functions.php" method="post" class="d-inline">
                  <input type="hidden" name="service_tier" value="<?php echo htmlspecialchars($service_tier); ?>">
                  <button type="submit" name="remove_from_shipment" class="btn btn-danger btn-sm">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="text-end">
        <h3>Subtotal: $<?php echo number_format($subtotal, 2); ?></h3>
        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
      </div>
    <?php endif; ?>
    <a href="eggspress.php" class="btn btn-secondary mt-3">Continue Shopping</a>
  </div>
</body>
</html>