<?php
require_once 'session_handler.php';

if (!isset($_GET['producer_id'])) {
    header('Location: producers.php');
    exit;
}
$producer_id = $_GET['producer_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order from Producer - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="dashboard-styles.css?v=3.2" rel="stylesheet">
  <style>
    .producer-logo-large {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border: 2px solid #ffc107;
    }
    .form-label.fw-bold {
        font-weight: 500 !important;
        margin-bottom: 0.5rem;
    }
    .egg-option-card .form-check-label {
        display: block;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: border-color .15s ease-in-out, background-color .15s ease-in-out;
    }
    .egg-option-card .form-check-input:checked + .form-check-label {
        background-color: #fff9e0;
        border-color: #ffc107;
    }
    .egg-option-card .form-check-label:hover {
        background-color: #f8f9fa;
    }
    .vehicle-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }
    .vehicle-type-card {
        padding: 0;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    .vehicle-type-card .form-check-input { display: none; }
    .vehicle-type-card .form-check-label {
        display: flex;
        align-items: center;
        padding: 1rem;
        width: 100%;
        cursor: pointer;
        border-radius: 0.375rem;
    }
    .vehicle-icon {
        font-size: 2.5rem;
        color: #495057;
        margin-right: 1rem;
        width: 40px;
        text-align: center;
    }
    .vehicle-details { display: flex; flex-direction: column; line-height: 1.3; }
    .vehicle-type-name { font-weight: bold; color: #212529; }
    .vehicle-fee { font-weight: 500; color: #198754; }
    .vehicle-capacity-range { font-size: 0.85rem; color: #6c757d; }
    .vehicle-type-card .form-check-input:checked + .form-check-label {
        background-color: #fff3cd;
        border-color: #ffc107;
    }
    .vehicle-type-card:hover { border-color: #ffc107; }
  </style>
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col p-4">
        <div class="card shadow-sm border-0 p-4">
            <div id="content-container">
                <div id="producer-info-placeholder"></div>
                <div id="order-form-container"></div>
            </div>
        </div>
      </main>
    </div>
  </div>
  
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="cartToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header"><strong class="me-auto">CrackCart</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>
      <div class="toast-body"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const producerId = new URLSearchParams(window.location.search).get('producer_id');
    const producerInfoContainer = document.getElementById('producer-info-placeholder');
    const orderFormContainer = document.getElementById('order-form-container');
    const cartToastEl = document.getElementById('cartToast');
    const cartToast = new bootstrap.Toast(cartToastEl);

    if (!producerId) {
        orderFormContainer.innerHTML = `<div class="alert alert-danger">No producer selected. Please <a href="producers.php" class="alert-link">go back</a> and select a producer.</div>`;
        return;
    }

    const getVehicleIcon = (type) => {
        const normalizedType = type ? type.toLowerCase() : '';
        if (normalizedType.includes('motor')) return 'bi-bicycle';
        if (normalizedType.includes('suv')) return 'bi-car-front-fill';
        if (normalizedType.includes('car')) return 'bi-car-front-fill';
        if (normalizedType.includes('truck')) return 'bi-truck';
        return 'bi-question-circle';
    };

    Promise.all([
        fetch(`api/producers.php?producer_id=${producerId}`).then(res => res.json()),
        fetch('api/get_vehicle_types.php').then(res => res.json())
    ]).then(([producerData, vehicleTypeData]) => {
        if (producerData.status !== 'success' || !producerData.data) {
             throw new Error(producerData.message || 'Could not load producer details.');
        }
        if (vehicleTypeData.status !== 'success') {
            throw new Error(vehicleTypeData.message || 'Could not load vehicle types.');
        }

        const producer = producerData.data;
        const vehicleTypes = vehicleTypeData.data;

        producerInfoContainer.innerHTML = `
            <div class="d-flex align-items-center mb-4">
                <img src="${producer.logo}" alt="${producer.name}" class="producer-logo-large rounded-circle me-3">
                <div>
                    <h2 class="fw-bold mb-0">Order from ${producer.name}</h2>
                    <p class="text-muted mb-0"><i class="bi bi-geo-alt-fill"></i> ${producer.location}</p>
                </div>
            </div>`;

        orderFormContainer.innerHTML = `
        <form id="orderForm">
            <input type="hidden" name="producer_id" value="${producer.producer_id}">
            
            <div class="mb-4">
                <label class="form-label fw-bold">1. Select Egg Type</label>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">${producer.products.map((p, index) => `
                    <div class="col form-check egg-option-card">
                        <input class="form-check-input" type="radio" name="product_details" id="product_${index}" value='${JSON.stringify({ type: p.type, price: p.price, stock: p.stock })}' ${p.stock <= 0 ? 'disabled' : ''} required>
                        <label class="form-check-label" for="product_${index}">
                            <span class="fw-bold">${p.type}</span><br>
                            <span class="text-success">₱${p.price.toFixed(2)}</span> / ${p.per}<br>
                            ${p.stock > 0 ? `<span class="text-muted small">Stock: ${p.stock} trays</span>` : '<span class="text-danger small">Out of Stock</span>'}
                        </label>
                    </div>`).join('') || '<div class="col-12"><div class="alert alert-info">This producer has no products available.</div></div>'}</div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="quantityInput" class="form-label fw-bold">2. Quantity of Trays</label>
                    <input type="number" class="form-control" id="quantityInput" name="quantity" min="1" value="1" required disabled>
                </div>
                 <div class="col-md-6">
                    <label for="tray-size" class="form-label fw-bold">3. Tray Size (eggs per tray)</label>
                    <select class="form-select" id="tray-size" name="tray_size">
                        <option value="30" selected>30 (Standard)</option>
                        <option value="12">12</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">4. Select Vehicle for Delivery</label>
                <div class="vehicle-grid">${vehicleTypes.map(vt => `
                    <div class="form-check vehicle-type-card">
                        <input class="form-check-input" type="radio" name="vehicle_details" id="vehicle_type_${vt.type.replace(/[^a-zA-Z0-9]/g, '_')}" value='${JSON.stringify({ type: vt.type, fee: vt.delivery_fee })}' required>
                        <label class="form-check-label" for="vehicle_type_${vt.type.replace(/[^a-zA-Z0-9]/g, '_')}">
                            <i class="bi ${getVehicleIcon(vt.type)} vehicle-icon"></i>
                            <div class="vehicle-details">
                                <span class="vehicle-type-name">${vt.type}</span>
                                <span class="vehicle-fee">₱${vt.delivery_fee.toFixed(2)}</span>
                                <span class="vehicle-capacity-range">Capacity: ${vt.min_capacity} - ${vt.max_capacity} trays</span>
                            </div>
                        </label>
                    </div>`).join('') || '<div class="alert alert-warning">No delivery options available at the moment.</div>'}</div>
            </div>

            <div class="mb-4">
                <label for="notesInput" class="form-label fw-bold">5. Notes (Optional)</label>
                <textarea class="form-control" id="notesInput" name="notes" rows="3" placeholder="Any special instructions for the producer or driver?"></textarea>
            </div>

            <button type="submit" class="btn btn-warning w-100 btn-lg mt-3">Add to Cart</button>
        </form>`;

        const quantityInput = document.getElementById('quantityInput');
        document.querySelectorAll('input[name="product_details"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if(this.checked) {
                    const selectedProduct = JSON.parse(this.value);
                    quantityInput.max = selectedProduct.stock;
                    quantityInput.disabled = false;
                    quantityInput.value = 1; 
                }
            });
        });

        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedProductRadio = document.querySelector('input[name="product_details"]:checked');
            const selectedVehicleRadio = document.querySelector('input[name="vehicle_details"]:checked');

            if (!selectedProductRadio) { alert('Please select an egg type.'); return; }
            if (vehicleTypes.length > 0 && !selectedVehicleRadio) { alert('Please select a delivery vehicle type.'); return; }
            
            const productDetails = JSON.parse(selectedProductRadio.value);
            const vehicleDetails = selectedVehicleRadio ? JSON.parse(selectedVehicleRadio.value) : { type: null, fee: 0 };
            const quantity = parseInt(document.getElementById('quantityInput').value);

            if (quantity > productDetails.stock) { alert('The order quantity exceeds the available stock.'); return; }

            const formData = new FormData(this);
            const cartData = {
                producer_id: parseInt(formData.get('producer_id')),
                product_type: productDetails.type,
                price: productDetails.price,
                quantity: quantity,
                tray_size: parseInt(formData.get('tray_size')),
                vehicle_type: vehicleDetails.type,
                delivery_fee: vehicleDetails.fee, // Pass the fee directly
                notes: formData.get('notes')
            };

            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', item: cartData })
            })
            .then(res => res.json())
            .then(data => {
                const toastBody = cartToastEl.querySelector('.toast-body');
                if (data.status === 'success') {
                    toastBody.textContent = data.message || 'Item added to cart!';
                    cartToast.show();
                    setTimeout(() => window.location.href = 'producers.php', 1500);
                } else {
                    toastBody.textContent = data.message || 'An unknown error occurred.';
                    cartToast.show();
                }
            }).catch(err => {
                cartToastEl.querySelector('.toast-body').textContent = 'Request failed: ' + err;
                cartToast.show();
            });
        });

    }).catch(error => {
        console.error("Error loading page content:", error);
        orderFormContainer.innerHTML = `<div class="alert alert-danger">There was a problem loading the order page. Please <a href="producers.php" class="alert-link">try again</a>.<br><small>${error.message}</small></div>`;
    });
});
  </script>
</body>
</html>
