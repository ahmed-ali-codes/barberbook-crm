<?php
require_once '../includes/auth.php';
require_once '../includes/storage.php';

require_login();

if (isset($_GET['logout'])) {
    logout();
}

$appointments = get_appointments();

// Calculate Stats
$today = date('Y-m-d');
$this_month = date('Y-m');

$todays_bookings = 0;
$walkins_today = 0;
$pending_requests = 0;
$total_this_month = 0;

$stylist_counts = array_fill_keys($STYLISTS, 0);
$service_counts = [];

foreach ($appointments as $app) {
    if ($app['date'] === $today) {
        $todays_bookings++;
        if (isset($app['appointment_type']) && strtolower($app['appointment_type']) === 'walk-in') {
            $walkins_today++;
        }
    }
    
    if (strpos($app['date'], $this_month) === 0) {
        $total_this_month++;
    }
    
    if ($app['status'] === 'Pending') {
        $pending_requests++;
    }
    
    // Stylist utilization (overall)
    $stylist = $app['stylist'] ?? '';
    if (array_key_exists($stylist, $stylist_counts)) {
        $stylist_counts[$stylist]++;
    }
    
    // Service popularity
    $service = $app['service_type'] ?? '';
    if ($service) {
        if (!isset($service_counts[$service])) $service_counts[$service] = 0;
        $service_counts[$service]++;
    }
}

// Prepare stylist utilization percentages
$max_stylist_count = max($stylist_counts) ?: 1;

// Prepare popular services (Top 3)
arsort($service_counts);
$top_services = array_slice($service_counts, 0, 3, true);

// Recent appointments (Top 5)
$recent_appointments = array_slice($appointments, 0, 5);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= CLINIC_NAME ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="dashboard.php" class="navbar-brand">
            <span style="font-size: 1.5rem; color: var(--gold);">&#x2702;</span> Admin
        </a>
        <input type="checkbox" id="nav-toggle" class="nav-toggle">
        <label for="nav-toggle" class="nav-toggle-label"><span></span></label>
        <ul class="navbar-nav">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="?logout=1" class="btn-action" onclick="return confirm('Are you sure you want to log out?');">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="dashboard-grid">
        <div class="card stat-card">
            <div class="stat-value"><?= $todays_bookings ?></div>
            <div class="stat-label">Today's Bookings</div>
        </div>
        <div class="card stat-card">
            <div class="stat-value"><?= $walkins_today ?></div>
            <div class="stat-label">Walk-ins Today</div>
        </div>
        <div class="card stat-card">
            <div class="stat-value"><?= $pending_requests ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
        <div class="card stat-card">
            <div class="stat-value"><?= $total_this_month ?></div>
            <div class="stat-label">Total This Month</div>
        </div>
    </div>

    <div class="form-row">
        <!-- Stylist Utilization -->
        <div class="card">
            <h3 style="color: var(--gold); margin-bottom: 20px;">Stylist Utilization</h3>
            <div class="utilization-section">
                <?php foreach ($stylist_counts as $stylist => $count): 
                    $percentage = ($count / $max_stylist_count) * 100;
                ?>
                    <div class="utilization-item">
                        <div class="utilization-header">
                            <span><?= htmlspecialchars($stylist) ?></span>
                            <span><?= $count ?> bookings</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: <?= $percentage ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Most Popular Service -->
        <div class="card">
            <h3 style="color: var(--gold); margin-bottom: 20px;">Most Popular Services</h3>
            <div class="popular-services">
                <?php 
                $rank = 1;
                foreach ($top_services as $service => $count): ?>
                    <div class="service-pill">
                        <span class="rank"><?= $rank ?></span>
                        <?= htmlspecialchars($service) ?> (<?= $count ?>)
                    </div>
                <?php 
                $rank++;
                endforeach; 
                if(empty($top_services)) echo "<p style='color: var(--text-muted);'>No data yet.</p>";
                ?>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3 style="color: var(--gold); margin-bottom: 20px;">Recent Appointments</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Stylist</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_appointments)): ?>
                        <tr><td colspan="5" style="text-align:center;">No recent appointments.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_appointments as $app): ?>
                            <tr class="table-row">
                                <td><?= htmlspecialchars($app['date']) ?> <br><small style="color:var(--text-muted)"><?= htmlspecialchars($app['time']) ?></small></td>
                                <td><?= htmlspecialchars($app['name']) ?></td>
                                <td><?= htmlspecialchars($app['service_type']) ?></td>
                                <td><?= htmlspecialchars($app['stylist']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($app['status']) ?>">
                                        <?= htmlspecialchars($app['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 20px; text-align: right;">
            <a href="appointments.php" class="btn btn-action">View All Appointments &rarr;</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    var toggle = document.getElementById('nav-toggle');
    if (toggle && toggle.checked && !e.target.closest('.navbar')) {
        toggle.checked = false;
    }
});
</script>
</body>
</html>
