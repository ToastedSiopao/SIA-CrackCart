<?php
require_once 'session_handler.php';
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $conn->begin_transaction();

    // You might need to delete or handle related data first, e.g., orders, cart, etc.
    // Example: DELETE FROM ORDERS WHERE USER_ID = ?
    // Example: DELETE FROM CART WHERE USER_ID = ?

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM USER WHERE USER_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Commit the transaction
        $conn->commit();

        // Destroy the session and redirect to login page
        session_destroy();
        header("Location: login.php?message=account_deleted");
        exit;
    } else {
        // Rollback if the user could not be found or deleted
        $conn->rollback();
        header("Location: profilePage.php?error=deletion_failed");
        exit;
    }

} catch (Exception $e) {
    // If any query fails, roll back the entire transaction
    $conn->rollback();
    // You might want to log this error instead of showing it to the user
    header("Location: profilePage.php?error=db_error");
    exit;
}
?>
