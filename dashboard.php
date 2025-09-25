<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// User info from session
$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

// Connect DB
include("db_connect.php");

// Dummy data for demonstration
$total_orders = 12;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="dashboard-styles.css" rel="stylesheet">
</head>
<body>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <!-- Sidebar -->
      <div class="col-auto col-md-3 col-lg-2 px-sm-2 px-0 sidebar">
        <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
            <a href="/" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-5 d-none d-sm-inline">CrackCart.</span>
            </a>
            <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                <li><a href="dashboard.php" class="nav-link px-0 align-middle active"><i class="fs-4 bi-speedometer2"></i> <span class="ms-1 d-none d-sm-inline">Dashboard</span></a></li>
                <li><a href="order.php" class="nav-link px-0 align-middle"><i class="fs-4 bi-cart3"></i> <span class="ms-1 d-none d-sm-inline">Make an Order</span></a></li>
                <li><a href="eggspress.php" class="nav-link px-0 align-middle"><i class="fs-4 bi-truck"></i> <span class="ms-1 d-none d-sm-inline">Eggspress</span></a></li>
                <li><a href="#" class="nav-link px-0 align-middle"><i class="fs-4 bi-chat-dots"></i> <span class="ms-1 d-none d-sm-inline">Messages</span></a></li>
                <li><a href="#" class="nav-link px-0 align-middle"><i class="fs-4 bi-clock-history"></i> <span class="ms-1 d-none d-sm-inline">Order History</span></a></li>
                <li><a href="#" class="nav-link px-0 align-middle"><i class="fs-4 bi-receipt"></i> <span class="ms-1 d-none d-sm-inline">Bills</span></a></li>
                 <li><a href="producers.php" class="nav-link px-0 align-middle"><i class="fs-4 bi-egg"></i> <span class="ms-1 d-none d-sm-inline">Producers</span></a></li>

            </ul>
            <div class="upgrade-box">
              <p>Upgrade your Account to Get Free Voucher</p>
              <button class="btn btn-sm">Upgrade</button>
            </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col py-3">
          <!-- Top Navbar -->
          <nav class="navbar navbar-expand-lg shadow-sm px-3 mb-4">
            <div class="container-fluid">
              <!-- Sidebar toggle (mobile only) -->
              <button class="btn btn-outline-dark d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
                <i class="bi bi-list"></i>
              </button>

              <!-- Brand -->
              <a class="navbar-brand fw-bold" href="#">Dashboard</a>

              <!-- Right side -->
              <div class="ms-auto d-flex align-items-center gap-4">
                <!-- Notification Bell -->
                <div class="dropdown">
                  <a href="#" class="text-dark fs-5 dropdown-toggle" id="notificationBell" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationBell" id="notification-list">
                    <!-- Notifications will be loaded here -->
                  </ul>
                </div>

                <!-- Username + Profile -->
                <div class="dropdown">
                  <a class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2 d-none d-sm-inline"><?php echo htmlspecialchars($user_name); ?></span>
                    <i class="bi bi-person-circle fs-4"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profilePage.php">Profile</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </nav>

        <div class="px-4">
            <div class="welcome-card mb-4">
                <h6 class="text-secondary">Overview</h6>
                <h4 class="mb-0">Welcome back, <?php echo htmlspecialchars($user_name); ?> 👋</h4>
            </div>

          <div class="row g-4">
            <div class="col-md-6 col-lg-3">
              <div class="category-card">
                <i class="bi bi-cart3"></i>
                <p class="mb-1">Orders</p>
                <h5><?php echo $total_orders; ?></h5>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="category-card">
                <i class="bi bi-chat-dots"></i>
                <p class="mb-1">Messages</p>
                <h5>0</h5>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="category-card">
                <i class="bi bi-clock-history"></i>
                <p class="mb-1">Order History</p>
                <h5><?php echo $total_orders; ?></h5>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="category-card active">
                <i class="bi bi-receipt"></i>
                <p class="mb-1">Bills</p>
                <h5>0</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Offcanvas Sidebar for Mobile -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title text-white">CrackCart.</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="nav flex-column mb-auto">
        <li><a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li><a href="order.php" class="nav-link"><i class="bi bi-cart3 me-2"></i> Order</a></li>
        <li><a href="eggspress.php" class="nav-link"><i class="bi bi-truck me-2"></i> Eggspress</a></li>
        <li><a href="#" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
        <li><a href="#" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
        <li><a href="#" class="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
        <li><a href="profilePage.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
      </ul>
      <div class="upgrade-box">
        <p>Upgrade your Account to Get Free Voucher</p>
        <button class="btn btn-sm">Upgrade</button>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const notificationBell = document.getElementById('notificationBell');
      const notificationCount = document.getElementById('notification-count');
      const notificationList = document.getElementById('notification-list');

      function fetchNotifications() {
        fetch('notifications.php')
          .then(response => response.json())
          .then(data => {
            if (data.error) {
              console.error(data.error);
              return;
            }

            updateNotificationUI(data);
          });
      }

      function updateNotificationUI(notifications) {
        notificationList.innerHTML = '';
        if (notifications.length > 0) {
          notificationCount.textContent = notifications.length;
          notificationCount.style.display = 'block';

          notifications.forEach(notification => {
            const item = document.createElement('li');
            item.innerHTML = `
              <a class="dropdown-item notification-item" href="#" data-id="${notification.NOTIFICATION_ID}">
                ${notification.MESSAGE}
                <div class="time">${new Date(notification.CREATED_AT).toLocaleString()}</div>
              </a>
            `;
            notificationList.appendChild(item);
          });
        } else {
          notificationCount.style.display = 'none';
          notificationList.innerHTML = '<li><a class="dropdown-item" href="#">No new notifications</a></li>';
        }
      }

      notificationBell.addEventListener('click', function() {
        // Optional: Mark as read when dropdown is opened
      });

      notificationList.addEventListener('click', function(e) {
        const target = e.target.closest('.notification-item');
        if (target) {
          const notificationId = target.dataset.id;
          fetch(`notifications.php?mark_as_read=${notificationId}`)
            .then(() => fetchNotifications()); // Refresh after marking as read
        }
      });

      // Fetch notifications every 30 seconds
      fetchNotifications();
      setInterval(fetchNotifications, 30000);
    });
  </script>
</body>
</html>
