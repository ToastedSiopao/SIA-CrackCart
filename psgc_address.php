<?php
// This file will contain the form for PSGC-based address input.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Address Form</title>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const regionDropdown = document.getElementById('region');
            const provinceDropdown = document.getElementById('province');
            const cityDropdown = document.getElementById('city');
            const barangayDropdown = document.getElementById('barangay');

            // Function to fetch and populate regions
            function populateRegions() {
                fetch('https://psgc.gitlab.io/api/regions/')
                    .then(response => response.json())
                    .then(data => {
                        data.sort((a, b) => a.name.localeCompare(b.name)); // Sort alphabetically
                        data.forEach(region => {
                            const option = document.createElement('option');
                            option.value = region.code;
                            option.textContent = region.name;
                            regionDropdown.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching regions:', error));
            }

            // Function to populate provinces based on region
            function populateProvinces(regionCode) {
                provinceDropdown.innerHTML = '<option value="">Select Province</option>';
                cityDropdown.innerHTML = '<option value="">Select City/Municipality</option>';
                barangayDropdown.innerHTML = '<option value="">Select Barangay</option>';
                if (regionCode) {
                    fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`)
                        .then(response => response.json())
                        .then(data => {
                            data.sort((a, b) => a.name.localeCompare(b.name)); // Sort alphabetically
                            data.forEach(province => {
                                const option = document.createElement('option');
                                option.value = province.code;
                                option.textContent = province.name;
                                provinceDropdown.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error fetching provinces:', error));
                }
            }

            // Function to populate cities/municipalities based on province
            function populateCities(provinceCode) {
                cityDropdown.innerHTML = '<option value="">Select City/Municipality</option>';
                barangayDropdown.innerHTML = '<option value="">Select Barangay</option>';
                if (provinceCode) {
                    fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`)
                        .then(response => response.json())
                        .then(data => {
                            data.sort((a, b) => a.name.localeCompare(b.name)); // Sort alphabetically
                            data.forEach(city => {
                                const option = document.createElement('option');
                                option.value = city.code;
                                option.textContent = city.name;
                                cityDropdown.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error fetching cities/municipalities:', error));
                }
            }

            // Function to populate barangays based on city/municipality
            function populateBarangays(cityCode) {
                barangayDropdown.innerHTML = '<option value="">Select Barangay</option>';
                if (cityCode) {
                    fetch(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`)
                        .then(response => response.json())
                        .then(data => {
                            data.sort((a, b) => a.name.localeCompare(b.name)); // Sort alphabetically
                            data.forEach(barangay => {
                                const option = document.createElement('option');
                                option.value = barangay.code;
                                option.textContent = barangay.name;
                                barangayDropdown.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error fetching barangays:', error));
                }
            }

            // Event listeners for dropdown changes
            regionDropdown.addEventListener('change', () => {
                populateProvinces(regionDropdown.value);
            });

            provinceDropdown.addEventListener('change', () => {
                populateCities(provinceDropdown.value);
            });

            cityDropdown.addEventListener('change', () => {
                populateBarangays(cityDropdown.value);
            });

            // Initial population of regions
            populateRegions();
        });
    </script>
</head>
<body>
    <form id="psgc-address-form">
        <div class="form-group">
            <label for="region">Region</label>
            <select id="region" name="region" class="form-control" required>
                <option value="">Select Region</option>
            </select>
        </div>
        <div class="form-group">
            <label for="province">Province</label>
            <select id="province" name="province" class="form-control" required>
                <option value="">Select Province</option>
            </select>
        </div>
        <div class="form-group">
            <label for="city">City/Municipality</label>
            <select id="city" name="city" class="form-control" required>
                <option value="">Select City/Municipality</option>
            </select>
        </div>
        <div class="form-group">
            <label for="barangay">Barangay</label>
            <select id="barangay" name="barangay" class="form-control" required>
                <option value="">Select Barangay</option>
            </select>
        </div>
        <div class="form-group">
            <label for="street">Street Address</label>
            <input type="text" id="street" name="street" class="form-control" placeholder="Street, Building, House No." required>
        </div>
        <div class="form-group">
            <label for="zipcode">ZIP Code</label>
            <input type="text" id="zipcode" name="zipcode" class="form-control">
        </div>
        <input type="hidden" name="address_id" id="address_id">
        <button type="submit" class="btn btn-primary">Save Address</button>
    </form>
</body>
</html>
