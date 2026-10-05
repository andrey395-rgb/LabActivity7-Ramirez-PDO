<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_auth();

// Deleting changes data, so it's only allowed through the POST form on the feed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();

$postId = valid_id($_POST['id'] ?? null);
if (!$postId) {
    abort(404, 'Post not found.');
}

try {
    $pdo->beginTransaction();

    // Lock the row while we check who owns it
    $stmt = $pdo->prepare('SELECT user_id FROM posts WHERE id = ? FOR UPDATE');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if (!$post) {
        $pdo->rollBack();
        abort(404, 'Post not found.');
    }
    // Only the author can delete their own post
    if ((int) $post['user_id'] !== current_user_id()) {
        $pdo->rollBack();
        abort(403, 'You can only delete your own posts.');
    }

    // The post's comments are removed automatically by ON DELETE CASCADE
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
    $stmt->execute([$postId, current_user_id()]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}

set_flash('success', 'Your post has been deleted.');
redirect('index.php');
