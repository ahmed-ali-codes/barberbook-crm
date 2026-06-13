<?php
// Handle PHP built-in server routing
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && $path !== '/index.php' && file_exists(__DIR__ . $path)) {
        return false;
    }
    if ($path === '/login.php' || $path === '/login') {
        header('Location: /admin/login.php');
        exit;
    }
}

require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= CLINIC_NAME ?> | Your Seat is Waiting</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="index.php" class="navbar-brand">
            <span style="font-size: 1.8rem; color: var(--gold);">&#x2702;</span> <?= CLINIC_NAME ?>
        </a>
        <input type="checkbox" id="nav-toggle" class="nav-toggle">
        <label for="nav-toggle" class="nav-toggle-label"><span></span></label>
        <ul class="navbar-nav">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="book.php" class="btn btn-primary" style="color: var(--bg-dark);">Book Now</a></li>
        </ul>
    </div>
</nav>

<div class="booking-header">
    <div class="container">
        <h1 class="booking-title">Welcome to <?= CLINIC_NAME ?></h1>
        <p class="booking-subtitle">Premium grooming experiences for the modern gentleman.</p>
        <div style="margin-top: 40px;">
            <a href="book.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 15px 40px;">Book Your Chair</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="dashboard-grid" style="margin-top: 60px;">
        <div class="card stat-card">
            <h3 style="color: var(--gold); font-size: 1.5rem; margin-bottom: 15px;">Expert Barbers</h3>
            <p style="color: var(--text-muted);">Our team of master barbers delivers precision cuts, classic shaves, and modern styles tailored to you.</p>
        </div>
        <div class="card stat-card">
            <h3 style="color: var(--gold); font-size: 1.5rem; margin-bottom: 15px;">Premium Services</h3>
            <p style="color: var(--text-muted);">From hot towel shaves to complete grooming packages, experience the pinnacle of men's care.</p>
        </div>
        <div class="card stat-card">
            <h3 style="color: var(--gold); font-size: 1.5rem; margin-bottom: 15px;">Easy Booking</h3>
            <p style="color: var(--text-muted);">Reserve your chair online in seconds. Choose your preferred stylist and time slot effortlessly.</p>
        </div>
    </div>
</div>

<footer style="text-align: center; padding: 40px 0; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 60px;">
    <p style="color: var(--text-muted);">&copy; <?= date('Y') ?> <?= CLINIC_NAME ?>. All rights reserved.</p>
    <p style="margin-top: 10px;"><a href="admin/login.php" style="font-size: 0.8rem; color: var(--text-muted);">Admin Portal</a></p>
</footer>

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
