<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manual - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-5">User Manual</h1>

        <!-- User Guide -->
        <section id="user-guide">
            <h2 class="mb-4">For Our Valued Customers</h2>
            <div class="accordion" id="user-accordion">

                <!-- Account Creation -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="user-heading-one">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#user-collapse-one">
                            <strong>Step 1:</strong> Creating Your Account
                        </button>
                    </h2>
                    <div id="user-collapse-one" class="accordion-collapse collapse" data-bs-parent="#user-accordion">
                        <div class="accordion-body">
                            <p>To begin shopping, you first need to register.</p>
                            <ol>
                                <li>Click on the "Sign Up" button in the navigation bar or go directly to the <a href="signup.php">Sign-Up Page</a>.</li>
                                <li>Fill out the registration form with your personal details.</li>
                                <li>Click "Sign Up". You will be automatically redirected to the login page.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Shopping -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="user-heading-two">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#user-collapse-two">
                            <strong>Step 2:</strong> Browsing and Shopping
                        </button>
                    </h2>
                    <div id="user-collapse-two" class="accordion-collapse collapse" data-bs-parent="#user-accordion">
                        <div class="accordion-body">
                            <p>Explore our range of products from various local producers.</p>
                            <ol>
                                <li>After logging in, you'll be taken to your dashboard.</li>
                                <li>Click on "Producers" in the navigation bar to see a list of our partner farms.</li>
                                <li>Select a producer to view their available products.</li>
                                <li>Enter the quantity you wish to purchase and click "Add to Cart".</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Checkout -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="user-heading-three">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#user-collapse-three">
                            <strong>Step 3:</strong> Completing Your Purchase
                        </button>
                    </h2>
                    <div id="user-collapse-three" class="accordion-collapse collapse" data-bs-parent="#user-accordion">
                        <div class="accordion-body">
                            <p>Finalize your order through our secure checkout process.</p>
                            <ol>
                                <li>Click the shopping cart icon in the navigation bar to review your items.</li>
                                <li>Proceed to the <a href="checkout.php">Checkout Page</a>.</li>
                                <li>Add or select your shipping address.</li>
                                <li>Choose your payment method: PayPal or Cash on Delivery (COD).</li>
                                <li>Confirm your order. You will be redirected to an order confirmation page with your details.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Order History -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="user-heading-four">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#user-collapse-four">
                            <strong>Step 4:</strong> Tracking Your Orders
                        </button>
                    </h2>
                    <div id="user-collapse-four" class="accordion-collapse collapse" data-bs-parent="#user-accordion">
                        <div class="accordion-body">
                            <p>Keep track of your past and present orders.</p>
                            <ol>
                                <li>Navigate to the <a href="my_orders.php">My Orders</a> page from your user dashboard or the navigation dropdown.</li>
                                <li>Here you can view your complete order history and the current status of each order.</li>
                            </ol>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>