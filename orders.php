<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrackCart - Place Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header-logo text-center py-4">
            <img src="assets/Logo.png" alt="CrackCart Logo" class="logo-img">
        </div>

        <h1 class="text-center mb-4">Place Your Order</h1>

        <?php
        if (isset($_GET['producer'])) {
            $producer_name = htmlspecialchars($_GET['producer']);
            echo "<h2 class='text-center mb-4'>You are ordering from: <strong>" . $producer_name . "</strong></h2>";
        } else {
            echo "<div class='alert alert-warning'>No producer selected. Please go back to the producers page and select a producer to order from.</div>";
        }
        ?>

        <form action="submit_order.php" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Shipping Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="egg_type" class="form-label">Egg Type</label>
                    <select class="form-select" id="egg_type" name="egg_type" required>
                        <option value="">Select an egg type...</option>
                        <option value="chicken">Chicken Eggs</option>
                        <option value="duck">Duck Eggs</option>
                        <option value="quail">Quail Eggs</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="quantity" class="form-label">Quantity (in dozens)</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                </div>
            </div>
            
            <input type="hidden" name="producer_name" value="<?php echo htmlspecialchars($_GET['producer'] ?? ''); ?>">

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Submit Order</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>