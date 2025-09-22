<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include("db_connect.php");
$user_id = $_SESSION['user_id'];
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
    $profile_picture = $user['profile_picture'] ?? 'default-avatar.png';
} else {
    // Handle user not found
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar">
                <!-- Sidebar content here -->
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Profile</h1>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>

                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">Profile Information</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">Security</button>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content" id="profileTabContent">
                    <!-- Profile Information Tab -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                        <form id="profileForm" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" class="profile-picture mb-3" alt="Profile Picture">
                                    <input type="file" name="profile_picture" class="form-control">
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="firstName" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="lastName" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" disabled>
                                        </div>
                                    </div>
                                    <!-- More fields -->
                                    <button type="button" id="editBtn" class="btn btn-primary">Edit</button>
                                    <button type="submit" id="saveBtn" class="btn btn-success d-none">Save</button>
                                    <button type="button" id="cancelBtn" class="btn btn-secondary d-none">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <form id="passwordForm">
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newPassword" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmNewPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="profile.js"></script>
</body>
</html>
