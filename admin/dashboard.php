<?php
session_start();

// --- Security check: ensure the user is an admin ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

$user_name = $_SESSION['user_first_name'] ?? 'Admin';

include_once("../db_connect.php");

// --- Check if DB connection is valid ---
if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// --- Fetch Dashboard Metrics ---
$total_trays = 0;
$active_orders = 0;
$total_revenue = 0;

// 1️⃣ Calculate total stock in trays
$tray_result = $conn->query("SELECT SUM(FLOOR(STOCK / tray_size)) AS total_trays FROM PRICE WHERE tray_size > 0");
if ($tray_result && $tray_result->num_rows > 0) {
    $total_trays = $tray_result->fetch_assoc()['total_trays'] ?? 0;
}

// 2️⃣ Calculate active (incomplete) orders
$order_result = $conn->query("SELECT COUNT(*) AS active_orders FROM product_orders WHERE status NOT IN ('delivered', 'cancelled')");
if ($order_result && $order_result->num_rows > 0) {
    $active_orders = $order_result->fetch_assoc()['active_orders'] ?? 0;
}

// 3️⃣ Calculate total revenue from delivered orders
$revenue_result = $conn->query("SELECT SUM(total_amount) AS total_revenue FROM product_orders WHERE status = 'delivered'");
if ($revenue_result && $revenue_result->num_rows > 0) {
    $total_revenue = $revenue_result->fetch_assoc()['total_revenue'] ?? 0;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="admin-styles.css?v=1.4" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="card shadow-sm border-0 p-4">
                    <h6 class="text-muted">Overview</h6>
                    <h4 class="mb-4">Welcome back, <?php echo htmlspecialchars($user_name); ?> 👋</h4>
                    
                    <div class="row g-4">
                        <!-- Total Stock -->
                        <div class="col-md-4">
                            <div class="category-card dashboard-metric h-100">
                                <i class="bi bi-box-seam"></i>
                                <div>
                                    <p class="mb-0">Total Stock (Trays)</p>
                                    <h5><?php echo number_format($total_trays); ?></h5>
                                </div>
                                <a href="products.php" class="stretched-link" title="Manage Products"></a>
                            </div>
                        </div>

                        <!-- Active Orders -->
                        <div class="col-md-4">
                             <div class="category-card dashboard-metric h-100">
                                <i class="bi bi-cart3"></i>
                                <div>
                                    <p class="mb-0">Active Orders</p>
                                    <h5><?php echo number_format($active_orders); ?></h5> 
                                </div>
                                <a href="manage_orders.php" class="stretched-link" title="Manage Orders"></a>
                            </div>
                        </div>

                        <!-- Total Revenue -->
                        <div class="col-md-4">
                             <div class="category-card dashboard-metric h-100">
                                <i class="bi bi-cash-coin"></i>
                                <div>
                                    <p class="mb-0">Total Revenue</p>
                                    <h5>₱<?php echo number_format($total_revenue, 2); ?></h5>
                                </div>
                                <a href="manage_orders.php" class="stretched-link" title="View Order History"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
