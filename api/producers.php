<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../error_handler.php';

// Initialize response
$response = ['status' => 'error', 'message' => 'Invalid request', 'data' => []];

// --- Logic for fetching producers ---
try {
    // Base SQL query
    // We join PRICE so we can filter by it. Producers without products matching the filters won't be shown.
    $sql = "SELECT p.PRODUCER_ID as producer_id, p.NAME as name, p.LOCATION as location, p.LOGO as logo,
                   pr.PRICE_ID as product_id, pr.TYPE as product_type, pr.PRICE as price, pr.STOCK as stock
            FROM PRODUCER p
            JOIN PRICE pr ON p.PRODUCER_ID = pr.PRODUCER_ID
            WHERE pr.STATUS = 'active'";

    $params = [];
    $types = '';

    // Handle Category Filter
    if (!empty($_GET['category'])) {
        $sql .= " AND pr.TYPE = ?";
        $params[] = $_GET['category'];
        $types .= 's';
    }

    // Handle Max Price Filter
    if (!empty($_GET['max_price'])) {
        $sql .= " AND pr.PRICE <= ?";
        $params[] = $_GET['max_price'];
        $types .= 'd';
    }

    // The main query should return all products that match the filters.
    // We will then group them by producer.
    $main_sql = $sql . " ORDER BY p.PRODUCER_ID, pr.PRICE";

    $stmt = $conn->prepare($main_sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $producers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $producer_id = intval($row['producer_id']);

            // If producer is not yet in our list, add them
            if (!isset($producers[$producer_id])) {
                $producers[$producer_id] = [
                    'producer_id' => $producer_id,
                    'name'        => $row['name'],
                    'location'    => $row['location'],
                    'logo'        => $row['logo'],
                    'products'    => []
                ];
            }
        }
    }
    
    // For producers that match the filter, we still want to show their *other* products.
    // Get all IDs of producers that have at least one product matching the filter.
    $matching_producer_ids = array_keys($producers);

    if (!empty($matching_producer_ids)) {
        // Now, fetch ALL active products for those producers
        $all_products_sql = "SELECT p.PRODUCER_ID as producer_id, pr.TYPE as product_type, pr.PRICE as price, pr.STOCK as stock
                             FROM PRODUCER p
                             JOIN PRICE pr ON p.PRODUCER_ID = pr.PRODUCER_ID
                             WHERE pr.STATUS = 'active' AND p.PRODUCER_ID IN (". implode(',', $matching_producer_ids) . ")
                             ORDER BY p.PRODUCER_ID, pr.PRICE";
        
        $all_products_result = $conn->query($all_products_sql);
        
        // First, clear the products list for our matching producers
        foreach ($producers as &$producer) {
            $producer['products'] = [];
        }
        unset($producer); // Unset reference

        // Now, repopulate with all products
        while ($row = $all_products_result->fetch_assoc()) {
             $producer_id = intval($row['producer_id']);
             $producers[$producer_id]['products'][] = [
                'type'  => $row['product_type'],
                'price' => floatval($row['price']),
                'stock' => intval($row['stock'])
            ];
        }
    }

    $response = ['status' => 'success', 'data' => array_values($producers)];

} catch (Exception $e) {
    // Catch any exceptions and return a proper error response
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>