<?php
      require_once 'error_handler.php';
      session_start();
      include("db_connect.php");
      
      if ($_SERVER["REQUEST_METHOD"] !== "POST") {
          header("Location: 2fa_page.php?error=Invalid+request+method");
          exit();
      }
      
      if (!isset($_SESSION['2fa_user']) || !isset($_SESSION['2fa_code']) || !isset($_SESSION['2fa_expires'])) {
          header("Location: login.php?error=2FA+session+expired");
          exit();
      }
      
      $input_code = trim($_POST['code'] ?? '');
      
      if (empty($input_code)) {
          header("Location: 2fa_page.php?error=2FA+code+is+required");
          exit();
      }
      
      if (time() > $_SESSION['2fa_expires']) {
          unset($_SESSION['2fa_user'], $_SESSION['2fa_code'], $_SESSION['2fa_expires']);
          header("Location: login.php?error=2FA+code+expired");
          exit();
      }
      
      if ($_SESSION['2fa_code'] !== $input_code) {
          header("Location: 2fa_page.php?error=Invalid+2FA+code");
          exit();
      }
      
      // ✅ Successful verification
      $user_id = intval($_SESSION['2fa_user']);
      // Fetch user role
      $stmt = $conn->prepare("SELECT ROLE FROM USER WHERE USER_ID = ?");
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $user = $result->fetch_assoc();
      $role = $user['ROLE'];
      $stmt->close();
      
      session_regenerate_id(true);
      
      $_SESSION['user_id'] = $user_id;
      $_SESSION['role'] = $role;
      unset($_SESSION['2fa_user'], $_SESSION['2fa_code'], $_SESSION['2fa_expires']);
      
      $_SESSION['flash_message'] = "✅ Two-Factor Authentication successful! Welcome back.";
      
      if (strtolower($role) === 'driver') {
          header("Location: Driver/driver_page.php");
      } else {
          header("Location: dashboard.php");
      }
      exit();
      ?>