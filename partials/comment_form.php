<?php
/**
 * Comment form, shared by the feed, comment_create.php and comment_edit.php.
 *
 * Expects: $commentAction, $commentPostId (nullable; sent as a hidden field when creating),
 *          $commentValue, $commentError (nullable), $commentSubmitLabel, $commentCancelUrl (nullable)
 */
$commentFieldId = 'comment-body-' . ($commentPostId ?? 'edit');
?>
<form method="post" action="<?= e($commentAction) ?>" class="form comment-form">
    <?= csrf_field() ?>
    <?php if ($commentPostId): ?>
        <input type="hidden" name="post_id" value="<?= (int) $commentPostId ?>">
    <?php endif; ?>

    <label for="<?= $commentFieldId ?>" class="sr-only">Comment</label>
    <textarea id="<?= $commentFieldId ?>" name="body" rows="2" required maxlength="<?= COMMENT_MAX ?>"
              placeholder="Write a comment…"
              class="<?= $commentError ? 'is-invalid' : '' ?>"><?= e($commentValue) ?></textarea>
    <?php if ($commentError): ?>
        <p class="field-error"><?= e($commentError) ?></p>
    <?php endif; ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-small"><?= e($commentSubmitLabel) ?></button>
        <?php if ($commentCancelUrl): ?>
            <a href="<?= e($commentCancelUrl) ?>" class="btn btn-small btn-secondary">Cancel</a>
        <?php endif; ?>
    </div>
</form>
