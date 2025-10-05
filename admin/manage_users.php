<?php
require_once '../session_handler.php';
require_once '../db_connect.php';

// Ensure user is an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch all users (excluding the current admin to prevent self-modification)
$current_admin_id = $_SESSION['user_id'];
$query = "SELECT id, user_name, email, status, role, created_at FROM users WHERE id != ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_admin_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="admin-styles.css?v=1.3" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row flex-nowrap">
        <?php include 'admin_sidebar.php'; ?>
        <?php include 'admin_offcanvas_sidebar.php'; ?>

        <div class="col p-0">
            <?php include 'admin_header.php'; ?>

            <main class="container-fluid p-4">
                <h1 class="mb-4">Manage User Accounts</h1>

                <div id="alert-container"></div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr><td colspan="6" class="text-center">No other user accounts found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr id="user-row-<?php echo $user['id']; ?>">
                                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $user['status'] === 'LOCKED' ? 'bg-danger' : 'bg-success'; ?>" id="status-badge-<?php echo $user['id']; ?>">
                                                        <?php echo htmlspecialchars($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" onchange="updateRole(<?php echo $user['id']; ?>, this.value)" id="role-select-<?php echo $user['id']; ?>">
                                                        <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <?php if ($user['status'] === 'LOCKED'): ?>
                                                        <button class="btn btn-sm btn-outline-success" id="unlock-btn-<?php echo $user['id']; ?>" onclick="unlockUser(<?php echo $user['id']; ?>)">
                                                            <i class="bi bi-unlock"></i> Unlock
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic small">Account Active</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    
    const alertHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
    
    alertContainer.innerHTML = alertHTML;

    setTimeout(() => {
        const alert = bootstrap.Alert.getInstance(alertContainer.firstChild);
        if(alert) alert.close();
    }, 5000);
}

function updateRole(userId, newRole) {
    if (!confirm(`Are you sure you want to change this user's role to "${newRole}"?`)) {
        // Revert dropdown if user cancels
        const dropdown = document.getElementById(`role-select-${userId}`);
        dropdown.value = newRole === 'admin' ? 'customer' : 'admin';
        return;
    }

    fetch('api/update_user_role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ user_id: userId, role: newRole })
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.status);
        if (data.status !== 'success') { // Revert on failure
            const dropdown = document.getElementById(`role-select-${userId}`);
            dropdown.value = newRole === 'admin' ? 'customer' : 'admin';
        }
    })
    .catch(error => {
        console.error('Role Update Error:', error);
        showAlert('An unexpected error occurred while updating the role.', 'danger');
        const dropdown = document.getElementById(`role-select-${userId}`);
        dropdown.value = newRole === 'admin' ? 'customer' : 'admin';
    });
}

function unlockUser(userId) {
    if (!confirm("Are you sure you want to unlock this user's account?")) return;

    fetch('api/unlock_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.status);
        if (data.status === 'success') {
            const statusBadge = document.getElementById(`status-badge-${userId}`);
            statusBadge.textContent = 'ACTIVE';
            statusBadge.classList.remove('bg-danger');
            statusBadge.classList.add('bg-success');

            const unlockButton = document.getElementById(`unlock-btn-${userId}`);
            if(unlockButton) {
                unlockButton.outerHTML = '<span class="text-muted fst-italic small">Account Active</span>';
            }
        }
    })
    .catch(error => {
        console.error('Unlock Error:', error);
        showAlert('An unexpected error occurred while unlocking the user.', 'danger');
    });
}
</script>

</body>
</html>