<?php
require_once '../includes/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BarberBook Admin Panel - Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="login-wrapper">

<div class="login-card">
    <div class="login-icon">&#x2702;</div>
    <h2 class="login-title">BarberBook Admin Panel</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group" style="text-align: left;">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        <div class="form-group" style="text-align: left;">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Login</button>
    </form>
    
    <div style="margin-top: 30px;">
        <a href="../index.php" style="font-size: 0.9rem; color: var(--text-muted);">&larr; Back to Public Site</a>
    </div>
</div>

</body>
</html>
