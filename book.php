<?php
require_once 'includes/storage.php';
require_once 'includes/config.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $required = ['name', 'phone', 'email', 'date', 'time', 'service_type', 'stylist', 'appointment_type', 'duration_estimate'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }
        
        $id = add_appointment($_POST);
        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Chair | <?= CLINIC_NAME ?></title>
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
            <li><a href="index.php">Home</a></li>
            <li><a href="book.php" class="active btn btn-primary" style="color: var(--bg-dark); padding: 5px 15px;">Book Now</a></li>
        </ul>
    </div>
</nav>

<div class="booking-header">
    <div class="container">
        <h1 class="booking-title">Book Your Chair</h1>
        <p class="booking-subtitle">BarberBook — Your Seat is Waiting</p>
    </div>
</div>

<div class="container">
    <div class="card booking-form-card">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>Booking Received!</strong> Your request has been sent. We will confirm shortly.
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn btn-primary">Return Home</a>
            </div>
        <?php else: ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="book.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="tel" name="phone" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Preferred Date *</label>
                        <input type="date" name="date" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Preferred Time *</label>
                        <input type="time" name="time" required min="<?= CLINIC_OPEN_TIME ?>" max="<?= CLINIC_CLOSE_TIME ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Service Type *</label>
                        <select name="service_type" required>
                            <option value="">Select a service...</option>
                            <?php foreach ($SERVICE_TYPES as $service): ?>
                                <option value="<?= htmlspecialchars($service) ?>"><?= htmlspecialchars($service) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stylist Preference *</label>
                        <select name="stylist" required>
                            <option value="Any Stylist">Any Stylist (First Available)</option>
                            <?php foreach ($STYLISTS as $stylist): ?>
                                <option value="<?= htmlspecialchars($stylist) ?>"><?= htmlspecialchars($stylist) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Appointment Type *</label>
                        <div class="radio-group" style="padding-top: 10px;">
                            <label class="radio-item"><input type="radio" name="appointment_type" value="Pre-booked" checked> Pre-booked</label>
                            <label class="radio-item"><input type="radio" name="appointment_type" value="Walk-in"> Walk-in</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Duration Estimate *</label>
                        <select name="duration_estimate" required>
                            <?php foreach ($DURATIONS as $dur): ?>
                                <option value="<?= htmlspecialchars($dur) ?>"><?= htmlspecialchars($dur) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" placeholder="Any special requests or instructions..."></textarea>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="font-size: 1.1rem; padding: 15px 40px;">Reserve My Spot</button>
                </div>
            </form>
        <?php endif; ?>
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
