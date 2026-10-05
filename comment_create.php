<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_auth($pdo);

// Comments are submitted from the feed; this page only handles the POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();

$postId = valid_id($_POST['post_id'] ?? null);
if (!$postId) {
    abort(404, 'Post not found.');
}

$stmt = $pdo->prepare('SELECT id, title FROM posts WHERE id = ?');
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    abort(404, 'Post not found.');
}

$body = post_string('body');
$error = validate_comment($body);

if (!$error) {
    try {
        $pdo->beginTransaction();

        // Lock the post row so it can't be deleted between this check and the insert
        $stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ? FOR UPDATE');
        $stmt->execute([$postId]);
        $postStillExists = (bool) $stmt->fetch();

        if ($postStillExists) {
            $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)');
            $stmt->execute([$postId, current_user_id(), $body]);
            $commentId = (int) $pdo->lastInsertId();

            $pdo->commit();
        } else {
            $pdo->rollBack();
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    if (!$postStillExists) {
        abort(404, 'Post not found. It may have been deleted.');
    }

    set_flash('success', 'Your comment has been added.');
    redirect('index.php#comment-' . $commentId);
}

// Validation failed: show the form again with the error
$pageTitle = 'Add comment';
require __DIR__ . '/partials/header.php';
?>
<div class="card">
    <h1>Add a comment</h1>
    <p class="muted">On: <strong><?= e($post['title']) ?></strong></p>
    <?php
    $commentAction = 'comment_create.php';
    $commentPostId = $postId;
    $commentValue = $body;
    $commentError = $error;
    $commentSubmitLabel = 'Comment';
    $commentCancelUrl = 'index.php#post-' . $postId;
    require __DIR__ . '/partials/comment_form.php';
    ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
