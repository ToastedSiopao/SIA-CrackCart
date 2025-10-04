<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Addresses - CrackCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="dashboard-styles.css?v=2.5" rel="stylesheet">
</head>
<body>
    <?php include("navbar.php"); ?>

    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include("sidebar.php"); ?>
            <?php include("offcanvas_sidebar.php"); ?>

            <main class="col ps-md-2 pt-2">
                <div class="container">
                    <h2 class="text-center mb-4">Manage Addresses</h2>

                    <!-- Add/Edit Address Form -->
                    <div class="card shadow-sm border-0 p-4 mb-4">
                        <h4 id="form-title">Add New Address</h4>
                        <form id="address-form">
                            <input type="hidden" id="address-id">
                            <div class="mb-3">
                                <label for="street" class="form-label">Street</label>
                                <input type="text" class="form-control" id="street" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="state" class="form-label">State / Province</label>
                                    <input type="text" class="form-control" id="state" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="zip_code" class="form-label">Zip / Postal Code</label>
                                    <input type="text" class="form-control" id="zip_code" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Address</button>
                            <button type="button" class="btn btn-secondary" id="cancel-edit" style="display: none;">Cancel Edit</button>
                        </form>
                    </div>

                    <!-- Address List -->
                    <h4>Your Saved Addresses</h4>
                    <div id="address-list" class="row g-4">
                        <!-- Addresses will be loaded here -->
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addressList = document.getElementById('address-list');
            const addressForm = document.getElementById('address-form');
            const formTitle = document.getElementById('form-title');
            const addressIdInput = document.getElementById('address-id');
            const streetInput = document.getElementById('street');
            const cityInput = document.getElementById('city');
            const stateInput = document.getElementById('state');
            const zipCodeInput = document.getElementById('zip_code');
            const countryInput = document.getElementById('country');
            const cancelEditBtn = document.getElementById('cancel-edit');

            const fetchAddresses = async () => {
                const response = await fetch('api/addresses.php');
                const result = await response.json();

                if (result.status === 'success') {
                    addressList.innerHTML = '';
                    result.data.forEach(addr => {
                        const card = document.createElement('div');
                        card.className = 'col-md-6';
                        card.innerHTML = `
                            <div class="card">
                                <div class="card-body">
                                    <p>
                                        ${addr.street}, ${addr.city}, ${addr.state}<br>
                                        ${addr.zip_code}, ${addr.country}
                                    </p>
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${addr.address_id}">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${addr.address_id}">Delete</button>
                                </div>
                            </div>
                        `;
                        addressList.appendChild(card);
                    });
                }
            };

            addressForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const addressId = addressIdInput.value;
                const isEditing = !!addressId;

                const addressData = {
                    street: streetInput.value,
                    city: cityInput.value,
                    state: stateInput.value,
                    zip_code: zipCodeInput.value,
                    country: countryInput.value
                };

                let url = 'api/addresses.php';
                let method = 'POST';

                if (isEditing) {
                    addressData.address_id = addressId;
                    method = 'PUT';
                }

                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(addressData)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    resetForm();
                    fetchAddresses();
                }
            });

            addressList.addEventListener('click', async (e) => {
                if (e.target.classList.contains('edit-btn')) {
                    const addressId = e.target.dataset.id;
                    const address = await getAddressById(addressId);
                    if (address) {
                        formTitle.textContent = 'Edit Address';
                        addressIdInput.value = address.address_id;
                        streetInput.value = address.street;
                        cityInput.value = address.city;
                        stateInput.value = address.state;
                        zipCodeInput.value = address.zip_code;
                        countryInput.value = address.country;
                        cancelEditBtn.style.display = 'inline-block';
                        window.scrollTo(0, 0);
                    }
                } else if (e.target.classList.contains('delete-btn')) {
                    const addressId = e.target.dataset.id;
                    if (confirm('Are you sure you want to delete this address?')) {
                        const response = await fetch('api/addresses.php', {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ address_id: addressId })
                        });
                        const result = await response.json();
                        if (result.status === 'success') {
                            fetchAddresses();
                        }
                    }
                }
            });
            
            const getAddressById = async (id) => {
                 const response = await fetch('api/addresses.php');
                 const result = await response.json();
                 return result.data.find(addr => addr.address_id == id);
            }

            const resetForm = () => {
                formTitle.textContent = 'Add New Address';
                addressForm.reset();
                addressIdInput.value = '';
                cancelEditBtn.style.display = 'none';
            };
            
            cancelEditBtn.addEventListener('click', resetForm);

            fetchAddresses();
        });
    </script>
</body>
</html>