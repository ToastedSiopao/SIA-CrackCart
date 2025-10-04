<?php
session_start();
// Security check: ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not an admin, redirect to the login page
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../dashboard-styles.css?v=1.1" rel="stylesheet"> <!-- Link to existing stylesheet -->
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <?php include('admin_sidebar.php'); ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h2>
                <p>This is your central hub for managing products, orders, and more. Use the navigation on the left to get started.</p>

                <!-- Example Content/Widgets can go here -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Products</h5>
                                <p class="card-text">Manage your product catalog, including adding, editing, and deleting items.</p>
                                <a href="products.php" class="btn btn-primary">Go to Products</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Orders</h5>
                                <p class="card-text">View and manage customer orders, including bulk updates and returns.</p>
                                <a href="orders.php" class="btn btn-primary">Go to Orders</a>
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
