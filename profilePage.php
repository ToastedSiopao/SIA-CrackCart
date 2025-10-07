<?php
require_once 'session_handler.php';
require_once 'db_connect.php';
require_once 'log_function.php';

$user_id = $_SESSION['user_id'];
$user_data = null;
$error_msg = '';
$success_msg = '';

// --- File upload settings ---
define('UPLOAD_DIR', 'uploads/profile_pictures/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];

// --- Fetch user data ---
try {
    $stmt = $conn->prepare("SELECT FIRST_NAME, LAST_NAME, EMAIL, PHONE, CREATED_AT, PROFILE_PICTURE FROM USER WHERE USER_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) throw new Exception('User not found.');
    $user_data = $result->fetch_assoc();
    if (empty($user_data['PROFILE_PICTURE']) || !file_exists($user_data['PROFILE_PICTURE'])) {
        $user_data['PROFILE_PICTURE'] = UPLOAD_DIR . 'default_avatar.png';
    }
} catch (Exception $e) {
    $error_msg = "Error loading user data: " . $e->getMessage();
}

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_data) {

    // Handle Profile Picture Upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);

        if ($file['size'] > MAX_FILE_SIZE) {
            $error_msg = "File is too large. Max size is 5 MB.";
        } elseif (!in_array($mime_type, $allowed_mimes)) {
            $error_msg = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        } else {
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = UPLOAD_DIR . bin2hex(random_bytes(16)) . '.' . $file_extension;

            if (move_uploaded_file($file['tmp_name'], $new_filename)) {
                try {
                    $conn->begin_transaction();
                    $old_picture = $user_data['PROFILE_PICTURE'];

                    $stmt = $conn->prepare("UPDATE USER SET PROFILE_PICTURE = ? WHERE USER_ID = ?");
                    $stmt->bind_param("si", $new_filename, $user_id);
                    $stmt->execute();
                    $conn->commit();

                    $success_msg = "Profile picture updated!";
                    $user_data['PROFILE_PICTURE'] = $new_filename;

                    if (basename($old_picture) !== 'default_avatar.png' && file_exists($old_picture)) {
                        unlink($old_picture);
                    }
                    log_action($user_id, 'Profile Update', 'User updated their profile picture.');
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_msg = "DB Error: " . $e->getMessage();
                    if (file_exists($new_filename)) unlink($new_filename);
                }
            } else {
                $error_msg = "Failed to move uploaded file.";
            }
        }
    }

    // Handle Profile Information Update
    if (isset($_POST['save_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $phone = preg_replace('/[^0-9+]/i', '', $_POST['phone']); // Sanitize phone
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($first_name) || empty($last_name)) {
            $error_msg = "First and last names cannot be empty.";
        } elseif (!empty($password) && strlen($password) < 8) {
            $error_msg = "Password must be at least 8 characters long.";
        } elseif (!empty($password) && $password !== $confirm_password) {
            $error_msg = "New passwords do not match.";
        } else {
            try {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE USER SET FIRST_NAME = ?, LAST_NAME = ?, PHONE = ?, PASSWORD = ? WHERE USER_ID = ?");
                    $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $hashed_password, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE USER SET FIRST_NAME = ?, LAST_NAME = ?, PHONE = ? WHERE USER_ID = ?");
                    $stmt->bind_param("sssi", $first_name, $last_name, $phone, $user_id);
                }

                if ($stmt->execute()) {
                    $success_msg = "Profile details updated successfully!";
                    $user_data['FIRST_NAME'] = $first_name;
                    $user_data['LAST_NAME'] = $last_name;
                    $user_data['PHONE'] = $phone;
                    $_SESSION['user_first_name'] = $first_name;
                    log_action($user_id, 'Profile Update', 'User updated their profile information.');
                } else {
                    throw new Exception('Failed to update profile.');
                }
            } catch (mysqli_sql_exception $e) {
                if($e->getCode() == 1062){ // Duplicate entry
                    $error_msg = "An account with this phone number already exists.";
                } else {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } catch (Exception $e) {
                 $error_msg = "An unexpected error occurred: " . $e->getMessage();
            }
        }
    }
}

$profile_pic_path = $user_data['PROFILE_PICTURE'] ?? (UPLOAD_DIR . 'default_avatar.png');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Profile - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=3.4" rel="stylesheet">
  <style>
    .profile-avatar { width: 120px; height: 120px; object-fit: cover; border: 4px solid #ffc107; }
    .form-control-icon { position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); color: #6c757d; }
    .card { border: none; border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
  </style>
</head>
<body>
  <?php include("navbar.php"); ?>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">My Profile</h2>
             <a href="settings.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left"></i> Back to Settings</a>
        </div>

        <!-- Alerts -->
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success_msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
             <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error_msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if ($user_data): ?>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card text-center p-4 h-100 justify-content-center">
                        <img src="<?= htmlspecialchars($profile_pic_path) ?>?v=<?= time() ?>" alt="User Avatar" class="profile-avatar rounded-circle mx-auto mb-3">
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($user_data['FIRST_NAME'] . ' ' . $user_data['LAST_NAME']) ?></h4>
                        <p class="text-muted mb-3"><?= htmlspecialchars($user_data['EMAIL']) ?></p>
                        
                        <form method="POST" enctype="multipart/form-data" id="pictureForm">
                            <label for="profile_picture_upload" class="btn btn-sm btn-warning">Change Picture</label>
                            <input type="file" id="profile_picture_upload" name="profile_picture" class="d-none" onchange="document.getElementById('pictureForm').submit()">
                        </form>
                        
                        <hr class="my-4">
                        <p class="text-muted small mb-1">Member Since</p>
                        <p class="fw-500"><?= date("F j, Y", strtotime($user_data['CREATED_AT'])) ?></p>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header fw-bold">Edit Your Information</div>
                        <div class="card-body p-4">
                            <form method="POST" id="profile-form" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6"><label for="first_name" class="form-label">First Name</label><input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user_data['FIRST_NAME']) ?>" required></div>
                                    <div class="col-md-6"><label for="last_name" class="form-label">Last Name</label><input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user_data['LAST_NAME']) ?>" required></div>
                                    <div class="col-md-6"><label for="email" class="form-label">Email Address</label><input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user_data['EMAIL']) ?>" disabled readonly></div>
                                    <div class="col-md-6"><label for="phone" class="form-label">Phone Number</label><input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user_data['PHONE'] ?? '') ?>"></div>
                                </div>
                                <hr class="my-4">
                                <p class="fw-bold">Change Password</p>
                                <div class="row g-3">
                                    <div class="col-md-6"><label for="password" class="form-label">New Password</label><input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current" autocomplete="new-password"></div>
                                    <div class="col-md-6"><label for="confirm_password" class="form-label">Confirm New Password</label><input type="password" class="form-control" id="confirm_password" name="confirm_password"></div>
                                </div>
                                <button type="submit" name="save__profile" class="btn btn-warning btn-lg mt-4">Save Changes</button>
                            </form>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-header text-danger fw-bold">Danger Zone</div>
                        <div class="card-body p-4 d-flex justify-content-between align-items-center">
                            <div><h6 class="fw-bold mb-1">Delete Account</h6><p class="mb-0 text-muted small">Once you delete your account, there is no going back. All associated data will be permanently removed.</p></div>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Delete Account</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
             <div class="alert alert-danger">Could not load user profile. Please try refreshing the page.</div>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <!-- Delete Account Modal -->
  <div class="modal fade" id="deleteAccountModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Are you absolutely sure?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>This action cannot be undone. This will permanently delete your account and remove your data from our servers.</p><p>Please type your email <strong><?= htmlspecialchars($user_data['EMAIL']) ?></strong> to confirm.</p><input type="email" class="form-control" id="delete-confirm-email" placeholder="Enter your email"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><a href="#" id="delete-account-btn" class="btn btn-danger disabled">I understand, delete my account</a></div></div></div></div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // JavaScript for delete confirmation modal
    const deleteConfirmEmail = document.getElementById('delete-confirm-email');
    const deleteAccountBtn = document.getElementById('delete-account-btn');
    const correctEmail = "<?= htmlspecialchars($user_data['EMAIL']) ?>";

    if(deleteConfirmEmail) {
        deleteConfirmEmail.addEventListener('keyup', function() {
            if (this.value === correctEmail) {
                deleteAccountBtn.classList.remove('disabled');
                deleteAccountBtn.href = 'delete_account.php?confirm=true';
            } else {
                deleteAccountBtn.classList.add('disabled');
                deleteAccountBtn.href = '#';
            }
        });
    }    
  </script>
</body>
</html>
