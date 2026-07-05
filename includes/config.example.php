<?php
// ============================================================
// ADMIN CREDENTIALS & SETTINGS — Change these before deploying!
// 
// SETUP INSTRUCTIONS:
// 1. Copy this file to "config.php" in the same directory
// 2. Update all values below with your own credentials
// 3. Never commit config.php to version control
// ============================================================

// Admin login credentials
// Generate a password hash with: php -r "echo password_hash('your_password', PASSWORD_BCRYPT, ['cost' => 12]);"
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', 'REPLACE_WITH_YOUR_BCRYPT_HASH');
define('ADMIN_EMAIL', 'your-email@example.com');

// Clinic / Business Settings
define('CLINIC_NAME', 'BarberBook CRM');
define('CLINIC_TIMEZONE', 'America/New_York');
define('CLINIC_OPEN_TIME', '09:00');
define('CLINIC_CLOSE_TIME', '20:00');

// Cron job secret key — use a long random string
// Generate one with: php -r "echo bin2hex(random_bytes(32));"
define('CRON_SECRET_KEY', 'CHANGE_ME_TO_A_RANDOM_SECRET');

// Stylists Array
$STYLISTS = [
    'Alex (Master Barber)',
    'Sarah (Color Specialist)',
    'Mike (Fade Expert)',
    'Jessica (Senior Stylist)',
    'David (Barber)'
];

// Service Types Array
$SERVICE_TYPES = [
    'Haircut',
    'Beard Trim',
    'Color & Highlights',
    'Hair Treatment',
    'Kids Cut',
    'Full Package',
    'Other'
];

// Durations Array
$DURATIONS = [
    '30 min',
    '45 min',
    '60 min',
    '90 min'
];

// Set default timezone for the application
date_default_timezone_set(CLINIC_TIMEZONE);
?>
