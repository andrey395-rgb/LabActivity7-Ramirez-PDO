<?php
/**
 * Post form, shared by the feed, post_create.php and post_edit.php.
 *
 * Expects: $values (['title', 'body']), $errors, $action, $submitLabel, $cancelUrl (nullable)
 */
?>
<form method="post" action="<?= e($action) ?>" class="form">
    <?= csrf_field() ?>

    <div class="field">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= e($values['title']) ?>"
               required minlength="<?= TITLE_MIN ?>" maxlength="<?= TITLE_MAX ?>"
               class="<?= invalid_class($errors, 'title') ?>">
        <?= field_error($errors, 'title') ?>
    </div>

    <div class="field">
        <label for="body">Content</label>
        <textarea id="body" name="body" rows="6" required maxlength="<?= BODY_MAX ?>"
                  placeholder="What's on your mind?"
                  class="<?= invalid_class($errors, 'body') ?>"><?= e($values['body']) ?></textarea>
        <?= field_error($errors, 'body') ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn"><?= e($submitLabel) ?></button>
        <?php if ($cancelUrl): ?>
            <a href="<?= e($cancelUrl) ?>" class="btn btn-secondary">Cancel</a>
        <?php endif; ?>
    </div>
</form>
