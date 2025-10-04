
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Settings - CrackCart</title>
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
        <h3 class="mb-4 text-warning fw-bold">Settings</h3>

        <div class="row g-4">
          <!-- Profile Settings Card -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <i class="bi bi-person-circle fs-1 text-warning"></i>
                <h5 class="card-title mt-3">Profile</h5>
                <p class="card-text">Manage your personal information, such as your name, email, and password.</p>
                <a href="profilePage.php" class="btn btn-warning">Go to Profile</a>
              </div>
            </div>
          </div>

          <!-- Address Settings Card -->
          <div class="col-md-6 col-lg-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <i class="bi bi-geo-alt-fill fs-1 text-warning"></i>
                <h5 class="card-title mt-3">Manage Addresses</h5>
                <p class="card-text">Add, edit, or remove your shipping and billing addresses.</p>
                <a href="addresses.php" class="btn btn-warning">Go to Addresses</a>
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
