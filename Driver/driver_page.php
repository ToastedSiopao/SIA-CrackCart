<?php
session_start();

// --- Security check: ensure the user is a driver ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'driver') {
    header("Location: ../login.php?error=Please log in to access the driver panel.");
    exit();
}

$user_name = $_SESSION['user_first_name'] ?? 'Driver';
$user_id = $_SESSION['user_id'];

include_once("../db_connect.php");

// --- Fetch Driver and Vehicle Info ---
$driver_id = null;
$vehicle_plate = "Not Assigned";
$vehicle_type = "";

$driver_stmt = $conn->prepare("
    SELECT d.driver_id, v.plate_no, v.type
    FROM Driver d
    LEFT JOIN Vehicle v ON d.vehicle_id = v.vehicle_id
    WHERE d.driver_id = ?
");
$driver_stmt->bind_param("i", $user_id);
$driver_stmt->execute();
$driver_result = $driver_stmt->get_result();
if ($driver_result && $driver_result->num_rows > 0) {
    $driver_data = $driver_result->fetch_assoc();
    $driver_id = $driver_data['driver_id'];
    $vehicle_plate = $driver_data['plate_no'] ?? 'Not Assigned';
    $vehicle_type = $driver_data['type'] ?? '';
}
$driver_stmt->close();

if (!$driver_id) {
    die("Driver not found in the Driver table.");
}

// --- Fetch Dashboard Metrics ---
$assigned_deliveries = 0;
$completed_deliveries = 0;
$total_earnings = 0; // Placeholder

// 1. Assigned Deliveries
$assigned_stmt = $conn->prepare("SELECT COUNT(*) AS assigned_deliveries FROM Delivery_Assignment WHERE driver_id = ? AND status NOT IN ('delivered', 'cancelled')");
$assigned_stmt->bind_param("i", $driver_id);
$assigned_stmt->execute();
$assigned_result = $assigned_stmt->get_result();
if ($assigned_result && $assigned_result->num_rows > 0) {
    $assigned_deliveries = $assigned_result->fetch_assoc()['assigned_deliveries'] ?? 0;
}
$assigned_stmt->close();

// 2. Completed Deliveries
$completed_stmt = $conn->prepare("SELECT COUNT(*) AS completed_deliveries FROM Delivery_Assignment WHERE driver_id = ? AND status = 'delivered'");
$completed_stmt->bind_param("i", $driver_id);
$completed_stmt->execute();
$completed_result = $completed_stmt->get_result();
if ($completed_result && $completed_result->num_rows > 0) {
    $completed_deliveries = $completed_result->fetch_assoc()['completed_deliveries'] ?? 0;
}
$completed_stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="driver-styles.css?v=1.1" rel="stylesheet">
</head>
<body>
    <?php include('driver_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('driver_sidebar.php'); ?>
            <?php include('driver_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="card shadow-sm border-0 p-4">
                    <h6 class="text-muted">Overview</h6>
                    <h4 class="mb-4">Welcome back, <?php echo htmlspecialchars($user_name); ?> 👋</h4>
                    
                    <div class="row g-4">
                        <!-- Assigned Deliveries -->
                        <div class="col-md-6">
                            <div class="category-card dashboard-metric h-100">
                                <i class="bi bi-truck"></i>
                                <div>
                                    <p class="mb-0">Assigned Deliveries</p>
                                    <h5><?php echo number_format($assigned_deliveries); ?></h5>
                                </div>
                                <a href="#" class="stretched-link" title="View Assigned Deliveries"></a>
                            </div>
                        </div>

                        <!-- Completed Deliveries -->
                        <div class="col-md-6">
                             <div class="category-card dashboard-metric h-100">
                                <i class="bi bi-check-circle"></i>
                                <div>
                                    <p class="mb-0">Completed Deliveries</p>
                                    <h5><?php echo number_format($completed_deliveries); ?></h5> 
                                </div>
                                <a href="#" class="stretched-link" title="View Completed Deliveries"></a>
                            </div>
                        </div>

                        <!-- Assigned Vehicle -->
                        <div class="col-md-6">
                            <div class="category-card dashboard-metric h-100">
                                <i class="bi bi-car-front-fill"></i>
                                <div>
                                    <p class="mb-0">Assigned Vehicle</p>
                                    <h5><?php echo htmlspecialchars($vehicle_plate); ?></h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($vehicle_type); ?></small>
                                </div>
                                <a href="#" class="stretched-link" title="View Vehicle Details"></a>
                            </div>
                        </div>

                        <!-- Total Earnings -->
                        <div class="col-md-6">
                             <div class="category-card dashboard-metric h-100">
                                <i class="bi bi-cash-coin"></i>
                                <div>
                                    <p class="mb-0">Total Earnings</p>
                                    <h5>₱<?php echo number_format($total_earnings, 2); ?></h5>
                                </div>
                                <a href="#" class="stretched-link" title="View Earnings"></a>
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
