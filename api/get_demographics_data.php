<?php
header('Content-Type: application/json');

include_once("../db_connect.php");

// Check if DB connection is valid
if (!$conn || $conn->connect_error) {
    die(json_encode(['error' => "Database connection failed: " . $conn->connect_error]));
}

// --- Fetch Weekly Transactions ---
$weekly_sql = "SELECT COUNT(*) as weekly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 7 DAY";
$weekly_result = $conn->query($weekly_sql);
$weekly_transactions = $weekly_result->fetch_assoc()['weekly_transactions'] ?? 0;

// --- Fetch Monthly Transactions ---
$monthly_sql = "SELECT COUNT(*) as monthly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 MONTH";
$monthly_result = $conn->query($monthly_sql);
$monthly_transactions = $monthly_result->fetch_assoc()['monthly_transactions'] ?? 0;

// --- Fetch Yearly Transactions ---
$yearly_sql = "SELECT COUNT(*) as yearly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 YEAR";
$yearly_result = $conn->query($yearly_sql);
$yearly_transactions = $yearly_result->fetch_assoc()['yearly_transactions'] ?? 0;

// --- Fetch Losses from Returns ---
$returns_sql = "SELECT SUM(oi.quantity * p.price) as return_losses FROM `returns` r JOIN order_items oi ON r.order_item_id = oi.order_item_id JOIN price p ON oi.product_id = p.product_id WHERE r.status = 'approved'";
$returns_result = $conn->query($returns_sql);
$return_losses = $returns_result->fetch_assoc()['return_losses'] ?? 0;

$conn->close();

echo json_encode([
    'weekly_transactions' => $weekly_transactions,
    'monthly_transactions' => $monthly_transactions,
    'yearly_transactions' => $yearly_transactions,
    'return_losses' => number_format($return_losses, 2)
]);
?>