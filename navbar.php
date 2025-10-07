<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">CrackCart</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="my_orders.php">My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact Us</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                 <?php if (isset($_SESSION['user_id'])): ?>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge rounded-pill bg-danger" id="notification-count" style="display: none;"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="notification-list" aria-labelledby="notificationDropdown">
                            <!-- Notifications will be loaded here -->
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">Sign Up</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count" class="badge bg-danger">0</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
.notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #eee;
}
.notification-item:last-child {
    border-bottom: none;
}
.notification-item.unread {
    background-color: #f8f9fa;
}
.notification-title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}
.notification-message {
    font-size: 0.85rem;
    color: #6c757d;
}
.notification-time {
    font-size: 0.75rem;
    color: #adb5bd;
    margin-top: 0.5rem;
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

    function markAsRead(notificationId) {
        fetch('api/mark_notification_read.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(() => fetchNotifications());
    }

    function updateNotificationUI(notifications, unreadCount) {
        notificationList.innerHTML = ''; 

        if (notifications.length === 0) {
            notificationList.innerHTML = '<li><a class="dropdown-item text-muted">No notifications</a></li>';
        } else {
            notifications.forEach(notification => {
                const item = document.createElement('li');
                item.className = `notification-item ${notification.IS_READ == 0 ? 'unread' : ''}`;

                const link = document.createElement('a');
                link.href = notification.link || '#';
                link.className = 'text-decoration-none text-dark';
                link.onclick = (e) => {
                    if (notification.IS_READ == 0) {
                        e.preventDefault();
                        markAsRead(notification.NOTIFICATION_ID);
                        window.location.href = link.href;
                    }
                };

                const titleText = notification.MESSAGE.includes("return request") ? "Return Request Update" : "New Notification";

                link.innerHTML = `
                    <div class="notification-title">${titleText}</div>
                    <div class="notification-message">${notification.MESSAGE}</div>
                    <div class="notification-time">${new Date(notification.CREATED_AT).toLocaleString()}</div>
                `;
                
                item.appendChild(link);
                notificationList.appendChild(item);
            });
        }

        if (unreadCount > 0) {
            notificationCount.textContent = unreadCount;
            notificationCount.style.display = 'inline-block';
        } else {
            notificationCount.style.display = 'none';
        }
    }

    fetchNotifications();
    setInterval(fetchNotifications, 60000); 
});
</script>
