<?php
session_start();
// Security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

// Fetch archived user data
$query = "SELECT USER_ID, FIRST_NAME, LAST_NAME, EMAIL, last_login_at, ACCOUNT_STATUS FROM archived_users ORDER BY last_login_at DESC";

$result = $conn->query($query);
$archived_users = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $archived_users[] = $row;
    }
}

$conn->close();
$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Users - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="admin-styles.css?v=1.6" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0 h2">Archived Users</h1>
                </div>

                <div class="alert alert-warning" role="alert">
                    <h5 class="alert-heading">About Archived Users</h5>
                    <p>This page lists all accounts that have been archived. Archiving an account removes it from the main user list but preserves the data. You can restore an account at any time, which will make it active again.</p>
                    <p class="mb-0"><strong>Note:</strong> If you try to restore a user whose email address is already in use by an active account, the process will fail to prevent conflicts.</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($archived_users)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No archived users found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($archived_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['USER_ID']); ?></td>
                                        <td><?php echo htmlspecialchars($user['FIRST_NAME'] . ' ' . $user['LAST_NAME']); ?></td>
                                        <td><?php echo htmlspecialchars($user['EMAIL']); ?></td>
                                        <td><?php echo $user['last_login_at'] ? date("M j, Y, g:i a", strtotime($user['last_login_at'])) : 'Never'; ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                Archived
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-success btn-sm" onclick="restoreUser(<?php echo $user['USER_ID']; ?>)">Restore</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function restoreUser(userId) {
        if (!confirm(`Are you sure you want to restore this account?`)) {
            return;
        }

        fetch('api/update_user_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, status: 'ACTIVE' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to restore account: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while restoring the account.');
        });
    }
    </script>
</body>
</html>
