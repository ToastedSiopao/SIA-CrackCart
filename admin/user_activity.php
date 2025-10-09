<?php
session_start();
// Security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

// Fetch user data
$inactivity_days = isset($_GET['days']) ? (int)$_GET['days'] : 90;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$query = "SELECT USER_ID, FIRST_NAME, LAST_NAME, EMAIL, last_login_at, ACCOUNT_STATUS FROM USER WHERE role = 'customer'";

if ($filter === 'inactive') {
    $query .= " AND (last_login_at IS NULL OR last_login_at < NOW() - INTERVAL $inactivity_days DAY)";
}

$query .= " ORDER BY last_login_at DESC";

$result = $conn->query($query);
$users = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
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
    <title>User Activity - CrackCart</title>
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
                    <h1 class="mb-0 h2">User Activity</h1>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="user_activity.php" class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label for="filter" class="form-label">Filter Users</label>
                                <select name="filter" id="filter" class="form-select">
                                    <option value="all" <?php echo ($filter === 'all') ? 'selected' : ''; ?>>All Users</option>
                                    <option value="inactive" <?php echo ($filter === 'inactive') ? 'selected' : ''; ?>>Inactive Users</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="days" class="form-label">Inactive for (days)</label>
                                <input type="number" name="days" id="days" class="form-control" value="<?php echo $inactivity_days; ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Apply Filter</button>
                            </div>
                        </form>
                    </div>
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
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No users found matching the criteria.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['USER_ID']); ?></td>
                                        <td><?php echo htmlspecialchars($user['FIRST_NAME'] . ' ' . $user['LAST_NAME']); ?></td>
                                        <td><?php echo htmlspecialchars($user['EMAIL']); ?></td>
                                        <td><?php echo $user['last_login_at'] ? date("M j, Y, g:i a", strtotime($user['last_login_at'])) : 'Never'; ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['ACCOUNT_STATUS'] === 'ACTIVE' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo ucfirst(strtolower(htmlspecialchars($user['ACCOUNT_STATUS']))); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['ACCOUNT_STATUS'] === 'ACTIVE'): ?>
                                                <button class="btn btn-warning btn-sm" onclick="updateUserStatus(<?php echo $user['USER_ID']; ?>, 'INACTIVE')">Archive</button>
                                            <?php else: ?>
                                                <button class="btn btn-success btn-sm" onclick="updateUserStatus(<?php echo $user['USER_ID']; ?>, 'ACTIVE')">Activate</button>
                                            <?php endif; ?>
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
    function updateUserStatus(userId, newStatus) {
        const actionText = newStatus === 'INACTIVE' ? 'archive' : 'activate';
        if (!confirm(`Are you sure you want to ${actionText} this account?`)) {
            return;
        }

        fetch('api/update_user_status.php', { // Corrected Path
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to ' + actionText + ' account: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while trying to ' + actionText + ' the account.');
        });
    }
    </script>
</body>
</html>
