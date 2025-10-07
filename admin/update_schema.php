<?php
include '../db_connect.php';

$sql = "ALTER TABLE product_order_items ADD COLUMN tray_size INT NOT NULL DEFAULT 30";

if ($conn->query($sql) === TRUE) {
    echo "Table product_order_items altered successfully";
} else {
    echo "Error altering table: " . $conn->error;
}

$conn->close();
?>
