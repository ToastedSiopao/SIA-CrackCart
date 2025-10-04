<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $producer_name = $_POST['producer_name'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $egg_type = $_POST['egg_type'];
    $quantity = $_POST['quantity'];
    $special_instructions = $_POST['special_instructions'];

    // Get producer ID
    $stmt = $conn->prepare("SELECT PRODUCER_ID FROM PRODUCER WHERE NAME = ?");
    $stmt->bind_param("s", $producer_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $producer = $result->fetch_assoc();
    $producer_id = $producer['PRODUCER_ID'];

    // For simplicity, we're not creating a new address for every order.
    // We'll use a placeholder for pickup_address_id and delivery_address_id
    $pickup_address_id = 1; // Placeholder
    $delivery_address_id = 2; // Placeholder

    $stmt = $conn->prepare("INSERT INTO orders (user_id, pickup_address_id, delivery_address_id, tray_quantity, special_instructions) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $user_id, $pickup_address_id, $delivery_address_id, $quantity, $special_instructions);

    if ($stmt->execute()) {
        echo "<h1>Order Submitted Successfully!</h1>";
        echo "<p>Thank you for your order, $full_name. We will contact you at $email shortly.</p>";
        echo "<a href='dashboard.php'>Go to Dashboard</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: order.php");
    exit();
}
?>