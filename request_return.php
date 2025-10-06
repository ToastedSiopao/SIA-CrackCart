<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$order_item_id = isset($_GET['order_item_id']) ? intval($_GET['order_item_id']) : 0;

if ($order_item_id === 0) {
    die("Invalid item specified.");
}

$user_id = $_SESSION['user_id'];

// CORRECTED QUERY: Using uppercase for STATUS and USER_ID to match the database schema.
$item_query = "SELECT 
                    poi.order_item_id, 
                    po.order_id, 
                    poi.product_type, 
                    po.STATUS AS order_status,  -- Using the correct uppercase column name
                    po.USER_ID
               FROM 
                    product_order_items poi
               JOIN 
                    product_orders po ON poi.order_id = po.order_id
               LEFT JOIN 
                    returns r ON poi.order_item_id = r.order_item_id
               WHERE 
                    poi.order_item_id = ? AND po.USER_ID = ? AND r.return_id IS NULL";

$item_stmt = $conn->prepare($item_query);
$item_stmt->bind_param("ii", $order_item_id, $user_id);
$item_stmt->execute();
$result = $item_stmt->get_result();
$item = $result->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Return - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h2 class="text-center">Request a Return</h2>
                </div>
                <div class="card-body">
                    <?php 
                    if ($item && strtolower(trim($item['order_status'])) === 'delivered'): 
                    ?>
                        <div class="mb-4">
                            <h5>Item Details</h5>
                            <div>
                                <strong><?php echo htmlspecialchars($item['product_type']); ?></strong><br>
                                <small class="text-muted">From Order #<?php echo $item['order_id']; ?></small>
                            </div>
                        </div>
                        <hr>
                        <form id="returnRequestForm">
                            <input type="hidden" name="order_item_id" value="<?php echo $item['order_item_id']; ?>">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Return</label>
                                <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Please describe the issue with the item (e.g., wrong size, damaged in transit, etc.)"></textarea>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="view_order.php?order_id=<?php echo $item['order_id']; ?>" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Submit Request</button>
                            </div>
                        </form>
                        <div id="formMessage" class="mt-3"></div>
                    <?php else:
                        $errorMessage = "This item is not eligible for a return.";
                        if (!$item) {
                            $errorMessage = "The specified item could not be found, does not belong to you, or a return has already been requested.";
                        } elseif (isset($item['order_status']) && strtolower(trim($item['order_status'])) !== 'delivered') {
                            $errorMessage = "You can only request a return for items that have been delivered.";
                        }
                    ?>
                        <div class="alert alert-warning"><?php echo $errorMessage; ?></div>
                        <a href="my_orders.php" class="btn btn-secondary">Back to My Orders</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const returnForm = document.getElementById('returnRequestForm');
if (returnForm) {
    returnForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const formMessage = document.getElementById('formMessage');
        const orderId = <?php echo $item['order_id'] ?? 0; ?>;
        formMessage.innerHTML = '';

        try {
            const response = await fetch('api/submit_return.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                form.style.display = 'none';
                formMessage.innerHTML = `<div class="alert alert-success">${result.message} You will be redirected shortly.</div>`;
                setTimeout(() => window.location.href = 'view_order.php?order_id=' + orderId, 3000);
            } else {
                formMessage.innerHTML = `<div class="alert alert-danger">${result.message || 'An error occurred.'}</div>`;
            }
        } catch (error) {
            formMessage.innerHTML = `<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>`;
        }
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
