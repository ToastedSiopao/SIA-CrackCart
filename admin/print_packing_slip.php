<?php
session_start();
// Security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    die("Invalid Order ID.");
}

// Fetch order details
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
    die("Order not found.");
}

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

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Packing Slip #<?php echo htmlspecialchars($order['order_id']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .packing-slip-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .packing-slip-container {
                margin: 0;
                border: none;
                width: 100%;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="packing-slip-container">
        <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
            <h1 class="h3">Packing Slip</h1>
            <div class="no-print">
                <button onclick="window.print()" class="btn btn-primary">Print Packing Slip</button>
                <a href="order_details.php?order_id=<?php echo $order_id; ?>" class="btn btn-secondary">Back to Details</a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <strong>Shipping To:</strong><br>
                <?php echo htmlspecialchars($order['customer_name']); ?><br>
            </div>
            <div class="col-6 text-end">
                <strong>Order #:</strong> <?php echo htmlspecialchars($order['order_id']); ?><br>
                <strong>Date:</strong> <?php echo date("M d, Y", strtotime($order['order_date'])); ?><br>
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Producer</th>
                    <th class="text-center">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $order_items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['producer_name']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mt-4">
            <strong>Shipping Address:</strong><br>
            <?php echo htmlspecialchars($order['address_line1']); ?><br>
            <?php if($order['address_line2']) echo htmlspecialchars($order['address_line2']) . '<br>'; ?>
            <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['zip_code']); ?><br>
            <?php echo htmlspecialchars($order['country']); ?>
        </div>
    </div>
</body>
</html>
