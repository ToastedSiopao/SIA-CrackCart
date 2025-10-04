<?php
include '../db_connect.php';

$sql = "ALTER TABLE PRICE ADD COLUMN STOCK INT NOT NULL DEFAULT 0";

if ($conn->query($sql) === TRUE) {
    echo "Table PRICE altered successfully";
} else {
    echo "Error altering table: " . $conn->error;
}

$conn->close();
?>
