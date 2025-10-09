<?php
include('../db_connect.php');

$sql = "CREATE TABLE `delivery_issues` (
  `issue_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `issue_description` text NOT NULL,
  `status` enum('reported','resolved') NOT NULL DEFAULT 'reported',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`issue_id`),
  KEY `order_id` (`order_id`),
  KEY `reported_by` (`reported_by`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table delivery_issues created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>