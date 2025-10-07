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
        // Fetch order items with all necessary details
        $items_query = "SELECT oi.*, p.TYPE as product_name, p.STOCK as current_stock, pr.NAME as producer_name 
                        FROM product_order_items oi
                        LEFT JOIN PRICE p ON oi.product_type = p.TYPE AND oi.producer_id = p.PRODUCER_ID AND oi.tray_size = p.TRAY_SIZE
                        LEFT JOIN PRODUCER pr ON oi.producer_id = pr.PRODUCER_ID
                        WHERE oi.order_id = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $order_items_result = $items_stmt->get_result();
        $order_items = [];
        while ($item = $order_items_result->fetch_assoc()) {
            $order_items[] = $item;
        }
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
    <link href="admin-styles.css?v=1.1" rel="stylesheet">
    <style>
        .table th { 
            font-weight: 500;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Order Details</h4>
                        <div>
                            <a href="print_invoice.php?order_id=<?php echo $order_id; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> Invoice</a>
                            <a href="print_packing_slip.php?order_id=<?php echo $order_id; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-seam"></i> Packing Slip</a>
                            <a href="manage_orders.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-arrow-left"></i> Back</a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php elseif ($order): ?>
                            <div class="row g-4">
                                <div class="col-lg-8">
                                    <div class="card mb-4">
                                        <div class="card-header">Order #<?php echo htmlspecialchars($order['order_id']); ?> - Items</div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Producer</th>
                                                            <th class="text-center">Qty (Trays)</th>
                                                            <th class="text-center">Tray Size</th>
                                                            <th class="text-center">Stock (Trays)</th>
                                                            <th class="text-end">Price/Tray</th>
                                                            <th class="text-end">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        foreach ($order_items as $item): 
                                                            $stock_in_trays = ($item['tray_size'] > 0) ? floor($item['current_stock'] / $item['tray_size']) : 0;
                                                            $is_stock_sufficient = $stock_in_trays >= $item['quantity'];
                                                            $item_subtotal = (float)$item['price_per_item'] * (int)$item['quantity'];
                                                        ?>
                                                            <tr class="<?php echo !$is_stock_sufficient ? 'table-danger' : '' ?>">
                                                                <td><?php echo htmlspecialchars($item['product_name'] ?? 'N/A'); ?></td>
                                                                <td><?php echo htmlspecialchars($item['producer_name'] ?? 'N/A'); ?></td>
                                                                <td class="text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                                <td class="text-center"><?php echo htmlspecialchars($item['tray_size']); ?></td>
                                                                <td class="text-center fw-bold">
                                                                    <?php echo $stock_in_trays; ?>
                                                                    <?php if (!$is_stock_sufficient): ?>
                                                                        <i class="bi bi-exclamation-triangle-fill text-danger" title="Insufficient stock to fulfill this item!"></i>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-end">₱<?php echo number_format($item['price_per_item'], 2); ?></td>
                                                                <td class="text-end">₱<?php echo number_format($item_subtotal, 2); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card mb-4">
                                        <div class="card-header">Order Summary</div>
                                        <div class="card-body">
                                            <form method="POST" class="mb-3">
                                                <div class="input-group">
                                                    <select class="form-select" name="order_status">
                                                        <?php foreach ($order_statuses as $status): ?>
                                                            <option value="<?php echo $status; ?>" <?php echo ($order['status'] == $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button class="btn btn-primary" type="submit" name="update_status">Update</button>
                                                </div>
                                            </form>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Subtotal
                                                    <span>₱<?php echo number_format($order['total_amount'] - $order['delivery_fee'], 2); ?></span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Delivery Fee
                                                    <span>₱<?php echo number_format($order['delivery_fee'], 2); ?></span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center fs-5 fw-bold">
                                                    Grand Total
                                                    <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card">
                                         <div class="card-header">Customer & Shipping</div>
                                         <div class="card-body">
                                            <p class="mb-1"><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                                            <p class="mb-2 text-muted"><?php echo htmlspecialchars($order['EMAIL']); ?></p>
                                            <hr>
                                            <p class="mb-1"><strong>Shipping Address:</strong></p>
                                            <address class="mb-0">
                                                <?php echo htmlspecialchars($order['address_line1']); ?><br>
                                                <?php if($order['address_line2']) echo htmlspecialchars($order['address_line2']) . '<br>'; ?>
                                                <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['zip_code']); ?><br>
                                                <?php echo htmlspecialchars($order['country']); ?>
                                            </address>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>