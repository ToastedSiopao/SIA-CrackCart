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
                    
                    <!-- Sales & Losses Totals -->
                    <div class="col-lg-4 col-md-6 time-based-card" data-timeframe="weekly">
                        <div class="card text-dark bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Weekly Sales</h5>
                                <p class="card-text h3" id="weekly-sales-total">-</p>
                            </div>
                        </div>
                    </div>
                     <div class="col-lg-4 col-md-6 time-based-card" data-timeframe="weekly">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Weekly Losses</h5>
                                <p class="card-text h3" id="weekly-losses-total">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 time-based-card" data-timeframe="monthly">
                        <div class="card text-dark bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Sales</h5>
                                <p class="card-text h3" id="monthly-sales-total">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 time-based-card" data-timeframe="monthly">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Losses</h5>
                                <p class="card-text h3" id="monthly-losses-total">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 time-based-card" data-timeframe="yearly">
                        <div class="card text-dark bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Yearly Sales</h5>
                                <p class="card-text h3" id="yearly-sales-total">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 time-based-card" data-timeframe="yearly">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Yearly Losses</h5>
                                <p class="card-text h3" id="yearly-losses-total">-</p>
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

                <!-- Top Performers -->
                <div class="row mt-5 time-based-card" data-timeframe="weekly">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Most Bought Item (Weekly)</h5>
                                <p class="card-text h4" id="weekly-top-item">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Top Producer (Weekly)</h5>
                                <p class="card-text h4" id="weekly-top-producer">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-5 time-based-card" data-timeframe="monthly">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Most Bought Item (Monthly)</h5>
                                <p class="card-text h4" id="monthly-top-item">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Top Producer (Monthly)</h5>
                                <p class="card-text h4" id="monthly-top-producer">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-5 time-based-card" data-timeframe="yearly">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Most Bought Item (Yearly)</h5>
                                <p class="card-text h4" id="yearly-top-item">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Top Producer (Yearly)</h5>
                                <p class="card-text h4" id="yearly-top-producer">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Off-canvas Sidebar -->
    <?php include('admin_offcanvas_sidebar.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        let salesChart = null; // To hold the chart instance

        function renderChart(salesData, lossesData, timeframe) {
            const ctx = document.getElementById('sales-chart').getContext('2d');
            let labels, sales, losses, salesLabel, lossesLabel;

            if (timeframe === 'weekly') {
                labels = salesData.weekly.map(d => d.sale_date);
                sales = salesData.weekly.map(d => d.total_sales);
                losses = lossesData.weekly.map(d => d.total_losses);
                salesLabel = 'Weekly Sales';
                lossesLabel = 'Weekly Losses';
            } else if (timeframe === 'monthly') {
                labels = salesData.monthly.map(d => d.sale_week);
                sales = salesData.monthly.map(d => d.total_sales);
                losses = lossesData.monthly.map(d => d.total_losses);
                salesLabel = 'Monthly Sales';
                lossesLabel = 'Monthly Losses';
            } else if (timeframe === 'yearly') {
                labels = salesData.yearly.map(d => d.sale_month);
                sales = salesData.yearly.map(d => d.total_sales);
                losses = lossesData.yearly.map(d => d.total_losses);
                salesLabel = 'Yearly Sales';
                lossesLabel = 'Yearly Losses';
            }

            if (salesChart) {
                salesChart.destroy(); // Destroy previous chart instance
            }

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: salesLabel,
                            data: sales,
                            backgroundColor: 'rgba(40, 167, 69, 0.5)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            yAxisID: 'y-sales'
                        },
                        {
                            label: lossesLabel,
                            data: losses,
                            backgroundColor: 'rgba(220, 53, 69, 0.5)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            yAxisID: 'y-losses'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        'y-sales': {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return `PHP ${value.toLocaleString()}`;
                                }
                            }
                        },
                        'y-losses': {
                            type: 'linear',
                            position: 'right',
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
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    $('#weekly-transactions').text(data.weekly_transactions);
                    $('#monthly-transactions').text(data.monthly_transactions);
                    $('#yearly-transactions').text(data.yearly_transactions);
                    
                    $('#weekly-sales-total').text(`PHP ${data.weekly_sales_total}`);
                    $('#monthly-sales-total').text(`PHP ${data.monthly_sales_total}`);
                    $('#yearly-sales-total').text(`PHP ${data.yearly_sales_total}`);

                    $('#weekly-losses-total').text(`PHP ${data.weekly_losses_total}`);
                    $('#monthly-losses-total').text(`PHP ${data.monthly_losses_total}`);
                    $('#yearly-losses-total').text(`PHP ${data.yearly_losses_total}`);

                    // Update top performers
                    $('#weekly-top-item').text(`${data.top_performers.weekly.item.product_type} (${data.top_performers.weekly.item.total_quantity} units)`);
                    $('#weekly-top-producer').text(`${data.top_performers.weekly.producer.NAME} (${data.top_performers.weekly.producer.total_quantity} units)`);
                    $('#monthly-top-item').text(`${data.top_performers.monthly.item.product_type} (${data.top_performers.monthly.item.total_quantity} units)`);
                    $('#monthly-top-producer').text(`${data.top_performers.monthly.producer.NAME} (${data.top_performers.monthly.producer.total_quantity} units)`);
                    $('#yearly-top-item').text(`${data.top_performers.yearly.item.product_type} (${data.top_performers.yearly.item.total_quantity} units)`);
                    $('#yearly-top-producer').text(`${data.top_performers.yearly.producer.NAME} (${data.top_performers.yearly.producer.total_quantity} units)`);

                    // Initial setup
                    $('.time-based-card').hide();
                    $('.time-based-card[data-timeframe="weekly"]').show();
                    renderChart(data.sales_data, data.losses_data, 'weekly'); 

                    // Handle timeframe button clicks
                    $('.btn-group .btn').on('click', function() {
                        const timeframe = $(this).data('timeframe');
                        $(this).addClass('active').siblings().removeClass('active');
                        
                        $('.time-based-card').hide();
                        $('.time-based-card[data-timeframe="' + timeframe + '"]').show();
                        
                        renderChart(data.sales_data, data.losses_data, timeframe);
                    });
                })
                .catch(error => console.error('Fetch error:', error));
        }

        fetchDemographics();
    });
    </script>

</body>
</html>