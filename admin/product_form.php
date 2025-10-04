<?php
include 'admin_header.php';
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

if ($price_id > 0) {
    $page_title = "Edit Product";
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
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $page_title; ?></h1>
        <a href="products.php" class="btn btn-sm btn-outline-secondary">Back to Products</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="productForm" method="POST">
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
                    <select class="form-control" id="status" name="status" required>
                        <option value="active" <?php echo ($product['STATUS'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($product['STATUS'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="out of stock" <?php echo ($product['STATUS'] == 'out of stock') ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save Product</button>
            </form>
            <div id="formMessage" class="mt-3"></div>
        </div>
    </div>
</main>

<script>
document.getElementById('productForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const formMessage = document.getElementById('formMessage');
    const submitButton = form.querySelector('button[type="submit"]');
    formMessage.innerHTML = '';

    const priceId = formData.get('price_id');
    const action = priceId > 0 ? 'api/edit_product.php' : 'api/add_product.php';

    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

    try {
        const response = await fetch(action, {
            method: 'POST',
            body: new URLSearchParams(formData).toString(), // Send as form-urlencoded
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        const result = await response.json();

        if (response.ok) {
            formMessage.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
            setTimeout(() => {
                window.location.href = 'products.php';
            }, 2000);
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

<?php include '../includes/admin_footer.php'; ?>
