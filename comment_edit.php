<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_auth();

$commentId = valid_id($_GET['id'] ?? null);
if (!$commentId) {
    abort(404, 'Comment not found.');
}

$stmt = $pdo->prepare(
    'SELECT c.id, c.post_id, c.user_id, c.body, p.title AS post_title
     FROM comments c
     JOIN posts p ON p.id = c.post_id
     WHERE c.id = ?'
);
$stmt->execute([$commentId]);
$comment = $stmt->fetch();

if (!$comment) {
    abort(404, 'Comment not found.');
}
// Only the author can edit their own comment
if ((int) $comment['user_id'] !== current_user_id()) {
    abort(403, 'You can only edit your own comments.');
}

$body = $comment['body'];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $body = post_string('body');
    $error = validate_comment($body);

    if (!$error) {
        if ($body === $comment['body']) {
            // Nothing changed, so don't mark the comment as edited
            set_flash('info', 'No changes were made.');
        } else {
            // user_id in the WHERE clause guarantees only the owner's row is updated
            $stmt = $pdo->prepare('UPDATE comments SET body = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
            $stmt->execute([$body, $commentId, current_user_id()]);
            set_flash('success', 'Your comment has been updated.');
        }
        redirect('index.php#comment-' . $commentId);
    }
}

$pageTitle = 'Edit comment';
require __DIR__ . '/partials/header.php';
?>
<div class="card">
    <h1>Edit comment</h1>
    <p class="muted">On: <strong><?= e($comment['post_title']) ?></strong></p>
    <?php
    $commentAction = 'comment_edit.php?id=' . $commentId;
    $commentPostId = null;
    $commentValue = $body;
    $commentError = $error;
    $commentSubmitLabel = 'Save changes';
    $commentCancelUrl = 'index.php#comment-' . $commentId;
    require __DIR__ . '/partials/comment_form.php';
    ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
