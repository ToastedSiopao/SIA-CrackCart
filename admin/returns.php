<?php
session_start();
// Security check: ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

// Fetch all returns with user information
$query = "SELECT r.return_id, r.order_id, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, r.status, r.requested_at
          FROM returns r
          JOIN USER u ON r.user_id = u.USER_ID
          ORDER BY r.requested_at DESC";
$result = $conn->query($query);

$user_name = $_SESSION['user_first_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Management - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="admin-styles.css?v=1.0" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="card shadow-sm border-0 p-4">
                    <h4 class="mb-4">Return Management</h4>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Return ID</th>
                                    <th scope="col">Order ID</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Date Requested</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['return_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                            <td><?php echo date("M j, Y, g:i a", strtotime($row['requested_at'])); ?></td>
                                            <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span></td>
                                            <td>
                                                <a href="return_details.php?return_id=<?php echo $row['return_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No return requests found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
