<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_auth($pdo);

$errors = [];
$values = ['title' => '', 'body' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $values = [
        'title' => post_string('title'),
        'body'  => post_string('body'),
    ];
    $errors = validate_post($values);

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO posts (user_id, title, body) VALUES (?, ?, ?)');
            $stmt->execute([current_user_id(), $values['title'], $values['body']]);
            $postId = (int) $pdo->lastInsertId();

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        set_flash('success', 'Your post has been published.');
        redirect('index.php#post-' . $postId);
    }
}

$pageTitle = 'New post';
require __DIR__ . '/partials/header.php';
?>
<div class="card">
    <h1>Write a post</h1>
    <?php
    $action = 'post_create.php';
    $submitLabel = 'Publish';
    $cancelUrl = 'index.php';
    require __DIR__ . '/partials/post_form.php';
    ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
