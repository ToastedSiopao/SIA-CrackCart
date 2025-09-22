<?php
include("db_connect.php");

$keyword = $_GET['keyword'] ?? '';
$location = $_GET['location'] ?? '';

$sql = "SELECT * FROM producers WHERE (name LIKE ? OR location LIKE ?) AND location LIKE ?";

$stmt = $conn->prepare($sql);
$search_keyword = "%" . $keyword . "%";
$search_location = "%" . $location . "%";
$stmt->bind_param("sss", $search_keyword, $search_keyword, $search_location);
$stmt->execute();

$result = $stmt->get_result();
$producers = [];
while ($row = $result->fetch_assoc()) {
    $producers[] = $row;
}

header('Content-Type: application/json');
echo json_encode($producers);
?>