<?php
include('db_connect.php');

$sql = "ALTER TABLE product_orders ADD COLUMN vehicle_type VARCHAR(255) DEFAULT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Column 'vehicle_type' added to 'product_orders' table successfully.";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>