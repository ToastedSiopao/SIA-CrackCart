<?php
require_once '../session_handler.php';
require_once '../db_connect.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch all orders with customer information
$query = "SELECT po.order_id, u.user_name, po.order_date, po.total_price, po.status 
          FROM product_orders po
          JOIN users u ON po.user_id = u.id
          ORDER BY po.order_date DESC";
$result = $conn->query($query);

$orders = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="admin-styles.css?v=1.2" rel="stylesheet">
  <style>
      .status-badge { font-size: 0.9em; }
  </style>
</head>
<body>

<div class="container-fluid">
    <div class="row flex-nowrap">
        <?php include 'admin_sidebar.php'; ?>
        <?php include 'admin_offcanvas_sidebar.php'; ?>

        <div class="col p-0">
            <?php include 'admin_header.php'; ?>

            <main class="container-fluid p-4">
                <h1 class="mb-4">Manage Customer Orders</h1>

                <div id="alert-container"></div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Order Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                        <tr><td colspan="6" class="text-center">No orders found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                                <td><?php echo date("F j, Y, g:i a", strtotime($order['order_date'])); ?></td>
                                                <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?php echo getStatusClass($order['status']); ?> status-badge" id="status-<?php echo $order['order_id']; ?>">
                                                        <?php echo htmlspecialchars($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton-<?php echo $order['order_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Update Status
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-<?php echo $order['order_id']; ?>">
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'To Pay')">To Pay</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'To Ship')">To Ship</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'To Receive')">To Receive</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'Completed')">Completed</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'Cancelled')">Cancelled</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(orderId, newStatus) {
    if (!confirm(`Are you sure you want to update order #${orderId} to "${newStatus}"?`)) {
        return;
    }

    fetch('api/update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ order_id: orderId, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        const alertContainer = document.getElementById('alert-container');
        let alertClass = data.status === 'success' ? 'alert-success' : 'alert-danger';
        
        alertContainer.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${data.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;

        if (data.status === 'success') {
            const statusBadge = document.getElementById(`status-${orderId}`);
            statusBadge.textContent = newStatus;
            statusBadge.className = `badge rounded-pill ${getStatusClass(newStatus)} status-badge`;
        }
        
        // Auto-dismiss alert
        setTimeout(() => {
            const alert = bootstrap.Alert.getInstance(alertContainer.firstChild);
            if(alert) alert.close();
        }, 5000);
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        const alertContainer = document.getElementById('alert-container');
        alertContainer.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
            An unexpected error occurred. Please check the console for details.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
    });
}

function getStatusClass(status) {
    switch (status) {
        case 'Completed': return 'bg-success';
        case 'To Ship': return 'bg-info';
        case 'To Receive': return 'bg-primary';
        case 'Cancelled': return 'bg-danger';
        case 'To Pay':
        default: return 'bg-warning text-dark';
    }
}
</script>
<?php
function getStatusClass($status) {
    switch ($status) {
        case 'Completed': return 'bg-success';
        case 'To Ship': return 'bg-info';
        case 'To Receive': return 'bg-primary';
        case 'Cancelled': return 'bg-danger';
        case 'To Pay':
        default: return 'bg-warning text-dark';
    }
}
?>
</body>
</html>