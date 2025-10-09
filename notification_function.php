<?php
/**
 * Creates a notification for a user.
 *
 * @param mysqli $conn The database connection object.
 * @param int $user_id The ID of the user to notify.
 * @param string $message The notification message.
 * @return bool True on success, false on failure.
 */
function create_notification($conn, $user_id, $message) {
    if ($conn && $user_id && !empty($message)) {
        $sql = "INSERT INTO NOTIFICATION (USER_ID, MESSAGE) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("is", $user_id, $message);
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
        }
    }
    // Log error or handle failure
    if (isset($stmt) && $stmt->error) {
        error_log("Notification creation failed: " . $stmt->error);
    }
    return false;
}
?>