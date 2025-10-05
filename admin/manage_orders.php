<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch all orders with customer and vehicle information
$query = "SELECT po.order_id, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as user_name, po.order_date, po.total_amount, po.status, po.vehicle_type as requested_vehicle_type, v.type as vehicle_name, v.plate_no as vehicle_plate
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

// Fetch all available vehicles
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
  <link href="admin-styles.css?v=1.4" rel="stylesheet">
  <style>
      .status-badge { font-size: 0.9em; }
      .vehicle-info { font-size: 0.8em; color: #6c757d; }
      .vehicle-info .bi { vertical-align: middle; }
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
                                                        <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                                    </span>
                                                    <div class="vehicle-info mt-1" id="vehicle-info-<?php echo $order['order_id']; ?>">
                                                        <?php if ($order['requested_vehicle_type']): ?>
                                                            <div><i class="bi bi-card-list"></i> Req: <?php echo htmlspecialchars($order['requested_vehicle_type']); ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($order['vehicle_name']): ?>
                                                            <div><i class="bi bi-truck-front"></i> Assigned: <?php echo htmlspecialchars($order['vehicle_name'] . ' (' . $order['vehicle_plate'] . ')'); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="openAssignVehicleModal(<?php echo $order['order_id']; ?>)" 
                                                                <?php echo $order['status'] !== 'processing' || !$order['requested_vehicle_type'] ? 'disabled' : ''; ?>
                                                                title="<?php echo $order['status'] !== 'processing' || !$order['requested_vehicle_type'] ? 'Order must be in 'processing' status and have a requested vehicle.' : 'Assign a vehicle to this order'; ?>">
                                                            <i class="bi bi-truck"></i> Assign
                                                        </button>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Update
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
            <p>Requested Vehicle Type: <strong id="requestedVehicleType"></strong></p>
            <label for="vehicleSelect" class="form-label">Available Vehicles:</label>
            <select class="form-select" id="vehicleSelect">
                <!-- Options will be populated by JavaScript -->
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="assignVehicleBtn" onclick="assignVehicle()">Assign</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const orders = <?php echo json_encode($orders); ?>;
const available_vehicles = <?php echo json_encode($available_vehicles); ?>;
let assignVehicleModal;

document.addEventListener("DOMContentLoaded", function() {
    assignVehicleModal = new bootstrap.Modal(document.getElementById('assignVehicleModal'));
});

function openAssignVehicleModal(orderId) {
    document.getElementById('modalOrderId').value = orderId;
    
    const order = orders.find(o => o.order_id == orderId);
    const requestedType = order ? order.requested_vehicle_type : null;
    
    const vehicleSelect = document.getElementById('vehicleSelect');
    const requestedVehicleTypeElem = document.getElementById('requestedVehicleType');
    const assignBtn = document.getElementById('assignVehicleBtn');
    vehicleSelect.innerHTML = ''; // Clear previous options

    if (requestedType) {
        requestedVehicleTypeElem.textContent = requestedType;
        const matchingVehicles = available_vehicles.filter(v => v.type === requestedType);

        if (matchingVehicles.length > 0) {
            matchingVehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.vehicle_id;
                option.textContent = `${vehicle.type} (${vehicle.plate_no})`;
                vehicleSelect.appendChild(option);
            });
            vehicleSelect.disabled = false;
            assignBtn.disabled = false;
        } else {
            const option = document.createElement('option');
            option.textContent = `No "${requestedType}" vehicles are available.`;
            vehicleSelect.appendChild(option);
            vehicleSelect.disabled = true;
            assignBtn.disabled = true;
        }
    } else {
        requestedVehicleTypeElem.textContent = 'Not specified';
        const option = document.createElement('option');
        option.textContent = 'No vehicle type was requested for this order.';
        vehicleSelect.appendChild(option);
        vehicleSelect.disabled = true;
        assignBtn.disabled = true;
    }
    
    assignVehicleModal.show();
}

function assignVehicle() {
    const orderId = document.getElementById('modalOrderId').value;
    const vehicleId = document.getElementById('vehicleSelect').value;

    if (!vehicleId) {
        showAlert('Please select a vehicle.', 'danger');
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
            setTimeout(() => location.reload(), 1500); // Reload to reflect changes
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAlert('An unexpected error occurred. Please try again.', 'danger');
    });
}

function updateStatus(orderId, newStatus) {
    if (!confirm(`Are you sure you want to update order #${orderId} to "${newStatus}"?`)) {
        return;
    }

    fetch('api/update_order_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({ order_id: orderId, status: newStatus })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        showAlert(data.message, data.status);

        if (data.status === 'success') {
            // Reload the page to ensure all data (status, vehicle availability) is fresh
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAlert('An unexpected error occurred. Check the console for details.', 'danger');
    });
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
    alertContainer.innerHTML = alertHTML;
    
    setTimeout(() => {
        const alertNode = alertContainer.querySelector('.alert');
        if (alertNode) {
            bootstrap.Alert.getOrCreateInstance(alertNode).close();
        }
    }, 5000);
}

function getStatusClass(status) {
    switch (status) {
        case 'delivered': return 'bg-success';
        case 'shipped': return 'bg-info text-dark';
        case 'processing': return 'bg-primary';
        case 'cancelled': return 'bg-danger';
        case 'pending':
        case 'paid': 
        default: return 'bg-warning text-dark';
    }
}
?>
<?php
function getStatusClass($status) {
    switch ($status) {
        case 'delivered': return 'bg-success';
        case 'shipped': return 'bg-info text-dark';
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
