<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_name = $_SESSION['user_first_name'] ?? 'Guest';
?>
<nav class="navbar navbar-expand-lg shadow-sm px-3" style="background-color: #ffeb3b;">
    <div class="container-fluid">
      <button class="btn btn-dark d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <i class="bi bi-list"></i>
      </button>
      <a class="navbar-brand fw-bold" href="dashboard.php" style="color: #000;">CrackCart.</a>
      <div class="ms-auto d-flex align-items-center gap-4">
        <div class="dropdown">
          <a href="#" class="text-dark fs-5 position-relative" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count" style="display: none;"></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="notificationDropdown" id="notification-list" style="width: 320px; max-height: 400px; overflow-y: auto;">
          </ul>
        </div>
        <div class="dropdown">
          <a class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="me-2"><?php echo htmlspecialchars($user_name); ?></span>
            <i class="bi bi-person-circle fs-4"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="profilePage.php">Profile</a></li>
            <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
            <li><a class="dropdown-item" href="contact.php">Contact Us</a></li>
            <li><a class="dropdown-item" href="profilePage.php">Settings</a></li>
            <li><a class="dropdown-item" href="user_manual.php">User Manual</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
</nav>

<style>
  /* Notification Dropdown Styling */
  #notification-list {
    background-color: #fff;
    border-radius: 10px;
  }

  #notification-list li {
    border-bottom: 1px solid #eee;
  }

  #notification-list li:last-child {
    border-bottom: none;
  }

  #notification-list .dropdown-item {
    padding: 10px 15px;
    white-space: normal;
    line-height: 1.4;
    transition: background-color 0.2s ease-in-out;
  }

  #notification-list .dropdown-item:hover {
    background-color: #f7f7f7;
  }

  #notification-list .dropdown-item.fw-bold {
    background-color: #fff8e1;
  }

  #notification-list .dropdown-item div:first-child {
    font-size: 0.95rem;
    color: #333;
  }

  #notification-list .dropdown-item .text-muted {
    font-size: 0.8rem;
    color: #777 !important;
  }

  /* Scrollbar customization */
  #notification-list::-webkit-scrollbar {
    width: 6px;
  }
  #notification-list::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 4px;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationList = document.getElementById('notification-list');
    const notificationCount = document.getElementById('notification-count');

    function fetchNotifications() {
        fetch('api/get_notifications.php')
            .then(response => response.json())
            .then(data => {
                updateNotificationUI(data.notifications, data.unread_count);
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    function updateNotificationUI(notifications, unreadCount) {
        notificationList.innerHTML = '';

        if (notifications.length === 0) {
            notificationList.innerHTML = '<li><a class="dropdown-item text-muted text-center py-3">No new notifications</a></li>';
        }

        notifications.forEach(notification => {
            const item = document.createElement('li');
            const link = document.createElement('a');
            link.href = '#'; 
            link.className = `dropdown-item ${notification.IS_READ == 0 ? 'fw-bold' : ''}`;
            link.innerHTML = `
                <div>${notification.MESSAGE}</div>
                <div class="text-muted small mt-1">${new Date(notification.CREATED_AT).toLocaleString()}</div>
            `;
            link.onclick = (e) => {
                e.preventDefault();
                if (notification.IS_READ == 0) {
                    markAsRead(notification.NOTIFICATION_ID);
                }
            };
            item.appendChild(link);
            notificationList.appendChild(item);
        });

        if (unreadCount > 0) {
            notificationCount.textContent = unreadCount;
            notificationCount.style.display = 'block';
        } else {
            notificationCount.style.display = 'none';
        }
    }

    function markAsRead(notificationId) {
        fetch('api/mark_notification_read.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchNotifications();
            } 
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    fetchNotifications();
    setInterval(fetchNotifications, 30000);
});
</script>
