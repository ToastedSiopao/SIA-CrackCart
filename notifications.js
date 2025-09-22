document.addEventListener('DOMContentLoaded', function() {
    const notificationBell = document.getElementById('notificationBell');
    const notificationPanel = document.getElementById('notificationPanel');
    const notificationCount = notificationBell.querySelector('.badge');
    const notificationBody = notificationPanel.querySelector('.notification-body');

    function fetchNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateNotificationUI(data.notifications);
                } else {
                    console.error('Failed to fetch notifications:', data.message);
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    function updateNotificationUI(notifications) {
        notificationBody.innerHTML = ''; // Clear previous notifications
        let unreadCount = 0;

        if (notifications.length === 0) {
            notificationBody.innerHTML = '<p class="text-center p-3">No new notifications.</p>';
            notificationCount.style.display = 'none';
            return;
        }

        notifications.forEach(notif => {
            if (notif.is_read == 0) {
                unreadCount++;
            }

            const notifItem = `
                <div class="notification-item ${notif.is_read == 0 ? 'unread' : ''}" data-id="${notif.id}">
                    <i class="notification-icon fas fa-bell"></i>
                    <div class="notification-content">
                        <p>${notif.message}</p>
                        <span class="time">${new Date(notif.created_at).toLocaleString()}</span>
                    </div>
                </div>
            `;
            notificationBody.innerHTML += notifItem;
        });

        if (unreadCount > 0) {
            notificationCount.textContent = unreadCount;
            notificationCount.style.display = 'block';
        } else {
            notificationCount.style.display = 'none';
        }
    }

    notificationBell.addEventListener('click', function(e) {
        e.preventDefault();
        notificationPanel.style.display = notificationPanel.style.display === 'block' ? 'none' : 'block';
        
        if (notificationPanel.style.display === 'block') {
            // Mark notifications as read when panel is opened
            fetch('get_notifications.php?action=mark_read')
                .then(() => {
                    notificationCount.style.display = 'none'; // Hide badge immediately
                    // Optionally, you can visually mark items as read without a full refresh
                    notificationBody.querySelectorAll('.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                });
        }
    });

    // Close panel if clicking outside
    document.addEventListener('click', function(event) {
        if (!notificationPanel.contains(event.target) && !notificationBell.contains(event.target)) {
            notificationPanel.style.display = 'none';
        }
    });

    // Periodically check for new notifications
    setInterval(fetchNotifications, 30000); // Check every 30 seconds

    // Initial fetch
    fetchNotifications();
});
