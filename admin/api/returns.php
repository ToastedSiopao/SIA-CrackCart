<?php
session_start();
// Security & permission check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

include '../db_connect.php';

// CORRECTED QUERY: Using correct uppercase table 'USER' and column names 'USER_ID', 'FIRST_NAME', 'LAST_NAME'
$query = "
    SELECT 
        r.return_id, r.order_id, r.status, r.created_at,
        CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name
    FROM returns r
    JOIN product_orders po ON r.order_id = po.order_id
    JOIN `USER` u ON po.user_id = u.USER_ID
    ORDER BY r.created_at DESC
";
$result = $conn->query($query);
$returns = $result->fetch_all(MYSQLI_ASSOC);

$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Returns - CrackCart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="admin-styles.css?v=1.1" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="card shadow-sm border-0 p-4">
                    <h4 class="mb-4">Return Requests</h4>
                    <div class="table-responsive">
                        <table id="returnsTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Return ID</th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Date Requested</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returns as $return): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($return['return_id']); ?></td>
                                        <td><?php echo htmlspecialchars($return['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($return['customer_name']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($return['status']); ?></span></td>
                                        <td><?php echo date("M d, Y, h:i A", strtotime($return['created_at'])); ?></td>
                                        <td>
                                            <a href="return_details.php?return_id=<?php echo $return['return_id']; ?>" class="btn btn-primary btn-sm">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#returnsTable').DataTable();
    });
    </script>
</body>
</html>