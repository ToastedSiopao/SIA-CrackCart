<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manual - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
    <link href="admin_styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Administrator Manual</h1>
                </div>

                <section id="admin-guide">
                    <div class="accordion" id="admin-accordion">

                        <!-- Admin Login -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="admin-heading-one">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin-collapse-one">
                                    <strong>Step 1:</strong> Logging into the Admin Panel
                                </button>
                            </h2>
                            <div id="admin-collapse-one" class="accordion-collapse collapse" data-bs-parent="#admin-accordion">
                                <div class="accordion-body">
                                    <p>Access the administrative dashboard to manage the platform.</p>
                                    <ol>
                                        <li>Go to the <a href="index.php">Admin Login Page</a>.</li>
                                        <li>Use your specific admin credentials to sign in.</li>
                                        <li>You will land on the main Admin Dashboard, which provides an overview of platform activity.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Product Management -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="admin-heading-two">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin-collapse-two">
                                    <strong>Step 2:</strong> Product Management
                                </button>
                            </h2>
                            <div id="admin-collapse-two" class="accordion-collapse collapse" data-bs-parent="#admin-accordion">
                                <div class="accordion-body">
                                    <p>Control the entire product lifecycle from the admin panel.</p>
                                    <ol>
                                        <li>Navigate to the <a href="products.php">Products</a> page from the admin sidebar.</li>
                                        <li><strong>Add a Product:</strong> Click "Add Product", fill in details like name, price, stock, and image, then save.</li>
                                        <li><strong>Edit a Product:</strong> Click the "Edit" icon next to any product to modify its details.</li>
                                        <li><strong>Delete a Product:</strong> Click the "Delete" icon to remove a product.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Order Management -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="admin-heading-three">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin-collapse-three">
                                    <strong>Step 3:</strong> Order Fulfillment
                                </button>
                            </h2>
                            <div id="admin-collapse-three" class="accordion-collapse collapse" data-bs-parent="#admin-accordion">
                                <div class="accordion-body">
                                    <p>Oversee and manage all customer orders.</p>
                                     <ol>
                                        <li>Go to the <a href="orders.php">Orders</a> page.</li>
                                        <li>View all orders and their statuses (Pending, Confirmed, Shipped, etc.).</li>
                                        <li>Click on an order to view details. From here, you can update the order status as it moves through the fulfillment process.</li>
                                        <li>Manage cancellation requests via the <a href="manage_cancellations.php">Cancellations</a> tab.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                         <!-- User Management -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="admin-heading-four">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin-collapse-four">
                                    <strong>Step 4:</strong> User and Role Management
                                </button>
                            </h2>
                            <div id="admin-collapse-four" class="accordion-collapse collapse" data-bs-parent="#admin-accordion">
                                <div class="accordion-body">
                                    <p>Manage all registered users and their roles on the platform.</p>
                                    <ol>
                                        <li>Navigate to the <a href="manage_users.php">Manage Users</a> page.</li>
                                        <li>View all users, their status (Active, Inactive, Locked), and their role.</li>
                                        <li>You can update a user's status or change their role (e.g., promote a user to a "Driver" or "Admin").</li>
                                        <li>Locked accounts due to failed login attempts can be unlocked here.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>