<?php
session_start();
// Security & permission check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Unauthorized access.");
    exit();
}

include '../db_connect.php';

$return_id = isset($_GET['return_id']) ? intval($_GET['return_id']) : 0;
$return_statuses = ['requested', 'approved', 'rejected', 'processing', 'completed'];

if ($return_id <= 0) {
    $error_message = "Invalid Return ID.";
} else {
    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $new_status = $_POST['return_status'];
        if (in_array($new_status, $return_statuses)) {
            $update_stmt = $conn->prepare("UPDATE product_returns SET return_status = ? WHERE return_id = ?");
            $update_stmt->bind_param("si", $new_status, $return_id);
            $update_stmt->execute();
            $update_stmt->close();
            header("Location: return_details.php?return_id=" . $return_id);
            exit;
        }
    }

    // Fetch return details
    $query = "SELECT pr.*, po.order_id, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name
              FROM product_returns pr
              JOIN product_orders po ON pr.order_id = po.order_id
              JOIN USER u ON po.user_id = u.USER_ID
              WHERE pr.return_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $return_id);
    $stmt->execute();
    $return_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$return_details) {
        $error_message = "Return request not found.";
    }
}

$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Details - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin-styles.css?v=1.0" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="card shadow-sm border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Return Details</h4>
                        <a href="returns.php" class="btn btn-outline-secondary">Back to Returns</a>
                    </div>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($return_details): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card mb-4">
                                    <div class="card-header">Return #<?php echo htmlspecialchars($return_details['return_id']); ?></div>
                                    <div class="card-body">
                                        <p><strong>Order ID:</strong> <a href="order_details.php?order_id=<?php echo $return_details['order_id']; ?>"><?php echo htmlspecialchars($return_details['order_id']); ?></a></p>
                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($return_details['customer_name']); ?></p>
                                        <p><strong>Reason for Return:</strong></p>
                                        <p><?php echo nl2br(htmlspecialchars($return_details['return_reason'])); ?></p>
                                        <hr>
                                        <p><strong>Status:</strong> <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($return_details['return_status'])); ?></span></p>
                                        <p><strong>Date Requested:</strong> <?php echo date("M d, Y, h:i A", strtotime($return_details['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">Update Status</div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="input-group">
                                                <select class="form-select" name="return_status">
                                                    <?php foreach ($return_statuses as $status): ?>
                                                        <option value="<?php echo $status; ?>" <?php echo ($return_details['return_status'] == $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-primary" type="submit" name="update_status">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
