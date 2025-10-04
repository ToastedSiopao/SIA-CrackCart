<?php
include 'admin_header.php';
include '../db_connect.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    echo "<main class='col-md-9 ms-sm-auto col-lg-10 px-md-4'><div class='alert alert-danger'>Invalid Order ID.</div></main>";
    include '../includes/admin_footer.php';
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);
    $update_stmt->execute();
    $update_stmt->close();
    // Refresh the page to show the updated status
    header("Location: order_details.php?order_id=" . $order_id);
    exit;
}

// Fetch order details
$query = "SELECT o.*, CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS customer_name, u.EMAIL, sa.address_line_1, sa.address_line_2, sa.city, sa.state, sa.postal_code, sa.country
          FROM orders o
          JOIN USER u ON o.user_id = u.USER_ID
          JOIN shipping_address sa ON o.shipping_address_id = sa.address_id
          WHERE o.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch order items
$items_query = "SELECT p.name, p.price, oi.quantity FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();

$conn->close();

$order_statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Order Details</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="orders.php" class="btn btn-sm btn-outline-secondary me-2">Back to Orders</a>
            <a href="print_invoice.php?order_id=<?php echo $order_id; ?>" target="_blank" class="btn btn-sm btn-outline-primary">Print Invoice</a>
        </div>
    </div>

    <?php if ($order): ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Order #<?php echo htmlspecialchars($order['order_id']); ?></div>
                <div class="card-body">
                     <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Grand Total:</th>
                                <th>$<?php echo number_format($order['total_amount'], 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Update Status</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="order_status" class="form-label">Order Status</label>
                            <select class="form-select" id="order_status" name="order_status">
                                <?php foreach ($order_statuses as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($order['status'] == $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
             <div class="card mb-4">
                <div class="card-header">Customer & Shipping</div>
                <div class="card-body">
                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                    <?php echo htmlspecialchars($order['EMAIL']); ?><br><br>
                    <strong>Shipping Address:</strong><br>
                    <?php echo htmlspecialchars($order['address_line_1']); ?><br>
                    <?php if(!empty($order['address_line_2'])) echo htmlspecialchars($order['address_line_2']) . '<br>'; ?>
                    <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['postal_code']); ?><br>
                    <?php echo htmlspecialchars($order['country']); ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning">Order not found.</div>
    <?php endif; ?>
</main>

<?php
// Assuming a footer file exists
// include '../includes/admin_footer.php'; 
?>
