<?php
session_start();
include("db_connect.php");
include("error_handler.php");

$name = "";
$email = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT FIRST_NAME, LAST_NAME, EMAIL FROM USER WHERE USER_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $name = trim($user['FIRST_NAME'] . " " . $user['LAST_NAME']);
        $email = $user['EMAIL'];
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="dashboard-styles.css?v=2.9" rel="stylesheet">
</head>
<body>
    <?php include("navbar.php"); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php if (isset($_SESSION['user_id'])) {
                include("sidebar.php");
                include("offcanvas_sidebar.php");
            }
            ?>

            <main class="col ps-md-2 pt-2">
                <div class="container">
                    <div class="page-header">
                        <h1 class="text-center">Contact Us</h1>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-center">Have a question or need help? Fill out the form below to get in touch with our team.</p>
                                    <div id="contact-alert-container"></div>
                                    <form id="contactForm">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="subject" class="form-label">Subject</label>
                                            <input type="text" class="form-control" id="subject" name="subject" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="message" class="form-label">Message</label>
                                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">Send Message</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('contactForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const alertContainer = document.getElementById('contact-alert-container');
        const submitButton = form.querySelector('button[type="submit"]');

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

        const showAlert = (type, message) => {
            alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        };

        try {
            const response = await fetch('api/submit_contact.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                showAlert('success', 'Your message has been sent successfully! We will get back to you shortly.');
                form.reset();
            } else {
                showAlert('danger', result.message || 'An error occurred. Please try again.');
            }
        } catch (error) {
            showAlert('danger', 'Could not connect to the server. Please check your internet connection and try again.');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Send Message';
        }
    });
    </script>
</body>
</html>
