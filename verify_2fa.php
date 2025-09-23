 <?php
require_once 'error_handler.php';

header('Content-Type: application/json');
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Must match the input name="code" in your 2fa.php form
    $two_fa_code = $_POST['code'] ?? '';

    if (isset($_SESSION['2fa_code']) && $two_fa_code === $_SESSION['2fa_code']) {
        // 2FA code is correct. Log the user in.
        $user_id = intval($_SESSION['2fa_user_id']); // sanitize

        $sql = "SELECT * FROM USER WHERE USER_ID='$user_id' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // Save user session
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['user_first_name'] = $user['FIRST_NAME'];
            $_SESSION['user_middle_name'] = $user['MIDDLE_NAME'];
            $_SESSION['user_last_name'] = $user['LAST_NAME'];
            $_SESSION['user_email'] = $user['EMAIL'];
            $_SESSION['user_phone'] = $user['PHONE'];
            $_SESSION['user_role'] = $user['ROLE'];
            
            // Address information
            $_SESSION['user_house_no'] = $user['HOUSE_NO'];
            $_SESSION['user_street_name'] = $user['STREET_NAME'];
            $_SESSION['user_barangay'] = $user['BARANGAY'];
            $_SESSION['user_city'] = $user['CITY'];

            // Clear 2FA temporary session data
            unset($_SESSION['2fa_code']);
            unset($_SESSION['2fa_user_id']);

            echo json_encode(['success' => true]);
            exit();
        } else {
            echo json_encode(['error' => ['message' => 'User not found']]);
            exit();
        }
    } else {
        echo json_encode(['error' => ['message' => 'Invalid 2FA code']]);
        exit();
    }
}
?>