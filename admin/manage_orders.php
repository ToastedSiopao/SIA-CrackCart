<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch all orders with customer and vehicle information
$query = "SELECT po.order_id, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as user_name, po.order_date, po.total_amount, po.status, v.type as vehicle_name 
          FROM product_orders po
          JOIN USER u ON po.user_id = u.USER_ID
          LEFT JOIN Vehicle v ON po.vehicle_id = v.vehicle_id
          ORDER BY po.order_date DESC";
$result = $conn->query($query);

$orders = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Fetch available vehicles
$vehicles_result = $conn->query("SELECT vehicle_id, type, plate_no FROM Vehicle WHERE status = 'available'");
$available_vehicles = [];
if ($vehicles_result->num_rows > 0) {
    while($row = $vehicles_result->fetch_assoc()) {
        $available_vehicles[] = $row;
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
  <link href="admin-styles.css?v=1.3" rel="stylesheet">
  <style>
      .status-badge { font-size: 0.9em; }
      .vehicle-info { font-size: 0.8em; color: #6c757d; }
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
                                        <th>Status & Vehicle</th>
                                        <th>Actions</th>
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
                                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?php echo getStatusClass($order['status']); ?> status-badge" id="status-<?php echo $order['order_id']; ?>">
                                                        <?php echo htmlspecialchars($order['status']); ?>
                                                    </span>
                                                    <div class="vehicle-info" id="vehicle-info-<?php echo $order['order_id']; ?>">
                                                        <?php if ($order['vehicle_name']): ?>
                                                            <i class="bi bi-truck"></i> <?php echo htmlspecialchars($order['vehicle_name']); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="openAssignVehicleModal(<?php echo $order['order_id']; ?>)" <?php echo $order['status'] !== 'processing' ? 'disabled' : ''; ?>>
                                                            <i class="bi bi-truck"></i> Assign Vehicle
                                                        </button>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Update Status
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'pending')">Pending</a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'paid')">Paid</a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'processing')">Processing</a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'shipped')">Shipped</a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'delivered')">Delivered</a></li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'cancelled')">Cancelled</a></li>
                                                            </ul>
                                                        </div>
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

<!-- Assign Vehicle Modal -->
<div class="modal fade" id="assignVehicleModal" tabindex="-1" aria-labelledby="assignVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="assignVehicleModalLabel">Assign Vehicle to Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modalOrderId">
        <div class="mb-3">
            <label for="vehicleSelect" class="form-label">Available Vehicles (Status: available)</label>
            <select class="form-select" id="vehicleSelect">
                <?php if (empty($available_vehicles)): ?>
                    <option>No vehicles available</option>
                <?php else: ?>
                    <?php foreach ($available_vehicles as $vehicle): ?>
                        <option value="<?php echo $vehicle['vehicle_id']; ?>">
                            <?php echo htmlspecialchars($vehicle['type'] . ' (' . $vehicle['plate_no'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="assignVehicle()" <?php echo empty($available_vehicles) ? 'disabled' : '';?>>Assign</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let assignVehicleModal;

document.addEventListener("DOMContentLoaded", function() {
    assignVehicleModal = new bootstrap.Modal(document.getElementById('assignVehicleModal'));
});

function openAssignVehicleModal(orderId) {
    document.getElementById('modalOrderId').value = orderId;
    assignVehicleModal.show();
}

function assignVehicle() {
    const orderId = document.getElementById('modalOrderId').value;
    const vehicleId = document.getElementById('vehicleSelect').value;

    if (!vehicleId || vehicleId === 'No vehicles available') {
        alert('Please select a vehicle.');
        return;
    }

    fetch('api/assign_vehicle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ order_id: orderId, vehicle_id: vehicleId })
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.status);
        if (data.status === 'success') {
            assignVehicleModal.hide();
            // Refresh page to show updated vehicle info and available vehicles
            setTimeout(() => location.reload(), 2000);
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAlert('An unexpected error occurred. Please try again.', 'danger');
    });
}

function updateStatus(orderId, newStatus) {
    // Previous updateStatus function code...
    if (!confirm(`Are you sure you want to update order #${orderId} to "${newStatus}"?`)) {
        return;
    }

    fetch('api/update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ order__id: orderId, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.status);

        if (data.status === 'success') {
            const statusBadge = document.getElementById(`status-${orderId}`);
            statusBadge.textContent = newStatus;
            statusBadge.className = `badge rounded-pill ${getStatusClass(newStatus)} status-badge`;
            
            // If order is completed, refresh to update vehicle status
            if (newStatus === 'delivered') {
                setTimeout(() => location.reload(), 2000);
            }
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAlert('An unexpected error occurred. Please check the console for details.', 'danger');
    });
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    alertContainer.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
    
    setTimeout(() => {
        const alert = bootstrap.Alert.getInstance(alertContainer.firstChild);
        if(alert) alert.close();
    }, 5000);
}

function getStatusClass(status) {
    switch (status) {
        case 'delivered': return 'bg-success';
        case 'shipped': return 'bg-info';
        case 'processing': return 'bg-primary';
        case 'cancelled': return 'bg-danger';
        case 'pending':
        case 'paid':
        default: return 'bg-warning text-dark';
    }
}
</script>
<?php
function getStatusClass($status) {
    switch ($status) {
        case 'delivered': return 'bg-success';
        case 'shipped': return 'bg-info';
        case 'processing': return 'bg-primary';
        case 'cancelled': return 'bg-danger';
        case 'pending':
        case 'paid':
        default: return 'bg-warning text-dark';
    }
}
?>
</body>
</html>
