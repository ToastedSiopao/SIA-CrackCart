<?php
session_start();

// --- Security check: ensure the user is a driver ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'driver') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

include_once("../../db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = $_SESSION['user_id'];
    $order_id = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;
    $incident_type = trim($_POST['incident_type']);
    $description = trim($_POST['description']);

    if (empty($incident_type) || empty($description)) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Incident type and description are required.']);
        exit();
    }

    // --- Insert into database ---
    $insert_stmt = $conn->prepare("
        INSERT INTO delivery_incidents (driver_id, order_id, incident_type, description)
        VALUES (?, ?, ?, ?)
    ");
    $insert_stmt->bind_param("iiss", $driver_id, $order_id, $incident_type, $description);
    
    if ($insert_stmt->execute()) {
        // --- Notify Admins (Placeholder) ---
        // In a real application, you would trigger a notification system here.
        // For now, we'll assume this is handled separately or is not required.

        echo json_encode(['status' => 'success', 'message' => 'Incident reported successfully.']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to save the report. Please try again.']);
    }

    $insert_stmt->close();
    $conn->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
