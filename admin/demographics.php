<?php
include_once('../db_connect.php');
include_once('../session_handler.php');

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login page if not an admin
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Demographics</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin-styles.css"> <!-- Using shared admin styles -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <?php include('admin_header.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include('admin_sidebar.php'); ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Demographics</h1>
                </div>

                <!-- Transaction & Sales Overviews -->
                <div class="row g-4">
                    <!-- Transaction Counts -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Weekly Transactions</h5>
                                <p class="card-text h3" id="weekly-transactions">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Transactions</h5>
                                <p class="card-text h3" id="monthly-transactions">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Yearly Transactions</h5>
                                <p class="card-text h3" id="yearly-transactions">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Losses from Returns</h5>
                                <p class="card-text h3" id="return-losses">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Totals -->
                    <div class="col-lg-4 col-md-6 sales-total-card" data-timeframe="weekly">
                        <div class="card text-dark bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Weekly Sales</h5>
                                <p class="card-text h3" id="weekly-sales-total">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 sales-total-card" data-timeframe="monthly">
                        <div class="card text-dark bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Sales</h5>
                                <p class="card-text h3" id="monthly-sales-total">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 sales-total-card" data-timeframe="yearly">
                        <div class="card text-dark bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Yearly Sales</h5>
                                <p class="card-text h3" id="yearly-sales-total">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Charts -->
                <div class="mt-5">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="btn-group" role="group" aria-label="Sales Data Timeframe">
                            <button type="button" class="btn btn-secondary active" data-timeframe="weekly">Weekly</button>
                            <button type="button" class="btn btn-secondary" data-timeframe="monthly">Monthly</button>
                            <button type="button" class="btn btn-secondary" data-timeframe="yearly">Yearly</button>
                        </div>
                    </div>
                    <div class="chart-container" style="position: relative; height: 400px;">
                        <canvas id="sales-chart"></canvas>
                    </div>
                </div>

            </main>
        </div>
    </div>

   

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        let salesChart = null; // To hold the chart instance

        function renderChart(salesData, timeframe) {
            const ctx = document.getElementById('sales-chart').getContext('2d');
            let labels, data, label;

            if (timeframe === 'weekly') {
                labels = salesData.weekly.map(d => d.sale_date);
                data = salesData.weekly.map(d => d.total_sales);
                label = 'Weekly Sales (Last 7 Days)';
            } else if (timeframe === 'monthly') {
                labels = salesData.monthly.map(d => d.sale_week);
                data = salesData.monthly.map(d => d.total_sales);
                label = 'Monthly Sales (Last 4 Weeks)';
            } else if (timeframe === 'yearly') {
                labels = salesData.yearly.map(d => d.sale_month);
                data = salesData.yearly.map(d => d.total_sales);
                label = 'Yearly Sales (Last 12 Months)';
            }

            if (salesChart) {
                salesChart.destroy(); // Destroy previous chart instance
            }

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: 'rgba(0, 123, 255, 0.5)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 2,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return `PHP ${value.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function fetchDemographics() {
            fetch('../api/get_demographics_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    $('#weekly-transactions').text(data.weekly_transactions);
                    $('#monthly-transactions').text(data.monthly_transactions);
                    $('#yearly-transactions').text(data.yearly_transactions);
                    $('#return-losses').text(`PHP ${data.return_losses}`);

                    $('#weekly-sales-total').text(`PHP ${data.weekly_sales_total}`);
                    $('#monthly-sales-total').text(`PHP ${data.monthly_sales_total}`);
                    $('#yearly-sales-total').text(`PHP ${data.yearly_sales_total}`);

                    // Initial setup
                    $('.sales-total-card').hide();
                    $('.sales-total-card[data-timeframe="weekly"]').show();
                    renderChart(data.sales_data, 'weekly'); 

                    // Handle timeframe button clicks
                    $('.btn-group .btn').on('click', function() {
                        const timeframe = $(this).data('timeframe');
                        $(this).addClass('active').siblings().removeClass('active');
                        
                        $('.sales-total-card').hide();
                        $('.sales-total-card[data-timeframe="' + timeframe + '"]').show();
                        
                        renderChart(data.sales_data, timeframe);
                    });
                })
                .catch(error => {
                    console.error('Fetch error:', error.message);
                });
        }

        fetchDemographics();
    });
    </script>

</body>
</html>