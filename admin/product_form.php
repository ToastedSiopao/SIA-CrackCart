<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_edit_mode = $product_id > 0;
$page_title = $is_edit_mode ? "Edit Product" : "Add New Product";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - CrackCart Admin</title>
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
                    <h1 class="h2"><?php echo $page_title; ?></h1>
                </div>

                <div id="alert-container"></div>

                <form id="product-form">
                    <input type="hidden" id="product-id" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product-name" name="product_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="producer" class="form-label">Producer/Brand</label>
                                <select class="form-select" id="producer" name="producer_id" required>
                                    <!-- Producers will be loaded here by JavaScript -->
                                </select>
                            </div>
                             <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                             <div class="mb-3">
                                <label for="unit" class="form-label">Unit (e.g., 'per tray')</label>
                                <input type="text" class="form-control" id="unit" name="unit" value="per tray" required>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productForm = document.getElementById('product-form');
            const productId = document.getElementById('product-id').value;
            const isEditMode = productId > 0;
            const alertContainer = document.getElementById('alert-container');

            const showAlert = (type, message) => {
                alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
                 window.scrollTo(0, 0);
            };

            const loadProducers = async () => {
                try {
                    const response = await fetch('api/get_producers.php'); // We will create this API endpoint
                    const result = await response.json();
                    if (result.status === 'success') {
                        const producerSelect = document.getElementById('producer');
                        producerSelect.innerHTML = result.data.map(p => `<option value="${p.PRODUCER_ID}">${p.NAME}</option>`).join('');
                    }
                } catch (error) {
                    showAlert('danger', 'Could not load producers.');
                }
            };

            const loadProductDetails = async () => {
                if (!isEditMode) return;
                try {
                    const response = await fetch(`api/get_product_details.php?id=${productId}`); // And this one
                    const result = await response.json();

                    if (result.status === 'success') {
                        const product = result.data;
                        document.getElementById('product-name').value = product.TYPE;
                        document.getElementById('producer').value = product.PRODUCER_ID;
                        document.getElementById('price').value = product.PRICE;
                        document.getElementById('unit').value = product.PER;
                    } else {
                        showAlert('danger', result.message);
                    }
                } catch (error) {
                    showAlert('danger', 'Could not load product details.');
                }
            };

            productForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const formData = new FormData(productForm);
                const url = isEditMode ? `api/update_product.php` : `api/create_product.php`; // And these two

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        window.location.href = 'products.php?success=' + encodeURIComponent(result.message);
                    } else {
                        showAlert('danger', result.message || 'An error occurred.');
                    }
                } catch (error) {
                    showAlert('danger', 'Could not connect to the server.');
                }
            });

            // Initial load
            Promise.all([loadProducers()]).then(() => {
                if (isEditMode) {
                    loadProductDetails();
                }
            });
        });
    </script>
</body>
</html>
