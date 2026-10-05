<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

// Logging out changes state, so it's only allowed through the POST form in the nav
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

require_auth($pdo);
verify_csrf();

// Clear the session data and its cookie
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// Start a fresh session just to carry the flash message
session_start();
set_flash('success', 'You have been logged out.');
redirect('login.php');
