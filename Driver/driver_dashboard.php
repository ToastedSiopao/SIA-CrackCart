<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['first_name']; ?>!</h1>
    <p>This is the driver dashboard.</p>
    <a href="logout.php">Logout</a>
</body>
</html>