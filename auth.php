<?php
// Session handling, guest/auth guards, flash messages and CSRF protection.
// Import at the top of every page with: require __DIR__ . '/auth.php';

require __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,  // JS can't read the session cookie
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,  // reject uninitialized session IDs
    ]);
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

// GUEST pages (login, register): logged-in users go to the feed
function require_guest(): void
{
    if (is_logged_in()) {
        redirect('index.php');
    }
}

// AUTHENTICATED pages: guests go to the login page (requires db.php to be loaded first)
function require_auth(): void
{
    global $pdo;

    if (is_logged_in()) {
        // Make sure the account still exists (it may have been deleted)
        $stmt = $pdo->prepare('SELECT name FROM users WHERE id = ?');
        $stmt->execute([current_user_id()]);
        $name = $stmt->fetchColumn();

        if ($name !== false) {
            $_SESSION['user_name'] = $name;
            return;
        }

        $_SESSION = [];
        session_regenerate_id(true);
    }

    set_flash('error', 'Please log in to continue.');
    redirect('login.php');
}

function login_user(array $user): void
{
    session_regenerate_id(true); // prevent session fixation
    $_SESSION['user_id']   = (int) $user['id'];
    $_SESSION['user_name'] = $user['name'];
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        abort(400, 'Your session expired or the form is invalid. Please go back, refresh the page and try again.');
    }
}
