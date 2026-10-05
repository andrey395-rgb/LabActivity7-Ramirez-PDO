<?php $flash = get_flash(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Blog Site') ?> · Blog Site</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav">
        <a class="brand" href="index.php">Blog Site</a>
        <nav class="nav-links">
            <?php if (is_logged_in()): ?>
                <a href="index.php">Feed</a>
                <a href="post_create.php">New post</a>
                <span class="nav-user"><?= e($_SESSION['user_name']) ?></span>
                <form method="post" action="logout.php" class="inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="link-button">Log out</button>
                </form>
            <?php else: ?>
                <a href="login.php">Log in</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    <?php endif; ?>
