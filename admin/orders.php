<?php
include 'admin_header.php';
include '../db_connect.php'; // Ensure the path to db_connect is correct

// Fetch all orders with user information
$query = "SELECT o.order_id, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, o.order_date, o.total_amount, o.status 
          FROM orders o
          JOIN USER u ON o.user_id = u.USER_ID
          ORDER BY o.order_date DESC";
$result = $conn->query($query);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Order Management</h1>
    </div>

    <div class="card">
        <div class="card-header">
            All Customer Orders
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Date</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo date("M j, Y, g:i a", strtotime($row['order_date'])); ?></td>
                                    <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span></td>
                                    <td>
                                        <a href="order_details.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
include '../includes/admin_footer.php'; // Assuming a footer file exists
$conn->close();
?>
