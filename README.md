# Blog Site

A simple blog site in plain PHP (PDO + MySQL) with session-based authentication.
Users can register, log in, write text-only posts, comment on any post, and edit or
delete their own posts and comments.

## Setup (XAMPP)

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Import `schema.sql` in phpMyAdmin (SQL tab) to create the `blog_site` database and its tables.
3. Check the credentials in `db.php` (XAMPP default: user `root`, no password).
4. Put this folder in `htdocs` and open `http://localhost/<folder>/` in the browser,
   e.g. `http://localhost/ramirez-test-folder/blog_site/`.

## Database

| Table      | Columns                                                                          |
|------------|----------------------------------------------------------------------------------|
| `users`    | `id` (PK, AUTO_INCREMENT), `name`, `email` (UNIQUE), `password`, `created_at`    |
| `posts`    | `id` (PK, AUTO_INCREMENT), `user_id` (FK → users), `title`, `body`, `created_at`, `updated_at` |
| `comments` | `id` (PK, AUTO_INCREMENT), `post_id` (FK → posts), `user_id` (FK → users), `body`, `created_at`, `updated_at` |

- `comments` is the pivot/junction table between `users` and `posts`.
- Every foreign key uses `ON DELETE CASCADE`.
- `updated_at` stays `NULL` until a row is edited; when set, the feed shows an **Edited** tag.

## Pages

| Page                  | Access        | Purpose                                         |
|-----------------------|---------------|-------------------------------------------------|
| `register.php`        | Guest only    | Create an account (`password_hash`)             |
| `login.php`           | Guest only    | Log in (`password_verify`)                      |
| `index.php`           | Authenticated | News feed: all posts, newest first, with comments |
| `post_create.php`     | Authenticated | Write a post                                    |
| `post_edit.php`       | Owner only    | Edit your own post                              |
| `post_delete.php`     | Owner only    | Delete your own post (its comments cascade)     |
| `comment_create.php`  | Authenticated | Add a comment                                   |
| `comment_edit.php`    | Owner only    | Edit your own comment                           |
| `comment_delete.php`  | Owner only    | Delete your own comment                         |
| `logout.php`          | Authenticated | Log out                                         |

Guests are redirected to the login page; logged-in users are redirected away from login/register.

## Project structure

```
db.php         PDO instance and DB configuration (imported with `require`)
auth.php       Sessions, guest/auth guards, flash messages, CSRF tokens
helpers.php    Escaping, input reading, validation rules, error pages
partials/      Shared header/footer and the post/comment forms
assets/        style.css and app.js (client-side validation)
schema.sql     Database schema
```

## Security and conventions

- **Prepared statements** for every query; real (non-emulated) prepares.
- **Transactions** (`beginTransaction` / `commit` / `rollBack`) around every write.
- **Validation** on both sides: HTML5 attributes + `app.js` in the browser, and the same limits re-checked in PHP.
- **Ownership** is enforced on the server (`403` otherwise), and every UPDATE/DELETE also filters by `user_id`.
- **CSRF tokens** on every form; state changes (create, edit, delete, logout) are POST only.
- **XSS protection**: all output is escaped with `htmlspecialchars`.
- **Sessions**: HTTP-only cookie, session ID regenerated on login.
- Internal files (`db.php`, `auth.php`, `helpers.php`, `partials/`, `schema.sql`) are blocked from direct browser access via `.htaccess`.
