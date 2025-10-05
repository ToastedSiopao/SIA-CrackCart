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
  <title>Order from Producer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="order-styles.css?v=1.2" rel="stylesheet"> <!-- version bumped -->
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <div class="col p-4">
        <div id="producer-info"></div>
        <div id="order-form-container"></div>
      </div>
    </div>
  </div>
  
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="cartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header"><strong class="me-auto">CrackCart</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>
      <div class="toast-body">Item added to cart successfully!</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const producerId = new URLSearchParams(window.location.search).get('producer_id');
    const producerInfoContainer = document.getElementById('producer-info');
    const orderFormContainer = document.getElementById('order-form-container');
    const cartToast = new bootstrap.Toast(document.getElementById('cartToast'));

    const getVehicleIcon = (type) => {
        switch (type.toLowerCase()) {
            case 'motorcycle': return 'bi-bicycle';
            case 'car': return 'bi-car-front-fill';
            case 'truck': return 'bi-truck';
            default: return 'bi-question-circle';
        }
    };

    Promise.all([
        fetch(`api/producers.php?producer_id=${producerId}`).then(res => res.json()),
        fetch('api/get_vehicles.php').then(res => res.json())
    ]).then(([producerData, vehicleData]) => {
        if (producerData.status !== 'success' || vehicleData.status !== 'success') {
            throw new Error('Failed to load page data.');
        }

        const producer = producerData.data;
        const vehicles = vehicleData.data;

        // 1. Display Producer Info
        producerInfoContainer.innerHTML = `
            <div class="producer-header">
                <img src="${producer.logo}" alt="${producer.name}" class="producer-logo-large">
                <div><h2 class="fw-bold">${producer.name}</h2><p class="text-muted">${producer.location}</p></div>
            </div>`;

        // 2. Build the main form structure
        orderFormContainer.innerHTML = `
        <form id="orderForm" class="mt-4">
            <input type="hidden" name="producer_id" value="${producer.producer_id}">
            
            <div class="mb-4">
                <label class="form-label fw-bold">1. Select Egg Size</label>
                <div class="d-flex flex-wrap gap-2">${producer.products.map(p => `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="egg_size" id="size_${p.type.toLowerCase()}" value='${JSON.stringify({ type: p.type, price: p.price, stock: p.stock })}' ${p.stock <= 0 ? 'disabled' : ''} required>
                        <label class="form-check-label" for="size_${p.type.toLowerCase()}">
                            ${p.type} <span class="text-muted">(₱${p.price.toFixed(2)})</span>
                            ${p.stock > 0 ? `<span class="text-success">- In Stock: ${p.stock}</span>` : '<span class="text-danger">- Out of Stock</span>'}
                        </label>
                    </div>`).join('')}</div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="tray-size" class="form-label fw-bold">2. Select Tray Size</label>
                    <select class="form-select" id="tray-size" name="tray_size">
                        <option value="12">12 pcs</option>
                        <option value="24">24 pcs</option>
                        <option value="30" selected>30 pcs (Standard)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="quantityInput" class="form-label fw-bold">3. Quantity of Trays</label>
                    <input type="number" class="form-control" id="quantityInput" name="quantity" min="1" value="1" required disabled>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">4. Select Vehicle for Delivery</label>
                <div id="vehicle-options-container" class="vehicle-grid"></div>
            </div>

            <div class="mb-4">
                <label for="notesInput" class="form-label fw-bold">5. Special Instructions (Optional)</label>
                <textarea class="form-control" id="notesInput" name="notes" rows="3" placeholder="e.g., Please handle with care."></textarea>
            </div>

            <button type="submit" class="btn btn-warning w-100 btn-lg mt-3">Add to Cart</button>
        </form>`;

        // 3. Populate Vehicle Options
        document.getElementById('vehicle-options-container').innerHTML = vehicles.map(v => `
            <div class="form-check vehicle-option-card">
                <input class="form-check-input" type="radio" name="vehicle_id" id="vehicle_${v.vehicle_id}" value="${v.vehicle_id}" required>
                <label class="form-check-label" for="vehicle_${v.vehicle_id}">
                    <i class="bi ${getVehicleIcon(v.type)} vehicle-icon"></i>
                    <div class="vehicle-details">
                        <span class="vehicle-type">${v.type} (${v.plate_no})</span>
                        <span class="vehicle-capacity">Capacity: ${v.capacity_trays} trays</span>
                    </div>
                </label>
            </div>`).join('');

        // 4. Add Event Listeners
        const quantityInput = document.getElementById('quantityInput');
        document.querySelectorAll('input[name="egg_size"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if(this.checked) {
                    const selectedProduct = JSON.parse(this.value);
                    quantityInput.max = selectedProduct.stock;
                    quantityInput.disabled = false;
                }
            });
        });

        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedEggSizeRadio = document.querySelector('input[name="egg_size"]:checked');
            const selectedVehicleRadio = document.querySelector('input[name="vehicle_id"]:checked');

            if (!selectedEggSizeRadio) { alert('Please select an egg size.'); return; }
            if (!selectedVehicleRadio) { alert('Please select a vehicle.'); return; } 
            
            const selectedProduct = JSON.parse(selectedEggSizeRadio.value);
            const quantity = parseInt(document.getElementById('quantityInput').value);

            if (quantity > selectedProduct.stock) { alert('You cannot order more than the available stock.'); return; }

            const formData = new FormData(this);
            const cartData = {
                producer_id: parseInt(formData.get('producer_id')),
                product_type: selectedProduct.type,
                price: selectedProduct.price,
                quantity: quantity,
                tray_size: parseInt(formData.get('tray_size')),
                vehicle_id: parseInt(formData.get('vehicle_id')), // Now sends vehicle_id
                notes: formData.get('notes')
            };

            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cartData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    cartToast.show();
                    setTimeout(() => window.location.href = 'producers.php', 2000);
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

    }).catch(error => {
        console.error("Error loading page:", error);
        orderFormContainer.innerHTML = `<div class="alert alert-danger">Could not load order details. Please <a href="producers.php">go back</a> and try again.</div>`;
    });
});
  </script>
</body>
</html>