<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_guest();

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old['name']  = post_string('name');
    $old['email'] = mb_strtolower(post_string('email'), 'UTF-8');
    $password     = post_raw('password');
    $confirm      = post_raw('password_confirm');

    // Name
    if ($old['name'] === '') {
        $errors['name'] = 'Name is required.';
    } elseif (str_length($old['name']) < NAME_MIN || str_length($old['name']) > NAME_MAX) {
        $errors['name'] = 'Name must be between ' . NAME_MIN . ' and ' . NAME_MAX . ' characters.';
    }

    // Email
    if ($old['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (str_length($old['email']) > EMAIL_MAX || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Password
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < PASSWORD_MIN) {
        $errors['password'] = 'Password must be at least ' . PASSWORD_MIN . ' characters.';
    } elseif (strlen($password) > PASSWORD_MAX) {
        $errors['password'] = 'Password must not exceed ' . PASSWORD_MAX . ' characters.';
    }

    if ($confirm === '') {
        $errors['password_confirm'] = 'Please confirm your password.';
    } elseif ($password !== $confirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    // Email must be unique
    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'That email is already registered.';
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$old['name'], $old['email'], password_hash($password, PASSWORD_DEFAULT)]);
        } catch (PDOException $e) {
            // 1062 = duplicate entry (UNIQUE email), e.g. two sign-ups at the same moment
            if (($e->errorInfo[1] ?? null) == 1062) {
                $errors['email'] = 'That email is already registered.';
            } else {
                throw $e;
            }
        }
    }

    if (!$errors) {
        set_flash('success', 'Account created! You can now log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/partials/header.php';
?>
<div class="card narrow">
    <h1>Create an account</h1>
    <p class="muted">Join and start sharing posts.</p>

    <form method="post" action="register.php" class="form">
        <?= csrf_field() ?>

        <div class="field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= e($old['name']) ?>"
                   required minlength="<?= NAME_MIN ?>" maxlength="<?= NAME_MAX ?>" autocomplete="name"
                   class="<?= invalid_class($errors, 'name') ?>">
            <?= field_error($errors, 'name') ?>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>"
                   required maxlength="<?= EMAIL_MAX ?>" autocomplete="email"
                   class="<?= invalid_class($errors, 'email') ?>">
            <?= field_error($errors, 'email') ?>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required minlength="<?= PASSWORD_MIN ?>" maxlength="<?= PASSWORD_MAX ?>" autocomplete="new-password"
                   class="<?= invalid_class($errors, 'password') ?>">
            <p class="hint">At least <?= PASSWORD_MIN ?> characters.</p>
            <?= field_error($errors, 'password') ?>
        </div>

        <div class="field">
            <label for="password_confirm">Confirm password</label>
            <input type="password" id="password_confirm" name="password_confirm"
                   required data-match="password" autocomplete="new-password"
                   class="<?= invalid_class($errors, 'password_confirm') ?>">
            <?= field_error($errors, 'password_confirm') ?>
        </div>

        <button type="submit" class="btn btn-block">Register</button>
    </form>

    <p class="form-footer">Already have an account? <a href="login.php">Log in</a></p>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
