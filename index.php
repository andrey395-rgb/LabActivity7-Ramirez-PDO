<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_auth();

$userId = current_user_id();

// All posts from all users, most recent first
$posts = $pdo->query(
    'SELECT p.id, p.user_id, p.title, p.body, p.created_at, p.updated_at, u.name AS author
     FROM posts p
     JOIN users u ON u.id = p.user_id
     ORDER BY p.created_at DESC, p.id DESC'
)->fetchAll();

// All comments in one query, grouped by post (oldest first under each post)
$commentsByPost = [];
if ($posts) {
    $comments = $pdo->query(
        'SELECT c.id, c.post_id, c.user_id, c.body, c.created_at, c.updated_at, u.name AS author
         FROM comments c
         JOIN users u ON u.id = c.user_id
         ORDER BY c.created_at ASC, c.id ASC'
    )->fetchAll();

    foreach ($comments as $comment) {
        $commentsByPost[$comment['post_id']][] = $comment;
    }
}

$pageTitle = 'Feed';
require __DIR__ . '/partials/header.php';
?>
<section class="card">
    <h2 class="card-title">Write a post</h2>
    <?php
    $values = ['title' => '', 'body' => ''];
    $errors = [];
    $action = 'post_create.php';
    $submitLabel = 'Publish';
    $cancelUrl = null;
    require __DIR__ . '/partials/post_form.php';
    ?>
</section>

<h1 class="feed-heading">News feed</h1>

<?php if (!$posts): ?>
    <div class="card empty-state">
        <p>No posts yet. Be the first to write one!</p>
    </div>
<?php endif; ?>

<?php foreach ($posts as $post): ?>
    <?php
    $isOwner = (int) $post['user_id'] === $userId;
    $postComments = $commentsByPost[$post['id']] ?? [];
    ?>
    <article class="card post" id="post-<?= (int) $post['id'] ?>">
        <header class="post-header">
            <div>
                <h2 class="post-title"><?= e($post['title']) ?></h2>
                <p class="meta">
                    by <strong><?= e($post['author']) ?></strong><?= $isOwner ? ' <span class="you">(you)</span>' : '' ?>
                    · <time datetime="<?= e($post['created_at']) ?>"><?= e(format_date($post['created_at'])) ?></time>
                    <?php if ($post['updated_at']): ?>
                        <span class="edited-tag" title="Edited <?= e(format_date($post['updated_at'])) ?>">
                            Edited <?= e(format_date($post['updated_at'])) ?>
                        </span>
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($isOwner): ?>
                <a class="btn btn-small btn-secondary" href="post_edit.php?id=<?= (int) $post['id'] ?>">Edit</a>
            <?php endif; ?>
        </header>

        <div class="post-body"><?= nl2br(e($post['body'])) ?></div>

        <section class="comments">
            <h3 class="comments-title">Comments (<?= count($postComments) ?>)</h3>

            <?php if ($postComments): ?>
                <ul class="comment-list">
                    <?php foreach ($postComments as $comment): ?>
                        <?php $isCommentOwner = (int) $comment['user_id'] === $userId; ?>
                        <li class="comment" id="comment-<?= (int) $comment['id'] ?>">
                            <div class="comment-header">
                                <p class="meta">
                                    <strong><?= e($comment['author']) ?></strong><?= $isCommentOwner ? ' <span class="you">(you)</span>' : '' ?>
                                    · <time datetime="<?= e($comment['created_at']) ?>"><?= e(format_date($comment['created_at'])) ?></time>
                                    <?php if ($comment['updated_at']): ?>
                                        <span class="edited-tag" title="Edited <?= e(format_date($comment['updated_at'])) ?>">Edited</span>
                                    <?php endif; ?>
                                </p>
                                <?php if ($isCommentOwner): ?>
                                    <a class="text-link" href="comment_edit.php?id=<?= (int) $comment['id'] ?>">Edit</a>
                                <?php endif; ?>
                            </div>
                            <div class="comment-body"><?= nl2br(e($comment['body'])) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php
            $commentAction = 'comment_create.php';
            $commentPostId = (int) $post['id'];
            $commentValue = '';
            $commentError = null;
            $commentSubmitLabel = 'Comment';
            $commentCancelUrl = null;
            require __DIR__ . '/partials/comment_form.php';
            ?>
        </section>
    </article>
<?php endforeach; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
