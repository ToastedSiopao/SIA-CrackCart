<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("../db_connect.php");

$user_id = $_SESSION['user_id'];
$first_name = $last_name = $email = $phone = $created_at = "";
$success_msg = $error_msg = "";

// Fetch user data
$stmt = $conn->prepare("SELECT FIRST_NAME, LAST_NAME, EMAIL, PHONE, CREATED_AT FROM USER WHERE USER_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $first_name = $user['FIRST_NAME'];
    $last_name = $user['LAST_NAME'];
    $email = $user['EMAIL'];
    $phone = $user['PHONE'] ?? '';
    $created_at = $user['CREATED_AT'] ?? '';
} else {
    $error_msg = "User not found!";
}

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    
    $password_sql = "";
    if (!empty($_POST['password'])) {
        if ($_POST['password'] !== $_POST['confirm_password']) {
            $error_msg = "Passwords do not match!";
        } elseif (strlen($_POST['password']) < 8) {
            $error_msg = "Password must be at least 8 characters long.";
        } else {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_sql = ", PASSWORD = ?";
        }
    }

    if (empty($error_msg)) {
        $sql = "UPDATE USER SET FIRST_NAME = ?, LAST_NAME = ?, PHONE = ? $password_sql WHERE USER_ID = ?";
        $stmt = $conn->prepare($sql);
        if (!empty($password_sql)) {
            $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $hashed_password, $user_id);
        } else {
            $stmt->bind_param("sssi", $first_name, $last_name, $phone, $user_id);
        }
        
        if ($stmt->execute()) {
            $success_msg = "Profile updated successfully!";
            $_SESSION['user_name'] = $first_name . ' ' . $last_name; // Update session name
        } else {
            $error_msg = "Error updating profile.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Profile - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.6" rel="stylesheet">
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
            <h3 class="mb-0 text-warning fw-bold">Manage Profile</h3>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header fw-bold">Your Information</div>
          <div class="card-body">
            <form method="POST">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="first_name" class="form-label">First Name</label>
                  <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="last_name" class="form-label">Last Name</label>
                  <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" disabled>
                  <div class="form-text">Your email address cannot be changed.</div>
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Phone Number</label>
                  <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>">
                </div>
                <hr class="my-4">
                <div class="col-md-6">
                  <label for="password" class="form-label">New Password</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                  <div class="form-text">Must be at least 8 characters long.</div>
                </div>
                <div class="col-md-6">
                  <label for="confirm_password" class="form-label">Confirm New Password</label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
                </div>
              </div>
              <button type="submit" name="save_profile" class="btn btn-warning mt-4">Save Changes</button>
            </form>
          </div>
        </div>

        <div class="card mt-4">
          <div class="card-header fw-bold">Account Details</div>
          <div class="card-body">
            <p class="mb-1"><strong>Member Since:</strong> <?= htmlspecialchars($created_at) ?></p>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
