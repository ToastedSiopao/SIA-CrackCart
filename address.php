<?php
require_once 'session_handler.php';

$user_id = $_SESSION['user_id'];

include("db_connect.php");

// Handle Add Address
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_address'])) {
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];
    $address_type = $_POST['address_type'];

    $stmt = $conn->prepare("INSERT INTO user_addresses (user_id, address_line1, address_line2, city, state, zip_code, country, address_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $address_line1, $address_line2, $city, $state, $zip_code, $country, $address_type);
    $stmt->execute();
    header("Location: address.php");
    exit();
}

// Fetch existing addresses
$addresses = [];
$stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Addresses - CrackCart</title>
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

      <div class="col p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="settings.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Settings</a>
            <h3 class="mb-0 text-warning fw-bold">Manage Addresses</h3>
        </div>

        <!-- Add New Address Form -->
        <div class="card mb-4">
          <div class="card-header fw-bold">Add New Address</div>
          <div class="card-body">
            <form method="POST">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="address_line1" class="form-label">Address Line 1</label>
                  <input type="text" class="form-control" id="address_line1" name="address_line1" value="123 Egg Street" required>
                </div>
                <div class="col-md-6">
                  <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                  <input type="text" class="form-control" id="address_line2" name="address_line2" value="Apt 4B">
                </div>
                <div class="col-md-4">
                  <label for="city" class="form-label">City</label>
                  <input type="text" class="form-control" id="city" name="city" value="Cebu City" required>
                </div>
                <div class="col-md-4">
                  <label for="state" class="form-label">State/Province</label>
                  <input type="text" class="form-control" id="state" name="state" value="Cebu" required>
                </div>
                <div class="col-md-4">
                  <label for="zip_code" class="form-label">ZIP Code</label>
                  <input type="text" class="form-control" id="zip_code" name="zip_code" value="6000" required>
                </div>
                 <div class="col-md-6">
                  <label for="country" class="form-label">Country</label>
                  <input type="text" class="form-control" id="country" name="country" value="Philippines" required>
                </div>
                <div class="col-md-6">
                  <label for="address_type" class="form-label">Address Type</label>
                  <select class="form-select" id="address_type" name="address_type">
                    <option value="shipping">Shipping</option>
                    <option value="billing">Billing</option>
                  </select>
                </div>
              </div>
              <button type="submit" name="add_address" class="btn btn-warning mt-3">Add Address</button>
            </form>
          </div>
        </div>

        <!-- Existing Addresses -->
        <div class="card">
          <div class="card-header fw-bold">Your Addresses</div>
          <div class="card-body">
            <?php if (empty($addresses)): ?>
              <p>You have no saved addresses.</p>
            <?php else: ?>
              <div class="list-group">
                <?php foreach ($addresses as $address): ?>
                  <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                      <h5 class="mb-1"><?= htmlspecialchars($address['address_type']) ?></h5>
                    </div>
                    <p class="mb-1">
                      <?= htmlspecialchars($address['address_line1']) ?><br>
                      <?php if(!empty($address['address_line2'])) echo htmlspecialchars($address['address_line2']) . "<br>"; ?>
                      <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> <?= htmlspecialchars($address['zip_code']) ?><br>
                      <?= htmlspecialchars($address['country']) ?>
                    </p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
