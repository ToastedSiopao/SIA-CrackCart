<?php
session_start();
// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php"); // Redirect to login page if not authorized
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - CrackCart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../dashboard-styles.css?v=1.3" rel="stylesheet">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Product Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="product_form.php" class="btn btn-sm btn-outline-secondary">Add New Product</a>
                    </div>
                </div>

                <div id="alert-container"></div>

                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Producer</th>
                                <th>Price</th>
                                <th>Unit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-table-body">
                            <!-- Product rows will be inserted here by JavaScript -->
                        </tbody>
                    </table>
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
                    productsTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No products found.</td></tr>';
                    return;
                }

                productsTableBody.innerHTML = products.map(product => `
                    <tr>
                        <td>${product.PRICE_ID}</td>
                        <td>${product.TYPE}</td>
                        <td>${product.PRODUCER_NAME}</td>
                        <td>₱${parseFloat(product.PRICE).toFixed(2)}</td>
                        <td>${product.PER}</td>
                        <td>
                            <a href="product_form.php?id=${product.PRICE_ID}" class="btn btn-sm btn-primary">Edit</a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${product.PRICE_ID}">Delete</button>
                        </td>
                    </tr>
                `).join('');
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
                    const response = await fetch(`api/delete_product.php?id=${productId}`, {
                        method: 'DELETE'
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
