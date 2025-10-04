<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_editing = $product_id > 0;
$page_title = $is_editing ? "Edit Product" : "Add Product";

$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin-styles.css?v=1.0" rel="stylesheet">
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
                        <h4 class="mb-0"><?php echo $page_title; ?></h4>
                        <a href="products.php" class="btn btn-outline-secondary">Back to Products</a>
                    </div>

                    <div id="alert-container"></div>

                    <form id="product-form">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="mb-3">
                            <label for="product-name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="product-name" name="product_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="producer" class="form-label">Producer</label>
                            <select class="form-select" id="producer" name="producer_id" required>
                                <!-- Options will be loaded by JS -->
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="unit" class="form-label">Unit (e.g., 'per tray')</label>
                                <input type="text" class="form-control" id="unit" name="unit" value="per tray" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="0" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const isEditing = <?php echo json_encode($is_editing); ?>;
            const productId = <?php echo json_encode($product_id); ?>;
            const form = document.getElementById('product-form');
            const producerSelect = document.getElementById('producer');
            const alertContainer = document.getElementById('alert-container');

            const showAlert = (type, message) => {
                alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            };

            // Fetch producers
            try {
                const response = await fetch('api/get_producers.php');
                const result = await response.json();
                if (result.status === 'success') {
                    producerSelect.innerHTML = result.data.map(p => `<option value="${p.PRODUCER_ID}">${p.NAME}</option>`).join('');
                } else {
                     showAlert('danger', 'Could not load producers.');
                }
            } catch (error) {
                showAlert('danger', 'Could not connect to the server to get producers.');
            }
            
            // If editing, fetch product details
            if (isEditing) {
                try {
                    const response = await fetch(`api/get_product_details.php?id=${productId}`);
                    const result = await response.json();
                    if (result.status === 'success') {
                        const product = result.data;
                        form.elements.product_name.value = product.TYPE;
                        form.elements.producer_id.value = product.PRODUCER_ID;
                        form.elements.price.value = product.PRICE;
                        form.elements.unit.value = product.PER;
                        form.elements.status.value = product.STATUS;
                        form.elements.stock.value = product.STOCK;
                    } else {
                        showAlert('danger', result.message);
                    }
                } catch (error) {
                    showAlert('danger', 'Could not connect to the server to get product details.');
                }
            }

            // Handle form submission
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                const formData = new FormData(form);
                const endpoint = isEditing ? 'api/update_product.php' : 'api/create_product.php';
                
                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        showAlert('success', result.message);
                        if (!isEditing) form.reset();
                        setTimeout(() => { window.location.href = 'products.php'; }, 1000);
                    } else {
                        showAlert('danger', result.message || 'An unknown error occurred.');
                    }
                } catch (error) {
                    showAlert('danger', 'Could not connect to the server.');
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
