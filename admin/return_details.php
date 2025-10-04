<?php
session_start();
// Security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

$return_id = isset($_GET['return_id']) ? intval($_GET['return_id']) : 0;

if ($return_id === 0) {
    $error_message = "Invalid Return ID.";
} else {
    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $new_status = $_POST['return_status'];
        $update_stmt = $conn->prepare("UPDATE returns SET status = ? WHERE return_id = ?");
        $update_stmt->bind_param("si", $new_status, $return_id);
        $update_stmt->execute();
        $update_stmt->close();
        header("Location: return_details.php?return_id=" . $return_id);
        exit;
    }

    // Fetch return details
    $query = "SELECT r.*, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, u.EMAIL, p.name AS product_name, p.price, p.image_url
              FROM returns r
              JOIN USER u ON r.user_id = u.USER_ID
              JOIN products p ON r.product_id = p.product_id
              WHERE r.return_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $return_id);
    $stmt->execute();
    $return = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$conn->close();

$return_statuses = ['pending', 'approved', 'rejected', 'processing', 'completed'];
$user_name = $_SESSION['user_first_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Request Details - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
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
                        <h4 class="mb-0">Return Request Details</h4>
                        <a href="returns.php" class="btn btn-outline-secondary">Back to Returns</a>
                    </div>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($return): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card mb-4">
                                    <div class="card-header">Return #<?php echo htmlspecialchars($return['return_id']); ?></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Product Details</h5>
                                        <div class="d-flex align-items-center">
                                            <img src="../<?php echo htmlspecialchars($return['image_url']); ?>" class="me-3 rounded" style="width: 100px; height: 100px; object-fit: cover;">
                                            <div>
                                                <strong><?php echo htmlspecialchars($return['product_name']); ?></strong><br>
                                                Order ID: <a href="order_details.php?order_id=<?php echo $return['order_id']; ?>"><?php echo $return['order_id']; ?></a><br>
                                                Price: $<?php echo number_format($return['price'], 2); ?><br>
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="card-title mt-4">Return Information</h5>
                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($return['customer_name']); ?> (<?php echo htmlspecialchars($return['EMAIL']); ?>)</p>
                                        <p><strong>Reason for Return:</strong></p>
                                        <p class="border p-3 bg-light rounded"><?php echo nl2br(htmlspecialchars($return['reason'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card mb-4">
                                    <div class="card-header">Update Status</div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="return_status" class="form-label">Return Status</label>
                                                <select class="form-select" id="return_status" name="return_status">
                                                    <?php foreach ($return_statuses as $status): ?>
                                                        <option value="<?php echo $status; ?>" <?php echo ($return['status'] == $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">Return request not found.</div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
