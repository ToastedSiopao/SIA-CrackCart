<?php
session_start();

// If user already verified, send them to dashboard
if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
  exit();
}

// Display error messages (passed via GET)
$error_message = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Two-Factor Authentication</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="2fa-styles.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }
    .tfa-container {
      max-width: 420px;
      margin: 80px auto;
      padding: 40px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .tfa-header img {
      width: 80px;
      margin-bottom: 15px;
    }
    .tfa-header h2 {
      font-weight: 600;
      color: #333;
    }
    .tfa-header p {
      color: #666;
      font-size: 0.95rem;
      margin-bottom: 25px;
    }
    .form-control {
      font-size: 1.3rem;
      letter-spacing: 5px;
      text-align: center;
      padding: 10px;
    }
    .btn-primary {
      background-color: #ffb700;
      border: none;
      font-weight: 600;
      transition: background 0.3s;
    }
    .btn-primary:hover {
      background-color: #e0a800;
    }
  </style>
</head>
<body>
  <div class="tfa-container">
    <div class="tfa-header">
      <img src="assets/Truck.png" alt="Truck Logo">
      <h2>2FA Verification</h2>
      <p>Enter the 6-digit code sent to your email.</p>
    </div>

    <!-- ✅ Direct POST to PHP -->
    <form method="POST" action="verify_2fa.php">
      <div class="mb-4">
        <input 
          type="text" 
          class="form-control" 
          name="code" 
          maxlength="6" 
          pattern="[0-9]{6}" 
          inputmode="numeric"
          placeholder="______"
          required 
          autofocus
        >
      </div>
      <button type="submit" class="btn btn-primary w-100">
        VERIFY CODE
      </button>

      <?php if ($error_message): ?>
        <div class="alert alert-danger mt-3">
          <?= htmlspecialchars($error_message) ?>
        </div>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>
