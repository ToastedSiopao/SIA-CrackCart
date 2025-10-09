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

$item_query = "SELECT 
                    poi.order_item_id, 
                    po.order_id, 
                    poi.product_type, 
                    po.STATUS AS order_status,
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
     <style>
        :root {
            --primary-color: #FFD500; 
            --secondary-color: #333;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 12px;
            --box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        body {
            background-color: var(--light-color);
            font-family: 'Poppins', sans-serif;
        }
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
        }
        .card-header h2 {
            color: var(--dark-color);
            font-weight: 700;
        }
        .item-details {
            background-color: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .item-details strong {
            font-size: 1.1rem;
            color: var(--secondary-color);
        }
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .form-check-label {
            padding-left: 0.5rem;
            width: 100%;
        }
        .form-check {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        .form-check:hover {
            background-color: #fefefe;
            border-color: #c7c7c7;
        }
        .form-check-input[type="radio"] {
             margin-top: 0;
        }
        .form-check-input[type="radio"]:checked + .form-check-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--dark-color);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #e6c000;
            border-color: #e6c000;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 213, 0, 0.4);
        }
        .btn-secondary {
           font-weight: 600;
           padding: 0.75rem 1.5rem;
           border-radius: 8px;
        }
        .return-policy-note {
            font-size: 0.9rem;
            background-color: #f0f0f0;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card">
                <div class="card-header py-3">
                    <h2 class="text-center mb-0">Request a Return</h2>
                </div>
                <div class="card-body p-4">
                    <?php 
                    if ($item && strtolower(trim($item['order_status'])) === 'delivered'): 
                    ?>
                        <div class="mb-4 item-details">
                            <h5 class="mb-2">Item Details</h5>
                            <div>
                                <strong><?php echo htmlspecialchars($item['product_type']); ?></strong><br>
                                <small class="text-muted">From Order #<?php echo $item['order_id']; ?></small>
                            </div>
                        </div>
                        <hr class="my-4">
                        <form id="returnRequestForm" novalidate enctype="multipart/form-data">
                            <input type="hidden" name="order_item_id" value="<?php echo $item['order_item_id']; ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold mb-3">Reason for Return</label>
                                <div class="row">
                                    <?php 
                                        $reasons = [
                                            "Damaged in transit", "Wrong item delivered", "Item is expired",
                                            "Missing items from order", "Quality not as expected", "Received a different size/type"
                                        ];
                                        foreach ($reasons as $index => $reasonText) {
                                            $id = "reason" . ($index + 1);
                                            echo '<div class="col-md-6">';
                                            echo '    <div class="form-check">';
                                            echo "        <input class='form-check-input' type='radio' name='reason' id='".$id."' value='".$reasonText."' required>";
                                            echo "        <label class='form-check-label' for='".$id."'>".$reasonText."</label>";
                                            echo '    </div>';
                                            echo '</div>';
                                        }
                                    ?>
                                </div>
                                <div id="reason-error" class="invalid-feedback d-block" style="display: none;">Please select a reason.</div>
                            </div>

                            <div class="mb-4" id="damaged_image_container" style="display: none;">
                                <label for="damaged_image" class="form-label">Upload Image of Damaged Item</label>
                                <input class="form-control" type="file" id="damaged_image" name="damaged_image" accept="image/*">
                                <div class="invalid-feedback">A picture is required for damaged items.</div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="view_order.php?order_id=<?php echo $item['order_id']; ?>" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Submit Request</button>
                            </div>
                        </form>
                        
                        <div class="return-policy-note">
                           Please select from the acceptable reasons for your return. For more details, refer to our <a href="/return-policy">return policy</a>.
                        </div>
                        
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
    const damagedImageContainer = document.getElementById('damaged_image_container');
    const damagedImageInput = document.getElementById('damaged_image');
    const reasonRadios = returnForm.querySelectorAll('input[name="reason"]');
    const reasonError = document.getElementById('reason-error');

    reasonRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            reasonError.style.display = 'none';
            if (this.value === 'Damaged in transit') {
                damagedImageContainer.style.display = 'block';
                damagedImageInput.setAttribute('required', 'required');
            } else {
                damagedImageContainer.style.display = 'none';
                damagedImageInput.removeAttribute('required');
            }
        });
    });

    returnForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;
        const formMessage = document.getElementById('formMessage');
        formMessage.innerHTML = '';
        damagedImageInput.classList.remove('is-invalid');

        const selectedReasonRadio = form.querySelector('input[name="reason"]:checked');
        let isValid = true;

        if (!selectedReasonRadio) {
            reasonError.style.display = 'block';
            isValid = false;
        }

        if (selectedReasonRadio && selectedReasonRadio.value === 'Damaged in transit') {
            if (!damagedImageInput.files || damagedImageInput.files.length === 0) {
                damagedImageInput.classList.add('is-invalid');
                isValid = false;
            }
        }

        if (!isValid) {
            return;
        }

        const formData = new FormData(form);
        const orderId = <?php echo $item['order_id'] ?? 0; ?>;

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
