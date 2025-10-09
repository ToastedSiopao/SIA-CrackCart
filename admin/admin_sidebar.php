<?php
$current_page = basename($_SERVER['PHP_SELF']);

$sidebar_links = [
    'dashboard.php' => '<i class="bi bi-house-door"></i> Dashboard',
    'products.php' => '<i class="bi bi-box-seam"></i> Products',
    'manage_orders.php' => '<i class="bi bi-pencil-square"></i> Manage Orders',
    'manage_returns.php' => '<i class="bi bi-arrow-return-left"></i> Manage Returns',
    'manage_users.php' => '<i class="bi bi-people"></i> Manage Users',
    'user_activity.php' => '<i class="bi bi-person-check"></i> User Activity',
    'archived_users.php' => '<i class="bi bi-archive"></i> Archived Users',
    'fleet_management.php' => '<i class="bi bi-truck"></i> Fleet Management',
    'driver_incident_reports.php' => '<i class="bi bi-exclamation-octagon"></i> Incident Reports',
    'logout.php' => '<i class="bi bi-box-arrow-left"></i> Logout'
];
?>

<!-- Desktop Sidebar -->
<nav class="col-lg-2 d-none d-lg-block sidebar">
    <div class="position-sticky">
        <ul class="nav flex-column">
            <?php foreach ($sidebar_links as $page => $link_html): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == $page) ? 'active' : ''; ?>" href="<?php echo $page; ?>">
                        <?php echo $link_html; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
