<?php
require_once 'session_handler.php';

$user_id = $_SESSION['user_id'];

include("db_connect.php");

// Fetch existing addresses
$addresses = [];
$stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Addresses - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css?v=2.5" rel="stylesheet">
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <div class="col p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="settings.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Settings</a>
            <h3 class="mb-0 text-warning fw-bold">Manage Addresses</h3>
        </div>

        <!-- Add New Address Button -->
        <div class="mb-4">
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addressModal" data-address-id="">
                Add New Address
            </button>
        </div>

        <!-- Existing Addresses -->
        <div class="card">
          <div class="card-header fw-bold">Your Addresses</div>
          <div class="card-body">
            <?php if (empty($addresses)): ?>
              <p>You have no saved addresses.</p>
            <?php else: ?>
              <div class="list-group">
                <?php foreach ($addresses as $address): ?>
                  <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                      <h5 class="mb-1"><?= htmlspecialchars($address['address_type']) ?></h5>
                      <div>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addressModal" data-address-id="<?= $address['address_id'] ?>"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                      </div>
                    </div>
                    <p class="mb-1">
                      <?= htmlspecialchars($address['address_line1']) ?><br>
                      <?php if(!empty($address['address_line2'])) echo htmlspecialchars($address['address_line2']) . "<br>"; ?>
                      <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> <?= htmlspecialchars($address['zip_code']) ?><br>
                      <?= htmlspecialchars($address['country']) ?>
                    </p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addressModalLabel">Add/Edit Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php include('psgc_address.php'); ?>
      </div>
    </div>
  </div>
</div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const addressModal = document.getElementById('addressModal');
        addressModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const addressId = button.getAttribute('data-address-id');
            const modalTitle = addressModal.querySelector('.modal-title');
            const addressIdInput = addressModal.querySelector('#address_id');
            const psgcForm = document.getElementById('psgc-address-form');

            if (addressId) {
                modalTitle.textContent = 'Edit Address';
                addressIdInput.value = addressId;
                
                // Fetch address details and populate the form
                fetch(`api/get_address.php?id=${addressId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            // This is a simplified population logic.
                            // A more robust solution would involve waiting for the PSGC dropdowns to be populated
                            // and then setting the values.
                            document.getElementById('street').value = data.street;
                            document.getElementById('zipcode').value = data.zip_code;
                            
                            // We will pre-select the region, province, city and barangay
                            const regionDropdown = document.getElementById('region');
                            const provinceDropdown = document.getElementById('province');
                            const cityDropdown = document.getElementById('city');
                            const barangayDropdown = document.getElementById('barangay');

                            // We will trigger the change events to populate the dropdowns
                            regionDropdown.value = data.region_code;
                            regionDropdown.dispatchEvent(new Event('change'));

                            // We need to wait for the provinces to be populated before setting the value
                            setTimeout(() => {
                                provinceDropdown.value = data.province_code;
                                provinceDropdown.dispatchEvent(new Event('change'));
                            }, 500);

                            // We need to wait for the cities to be populated before setting the value
                            setTimeout(() => {
                                cityDropdown.value = data.city_code;
                                cityDropdown.dispatchEvent(new Event('change'));
                            }, 1000);

                            // We need to wait for the barangays to be populated before setting the value
                            setTimeout(() => {
                                barangayDropdown.value = data.barangay_code;
                            }, 1500);

                        }
                    });

            } else {
                modalTitle.textContent = 'Add New Address';
                addressIdInput.value = '';
                psgcForm.reset();
            }
        });

        const psgcForm = document.getElementById('psgc-address-form');
        psgcForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(psgcForm);
            const addressId = formData.get('address_id');
            const url = addressId ? 'api/edit_address.php' : 'api/add_address.php';

            // Add text values of dropdowns to formData
            const regionDropdown = document.getElementById('region');
            const provinceDropdown = document.getElementById('province');
            const cityDropdown = document.getElementById('city');
            const barangayDropdown = document.getElementById('barangay');

            formData.append('region_text', regionDropdown.options[regionDropdown.selectedIndex].text);
            formData.append('province_text', provinceDropdown.options[provinceDropdown.selectedIndex].text);
            formData.append('city_text', cityDropdown.options[cityDropdown.selectedIndex].text);
            formData.append('barangay_text', barangayDropdown.options[barangayDropdown.selectedIndex].text);
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Simple reload for now
                } else {
                    alert('Error saving address: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            });
        });
    });
  </script>
</body>
</html>
