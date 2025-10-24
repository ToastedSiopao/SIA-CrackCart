<?php
header('Content-Type: application/json');

include_once("../db_connect.php");

// Check if DB connection is valid
if (!$conn || $conn->connect_error) {
    die(json_encode(['error' => "Database connection failed: " . $conn->connect_error]));
}

// --- Fetch Total Transaction Counts ---
$weekly_transactions_sql = "SELECT COUNT(*) as weekly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 7 DAY";
$weekly_transactions_result = $conn->query($weekly_transactions_sql);
$weekly_transactions = $weekly_transactions_result->fetch_assoc()['weekly_transactions'] ?? 0;

$monthly_transactions_sql = "SELECT COUNT(*) as monthly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 MONTH";
$monthly_transactions_result = $conn->query($monthly_transactions_sql);
$monthly_transactions = $monthly_transactions_result->fetch_assoc()['monthly_transactions'] ?? 0;

$yearly_transactions_sql = "SELECT COUNT(*) as yearly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 YEAR";
$yearly_transactions_result = $conn->query($yearly_transactions_sql);
$yearly_transactions = $yearly_transactions_result->fetch_assoc()['yearly_transactions'] ?? 0;

// --- Fetch Losses from Returns ---
$returns_sql = "SELECT SUM(oi.quantity * oi.price_per_item) as return_losses FROM `returns` r JOIN `product_order_items` oi ON r.order_item_id = oi.order_item_id WHERE r.status = 'approved'";
$returns_result = $conn->query($returns_sql);
$return_losses = $returns_result->fetch_assoc()['return_losses'] ?? 0;

// --- Fetch Total Sales ---
$weekly_sales_total_sql = "SELECT SUM(total_amount) as total FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 7 DAY";
$weekly_sales_total_result = $conn->query($weekly_sales_total_sql);
$weekly_sales_total = $weekly_sales_total_result->fetch_assoc()['total'] ?? 0;

$monthly_sales_total_sql = "SELECT SUM(total_amount) as total FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 MONTH";
$monthly_sales_total_result = $conn->query($monthly_sales_total_sql);
$monthly_sales_total = $monthly_sales_total_result->fetch_assoc()['total'] ?? 0;

$yearly_sales_total_sql = "SELECT SUM(total_amount) as total FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 YEAR";
$yearly_sales_total_result = $conn->query($yearly_sales_total_sql);
$yearly_sales_total = $yearly_sales_total_result->fetch_assoc()['total'] ?? 0;


// --- Fetch Sales Data for Charts ---

// Weekly Sales (last 7 days)
$weekly_sales_sql = "
    SELECT DATE_FORMAT(order_date, '%Y-%m-%d') as sale_date, SUM(total_amount) as total_sales
    FROM product_orders
    WHERE order_date >= CURDATE() - INTERVAL 7 DAY
    GROUP BY sale_date
    ORDER BY sale_date ASC
";
$weekly_sales_result = $conn->query($weekly_sales_sql);
$weekly_sales_data = [];
while ($row = $weekly_sales_result->fetch_assoc()) {
    $weekly_sales_data[] = $row;
}

// Monthly Sales (last 4 weeks)
$monthly_sales_sql = "
    SELECT CONCAT(YEAR(order_date), '-W', WEEK(order_date, 1)) as sale_week, SUM(total_amount) as total_sales
    FROM product_orders
    WHERE order_date >= CURDATE() - INTERVAL 4 WEEK
    GROUP BY sale_week
    ORDER BY sale_week ASC
";
$monthly_sales_result = $conn->query($monthly_sales_sql);
$monthly_sales_data = [];
while ($row = $monthly_sales_result->fetch_assoc()) {
    $monthly_sales_data[] = $row;
}

// Yearly Sales (last 12 months)
$yearly_sales_sql = "
    SELECT DATE_FORMAT(order_date, '%Y-%m') as sale_month, SUM(total_amount) as total_sales
    FROM product_orders
    WHERE order_date >= CURDATE() - INTERVAL 12 MONTH
    GROUP BY sale_month
    ORDER BY sale_month ASC
";
$yearly_sales_result = $conn->query($yearly_sales_sql);
$yearly_sales_data = [];
while ($row = $yearly_sales_result->fetch_assoc()) {
    $yearly_sales_data[] = $row;
}


$conn->close();

echo json_encode([
    'weekly_transactions' => $weekly_transactions,
    'monthly_transactions' => $monthly_transactions,
    'yearly_transactions' => $yearly_transactions,
    'return_losses' => number_format($return_losses, 2),
    'weekly_sales_total' => number_format($weekly_sales_total, 2),
    'monthly_sales_total' => number_format($monthly_sales_total, 2),
    'yearly_sales_total' => number_format($yearly_sales_total, 2),
    'sales_data' => [
        'weekly' => $weekly_sales_data,
        'monthly' => $monthly_sales_data,
        'yearly' => $yearly_sales_data
    ]
]);
?>