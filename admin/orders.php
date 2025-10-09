<?php
session_start();
// Security check: ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

// Corrected table name from 'orders' to 'product_orders'
$query = "SELECT o.order_id, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, o.order_date, o.total_amount, o.status 
          FROM product_orders o
          JOIN USER u ON o.user_id = u.USER_ID
          ORDER BY o.order_date DESC";
$result = $conn->query($query);

if (!$result) {
    $error_message = "Error fetching orders: " . $conn->error;
}

$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - CrackCart</title>
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
                    <h4 class="mb-4">Order Management</h4>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Order ID</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                            <td><?php echo date("M j, Y, g:i a", strtotime($row['order_date'])); ?></td>
                                            <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td>
                                                <?php
                                                $status = strtolower($row['status']);
                                                $badge_class = 'bg-secondary'; // Default
                                                if ($status == 'paid') $badge_class = 'bg-success';
                                                else if ($status == 'pending') $badge_class = 'bg-warning text-dark';
                                                else if (in_array($status, ['cancelled', 'failed'])) $badge_class = 'bg-danger';
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span>
                                            </td>
                                            <td>
                                                <a href="order_details.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No orders found.</td>
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
if(isset($result) && $result) $result->close();
$conn->close();
?>
