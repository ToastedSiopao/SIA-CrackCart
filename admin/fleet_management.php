<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../db_connect.php';

// Fetch all vehicles and categorize them by type
$vehicles_by_type = [];
$query = "SELECT vehicle_id, type, plate_no, status FROM Vehicle ORDER BY type, CASE WHEN status = 'available' THEN 0 ELSE 1 END, status, plate_no";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vehicles_by_type[$row['type']][] = $row;
    }
}

// Get a sorted list of vehicle types
$vehicle_types = array_keys($vehicles_by_type);
sort($vehicle_types);

$status_config = [
    'available' => ['icon' => 'bi-check-circle-fill', 'text_class' => 'text-success'],
    'in-transit' => ['icon' => 'bi-truck', 'text_class' => 'text-primary'],
    'maintenance' => ['icon' => 'bi-tools', 'text_class' => 'text-warning']
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Management - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="admin-styles.css?v=1.2" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Fleet Management</h1>
                </div>

                <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>

                <div class="row">
                    <?php if (empty($vehicle_types)) : ?>
                        <div class="col">
                            <p class="text-muted fst-italic">No vehicles found.</p>
                        </div>
                    <?php else : ?>
                        <?php foreach ($vehicle_types as $type) : ?>
                            <div class="col-lg-4 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($type); ?></h5>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <?php
                                        $last_status = null;
                                        foreach ($vehicles_by_type[$type] as $vehicle) :
                                            $current_status = $vehicle['status'];
                                            // Add a separator if the status changes from 'available' to something else
                                            if ($last_status === 'available' && $current_status !== 'available') {
                                                echo '<div class="list-group-item"><hr class="my-1"></div>';
                                            }
                                            $last_status = $current_status;
                                            $config = $status_config[$current_status];
                                        ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="<?php echo $config['text_class']; ?> me-2" title="<?php echo ucfirst($current_status); ?>"><i class="bi <?php echo $config['icon']; ?>"></i></span>
                                                        <span class="fw-bold"><?php echo htmlspecialchars($vehicle['plate_no']); ?></span>
                                                    </div>
                                                    <div class="btn-group">
                                                        <?php if ($current_status === 'available') : ?>
                                                            <button class="btn btn-sm btn-outline-primary" title="Set to In-Transit" onclick="updateVehicleStatus(<?php echo $vehicle['vehicle_id']; ?>, 'in-transit')"><i class="bi bi-truck"></i></button>
                                                            <button class="btn btn-sm btn-outline-secondary" title="Set to Maintenance" onclick="updateVehicleStatus(<?php echo $vehicle['vehicle_id']; ?>, 'maintenance')"><i class="bi bi-tools"></i></button>
                                                        <?php else : ?>
                                                            <button class="btn btn-sm btn-outline-success" title="Set to Available" onclick="updateVehicleStatus(<?php echo $vehicle['vehicle_id']; ?>, 'available')"><i class="bi bi-check-circle"></i></button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertContainer.appendChild(alertDiv);

        const bsAlert = new bootstrap.Alert(alertDiv);
        setTimeout(() => bsAlert.close(), 5000);
    }

    function updateVehicleStatus(vehicleId, newStatus) {
        const message = `Are you sure you want to set vehicle #${vehicleId} to "${newStatus}"?`;
        if (confirm(message)) {
            fetch('api/update_vehicle_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ vehicle_id: vehicleId, status: newStatus }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message || 'An unknown error occurred.', 'danger');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                showAlert('An unexpected network error occurred.', 'danger');
            });
        }
    }
    </script>
</body>
</html>
