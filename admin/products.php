<?php
session_start();
// Security check: ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not an admin, redirect to the login page
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="admin-styles.css?v=1.1" rel="stylesheet">
</head>
<body>
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include('admin_sidebar.php'); ?>
            <?php include('admin_offcanvas_sidebar.php'); ?>

            <main class="col p-4 main-content">
                <div class="card shadow-sm border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Product Management</h4>
                        <a href="product_form.php" class="btn btn-primary">Add New Product</a>
                    </div>

                    <div id="alert-container"></div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product Name</th>
                                    <th>Producer</th>
                                    <th>Price</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Stock (Trays)</th>
                                    <th>Tray Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="products-table-body">
                                <!-- Product rows will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productsTableBody = document.getElementById('products-table-body');
            const alertContainer = document.getElementById('alert-container');

            const showAlert = (type, message) => {
                alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            };

            const fetchProducts = async () => {
                try {
                    const response = await fetch('api/get_products.php');
                    const result = await response.json();

                    if (result.status === 'success') {
                        renderProducts(result.data);
                    } else {
                        showAlert('danger', result.message || 'Could not load products.');
                    }
                } catch (error) {
                    showAlert('danger', 'Could not connect to the server to get products.');
                }
            };

            const renderProducts = (products) => {
                if (products.length === 0) {
                    productsTableBody.innerHTML = '<tr><td colspan="9" class="text-center">No products found.</td></tr>';
                    return;
                }

                productsTableBody.innerHTML = products.map(product => {
                    const stockInTrays = product.TRAY_SIZE > 0 ? Math.floor(product.STOCK / product.TRAY_SIZE) : 0;
                    return `
                    <tr>
                        <td>${product.PRICE_ID}</td>
                        <td>${product.TYPE}</td>
                        <td>${product.PRODUCER_NAME}</td>
                        <td>₱${parseFloat(product.PRICE).toFixed(2)}</td>
                        <td>${product.PER}</td>
                        <td>${product.STATUS}</td>
                        <td>${stockInTrays}</td>
                        <td>${product.TRAY_SIZE || 'N/A'}</td>
                        <td>
                            <a href="product_form.php?id=${product.PRICE_ID}" class="btn btn-sm btn-primary">Edit</a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${product.PRICE_ID}">Delete</button>
                        </td>
                    </tr>
                `}).join('');
            };

            // Event delegation for delete buttons
            productsTableBody.addEventListener('click', async (event) => {
                if (event.target.classList.contains('delete-btn')) {
                    const productId = event.target.dataset.id;
                    if (confirm(`Are you sure you want to delete product ID #${productId}?`)) {
                        await deleteProduct(productId);
                    }
                }
            });

            const deleteProduct = async (productId) => {
                 try {
                    const formData = new FormData();
                    formData.append('id', productId);

                    const response = await fetch('api/delete_product.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        showAlert('success', result.message);
                        fetchProducts(); // Refresh the table
                    } else {
                        showAlert('danger', result.message || 'Could not delete the product.');
                    }
                } catch (error) {
                    showAlert('danger', 'Could not connect to the server to delete the product.');
                }
            };

            fetchProducts();
        });
    </script>
</body>
</html>