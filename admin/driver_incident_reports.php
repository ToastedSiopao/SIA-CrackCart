<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include_once('../db_connect.php');

// Fetch incidents
$incidents_sql = "
    SELECT 
        di.incident_id,
        di.order_id,
        di.incident_type,
        di.description,
        di.status,
        di.reported_at,
        u.FIRST_NAME,
        u.LAST_NAME
    FROM delivery_incidents di
    JOIN USER u ON di.driver_id = u.USER_ID
    ORDER BY di.reported_at DESC
";
$incidents_result = $conn->query($incidents_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Driver Incident Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="admin-styles.css" rel="stylesheet">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Driver Incident Reports</h1>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Incident ID</th>
                                <th>Order ID</th>
                                <th>Driver</th>
                                <th>Incident Type</th>
                                <th>Description</th>
                                <th>Reported At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($incidents_result->num_rows > 0): ?>
                                <?php while($row = $incidents_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['incident_id']); ?></td>
                                        <td><?php echo $row['order_id'] ? htmlspecialchars($row['order_id']) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($row['FIRST_NAME'] . ' ' . $row['LAST_NAME']); ?></td>
                                        <td><?php echo htmlspecialchars($row['incident_type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td><?php echo htmlspecialchars($row['reported_at']); ?></td>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">Resolve</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No incidents reported.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
