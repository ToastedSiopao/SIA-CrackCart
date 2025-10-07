<?php
require_once 'session_handler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Coupons - CrackCart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="dashboard-styles.css" rel="stylesheet">
</head>
<body>
  <?php include("navbar.php"); ?>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <?php include("sidebar.php"); ?>
      <?php include("offcanvas_sidebar.php"); ?>

      <main class="col ps-md-2 pt-2">
        <div class="container" id="main-container">
          <h2 class="text-center mb-4">My Coupons</h2>
          <div id="coupons-container">
            <div class="text-center">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const couponsContainer = document.getElementById('coupons-container');

      fetch('api/get_my_coupons.php')
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            renderCoupons(data.data);
          } else {
            couponsContainer.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
          }
        })
        .catch(error => {
          couponsContainer.innerHTML = `<div class="alert alert-danger">Could not fetch coupons. Please try again later.</div>`;
        });

      function renderCoupons(coupons) {
        if (coupons.length === 0) {
          couponsContainer.innerHTML = '<div class="alert alert-info">You do not have any coupons at this time.</div>';
          return;
        }

        let couponsHtml = '<div class="row g-3">'
        coupons.forEach(coupon => {
          couponsHtml += `
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Coupon Code: ${coupon.coupon_code}</h5>
                  <p class="card-text">Discount: ₱${coupon.discount_value}</p>
                  <p class="card-text">Expiry Date: ${new Date(coupon.expiry_date).toLocaleDateString()}</p>
                </div>
              </div>
            </div>
          `;
        });
        couponsHtml += '</div>';
        couponsContainer.innerHTML = couponsHtml;
      }
    });
  </script>
</body>
</html>
