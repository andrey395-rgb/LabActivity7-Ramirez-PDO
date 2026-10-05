<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_auth($pdo);

$postId = valid_id($_GET['id'] ?? null);
if (!$postId) {
    abort(404, 'Post not found.');
}

$stmt = $pdo->prepare('SELECT id, user_id, title, body FROM posts WHERE id = ?');
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    abort(404, 'Post not found.');
}
// Only the author can edit their own post
if ((int) $post['user_id'] !== current_user_id()) {
    abort(403, 'You can only edit your own posts.');
}

$errors = [];
$values = ['title' => $post['title'], 'body' => $post['body']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $values = [
        'title' => post_string('title'),
        'body'  => post_string('body'),
    ];
    $errors = validate_post($values);

    if (!$errors) {
        if ($values['title'] === $post['title'] && $values['body'] === $post['body']) {
            // Nothing changed, so don't mark the post as edited
            set_flash('info', 'No changes were made.');
        } else {
            try {
                $pdo->beginTransaction();

                // user_id in the WHERE clause guarantees only the owner's row is updated
                $stmt = $pdo->prepare('UPDATE posts SET title = ?, body = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
                $stmt->execute([$values['title'], $values['body'], $postId, current_user_id()]);

                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
            set_flash('success', 'Your post has been updated.');
        }
        redirect('index.php#post-' . $postId);
    }
}

$pageTitle = 'Edit post';
require __DIR__ . '/partials/header.php';
?>
<div class="card">
    <h1>Edit post</h1>
    <?php
    $action = 'post_edit.php?id=' . $postId;
    $submitLabel = 'Save changes';
    $cancelUrl = 'index.php#post-' . $postId;
    require __DIR__ . '/partials/post_form.php';
    ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
