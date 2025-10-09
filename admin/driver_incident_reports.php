<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include_once('../db_connect.php');

// Function to get a Bootstrap class based on status
function getStatusClass($status) {
    switch ($status) {
        case 'Resolved':
            return 'bg-success';
        case 'Pending User Action':
            return 'bg-warning text-dark';
        case 'User Responded - Replacement Requested':
             return 'bg-info text-dark';
        case 'User Responded - Cancelled':
            return 'bg-danger';
        case 'Reported':
        default:
            return 'bg-secondary';
    }
}

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
                                        <td><?php echo htmlspecialchars($row['reported_at']); ?></td>
                                        <td>
                                            <span class="badge <?php echo getStatusClass($row['status']); ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary view-report-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewReportModal"
                                                    data-incident-id="<?php echo htmlspecialchars($row['incident_id']); ?>"
                                                    data-order-id="<?php echo $row['order_id'] ? htmlspecialchars($row['order_id']) : 'N/A'; ?>"
                                                    data-driver-name="<?php echo htmlspecialchars($row['FIRST_NAME'] . ' ' . $row['LAST_NAME']); ?>"
                                                    data-incident-type="<?php echo htmlspecialchars($row['incident_type']); ?>"
                                                    data-reported-at="<?php echo htmlspecialchars($row['reported_at']); ?>"
                                                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                                    data-description="<?php echo htmlspecialchars($row['description']); ?>">
                                                View Report
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No incidents reported.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReportModalLabel">Incident Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Incident ID:</strong> <span id="modal-incident-id"></span></p>
                <p><strong>Order ID:</strong> <span id="modal-order-id"></span></p>
                <p><strong>Driver:</strong> <span id="modal-driver-name"></span></p>
                <p><strong>Incident Type:</strong> <span id="modal-incident-type"></span></p>
                <p><strong>Reported At:</strong> <span id="modal-reported-at"></span></p>
                <p><strong>Status:</strong> <span id="modal-status-text"></span></p>
                <p><strong>Description:</strong></p>
                <p id="modal-description"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="notifyUserBtn">Notify User of Incident</button>
                 <button type="button" class="btn btn-success" id="resolveIncidentBtn">Resolve Incident</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let currentIncidentId, currentOrderId, currentStatus;

    document.addEventListener('DOMContentLoaded', function () {
        var viewReportModal = document.getElementById('viewReportModal');
        viewReportModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            currentIncidentId = button.getAttribute('data-incident-id');
            currentOrderId = button.getAttribute('data-order-id');
            currentStatus = button.getAttribute('data-status');
            var driverName = button.getAttribute('data-driver-name');
            var incidentType = button.getAttribute('data-incident-type');
            var reportedAt = button.getAttribute('data-reported-at');
            var description = button.getAttribute('data-description');

            var modalTitle = viewReportModal.querySelector('.modal-title');
            var modalIncidentId = viewReportModal.querySelector('#modal-incident-id');
            var modalOrderId = viewReportModal.querySelector('#modal-order-id');
            var modalDriverName = viewReportModal.querySelector('#modal-driver-name');
            var modalIncidentType = viewReportModal.querySelector('#modal-incident-type');
            var modalReportedAt = viewReportModal.querySelector('#modal-reported-at');
            var modalStatusText = viewReportModal.querySelector('#modal-status-text');
            var modalDescription = viewReportModal.querySelector('#modal-description');
            
            modalTitle.textContent = 'Incident Report #' + currentIncidentId;
            modalIncidentId.textContent = currentIncidentId;
            modalOrderId.textContent = currentOrderId;
            modalDriverName.textContent = driverName;
            modalIncidentType.textContent = incidentType;
            modalReportedAt.textContent = reportedAt;
            modalStatusText.textContent = currentStatus;
            modalDescription.textContent = description;

            // Control button visibility and state
            var notifyBtn = document.getElementById('notifyUserBtn');
            var resolveBtn = document.getElementById('resolveIncidentBtn');

            // Can only notify if status is 'Reported'
            notifyBtn.style.display = (currentStatus === 'Reported') ? 'inline-block' : 'none';

            // Can only resolve if user has responded or if it's a simple incident not requiring user feedback
            resolveBtn.style.display = (currentStatus.startsWith('User Responded')) ? 'inline-block' : 'none';
        });

        document.getElementById('notifyUserBtn').addEventListener('click', function() {
            notifyUser();
        });

        document.getElementById('resolveIncidentBtn').addEventListener('click', function() {
            resolveIncident();
        });
    });

    function notifyUser() {
        fetch('api/notify_user_of_incident.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ incident_id: currentIncidentId, order_id: currentOrderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function resolveIncident() {
        if (!confirm('Are you sure you want to mark this incident as resolved?')) return;

        fetch('api/resolve_incident.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ incident_id: currentIncidentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>

</body>
</html>
