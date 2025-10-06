<?php
session_start();

$user_name = $_SESSION['user_first_name'] ?? 'Valued Customer';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return & Refund Policy - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css"> 
    <style>
        body {
            background-color: #f8f9fa;
        }
        .policy-header {
            background-color: #343a40;
            color: white;
            padding: 4rem 2rem;
            border-radius: 0.5rem;
            text-align: center;
        }
        .policy-header h1 {
            font-weight: 300;
        }
        .policy-section {
            background: white;
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
            transition: transform 0.2s ease-in-out;
        }
        .policy-section:hover {
            transform: translateY(-5px);
        }
        .policy-section .card-body {
            padding: 2rem;
        }
        .policy-section .icon {
            font-size: 3rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="policy-header mb-5">
        <h1 class="display-5">Return & Refund Policy</h1>
        <p class="lead">Our commitment to your satisfaction.</p>
    </div>

    <div class="row g-4">
        <!-- Section 1: Eligibility -->
        <div class="col-lg-6">
            <div class="card policy-section h-100">
                <div class="card-body text-center">
                    <div class="icon mb-3"><i class="bi bi-patch-check"></i></div>
                    <h4 class="card-title">1. Return Eligibility</h4>
                    <p class="card-text text-start">
                        To be eligible for a return, please ensure the following:
                    </p>
                    <ul class="list-group list-group-flush text-start">
                        <li class="list-group-item">Request is submitted within <strong>14 days</strong> of delivery.</li>
                        <li class="list-group-item">Item is unused and in its original packaging.</li>
                        <li class="list-group-item">A valid reason is provided (e.g., damaged, wrong item).</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section 2: How to Request -->
        <div class="col-lg-6">
            <div class="card policy-section h-100">
                <div class="card-body text-center">
                    <div class="icon mb-3"><i class="bi bi-box-seam"></i></div>
                    <h4 class="card-title">2. How to Request a Return</h4>
                    <p class="card-text">
                        Requesting a return is simple. Navigate to your <a href="my_orders.php">My Orders</a> page, select the item you wish to return, and click the 'Request Return' button. The form will guide you through the necessary steps.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 3: Refunds -->
        <div class="col-lg-6">
            <div class="card policy-section h-100">
                <div class="card-body text-center">
                    <div class="icon mb-3"><i class="bi bi-credit-card"></i></div>
                    <h4 class="card-title">3. Refund Process</h4>
                    <p class="card-text">
                        Once we receive and inspect the returned item, we will notify you of the approval or rejection of your refund. If approved, your refund will be processed, and a credit will automatically be applied to your original method of payment within <strong>5-7 business days</strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 4: Contact Us -->
        <div class="col-lg-6">
            <div class="card policy-section h-100">
                <div class="card-body text-center">
                    <div class="icon mb-3"><i class="bi bi-question-circle"></i></div>
                    <h4 class="card-title">4. Have Questions?</h4>
                    <p class="card-text">
                        If you have any questions or need assistance with our Return and Refund Policy, please do not hesitate to <a href="contact.php">contact our support team</a>. We are here to help!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted mt-5">
        <p>Last Updated: <?php echo date('F j, Y'); ?></p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>