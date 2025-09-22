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
    <title>Producers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navbar and Sidebar -->
    
    <div class="container py-5">
        <h1 class="mb-4">Find an Egg Producer</h1>
        
        <!-- Search and Filter Form -->
        <div class="card card-body mb-4">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" id="keywordSearch" placeholder="Search by keyword (e.g., farm name, egg type)">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="locationSearch" placeholder="Filter by location (e.g., city, province)">
                </div>
            </div>
        </div>

        <!-- Producer Listing -->
        <div id="producerList" class="row g-4">
            <!-- Producers will be loaded here dynamically -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const keywordInput = document.getElementById("keywordSearch");
            const locationInput = document.getElementById("locationSearch");
            const producerList = document.getElementById("producerList");

            function fetchProducers() {
                const keyword = keywordInput.value;
                const location = locationInput.value;

                fetch(`get_producers.php?keyword=${keyword}&location=${location}`)
                    .then(response => response.json())
                    .then(data => {
                        producerList.innerHTML = ''; // Clear existing list
                        if (data.length > 0) {
                            data.forEach(producer => {
                                const producerCard = `
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">${producer.name}</h5>
                                                <h6 class="card-subtitle mb-2 text-muted">${producer.location}</h6>
                                                <p class="card-text">${producer.description}</p>
                                                <a href="#" class="btn btn-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                producerList.innerHTML += producerCard;
                            });
                        } else {
                            producerList.innerHTML = '<p class="text-center">No producers found.</p>';
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching producers:", error);
                        producerList.innerHTML = '<p class="text-center text-danger">An error occurred while fetching data.</p>';
                    });
            }

            // Initial fetch
            fetchProducers();

            // Event listeners for real-time filtering
            keywordInput.addEventListener("keyup", fetchProducers);
            locationInput.addEventListener("keyup", fetchProducers);
        });
    </script>
</body>
</html>
