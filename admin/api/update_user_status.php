<?php
session_start();
header('Content-Type: application/json');

// Security check: Ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

include '../../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['user_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

$user_id = (int)$data['user_id'];
$new_status = $data['status'];
$allowed_statuses = ['ACTIVE', 'INACTIVE'];

if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit();
}

$conn->begin_transaction();

try {
    if ($new_status === 'INACTIVE') {
        // Archive user: Copy to archived_users, then delete from USER
        $stmt_copy = $conn->prepare("INSERT INTO archived_users SELECT * FROM USER WHERE USER_ID = ?");
        if (!$stmt_copy) throw new Exception("Prepare statement (copy) failed: " . $conn->error);
        $stmt_copy->bind_param('i', $user_id);
        if (!$stmt_copy->execute()) throw new Exception("Execute (copy) failed: " . $stmt_copy->error);
        $stmt_copy->close();

        $stmt_delete = $conn->prepare("DELETE FROM USER WHERE USER_ID = ?");
        if (!$stmt_delete) throw new Exception("Prepare statement (delete) failed: " . $conn->error);
        $stmt_delete->bind_param('i', $user_id);
        if (!$stmt_delete->execute()) throw new Exception("Execute (delete) failed: " . $stmt_delete->error);
        $stmt_delete->close();

    } else { // 'ACTIVE'
        // Restore user: Copy from archived_users, then delete from archived_users
        $stmt_copy = $conn->prepare("INSERT INTO USER SELECT * FROM archived_users WHERE USER_ID = ?");
        if (!$stmt_copy) throw new Exception("Prepare statement (copy) failed: " . $conn->error);
        $stmt_copy->bind_param('i', $user_id);
        if (!$stmt_copy->execute()) {
            // If this fails, it could be due to a duplicate email. Handle this gracefully.
            if ($conn->errno === 1062) { // Duplicate entry for key 'EMAIL'
                throw new Exception("Cannot restore user. An active user with the same email address already exists.");
            } else {
                throw new Exception("Execute (copy) failed: " . $stmt_copy->error);
            }
        }
        $stmt_copy->close();

        $stmt_delete = $conn->prepare("DELETE FROM archived_users WHERE USER_ID = ?");
        if (!$stmt_delete) throw new Exception("Prepare statement (delete) failed: " . $conn->error);
        $stmt_delete->bind_param('i', $user_id);
        if (!$stmt_delete->execute()) throw new Exception("Execute (delete) failed: " . $stmt_delete->error);
        $stmt_delete->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'User status updated successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]); // Send specific error back to the client
}

$conn->close();
?>
