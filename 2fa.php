 <?php
session_start();

// If user has no 2FA session, send them back to login
if (!isset($_SESSION['2fa_user_id']) || !isset($_SESSION['2fa_code'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url("assets/eggBG.png") no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            border-radius: 15px;
            border: 2px solid #000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }
        .card-header {
            background-color: #FFD700; /* yellow */
            color: #000;
            font-weight: bold;
            text-align: center;
            border-top-left-radius: 13px;
            border-top-right-radius: 13px;
        }
        .btn-primary {
            background: #FFD700;
            border: none;
            color: #000;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #ffeb4d;
            color: #000;
        }
        .form-control {
            text-align: center;
            font-size: 1.5rem;
            letter-spacing: 5px;
        }
    </style>
</head>
<body>
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <div class="card-header">
            Two-Factor Authentication
        </div>
        <div class="card-body">
            <p class="text-center text-muted">Enter the 6-digit code sent to your email.</p>

            <form id="2fa-form">
                <div class="mb-3">
                    <input type="text" class="form-control" id="code" name="code" maxlength="6" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify</button>
                <div id="error-message" class="text-danger mt-3 text-center"></div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('2fa-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const code = document.getElementById('code').value;
            const errorMessage = document.getElementById('error-message');

            try {
                const response = await fetch('verify_2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'code=' + encodeURIComponent(code)
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = "dashboard.php"; // redirect after success
                } else {
                    errorMessage.textContent = result.error || "Invalid code. Please try again.";
                }
            } catch (err) {
                errorMessage.textContent = "An error occurred. Please try again.";
            }
        });
    </script>
</body>
</html>