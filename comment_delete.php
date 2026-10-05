<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_auth();

// Deleting changes data, so it's only allowed through the POST form on the feed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();

$commentId = valid_id($_POST['id'] ?? null);
if (!$commentId) {
    abort(404, 'Comment not found.');
}

try {
    $pdo->beginTransaction();

    // Lock the row while we check who owns it
    $stmt = $pdo->prepare('SELECT post_id, user_id FROM comments WHERE id = ? FOR UPDATE');
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();

    if (!$comment) {
        $pdo->rollBack();
        abort(404, 'Comment not found.');
    }
    // Only the author can delete their own comment
    if ((int) $comment['user_id'] !== current_user_id()) {
        $pdo->rollBack();
        abort(403, 'You can only delete your own comments.');
    }

    $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ? AND user_id = ?');
    $stmt->execute([$commentId, current_user_id()]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}

set_flash('success', 'Your comment has been deleted.');
redirect('index.php#post-' . (int) $comment['post_id']);
