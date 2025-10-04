<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$inactivity_timeout = 1800; // 30 minutes in seconds

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check for session inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactivity_timeout)) {
    session_unset();
    session_destroy();
    header('Location: login.php?reason=inactive');
    exit();
}

$_SESSION['last_activity'] = time(); // Update last activity time

?>