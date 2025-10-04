<?php
require_once 'session_handler.php';
include('cart_functions.php');

$shipment = isset($_SESSION['shipment']) ? $_SESSION['shipment'] : array();
$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Shipment - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.5" rel="stylesheet">
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <!-- Main Content -->
      <main class="col ps-md-2 pt-2">
        <div class="container">
          <div class="row">
            <div class="col-12">
              <div class="card shadow-sm border-0 p-4">
                <div class="card-body">
                  <h2 class="card-title text-center mb-4">Your Shipment</h2>
                  <?php if (empty($shipment)): ?>
                    <div class="alert alert-info text-center" role="alert">
                      Your shipment is empty. <a href="eggspress.php" class="alert-link">Book a shipment</a>.
                    </div>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-hover align-middle">
                        <thead class="table-light">
                          <tr>
                            <th>Service Tier</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Action</th>
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
                              <td class="text-center">
                                <form action="cart_functions.php" method="post" class="d-inline-flex align-items-center">
                                  <input type="hidden" name="service_tier" value="<?php echo htmlspecialchars($service_tier); ?>">
                                  <input type="number" name="quantity" value="<?php echo $details['quantity']; ?>" min="1" class="form-control form-control-sm" style="width: 70px;">
                                  <button type="submit" name="update_shipment" class="btn btn-outline-primary btn-sm ms-2">Update</button>
                                </form>
                              </td>
                              <td class="text-end">$<?php echo number_format($details['price'], 2); ?></td>
                              <td class="text-end">$<?php echo number_format($total, 2); ?></td>
                              <td class="text-center">
                                <form action="cart_functions.php" method="post" class="d-inline">
                                  <input type="hidden" name="service_tier" value="<?php echo htmlspecialchars($service_tier); ?>">
                                  <button type="submit" name="remove_from_shipment" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                  </button>
                                </form>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="text-end mt-4">
                      <h3>Subtotal: $<?php echo number_format($subtotal, 2); ?></h3>
                      <a href="checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>
                    </div>
                  <?php endif; ?>
                  <div class="text-center mt-3">
                    <a href="eggspress.php" class="btn btn-secondary">Continue Shopping</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>