 <div class="col-auto col-md-3 col-lg-2 px-3 sidebar d-none d-md-block">
        <ul class="nav flex-column mb-auto mt-4">
          <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li><a href="producers.php" class="nav-link"><i class="bi bi-egg-fill me-2"></i>Make an Order</a></li>
          <li><a href="product_checkout.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Cart</a></li>
          <li>
            <a href="my_orders.php" class="nav-link d-flex justify-content-between align-items-center">
                <span><i class="bi bi-box-seam me-2"></i> My Orders</span>
                <span id="order-incident-alert" class="badge bg-danger rounded-pill" style="display: none;">!</span>
            </a>
          </li>
          <li><a href="my_coupons.php" class="nav-link"><i class="bi bi-ticket-fill me-2"></i> My Coupons</a></li>
          <li><a href="settings.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
          <li><a href="terms.php" class="nav-link" target="_blank"><i class="bi bi-file-text me-2"></i> Terms & Privacy</a></li>
          <li><a href="return-policy.php" class="nav-link"><i class="bi bi-box-arrow-in-left me-2"></i> Return Policy</a></li>

        </ul>
        <div class="upgrade-box">
          <p>Upgrade your Account to Get Free Voucher</p>
          <button class="btn btn-light btn-sm">Upgrade</button>
        </div>
      </div>
      <script>
        document.addEventListener("DOMContentLoaded", function() {
            const links = document.querySelectorAll(".sidebar .nav-link");
            const currentPage = window.location.pathname.split("/").pop();

            links.forEach(link => {
                const linkPage = link.getAttribute("href").split("/").pop();
                if (linkPage === currentPage) {
                    link.classList.add("active");
                }
            });
            
            const checkIncidents = async () => {
                try {
                    const response = await fetch('api/check_user_incidents.php');
                    const result = await response.json();
                    if (result.status === 'success' && result.has_incident) {
                        const alertIcon = document.getElementById('order-incident-alert');
                        if(alertIcon) alertIcon.style.display = 'inline';
                    }
                } catch (error) {
                    console.error('Could not check for incidents:', error);
                }
            };
            checkIncidents();
        });
      </script>