[file name]: login_process.php
[file content begin]
<?php
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Check if email exists
    $sql = "SELECT * FROM users WHERE email='$email' AND status='active'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect to dashboard or home page
            header("Location: index.php");
            exit();
        } else {
            header("Location: login.php?error=" . urlencode("Invalid email or password"));
            exit();
        }
    } else {
        header("Location: login.php?error=" . urlencode("Invalid email or password"));
        exit();
    }
}
?>
[file content end]