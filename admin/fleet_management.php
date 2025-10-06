<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../db_connect.php';
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
    <link href="admin-styles.css?v=1.0" rel="stylesheet">
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

                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Plate No</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT vehicle_id, type, plate_no, status FROM Vehicle ORDER BY vehicle_id DESC");
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['vehicle_id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['plate_no']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                echo "<td>";
                                if ($row['status'] == 'available') {
                                    echo "<button class=\"btn btn-primary btn-sm\" onclick=\"updateVehicleStatus({$row['vehicle_id']}, 'in-transit')\">Set to In Transit</button>";
                                } else {
                                    echo "<button class=\"btn btn-success btn-sm\" onclick=\"updateVehicleStatus({$row['vehicle_id']}, 'available')\">Set to Available</button>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function updateVehicleStatus(vehicleId, newStatus) {
        if (confirm('Are you sure you want to update the vehicle status?')) {
            fetch('api/update_vehicle_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ vehicle_id: vehicleId, status: newStatus }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Vehicle status updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating status: ' + data.message);
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
            });
        }
    }
    </script>
</body>
</html>
