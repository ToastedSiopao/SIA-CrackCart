<?php
include('../db_connect.php');

$sql = "ALTER TABLE user_addresses 
        ADD COLUMN region_code VARCHAR(255) AFTER region,
        ADD COLUMN province_code VARCHAR(255) AFTER province,
        ADD COLUMN city_code VARCHAR(255) AFTER city,
        ADD COLUMN barangay_code VARCHAR(255) AFTER barangay;";

if ($conn->multi_query($sql)) {
    echo "Table user_addresses updated successfully.";
} else {
    echo "Error updating table: " . $conn->error;
}

$conn->close();
?>