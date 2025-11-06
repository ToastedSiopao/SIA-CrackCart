<?php
header('Content-Type: application/json');
error_reporting(0); // Disable error reporting to the user

include_once("../db_connect.php");

try {
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // --- Fetch Transaction Counts ---
    $weekly_transactions_sql = "SELECT COUNT(*) as weekly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 7 DAY";
    $monthly_transactions_sql = "SELECT COUNT(*) as monthly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 MONTH";
    $yearly_transactions_sql = "SELECT COUNT(*) as yearly_transactions FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 YEAR";

    // --- Fetch Total Sales ---
    $weekly_sales_total_sql = "SELECT SUM(total_amount) as total FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 7 DAY";
    $monthly_sales_total_sql = "SELECT SUM(total_amount) as total FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 MONTH";
    $yearly_sales_total_sql = "SELECT SUM(total_amount) as total FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 1 YEAR";

    // --- Fetch Total Losses ---
    $weekly_losses_total_sql = "SELECT SUM(oi.quantity * oi.price_per_item) as total FROM `returns` r JOIN `product_order_items` oi ON r.order_item_id = oi.order_item_id WHERE r.status = 'approved' AND r.requested_at >= CURDATE() - INTERVAL 7 DAY";
    $monthly_losses_total_sql = "SELECT SUM(oi.quantity * oi.price_per_item) as total FROM `returns` r JOIN `product_order_items` oi ON r.order_item_id = oi.order_item_id WHERE r.status = 'approved' AND r.requested_at >= CURDATE() - INTERVAL 1 MONTH";
    $yearly_losses_total_sql = "SELECT SUM(oi.quantity * oi.price_per_item) as total FROM `returns` r JOIN `product_order_items` oi ON r.order_item_id = oi.order_item_id WHERE r.status = 'approved' AND r.requested_at >= CURDATE() - INTERVAL 1 YEAR";

    // --- Fetch Sales Data for Charts ---
    $weekly_sales_sql = "SELECT DATE_FORMAT(order_date, '%Y-%m-%d') as sale_date, SUM(total_amount) as total_sales FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 7 DAY GROUP BY sale_date ORDER BY sale_date ASC";
    $monthly_sales_sql = "SELECT CONCAT(YEAR(order_date), '-W', WEEK(order_date, 1)) as sale_week, SUM(total_amount) as total_sales FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 4 WEEK GROUP BY sale_week ORDER BY sale_week ASC";
    $yearly_sales_sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as sale_month, SUM(total_amount) as total_sales FROM product_orders WHERE order_date >= CURDATE() - INTERVAL 12 MONTH GROUP BY sale_month ORDER BY sale_month ASC";

    // --- Fetch Loss Data for Charts ---
    $weekly_losses_sql = "SELECT DATE_FORMAT(r.requested_at, '%Y-%m-%d') as loss_date, SUM(oi.quantity * oi.price_per_item) as total_losses FROM `returns` r JOIN `product_order_items` oi ON r.order_item_id = oi.order_item_id WHERE r.status = 'approved' AND r.requested_at >= CURDATE() - INTERVAL 7 DAY GROUP BY loss_date ORDER BY loss_date ASC";
    $monthly_losses_sql = "SELECT CONCAT(YEAR(r.requested_at), '-W', WEEK(r.requested_at, 1)) as loss_week, SUM(oi.quantity * oi.price_per_item) as total_losses FROM `returns` r JOIN `product_order_items` oi ON r.order_item_id = oi.order_item_id WHERE r.status = 'approved' AND r.requested_at >= CURDATE() - INTERVAL 4 WEEK GROUP BY loss_week ORDER BY loss_week ASC";
    $yearly_losses_sql = "SELECT DATE_FORMAT(r.requested_at, '%Y-%m') as loss_month, SUM(oi.quantity * oi.price_per_item) as total_losses FROM `returns` r JOIN `product_order_items` oi ON r.order_item_id = oi.order_item_id WHERE r.status = 'approved' AND r.requested_at >= CURDATE() - INTERVAL 12 MONTH GROUP BY loss_month ORDER BY loss_month ASC";

    // --- Fetch Top Performers ---
    $weekly_top_item_sql = "SELECT product_type, SUM(quantity) as total_quantity FROM product_order_items oi JOIN product_orders po ON oi.order_id = po.order_id WHERE po.order_date >= CURDATE() - INTERVAL 7 DAY GROUP BY product_type ORDER BY total_quantity DESC LIMIT 1";
    $monthly_top_item_sql = "SELECT product_type, SUM(quantity) as total_quantity FROM product_order_items oi JOIN product_orders po ON oi.order_id = po.order_id WHERE po.order_date >= CURDATE() - INTERVAL 1 MONTH GROUP BY product_type ORDER BY total_quantity DESC LIMIT 1";
    $yearly_top_item_sql = "SELECT product_type, SUM(quantity) as total_quantity FROM product_order_items oi JOIN product_orders po ON oi.order_id = po.order_id WHERE po.order_date >= CURDATE() - INTERVAL 1 YEAR GROUP BY product_type ORDER BY total_quantity DESC LIMIT 1";

    $weekly_top_producer_sql = "SELECT p.NAME, SUM(oi.quantity) as total_quantity FROM product_order_items oi JOIN product_orders po ON oi.order_id = po.order_id JOIN PRODUCER p ON oi.producer_id = p.PRODUCER_ID WHERE po.order_date >= CURDATE() - INTERVAL 7 DAY GROUP BY p.NAME ORDER BY total_quantity DESC LIMIT 1";
    $monthly_top_producer_sql = "SELECT p.NAME, SUM(oi.quantity) as total_quantity FROM product_order_items oi JOIN product_orders po ON oi.order_id = po.order_id JOIN PRODUCER p ON oi.producer_id = p.PRODUCER_ID WHERE po.order_date >= CURDATE() - INTERVAL 1 MONTH GROUP BY p.NAME ORDER BY total_quantity DESC LIMIT 1";
    $yearly_top_producer_sql = "SELECT p.NAME, SUM(oi.quantity) as total_quantity FROM product_order_items oi JOIN product_orders po ON oi.order_id = po.order_id JOIN PRODUCER p ON oi.producer_id = p.PRODUCER_ID WHERE po.order_date >= CURDATE() - INTERVAL 1 YEAR GROUP BY p.NAME ORDER BY total_quantity DESC LIMIT 1";

    // Function to execute a query and fetch results
    function fetch_data($conn, $sql) {
        $result = $conn->query($sql);
        if ($result === false) {
            throw new Exception("Query failed: " . $conn->error . " | SQL: " . $sql);
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Function to fetch a single value
    function fetch_single_value($conn, $sql, $column_name) {
        $result = $conn->query($sql);
        if ($result === false) {
            throw new Exception("Query failed: " . $conn->error . " | SQL: " . $sql);
        }
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()[$column_name] ?? 0;
        }
        return 0;
    }

    $response = [
        'weekly_transactions' => fetch_single_value($conn, $weekly_transactions_sql, 'weekly_transactions'),
        'monthly_transactions' => fetch_single_value($conn, $monthly_transactions_sql, 'monthly_transactions'),
        'yearly_transactions' => fetch_single_value($conn, $yearly_transactions_sql, 'yearly_transactions'),
        'weekly_sales_total' => number_format(fetch_single_value($conn, $weekly_sales_total_sql, 'total'), 2),
        'monthly_sales_total' => number_format(fetch_single_value($conn, $monthly_sales_total_sql, 'total'), 2),
        'yearly_sales_total' => number_format(fetch_single_value($conn, $yearly_sales_total_sql, 'total'), 2),
        'weekly_losses_total' => number_format(fetch_single_value($conn, $weekly_losses_total_sql, 'total'), 2),
        'monthly_losses_total' => number_format(fetch_single_value($conn, $monthly_losses_total_sql, 'total'), 2),
        'yearly_losses_total' => number_format(fetch_single_value($conn, $yearly_losses_total_sql, 'total'), 2),
        'sales_data' => [
            'weekly' => fetch_data($conn, $weekly_sales_sql),
            'monthly' => fetch_data($conn, $monthly_sales_sql),
            'yearly' => fetch_data($conn, $yearly_sales_sql)
        ],
        'losses_data' => [
            'weekly' => fetch_data($conn, $weekly_losses_sql),
            'monthly' => fetch_data($conn, $monthly_losses_sql),
            'yearly' => fetch_data($conn, $yearly_losses_sql)
        ],
        'top_performers' => [
            'weekly' => [
                'item' => fetch_data($conn, $weekly_top_item_sql)[0] ?? ['product_type' => 'N/A', 'total_quantity' => 0],
                'producer' => fetch_data($conn, $weekly_top_producer_sql)[0] ?? ['NAME' => 'N/A', 'total_quantity' => 0]
            ],
            'monthly' => [
                'item' => fetch_data($conn, $monthly_top_item_sql)[0] ?? ['product_type' => 'N/A', 'total_quantity' => 0],
                'producer' => fetch_data($conn, $monthly_top_producer_sql)[0] ?? ['NAME' => 'N/A', 'total_quantity' => 0]
            ],
            'yearly' => [
                'item' => fetch_data($conn, $yearly_top_item_sql)[0] ?? ['product_type' => 'N/A', 'total_quantity' => 0],
                'producer' => fetch_data($conn, $yearly_top_producer_sql)[0] ?? ['NAME' => 'N/A', 'total_quantity' => 0]
            ]
        ]
    ];

    $conn->close();

    echo json_encode($response);

} catch (Exception $e) {
    // Return a JSON error message
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>