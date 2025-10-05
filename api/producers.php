<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../error_handler.php';

$response = ['status' => 'error', 'message' => 'Invalid request', 'data' => []];

if (isset($_GET['producer_id'])) {
    // --- Fetch a SINGLE producer and their products ---
    $producer_id = intval($_GET['producer_id']);
    try {
        $sql = "SELECT p.PRODUCER_ID as producer_id, p.NAME as name, p.LOCATION as location, p.LOGO as logo, 
                       pr.TYPE as product_type, pr.PRICE as price, pr.STOCK as stock, pr.PER as per_unit
                FROM PRODUCER p
                LEFT JOIN PRICE pr ON p.PRODUCER_ID = pr.PRODUCER_ID AND pr.STATUS = 'active'
                WHERE p.PRODUCER_ID = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $producer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $producer_details = null;
            $products = [];

            while ($row = $result->fetch_assoc()) {
                if (!$producer_details) {
                    $producer_details = [
                        'producer_id' => intval($row['producer_id']),
                        'name' => $row['name'],
                        'location' => $row['location'],
                        'logo' => $row['logo']
                    ];
                }
                if ($row['product_type']) {
                    $products[] = [
                        'type' => $row['product_type'],
                        'price' => floatval($row['price']),
                        'stock' => intval($row['stock']),
                        'per' => $row['per_unit']
                    ];
                }
            }
            
            $producer_details['products'] = $products;
            $response = ['status' => 'success', 'data' => $producer_details];
        } else {
            $response['message'] = 'Producer not found or has no active products.';
        }
        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    // --- Fetch ALL producers and their products ---
    try {
        $sql = "SELECT p.PRODUCER_ID as producer_id, p.NAME as name, p.LOCATION as location, p.LOGO as logo,
                       pr.TYPE as product_type, pr.PRICE as price, pr.STOCK as stock, pr.PER as per_unit
                FROM PRODUCER p
                LEFT JOIN PRICE pr ON p.PRODUCER_ID = pr.PRODUCER_ID AND pr.STATUS = 'active'
                ORDER BY p.PRODUCER_ID";
        
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $producers = [];
            while ($row = $result->fetch_assoc()) {
                $producer_id = intval($row['producer_id']);
                if (!isset($producers[$producer_id])) {
                    $producers[$producer_id] = [
                        'producer_id' => $producer_id,
                        'name' => $row['name'],
                        'location' => $row['location'],
                        'logo' => $row['logo'],
                        'products' => []
                    ];
                }
                if ($row['product_type']) {
                    $producers[$producer_id]['products'][] = [
                        'type' => $row['product_type'],
                        'price' => floatval($row['price']),
                        'stock' => intval($row['stock']),
                        'per' => $row['per_unit']
                    ];
                }
            }
            $response = ['status' => 'success', 'data' => array_values($producers)];
        } else {
            $response = ['status' => 'success', 'message' => 'No producers found.', 'data' => []];
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

$conn->close();
echo json_encode($response);
?>
