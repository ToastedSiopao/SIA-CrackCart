<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../error_handler.php';

$response = ['status' => 'success', 'message' => 'Data loaded', 'data' => []];

// Check if a specific producer is requested
if (isset($_GET['producer_id']) && !empty($_GET['producer_id'])) {
    // --- Logic for fetching a single producer's details for order.php ---
    $producer_id = $_GET['producer_id'];

    try {
        $stmt = $conn->prepare("SELECT PRODUCER_ID, NAME, LOCATION, LOGO FROM PRODUCER WHERE PRODUCER_ID = ?");
        $stmt->bind_param('i', $producer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $producer = $result->fetch_assoc();
        $stmt->close();

        if (!$producer) {
            throw new Exception('Producer not found.');
        }

        $stmt = $conn->prepare("SELECT PRICE_ID, TYPE, PRICE, PER, STOCK, tray_size FROM PRICE WHERE PRODUCER_ID = ? AND STATUS = 'active' ORDER BY TYPE ASC");
        $stmt->bind_param('i', $producer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'price_id' => $row['PRICE_ID'],
                'type' => $row['TYPE'],
                'price' => floatval($row['PRICE']),
                'per' => $row['PER'],
                'stock' => intval($row['STOCK']),
                'tray_size' => intval($row['tray_size'])
            ];
        }
        $stmt->close();

        $response['data'] = [
            'producer_id' => $producer['PRODUCER_ID'],
            'name' => $producer['NAME'],
            'location' => $producer['LOCATION'],
            'logo' => $producer['LOGO'],
            'products' => $products
        ];

    } catch (Exception $e) {
        http_response_code(404);
        $response['status'] = 'error';
        $response['message'] = 'Producer not found or database error: ' . $e->getMessage();
    }

} else {
    // --- Logic for fetching all products for producers.php ---
    try {
        $sql = "SELECT 
                    p.PRODUCER_ID as producer_id, 
                    p.NAME as producer_name, 
                    p.LOCATION as producer_location,
                    pr.PRICE_ID, 
                    pr.TYPE as product_type, 
                    pr.PRICE, 
                    pr.PER, 
                    pr.STOCK, 
                    pr.tray_size,
                    pr.DATE_CREATED,
                    (SELECT AVG(rating) FROM product_reviews WHERE product_type = pr.TYPE) as avg_rating,
                    (SELECT COUNT(review_id) FROM product_reviews WHERE product_type = pr.TYPE) as total_reviews
                FROM PRICE pr
                JOIN PRODUCER p ON pr.PRODUCER_ID = p.PRODUCER_ID
                WHERE pr.STATUS = 'active'";

        $params = [];
        $types = '';

        // Filters
        if (!empty($_GET['category'])) {
            $sql .= " AND pr.TYPE = ?";
            $params[] = $_GET['category'];
            $types .= 's';
        }
        if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
            $sql .= " AND pr.PRICE >= ?";
            $params[] = $_GET['min_price'];
            $types .= 'd';
        }
        if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
            $sql .= " AND pr.PRICE <= ?";
            $params[] = $_GET['max_price'];
            $types .= 'd';
        }
        if (!empty($_GET['sizes'])) {
            $sizes = explode(',', $_GET['sizes']);
            if (!empty($sizes)) {
                $size_placeholders = implode(',', array_fill(0, count($sizes), '?'));
                $sql .= " AND pr.tray_size IN ($size_placeholders)";
                foreach ($sizes as $size) {
                    $params[] = intval($size);
                    $types .= 'i';
                }
            }
        }

        // Sorting
        $orderBy = " ORDER BY avg_rating DESC, total_reviews DESC";
        if (!empty($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'price_asc': $orderBy = " ORDER BY pr.PRICE ASC"; break;
                case 'price_desc': $orderBy = " ORDER BY pr.PRICE DESC"; break;
                case 'name_asc': $orderBy = " ORDER BY pr.TYPE ASC"; break;
                case 'name_desc': $orderBy = " ORDER BY pr.TYPE DESC"; break;
                case 'newness': $orderBy = " ORDER BY pr.DATE_CREATED DESC"; break;
            }
        }
        $sql .= $orderBy;

        $stmt = $conn->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $image_name = str_replace([' ', '/'], '_', strtolower($row['product_type']));
                $image_url = 'assets/' . $image_name . '.jpg';

                $products[] = [
                    'product_id' => $row['PRICE_ID'],
                    'product_type' => $row['product_type'],
                    'price' => floatval($row['PRICE']),
                    'per' => $row['PER'],
                    'stock' => intval($row['STOCK']),
                    'tray_size' => intval($row['tray_size']),
                    'date_created' => $row['DATE_CREATED'],
                    'avg_rating' => floatval($row['avg_rating']),
                    'total_reviews' => intval($row['total_reviews']),
                    'image_url' => $image_url,
                    'producer' => [
                        'id' => $row['producer_id'],
                        'name' => $row['producer_name'],
                        'location' => $row['producer_location']
                    ]
                ];
            }
        }
        $stmt->close();
        $response['data'] = $products;

    } catch (Exception $e) {
        http_response_code(500);
        $response['status'] = 'error';
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

$conn->close();
echo json_encode($response);
?>