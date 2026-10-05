<?php
// Shared helpers: output escaping, input reading, validation and error pages.

// Field limits, shared by the HTML attributes (client-side) and validators (server-side)
const NAME_MIN     = 2;
const NAME_MAX     = 100;
const EMAIL_MAX    = 255;
const PASSWORD_MIN = 8;
const PASSWORD_MAX = 72; // bcrypt ignores anything past 72 bytes
const TITLE_MIN    = 3;
const TITLE_MAX    = 255;
const BODY_MAX     = 5000;
const COMMENT_MAX  = 1000;

// Escape a value for safe HTML output (prevents XSS)
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// Read a trimmed string from $_POST (ignores arrays and other non-strings)
function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

// Read a raw string from $_POST without trimming (used for passwords)
function post_raw(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

// Validate a positive integer ID from user input; returns null if invalid
function valid_id($value): ?int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function str_length(string $value): int
{
    return mb_strlen($value, 'UTF-8');
}

function format_date(string $timestamp): string
{
    return date('M j, Y \a\t g:i A', strtotime($timestamp));
}

function validate_post(array $values): array
{
    $errors = [];

    if ($values['title'] === '') {
        $errors['title'] = 'Title is required.';
    } elseif (str_length($values['title']) < TITLE_MIN || str_length($values['title']) > TITLE_MAX) {
        $errors['title'] = 'Title must be between ' . TITLE_MIN . ' and ' . TITLE_MAX . ' characters.';
    }

    if ($values['body'] === '') {
        $errors['body'] = 'Content is required.';
    } elseif (str_length($values['body']) > BODY_MAX) {
        $errors['body'] = 'Content must not exceed ' . BODY_MAX . ' characters.';
    }

    return $errors;
}

function validate_comment(string $body): ?string
{
    if ($body === '') {
        return 'Comment cannot be empty.';
    }
    if (str_length($body) > COMMENT_MAX) {
        return 'Comment must not exceed ' . COMMENT_MAX . ' characters.';
    }
    return null;
}

// Render the error message under a form field, if any
function field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? '<p class="field-error">' . e($errors[$field]) . '</p>' : '';
}

function invalid_class(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' is-invalid' : '';
}

// Show an error page (403, 404, ...) and stop
function abort(int $code, string $message): void
{
    http_response_code($code);
    $pageTitle = 'Error ' . $code;
    require __DIR__ . '/partials/header.php';
    ?>
    <div class="card narrow">
        <h1>Error <?= $code ?></h1>
        <p><?= e($message) ?></p>
        <a class="btn" href="index.php">Back to feed</a>
    </div>
    <?php
    require __DIR__ . '/partials/footer.php';
    exit;
}
