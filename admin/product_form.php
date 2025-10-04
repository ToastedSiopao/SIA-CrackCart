<?php
session_start();
// Security check: ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in to access the admin panel.");
    exit();
}

include '../db_connect.php';

$price_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = [
    'TYPE' => '', 
    'PRODUCER_NAME' => '', 
    'PRICE' => '', 
    'PER' => '',
    'STATUS' => 'active',
    'STOCK' => 0
];
$page_title = "Add New Product";
$action = 'api/create_product.php'; // Action for new product

if ($price_id > 0) {
    $page_title = "Edit Product";
    $action = 'api/update_product.php'; // Action for updating a product
    $stmt = $conn->prepare("SELECT * FROM PRICE WHERE PRICE_ID = ?");
    $stmt->bind_param("i", $price_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
    $stmt->close();
}

$conn->close();
$user_name = $_SESSION['user_first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
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

                    <form id="productForm">
                        <input type="hidden" name="price_id" value="<?php echo $price_id; ?>">
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="type" name="type" value="<?php echo htmlspecialchars($product['TYPE']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="producer_name" class="form-label">Producer</label>
                            <input type="text" class="form-control" id="producer_name" name="producer_name" value="<?php echo htmlspecialchars($product['PRODUCER_NAME']); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['PRICE']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="per" class="form-label">Unit (e.g., kg, piece)</label>
                                <input type="text" class="form-control" id="per" name="per" value="<?php echo htmlspecialchars($product['PER']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($product['STOCK']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo ($product['STATUS'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($product['STATUS'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="out of stock" <?php echo ($product['STATUS'] == 'out of stock') ? 'selected' : ''; ?>>Out of Stock</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </form>
                    <div id="formMessage" class="mt-3"></div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('productForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const formMessage = document.getElementById('formMessage');
        const submitButton = form.querySelector('button[type="submit"]');
        formMessage.innerHTML = '';

        const priceId = formData.get('price_id');
        const action = '<?php echo $action; ?>';

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        try {
            const response = await fetch(action, {
                method: 'POST',
                body: new URLSearchParams(formData).toString(),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });

            const result = await response.json();

            if (result.status === 'success') {
                formMessage.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                setTimeout(() => {
                    window.location.href = 'products.php';
                }, 1500);
            } else {
                throw new Error(result.message || 'An unknown error occurred.');
            }
        } catch (error) {
            formMessage.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            submitButton.disabled = false;
            submitButton.innerHTML = 'Save Product';
        }
    });
    </script>
</body>
</html>
