<?php
session_start();

require_once __DIR__ . '/config.php';

function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ../admin/login.php');
        exit;
    }
}

function login($username, $password) {
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: ../admin/login.php');
    exit;
}
?>
