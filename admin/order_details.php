<?php
session_start();
// Security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order_statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'failed', 'refunded'];

if ($order_id <= 0) {
    $error_message = "Invalid Order ID.";
} else {
    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $new_status = $_POST['order_status'];
        if (in_array($new_status, $order_statuses)) {
            $update_stmt = $conn->prepare("UPDATE product_orders SET status = ? WHERE order_id = ?");
            $update_stmt->bind_param("si", $new_status, $order_id);
            $update_stmt->execute();
            $update_stmt->close();
            header("Location: order_details.php?order_id=" . $order_id);
            exit;
        }
    }

    // Fetch main order details
    $query = "SELECT po.*, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, u.EMAIL, 
                   ua.address_line1, ua.address_line2, ua.city, ua.state, ua.zip_code, ua.country
            FROM product_orders po
            JOIN USER u ON po.user_id = u.USER_ID
            LEFT JOIN user_addresses ua ON po.shipping_address_id = ua.address_id
            WHERE po.order_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        $error_message = "Order not found.";
    } else {
        // Fetch order items
        $items_query = "SELECT oi.*, p.TYPE as product_name, pr.NAME as producer_name
                        FROM product_order_items oi
                        JOIN PRICE p ON oi.product_type = p.TYPE AND oi.producer_id = p.PRODUCER_ID
                        JOIN PRODUCER pr ON oi.producer_id = pr.PRODUCER_ID
                        WHERE oi.order_id = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $order_items = $items_stmt->get_result();
        $items_stmt->close();
    }
}

$conn->close();
$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - CrackCart</title>
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
                        <h4 class="mb-0">Order Details</h4>
                        <div>
                            <a href="print_invoice.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-secondary">Print Invoice</a>
                            <a href="print_packing_slip.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-primary">Print Packing Slip</a>
                            <a href="orders.php" class="btn btn-outline-secondary">Back to Orders</a>
                        </div>
                    </div>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($order): ?>
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="card mb-4">
                                    <div class="card-header">Order #<?php echo htmlspecialchars($order['order_id']); ?></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Items</h5>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tbody>
                                                    <?php while($item = $order_items->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($item['producer_name']); ?></td>
                                                            <td>x <?php echo htmlspecialchars($item['quantity']); ?></td>
                                                            <td>$<?php echo number_format($item['price_per_item'], 2); ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card mb-4">
                                    <div class="card-header">Order Summary</div>
                                    <div class="card-body">
                                        <p><strong>Status:</strong> <span class="badge bg-success"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span></p>
                                        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                                        <hr>
                                        <h5 class="card-title">Update Status</h5>
                                        <form method="POST">
                                            <div class="input-group mb-3">
                                                <select class="form-select" name="order_status">
                                                    <?php foreach ($order_statuses as $status): ?>
                                                        <option value="<?php echo $status; ?>" <?php echo ($order['status'] == $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-primary" type="submit" name="update_status">Update</button>
                                            </div>
                                        </form>
                                        <hr>
                                        <h5 class="card-title">Customer & Shipping</h5>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['EMAIL']); ?></p>
                                        <p><strong>Shipping Address:</strong><br>
                                            <?php echo htmlspecialchars($order['address_line1']); ?><br>
                                            <?php if($order['address_line2']) echo htmlspecialchars($order['address_line2']) . '<br>'; ?>
                                            <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['zip_code']); ?><br>
                                            <?php echo htmlspecialchars($order['country']); ?>
                                        </p>
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
