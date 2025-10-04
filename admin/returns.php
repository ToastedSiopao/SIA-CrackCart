<?php
session_start();
// Security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

// Fetch all returns with customer information
$query = "SELECT pr.return_id, pr.order_id, pr.return_status, pr.created_at, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name
          FROM product_returns pr
          JOIN product_orders po ON pr.order_id = po.order_id
          JOIN USER u ON po.user_id = u.USER_ID
          ORDER BY pr.created_at DESC";

$result = $conn->query($query);

$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Returns - CrackCart</title>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Manage Returns</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Return ID</th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Date Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['return_id']); ?></td>
                                            <td><a href="order_details.php?order_id=<?php echo $row['order_id']; ?>"><?php echo htmlspecialchars($row['order_id']); ?></a></td>
                                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                            <td><span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($row['return_status'])); ?></span></td>
                                            <td><?php echo date("M d, Y, h:i A", strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <a href="return_details.php?return_id=<?php echo $row['return_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
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
