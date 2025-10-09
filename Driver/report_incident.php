<?php
session_start();

// --- Security check: ensure the user is a driver ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'driver') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

include_once("../db_connect.php");

$driver_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_first_name'] ?? 'Driver';

// --- Fetch Assigned Orders for the Driver ---
$assigned_orders = [];
$orders_stmt = $conn->prepare("
    SELECT po.order_id 
    FROM product_orders po
    JOIN Delivery_Assignment da ON po.order_id = da.booking_id
    WHERE da.driver_id = ? AND po.status = 'shipped'
");
$orders_stmt->bind_param("i", $driver_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
while ($row = $orders_result->fetch_assoc()) {
    $assigned_orders[] = $row;
}
$orders_stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Incident - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="driver-styles.css?v=1.1" rel="stylesheet">
    <link href="driver-incident-report.css?v=1.0" rel="stylesheet">
</head>
<body>
    <?php include('driver_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('driver_sidebar.php'); ?>
            <?php include('driver_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Report an Incident</h2>
                    <i class="bi bi-exclamation-triangle-fill incident-icon"></i>
                </div>

                <div id="alert-container"></div>

                <div class="card p-4">
                    <form id="incident-report-form" action="api/submit_incident.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="order-id" class="form-label">Related Order (Optional)</label>
                                <select class="form-select" id="order-id" name="order_id">
                                    <option value="">None</option>
                                    <?php foreach ($assigned_orders as $order): ?>
                                        <option value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                            Order #<?php echo htmlspecialchars($order['order_id']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="incident-type" class="form-label">Incident Type</label>
                                <select class="form-select" id="incident-type" name="incident_type" required>
                                    <option value="" disabled selected>Select an incident type</option>
                                    <option value="vehicle_issue">Vehicle Issue</option>
                                    <option value="customer_issue">Customer Issue</option>
                                    <option value="accident">Accident</option>
                                    <option value="traffic_delay">Traffic Delay</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Provide a detailed description of the incident..." required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-yellow">Submit Report</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('incident-report-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const alertContainer = document.getElementById('alert-container');

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let alertClass = data.status === 'success' ? 'alert-success' : 'alert-danger';
                let alertMessage = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                                        ${data.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>`;
                alertContainer.innerHTML = alertMessage;

                if (data.status === 'success') {
                    form.reset();
                }
                window.scrollTo(0, 0);
            })
            .catch(error => {
                let alertMessage = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        An unexpected error occurred. Please try again.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>`;
                alertContainer.innerHTML = alertMessage;
                window.scrollTo(0, 0);
            });
        });
    </script>
</body>
</html>
