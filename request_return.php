<?php
include 'includes/header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Invalid order specified.</div></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch order items that are eligible for return
$items_query = "SELECT oi.product_id, p.name, p.image_url 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                LEFT JOIN returns r ON oi.order_id = r.order_id AND oi.product_id = r.product_id
                WHERE oi.order_id = ? AND r.return_id IS NULL";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$eligible_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();
$conn->close();

?>

<div class="container my-5">
    <h2 class="mb-4">Request a Return</h2>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <?php if (count($eligible_items) > 0): ?>
                    <form id="returnRequestForm">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product to Return</label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="" disabled selected>Select a product...</option>
                                <?php foreach ($eligible_items as $item): ?>
                                    <option value="<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Return</label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Please describe the issue with the item (e.g., wrong size, damaged in transit, etc.)"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </form>
                    <div id="formMessage" class="mt-3"></div>
                    <?php else: ?>
                        <div class="alert alert-info">There are no items in this order eligible for a new return. You might have already requested a return for them.</div>
                        <a href="view_order.php?order_id=<?php echo $order_id; ?>" class="btn btn-secondary">Back to Order</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('returnRequestForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const formMessage = document.getElementById('formMessage');
    formMessage.innerHTML = '';

    try {
        const response = await fetch('api/submit_return.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            form.reset();
            formMessage.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
            setTimeout(() => window.location.href = 'view_order.php?order_id=<?php echo $order_id; ?>', 3000);
        } else {
            formMessage.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
        }
    } catch (error) {
        formMessage.innerHTML = `<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>`;
    }
});
</script>

<?php
include 'includes/footer.php';
?>
