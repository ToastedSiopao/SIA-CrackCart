<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin

include("../db_connect.php");

$producers = [];
$sql = "SELECT p.PRODUCER_ID, p.NAME, p.LOCATION, p.LOGO, p.URL, pr.TYPE, pr.PRICE, pr.PER, pr.STOCK, pr.STATUS
        FROM PRODUCER p 
        LEFT JOIN PRICE pr ON p.PRODUCER_ID = pr.PRODUCER_ID
        WHERE pr.STATUS = 'active'
        ORDER BY p.NAME, pr.TYPE";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $producer_id = $row['PRODUCER_ID'];

        // If this is the first time we see this producer, initialize it
        if (!isset($producers[$producer_id])) {
            $producers[$producer_id] = [
                'producer_id' => $producer_id,
                'name' => $row['NAME'],
                'location' => $row['LOCATION'],
                'logo' => $row['LOGO'],
                'url' => $row['URL'],
                'products' => []
            ];
        }

        // Add product price information if it exists
        if ($row['TYPE']) {
            $price_value = floatval(preg_replace('/[^0-9.]/', '', $row['PRICE']));

            $producers[$producer_id]['products'][] = [
                'type' => $row['TYPE'],
                'price' => $price_value,
                'per' => $row['PER'],
                'stock' => $row['STOCK'],
                'status' => $row['STATUS']
            ];
        }
    }
}

$conn->close();

// Re-index the array to be a simple list
$output = array_values($producers);

echo json_encode(['status' => 'success', 'data' => $output]);
?>