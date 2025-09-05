<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include("db_connect.php");

// Initialize variables
$user_id = $_SESSION['user_id'];
$full_name = $email = $phone = $role = $status = $created_at = "";
$success_msg = $error_msg = "";

// Fetch user data from database
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $full_name = $user['full_name'];
    $email = $user['email'];
    $phone = $user['phone'] ?? '';
    $role = $user['role'] ?? 'customer';
    $status = $user['status'] ?? 'active';
    $created_at = $user['created_at'] ?? '';
} else {
    $error_msg = "User not found!";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    // Get and sanitize form data
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Check if password is being changed
    $password_update = "";
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 8) {
            $error_msg = "Password must be at least 8 characters long.";
        } else {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_update = ", password = '$password'";
        }
    }
    
    // Only update if no error with password
    if (empty($error_msg)) {
        // Update user table
        $update_sql = "UPDATE users 
                      SET full_name = '$full_name', 
                          phone = '$phone'
                          $password_update 
                      WHERE user_id = '$user_id'";
        
        if (mysqli_query($conn, $update_sql)) {
            $success_msg = "Profile updated successfully!";
            // Update session variables
            $_SESSION['user_name'] = $full_name;
        } else {
            $error_msg = "Error updating profile: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Profile</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f4f0f2; font-family: Arial, sans-serif; }
    .sidebar { background-color: #fff; min-height: 100vh; }
    .sidebar .nav-link { color: #333; font-weight: 500; margin-bottom: .3rem; }
    .sidebar .nav-link.active { background-color: #ffb703; color: #fff; border-radius: 8px; }
    .upgrade-box { background: linear-gradient(45deg, #ffb703, #ff9e00); border-radius: 12px; padding: 15px; color: #fff; text-align: center; margin-top: 20px; }
    .navbar-yellow { background-color: #ffeb3b; }
    .profile-card { border-radius: 12px; background: #fff; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .account-info { background-color: #f8f9fa; border-radius: 8px; padding: 15px; margin-top: 20px; }
  </style>
</head>
<body>
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-yellow shadow-sm px-3">
    <div class="container-fluid">
        <h5 class="offcanvas-title">CrackCart.</h5>
      <div class="ms-auto d-flex align-items-center gap-4">
        <a href="#" class="text-dark fs-5"><i class="bi bi-bell"></i></a>
        <div class="dropdown">
          <a class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <span class="me-2"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
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
          <li><a href="#" class="nav-link"><i class="bi bi-cart3 me-2"></i> Order</a></li>
          <li><a href="#" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
          <li><a href="#" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
          <li><a href="#" class="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
          <li><a href="profilePage.php" class="nav-link active"><i class="bi bi-gear me-2"></i> Setting</a></li>
        </ul>
        <div class="upgrade-box">
          <p>Upgrade your Account to Get Free Voucher</p>
          <button class="btn btn-light btn-sm">Upgrade</button>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col p-4">
        <!-- Display success/error messages -->
        <?php if (!empty($success_msg)): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <div class="profile-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h5 id="profileName"><?php echo htmlspecialchars($full_name); ?></h5>
              <p class="text-muted small mb-0" id="profileEmail"><?php echo htmlspecialchars($email); ?></p>
              <p class="text-muted small mb-0">Role: <?php echo ucfirst(htmlspecialchars($role)); ?></p>
            </div>
            <div>
              <button id="editBtn" class="btn btn-success">Edit</button>
              <button type="submit" form="profileForm" name="save" id="saveBtn" class="btn btn-primary d-none">Save</button>
              <button id="cancelBtn" class="btn btn-secondary d-none">Cancel</button>
            </div>
          </div>

          <form id="profileForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" id="fullName" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" disabled required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" disabled>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Password (leave blank to keep current)</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" disabled>
                <div class="form-text">Must be at least 8 characters</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password" disabled>
              </div>
            </div>

            <div class="account-info">
              <h6 class="mb-3">Account Information</h6>
              <div class="row">
                <div class="col-md-6">
                  <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                  <p class="mb-1"><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($role)); ?></p>
                </div>
                <div class="col-md-6">
                  <p class="mb-1"><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($status)); ?></p>
                  <p class="mb-1"><strong>Member since:</strong> <?php echo htmlspecialchars($created_at); ?></p>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Profile Edit Logic -->
  <script>
    const editBtn = document.getElementById("editBtn");
    const saveBtn = document.getElementById("saveBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const formFields = document.querySelectorAll("#profileForm input");
    const passwordField = document.getElementById("password");
    const confirmPasswordField = document.getElementById("confirmPassword");

    let originalValues = {};

    // Enable editing
    editBtn.addEventListener("click", () => {
      originalValues = {};
      formFields.forEach(f => {
        f.disabled = false;
        originalValues[f.id] = f.value;
      });
      editBtn.classList.add("d-none");
      saveBtn.classList.remove("d-none");
      cancelBtn.classList.remove("d-none");
    });

    // Cancel changes
    cancelBtn.addEventListener("click", () => {
      formFields.forEach(f => {
        f.value = originalValues[f.id];
        f.disabled = true;
      });
      // Clear password fields
      passwordField.value = "";
      confirmPasswordField.value = "";
      
      editBtn.classList.remove("d-none");
      saveBtn.classList.add("d-none");
      cancelBtn.classList.add("d-none");
      
      // Hide any validation messages
      document.getElementById("profileForm").classList.remove("was-validated");
    });

    // Form validation before submission
    document.getElementById("profileForm").addEventListener("submit", function(event) {
      // Check if passwords match
      if (passwordField.value !== confirmPasswordField.value) {
        event.preventDefault();
        alert("Passwords do not match!");
        return false;
      }
      
      // Check password length if provided
      if (passwordField.value && passwordField.value.length < 8) {
        event.preventDefault();
        alert("Password must be at least 8 characters long!");
        return false;
      }
      
      return true;
    });
  </script>
</body>
</html>