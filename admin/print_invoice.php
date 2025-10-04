<?php
include '../db_connect.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die("Invalid Order ID.");
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

if (!$order) {
    die("Order not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice for Order #<?php echo htmlspecialchars($order['order_id']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; }
        .invoice-header { text-align: center; margin-bottom: 40px; }
        .invoice-header h1 { margin: 0; }
        .company-details, .customer-details { margin-bottom: 30px; }
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="invoice-header">
            <h1>Invoice</h1>
            <p class="text-muted">Order #<?php echo htmlspecialchars($order['order_id']); ?></p>
        </div>

        <div class="row">
            <div class="col-md-6 company-details">
                <h4>From:</h4>
                <p>
                    <strong>CrackCart Inc.</strong><br>
                    123 Market Street<br>
                    San Francisco, CA 94103<br>
                    Email: support@crackcart.com
                </p>
            </div>
            <div class="col-md-6 customer-details text-md-end">
                <h4>To:</h4>
                <p>
                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                     <?php echo htmlspecialchars($order['address_line_1']); ?><br>
                    <?php if(!empty($order['address_line_2'])) echo htmlspecialchars($order['address_line_2']) . '<br>'; ?>
                    <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['postal_code']); ?><br>
                </p>
                <p><strong>Date:</strong> <?php echo date("M j, Y", strtotime($order['order_date'])); ?></p>
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
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
                <tr class="table-light">
                    <td colspan="3" class="text-end"><strong>Grand Total</strong></td>
                    <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center mt-4 no-print">
            <button onclick="window.print();" class="btn btn-primary">Print Invoice</button>
            <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-secondary">Back to Details</a>
        </div>
    </div>
</body>
</html>
