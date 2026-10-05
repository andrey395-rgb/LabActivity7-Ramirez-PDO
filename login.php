<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_guest();

$errors = [];
$old = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old['email'] = mb_strtolower(post_string('email'), 'UTF-8');
    $password     = post_raw('password');

    if ($old['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Upgrade the hash if PHP's default algorithm/cost has changed
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }

            login_user($user);
            set_flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('index.php');
        }

        // Same message for unknown email and wrong password, so emails can't be probed
        $errors['login'] = 'Invalid email or password.';
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/partials/header.php';
?>
<div class="card narrow">
    <h1>Log in</h1>
    <p class="muted">Welcome back! Log in to see the feed.</p>

    <?php if (isset($errors['login'])): ?>
        <div class="alert alert-error"><?= e($errors['login']) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php" class="form">
        <?= csrf_field() ?>

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>"
                   required maxlength="<?= EMAIL_MAX ?>" autocomplete="email" autofocus
                   class="<?= invalid_class($errors, 'email') ?>">
            <?= field_error($errors, 'email') ?>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required autocomplete="current-password"
                   class="<?= invalid_class($errors, 'password') ?>">
            <?= field_error($errors, 'password') ?>
        </div>

        <button type="submit" class="btn btn-block">Log in</button>
    </form>

    <p class="form-footer">No account yet? <a href="register.php">Register</a></p>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
