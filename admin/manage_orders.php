<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// --- Filtering & Sorting --- //
$status_filter = isset($_GET['status']) && $_GET['status'] !== 'all' ? $_GET['status'] : '';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'status_priority';

$where_clause = '';
if ($status_filter) {
    $where_clause = "WHERE po.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$order_by_clause = '';
switch ($sort_order) {
    case 'date_desc':
        $order_by_clause = 'ORDER BY po.order_date DESC';
        break;
    case 'date_asc':
        $order_by_clause = 'ORDER BY po.order_date ASC';
        break;
    case 'history_desc':
        $order_by_clause = 'ORDER BY user_order_count DESC, po.order_date DESC';
        break;
    case 'total_desc':
        $order_by_clause = 'ORDER BY po.total_amount DESC';
        break;
    case 'total_asc':
        $order_by_clause = 'ORDER BY po.total_amount ASC';
        break;
    default: // status_priority
        $order_by_clause = "ORDER BY 
            CASE po.status
                WHEN 'pending' THEN 1
                WHEN 'paid' THEN 2
                WHEN 'processing' THEN 3
                WHEN 'shipped' THEN 4
                WHEN 'delivered' THEN 5
                WHEN 'cancelled' THEN 6
                ELSE 7
            END,
            user_order_count DESC,
            po.order_date DESC";
        break;
}

$query = "SELECT 
            po.order_id, 
            CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as user_name, 
            po.user_id,
            po.order_date, 
            po.total_amount, 
            po.status, 
            po.vehicle_type as requested_vehicle_type, 
            v.type as vehicle_name, 
            v.plate_no as vehicle_plate, 
            po.vehicle_id,
            (SELECT COUNT(*) FROM product_orders WHERE user_id = po.user_id) as user_order_count
          FROM product_orders po
          JOIN USER u ON po.user_id = u.USER_ID
          LEFT JOIN Vehicle v ON po.vehicle_id = v.vehicle_id
          $where_clause
          $order_by_clause";

$result = $conn->query($query);

$orders = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$all_statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];

$vehicles_result = $conn->query("SELECT vehicle_id, type, plate_no FROM Vehicle WHERE status = 'available'");
$available_vehicles = [];
if ($vehicles_result && $vehicles_result->num_rows > 0) {
    while($row = $vehicles_result->fetch_assoc()) {
        $available_vehicles[] = $row;
    }
}

$conn->close();

function getStatusClass($status) {
    switch (strtolower($status)) {
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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="admin-styles.css?v=1.9" rel="stylesheet">
  <style>
      #alert-container {
          position: fixed;
          top: 20px;
          right: 20px;
          z-index: 1055; 
          width: auto;
          max-width: 400px;
      }
      .card-text small { font-size: 0.9em; }
      .vehicle-info { font-size: 0.9em; }
  </style>
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0 h2">Manage Customer Orders</h1>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form id="filterForm" action="manage_orders.php" method="GET" class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label for="status-filter" class="form-label">Filter by Status</label>
                                <select id="status-filter" name="status" class="form-select">
                                    <option value="all" <?php echo ($status_filter === '') ? 'selected' : ''; ?>>All Statuses</option>
                                    <?php foreach ($all_statuses as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo ($status_filter === $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="sort-order" class="form-label">Sort By</label>
                                <select id="sort-order" name="sort" class="form-select">
                                    <option value="status_priority" <?php echo ($sort_order === 'status_priority') ? 'selected' : ''; ?>>Priority</option>
                                    <option value="date_desc" <?php echo ($sort_order === 'date_desc') ? 'selected' : ''; ?>>Date (Newest First)</option>
                                    <option value="date_asc" <?php echo ($sort_order === 'date_asc') ? 'selected' : ''; ?>>Date (Oldest First)</option>
                                    <option value="history_desc" <?php echo ($sort_order === 'history_desc') ? 'selected' : ''; ?>>Customer History</option>
                                    <option value="total_desc" <?php echo ($sort_order === 'total_desc') ? 'selected' : ''; ?>>Total (High to Low)</option>
                                    <option value="total_asc" <?php echo ($sort_order === 'total_asc') ? 'selected' : ''; ?>>Total (Low to High)</option>
                                </select>
                            </div>
                             <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <?php if (empty($orders)): ?>
                        <div class="col-12">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">No Orders Found</h5>
                                    <p class="card-text">No orders match the selected filter criteria. Try selecting a different filter.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                        <h6 class="mb-0 fw-bold">Order #<?php echo htmlspecialchars($order['order_id']); ?></h6>
                                        <span class="badge rounded-pill <?php echo getStatusClass($order['status']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                        </span>
                                    </div>
                                    <div class="card-body pb-2">
                                        <p class="card-text mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                                        <p class="card-text mb-1"><small class="text-muted">History: <?php echo htmlspecialchars($order['user_order_count']); ?> total orders</small></p>
                                        <p class="card-text mb-2"><small class="text-muted">Date: <?php echo date("M j, Y, g:i a", strtotime($order['order_date'])); ?></small></p>
                                        
                                        <h5 class="card-title my-3 text-center">₱<?php echo number_format($order['total_amount'], 2); ?></h5>

                                        <div class="vehicle-info border-top pt-2">
                                            <?php if ($order['requested_vehicle_type']): ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                   <span><i class="bi bi-card-list me-2"></i>Requested:</span> 
                                                   <strong><?php echo htmlspecialchars($order['requested_vehicle_type']); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($order['vehicle_name']): ?>
                                                 <div class="d-flex justify-content-between align-items-center text-primary">
                                                    <span><i class="bi bi-truck-front-fill me-2"></i>Assigned:</span> 
                                                    <strong><?php echo htmlspecialchars($order['vehicle_name'] . ' (' . $order['vehicle_plate'] . ')'); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white d-flex flex-column gap-2">
                                        <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-info w-100">View Details</a>
                                        <div class="d-flex gap-2 w-100">
                                            <button class="btn btn-sm btn-outline-primary w-50" 
                                                    onclick='openAssignVehicleModal(<?php echo json_encode($order); ?>)' 
                                                    <?php echo $order['status'] !== 'processing' || $order['vehicle_id'] != null ? 'disabled' : ''; ?>
                                                    title="<?php echo $order['status'] !== 'processing' ? 'Order must be in processing status.' : ($order['vehicle_id'] != null ? 'Vehicle already assigned.' : 'Assign a vehicle'); ?>">
                                                <i class="bi bi-truck"></i> Assign
                                            </button>
                                            <div class="dropdown w-50">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Update Status
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
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
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
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
const available_vehicles = <?php echo json_encode($available_vehicles); ?>;
let assignVehicleModal;

document.addEventListener("DOMContentLoaded", function() {
    assignVehicleModal = new bootstrap.Modal(document.getElementById('assignVehicleModal'));
});

function openAssignVehicleModal(order) {
    document.getElementById('modalOrderId').value = order.order_id;
    const requestedType = order.requested_vehicle_type;
    const vehicleSelect = document.getElementById('vehicleSelect');
    const requestedVehicleTypeElem = document.getElementById('requestedVehicleType');
    const assignBtn = document.getElementById('assignVehicleBtn');
    vehicleSelect.innerHTML = ''; // Clear previous options

    requestedVehicleTypeElem.textContent = requestedType || 'Not specified';

    const matchingVehicles = available_vehicles.filter(v => v.type === requestedType);
    const otherVehicles = available_vehicles.filter(v => v.type !== requestedType);

    if (available_vehicles.length > 0) {
        if (matchingVehicles.length > 0) {
            const matchingGroup = document.createElement('optgroup');
            matchingGroup.label = 'Matching Request';
            matchingVehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.vehicle_id;
                option.textContent = `${vehicle.type} (${vehicle.plate_no})`;
                matchingGroup.appendChild(option);
            });
            vehicleSelect.appendChild(matchingGroup);
        }

        if (otherVehicles.length > 0) {
            const otherGroup = document.createElement('optgroup');
            otherGroup.label = 'Other Available';
            otherVehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.vehicle_id;
                option.textContent = `${vehicle.type} (${vehicle.plate_no})`;
                otherGroup.appendChild(option);
            });
            vehicleSelect.appendChild(otherGroup);
        }
        vehicleSelect.disabled = false;
        assignBtn.disabled = false;
    } else {
        const option = document.createElement('option');
        option.textContent = 'No vehicles are available.';
        vehicleSelect.appendChild(option);
        vehicleSelect.disabled = true;
        assignBtn.disabled = true;
    }

    assignVehicleModal.show();
}

function assignVehicle() {
    const assignBtn = document.getElementById('assignVehicleBtn');
    assignBtn.disabled = true;
    assignBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Assigning...';

    const orderId = document.getElementById('modalOrderId').value;
    const vehicleId = document.getElementById('vehicleSelect').value;

    fetch('api/assign_vehicle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ order_id: orderId, vehicle_id: vehicleId })
    })
    .then(response => {
         if (!response.ok) { return response.json().then(err => { throw new Error(err.message || 'Server error'); }); }
        return response.json();
    })
    .then(data => {
        assignVehicleModal.hide();
        if (data.status === 'success') {
            showAlert(data.message, 'success', () => {
                const params = new URLSearchParams(window.location.search);
                window.location.search = params.toString();
            });
        } else {
            showAlert(data.message || 'An unknown error occurred.', 'danger');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAlert(error.message, 'danger');
    }).finally(() => {
        assignBtn.disabled = false;
        assignBtn.innerHTML = 'Assign';
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
        if (!response.ok) { return response.json().then(err => {throw new Error(err.message || 'Server error');}); }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            showAlert(data.message, 'success', () => {
                const params = new URLSearchParams(window.location.search);
                window.location.search = params.toString();
            });
        } else {
             showAlert(data.message || 'An unknown error occurred.', 'danger');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAlert(error.message, 'danger');
    });
}

function showAlert(message, type, onClosedCallback) {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    if (onClosedCallback) {
        alertDiv.addEventListener('closed.bs.alert', onClosedCallback, { once: true });
    }

    alertContainer.appendChild(alertDiv);
    const bsAlert = new bootstrap.Alert(alertDiv);

    if (!onClosedCallback) {
         setTimeout(() => {
            bsAlert.close();
        }, 5000);
    }
}
</script>
</body>
</html>
