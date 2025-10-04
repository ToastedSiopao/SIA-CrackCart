<?php
include 'admin_header.php';
include '../db_connect.php';

// Fetch all returns with user information
$query = "SELECT r.return_id, r.order_id, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, r.status, r.requested_at
          FROM returns r
          JOIN USER u ON r.user_id = u.USER_ID
          ORDER BY r.requested_at DESC";
$result = $conn->query($query);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Return Management</h1>
    </div>

    <div class="card">
        <div class="card-header">
            All Customer Return Requests
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Return ID</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Date Requested</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['return_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo date("M j, Y, g:i a", strtotime($row['requested_at'])); ?></td>
                                    <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span></td>
                                    <td>
                                        <a href="return_details.php?return_id=<?php echo $row['return_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No return requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
include '../includes/admin_footer.php'; 
$conn->close();
?>
