<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../error_handler.php';

// Set a default error response
$response = ['status' => 'error', 'message' => 'Invalid request'];

if (isset($_GET['producer_id'])) {
    $producer_id = intval($_GET['producer_id']);

    // Fetch a specific producer with their products from the PRICE table
    try {
        $sql = "SELECT p.PRODUCER_ID as producer_id, p.NAME as name, p.LOCATION as location, p.LOGO as logo, 
                       pr.TYPE as product_type, pr.PRICE as price, pr.STOCK as stock, pr.PER as per_unit
                FROM PRODUCER p
                LEFT JOIN PRICE pr ON p.PRODUCER_ID = pr.PRODUCER_ID
                WHERE p.PRODUCER_ID = ? AND pr.STATUS = 'active'";
        
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
                        'producer_id' => $row['producer_id'],
                        'name' => $row['name'],
                        'location' => $row['location'],
                        'logo' => $row['logo']
                    ];
                }
                // Add product if it exists
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
    // Fetch all producers (for producers.php page)
    try {
        $sql = "SELECT PRODUCER_ID as producer_id, NAME as name, LOCATION as location, LOGO as logo FROM PRODUCER";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $producers = [];
            while ($row = $result->fetch_assoc()) {
                $producers[] = $row;
            }
            $response = ['status' => 'success', 'data' => $producers];
        } else {
            $response['message'] = 'No producers found.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

$conn->close();
echo json_encode($response);
?>
