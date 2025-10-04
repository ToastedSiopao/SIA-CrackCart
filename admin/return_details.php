<?php
include 'admin_header.php';
include '../db_connect.php';

$return_id = isset($_GET['return_id']) ? intval($_GET['return_id']) : 0;

if ($return_id === 0) {
    echo "<main class='col-md-9 ms-sm-auto col-lg-10 px-md-4'><div class='alert alert-danger'>Invalid Return ID.</div></main>";
    include '../includes/admin_footer.php';
    exit;
}

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
$conn->close();

$return_statuses = ['pending', 'approved', 'rejected', 'processing', 'completed'];

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Return Request Details</h1>
        <a href="returns.php" class="btn btn-sm btn-outline-secondary">Back to Returns</a>
    </div>

    <?php if ($return): ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Return #<?php echo htmlspecialchars($return['return_id']); ?></div>
                <div class="card-body">
                    <h5 class="card-title">Product Details</h5>
                    <div class="d-flex">
                        <img src="../<?php echo htmlspecialchars($return['image_url']); ?>" class="me-3" style="width: 100px; height: 100px; object-fit: cover;">
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
        <div class="col-md-4">
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
</main>

<?php
include '../includes/admin_footer.php';
?>
