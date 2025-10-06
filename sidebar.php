 <div class="col-auto col-md-3 col-lg-2 px-3 sidebar d-none d-md-block">
        <ul class="nav flex-column mb-auto mt-4">
          <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li><a href="producers.php" class="nav-link"><i class="bi bi-egg-fill me-2"></i>Make an Order</a></li>
          <li><a href="product_checkout.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Cart</a></li>
          <li><a href="my_orders.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> My Orders</a></li>
          <li><a href="eggspress.php" class="nav-link"><i class="bi bi-truck me-2"></i> Eggspress</a></li>
          <li><a href="settings.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
          <li><a href="terms.php" class="nav-link" target="_blank"><i class="bi bi-file-text me-2"></i> Terms & Privacy</a></li>

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
                if (link.getAttribute("href") === currentPage) {
                    link.classList.add("active");
                }
            });
        });
      </script>