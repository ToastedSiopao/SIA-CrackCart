<?php
session_start();
// Security check: ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not an admin, redirect to the login page
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

$user_name = $_SESSION['user_first_name'] ?? 'Admin';

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
                    <h6 class="text-warning">Overview</h6>
                    <h4 class="mb-4">Welcome back, <?php echo htmlspecialchars($user_name); ?> 👋</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="category-card">
                                <i class="bi bi-box-seam"></i>
                                <p class="mb-0">Products</p>
                                <h5>Manage</h5>
                                <a href="products.php" class="stretched-link"></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="category-card">
                                <i class="bi bi-cart3"></i>
                                <p class="mb-0">Orders</p>
                                <h5>View</h5>
                                <a href="orders.php" class="stretched-link"></a>
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