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
    <style>
        .chat-prompt {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 60vh;
        }
        .chat-prompt .fa-comments {
            font-size: 5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
    </style>
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
                                <div class="card-body chat-prompt">
                                    <i class="fas fa-comments"></i>
                                    <h2>Have a Question?</h2>
                                    <p class="lead">Our team is here to help! Click the live chat bubble in the corner to start a conversation.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/68e341b3cacd7619509222d3/1j6rrh9d2';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!--End of Tawk.to Script-->
</body>
</html>
