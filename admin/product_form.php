<?php
session_start();
// Security check: ensure the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not an admin, redirect to the login page
    header("Location: login.php?error=Please log in to access this page.");
    exit();
}

// Include the database connection
require_once '../db_connect.php';

// Initialize variables
$product = null;
$producers = [];
$is_edit = false;
$page_title = 'Add New Product';

// Fetch all producers for the dropdown
$producer_query = "SELECT PRODUCER_ID, NAME FROM PRODUCER ORDER BY NAME ASC";
$producer_result = $conn->query($producer_query);
if ($producer_result) {
    while ($row = $producer_result->fetch_assoc()) {
        $producers[] = $row;
    }
}

// Check if an ID is provided for editing
if (isset($_GET['id'])) {
    $is_edit = true;
    $price_id = intval($_GET['id']);
    $page_title = 'Edit Product';

    // Fetch the product details
    $product_query = "SELECT p.PRICE_ID, p.PRODUCER_ID, p.TYPE, p.PRICE, p.PER, p.STOCK, p.TRAY_SIZE, pr.NAME as producer_name
                      FROM PRICE p
                      JOIN PRODUCER pr ON p.PRODUCER_ID = pr.PRODUCER_ID
                      WHERE p.PRICE_ID = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $price_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        // Product not found, redirect with an error
        header("Location: products.php?error=Product not found.");
        exit();
    }
    $stmt->close();
}

// Handle form submission for both add and edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $producer_id = intval($_POST['producer_id']);
    $type = trim($_POST['type']);
    $price = floatval($_POST['price']);
    $per = trim($_POST['per']);
    $stock = intval($_POST['stock']);
    $tray_size = intval($_POST['tray_size']);
    $price_id = isset($_POST['price_id']) ? intval($_POST['price_id']) : null;

    if ($price_id) { // This is an update
        $sql = "UPDATE PRICE SET PRODUCER_ID = ?, TYPE = ?, PRICE = ?, PER = ?, STOCK = ?, TRAY_SIZE = ? WHERE PRICE_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdsiii", $producer_id, $type, $price, $per, $stock, $tray_size, $price_id);
        $action = "updated";
    } else { // This is an insert
        $sql = "INSERT INTO PRICE (PRODUCER_ID, TYPE, PRICE, PER, STOCK, TRAY_SIZE) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdsii", $producer_id, $type, $price, $per, $stock, $tray_size);
        $action = "added";
    }

    if ($stmt->execute()) {
        header("Location: products.php?success=Product successfully {$action}.");
    } else {
        $error_message = "Error: " . $stmt->error;
        // Stay on the form and show an error
    }
    $stmt->close();
    $conn->close();
    exit();
}

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
    <link href="admin-styles.css?v=1.2" rel="stylesheet">
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
                        <a href="products.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Products
                        </a>
                    </div>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form id="productForm" action="product_form.php<?php echo $is_edit ? '?id=' . htmlspecialchars($product['PRICE_ID']) : ''; ?>" method="POST" class="needs-validation" novalidate>
                        <?php if ($is_edit): ?>
                            <input type="hidden" name="price_id" value="<?php echo htmlspecialchars($product['PRICE_ID']); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="producer_id" class="form-label">Producer</label>
                            <select class="form-select" id="producer_id" name="producer_id" required>
                                <option value="">Select Producer</option>
                                <?php foreach ($producers as $producer_item): ?>
                                    <option value="<?php echo $producer_item['PRODUCER_ID']; ?>"
                                        <?php echo ($is_edit && isset($product['PRODUCER_ID']) && $product['PRODUCER_ID'] == $producer_item['PRODUCER_ID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($producer_item['NAME']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a producer.</div>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Product Type/Name</label>
                            <input type="text" class="form-control" id="type" name="type"
                                   value="<?php echo $is_edit ? htmlspecialchars($product['TYPE']) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter the product type.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="price" name="price"
                                           value="<?php echo $is_edit ? htmlspecialchars($product['PRICE']) : ''; ?>" step="0.01" min="0" required>
                                    <div class="invalid-feedback">Please enter a valid price.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="per" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="per" name="per"
                                       value="<?php echo $is_edit ? htmlspecialchars($product['PER']) : 'tray'; ?>" required>
                                <small class="form-text text-muted">e.g., tray, piece, box</small>
                                <div class="invalid-feedback">Please enter the unit.</div>
                            </div>
                        </div>

                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock"
                                       value="<?php echo $is_edit ? htmlspecialchars($product['STOCK']) : '0'; ?>" min="0" required>
                                <div class="invalid-feedback">Please enter a valid stock quantity.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tray_size" class="form-label">Tray Size</label>
                                <select class="form-select" id="tray_size" name="tray_size">
                                    <option value="30" <?php echo ($is_edit && $product['TRAY_SIZE'] == 30) ? 'selected' : (!$is_edit ? 'selected' : ''); ?>>30 (Standard)</option>
                                    <option value="12" <?php echo ($is_edit && $product['TRAY_SIZE'] == 12) ? 'selected' : ''; ?>>12</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Save Changes' : 'Add Product'; ?></button>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple form validation script
        (function () {
          'use strict'
          var forms = document.querySelectorAll('.needs-validation')
          Array.prototype.slice.call(forms)
            .forEach(function (form) {
              form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                  event.preventDefault()
                  event.stopPropagation()
                }
                form.classList.add('was-validated')
              }, false)
            })
        })()
    </script>
</body>
</html>
