<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../error_handler.php';

// Initialize response
$response = ['status' => 'error', 'message' => 'Invalid request', 'data' => null];

try {
    // --- Logic for fetching a single producer ---
    if (isset($_GET['producer_id']) && !empty($_GET['producer_id'])) {
        $producer_id = intval($_GET['producer_id']);

        $stmt = $conn->prepare("SELECT PRODUCER_ID as producer_id, NAME as name, LOCATION as location, LOGO as logo FROM PRODUCER WHERE PRODUCER_ID = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("i", $producer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $producer = $result->fetch_assoc();

            $stmt_products = $conn->prepare("SELECT TYPE as type, PRICE as price, PER as per, STOCK as stock FROM PRICE WHERE PRODUCER_ID = ? AND STATUS = 'active'");
            if (!$stmt_products) {
                throw new Exception("Prepare failed for products: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt_products->bind_param("i", $producer_id);
            $stmt_products->execute();
            $products_result = $stmt_products->get_result();
            
            $products = [];
            while ($row = $products_result->fetch_assoc()) {
                $products[] = [
                    'type'  => $row['type'],
                    'price' => floatval($row['price']),
                    'per'   => $row['per'],
                    'stock' => intval($row['stock'])
                ];
            }
            $producer['products'] = $products;

            $response['status'] = 'success';
            $response['data'] = $producer;
            $response['message'] = 'Producer details loaded.';
        } else {
            $response['message'] = 'Producer not found.';
            $response['data'] = null;
        }
        $stmt->close();

    // --- Logic for fetching a list of producers ---
    } else {
        $sql = "SELECT DISTINCT p.PRODUCER_ID as producer_id, p.NAME as name, p.LOCATION as location, p.LOGO as logo
                FROM PRODUCER p
                JOIN PRICE pr ON p.PRODUCER_ID = pr.PRODUCER_ID
                WHERE pr.STATUS = 'active'";

        $params = [];
        $types = '';

        if (!empty($_GET['category'])) {
            $sql .= " AND pr.TYPE = ?";
            $params[] = $_GET['category'];
            $types .= 's';
        }

        if (!empty($_GET['max_price'])) {
            $sql .= " AND pr.PRICE <= ?";
            $params[] = $_GET['max_price'];
            $types .= 'd';
        }

        $stmt = $conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $producers = [];
        $producer_ids = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $producer_id = intval($row['producer_id']);
                $producer_ids[] = $producer_id;
                $producers[$producer_id] = [
                    'producer_id' => $producer_id,
                    'name'        => $row['name'],
                    'location'    => $row['location'],
                    'logo'        => $row['logo'],
                    'products'    => []
                ];
            }
        }
        $stmt->close();
        
        if (!empty($producer_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($producer_ids), '?'));
            $all_products_sql = "SELECT PRODUCER_ID as producer_id, TYPE as type, PRICE as price, PER as per, STOCK as stock
                                 FROM PRICE
                                 WHERE STATUS = 'active' AND PRODUCER_ID IN ($ids_placeholder)
                                 ORDER BY PRODUCER_ID, PRICE";
            
            $stmt_products = $conn->prepare($all_products_sql);
            $stmt_products->bind_param(str_repeat('i', count($producer_ids)), ...$producer_ids);
            $stmt_products->execute();
            $all_products_result = $stmt_products->get_result();

            while ($row = $all_products_result->fetch_assoc()) {
                 $producer_id = intval($row['producer_id']);
                 if(isset($producers[$producer_id])) {
                     $producers[$producer_id]['products'][] = [
                        'type'  => $row['type'],
                        'price' => floatval($row['price']),
                        'per'   => $row['per'],
                        'stock' => intval($row['stock'])
                    ];
                 }
            }
             $stmt_products->close();
        }

        $response = ['status' => 'success', 'data' => array_values($producers)];
    }

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
    $response['data'] = null;
}

$conn->close();
echo json_encode($response);
?>