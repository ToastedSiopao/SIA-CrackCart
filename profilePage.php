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
$first_name = $middle_name = $last_name = $email = $phone = $role = $house_no = $street_name = $barangay = $city = $created_at = "";
$success_msg = $error_msg = "";

// Fetch user data from database
$sql = "SELECT * FROM USER WHERE USER_ID = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $first_name = $user['FIRST_NAME'];
    $middle_name = $user['MIDDLE_NAME'] ?? '';
    $last_name = $user['LAST_NAME'];
    $email = $user['EMAIL'];
    $phone = $user['PHONE'] ?? '';
    $role = $user['ROLE'] ?? 'customer';
    $house_no = $user['HOUSE_NO'] ?? '';
    $street_name = $user['STREET_NAME'] ?? '';
    $barangay = $user['BARANGAY'] ?? '';
    $city = $user['CITY'] ?? '';
    $created_at = $user['CREATED_AT'] ?? '';
} else {
    $error_msg = "User not found!";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    // Get and sanitize form data
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $house_no = mysqli_real_escape_string($conn, $_POST['house_no']);
    $street_name = mysqli_real_escape_string($conn, $_POST['street_name']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    
    // Check if password is being changed
    $password_update = "";
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 8) {
            $error_msg = "Password must be at least 8 characters long.";
        } else {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_update = ", PASSWORD = '$password'";
        }
    }
    
    // Only update if no error with password
    if (empty($error_msg)) {
        // Update user table
        $update_sql = "UPDATE USER 
                      SET FIRST_NAME = '$first_name', 
                          MIDDLE_NAME = '$middle_name',
                          LAST_NAME = '$last_name',
                          PHONE = '$phone',
                          HOUSE_NO = '$house_no',
                          STREET_NAME = '$street_name',
                          BARANGAY = '$barangay',
                          CITY = '$city'
                          $password_update 
                      WHERE USER_ID = '$user_id'";
        
        if (mysqli_query($conn, $update_sql)) {
            $success_msg = "Profile updated successfully!";
            // Update session variables
            $_SESSION['user_first_name'] = $first_name;
            $_SESSION['user_middle_name'] = $middle_name;
            $_SESSION['user_last_name'] = $last_name;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_house_no'] = $house_no;
            $_SESSION['user_street_name'] = $street_name;
            $_SESSION['user_barangay'] = $barangay;
            $_SESSION['user_city'] = $city;
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
            <span class="me-2"><?php echo htmlspecialchars($_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name']); ?></span>
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
          <li><a href="order.php" class="nav-link"><i class="bi bi-cart3 me-2"></i>Make an Order</a></li>
          <li><a href="eggspress.php" class="nav-link"><i class="bi bi-truck me-2"></i> Eggspress</a></li>
          <li><a href="messages.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
          <li><a href="history.php" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
          <li><a href="bills.php" class="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
          <li><a href="profilePage.php" class="nav-link active"><i class="bi bi-gear me-2"></i> Setting</a></li>
          <li><a href="producers.php" class="nav-link"><i class="bi bi-egg me-2"></i> Producers</a></li>

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
              <h5 id="profileName"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h5>
              <p class="text-muted small mb-0" id="profileEmail"><?php echo htmlspecialchars($email); ?></p>
            </div>
            <div>
              <button id="editBtn" class="btn btn-success">Edit</button>
              <button type="submit" form="profileForm" name="save" id="saveBtn" class="btn btn-primary d-none">Save</button>
              <button id="cancelBtn" class="btn btn-secondary d-none">Cancel</button>
            </div>
          </div>

          <form id="profileForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Name Fields -->
            <div class="row mb-3">
              <div class="col-md-4">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" disabled required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="middleName" name="middle_name" value="<?php echo htmlspecialchars($middle_name); ?>" disabled>
              </div>
              <div class="col-md-4">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" disabled required>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
                <div class="form-text">Email cannot be changed</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" disabled>
              </div>
            </div>

            <!-- Address Fields -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">House No.</label>
                <input type="text" class="form-control" id="houseNo" name="house_no" value="<?php echo htmlspecialchars($house_no); ?>" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">Street Name</label>
                <input type="text" class="form-control" id="streetName" name="street_name" value="<?php echo htmlspecialchars($street_name); ?>" disabled>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Barangay</label>
                <input type="text" class="form-control" id="barangay" name="barangay" value="<?php echo htmlspecialchars($barangay); ?>" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">City</label>
                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" disabled required>
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
                </div>
                <div class="col-md-6">
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
        // Skip email field as it cannot be changed
        if (f.type !== 'email') {
          f.disabled = false;
          originalValues[f.id] = f.value;
        }
      });
      editBtn.classList.add("d-none");
      saveBtn.classList.remove("d-none");
      cancelBtn.classList.remove("d-none");
    });

    // Cancel changes
    cancelBtn.addEventListener("click", () => {
      formFields.forEach(f => {
        if (f.type !== 'email') {
          f.value = originalValues[f.id];
          f.disabled = true;
        }
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
</html