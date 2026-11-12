# PHP/MySQL User Authentication

A small learning project demonstrating user registration, password verification and PHP session-based login using MySQL.

> **Security note:** This is an educational project, not a production-ready identity service. Review the deployment checklist below before exposing it to the internet.

## Stack
- PHP with MySQLi
- MySQL / MariaDB
- HTML and CSS

## Local setup
1. Install PHP 8+ with the `mysqli` and `mbstring` extensions, plus MySQL/MariaDB (XAMPP or another local stack is suitable).
2. Create a database named `mydatabase` or set `DB_NAME` to your chosen database.
3. Import `database.sql`.
4. Configure `DB_HOST`, `DB_USER`, `DB_PASSWORD` and `DB_NAME` as environment variables where possible. The defaults in `connect.php` are intended only for local development.
5. Serve the project through your local PHP web server and open `register.php`.

## Authentication safeguards in this branch
- Passwords for new accounts use PHP `password_hash()` and are checked with `password_verify()`.
- Legacy 32-character MD5 hashes are verified only for migration, then replaced with a modern password hash after a successful login. MD5 is not used for new registrations.
- Database operations use prepared statements.
- Session IDs are regenerated after successful authentication; session cookies use `HttpOnly`, `SameSite=Lax`, and `Secure` when HTTPS is active.
- Login, registration and logout requests use session-bound CSRF tokens.
- The authenticated page is `loggedin.php`; logout is a POST request handled by `logout.php`.
- User-supplied text is escaped before it is rendered in HTML.
- Database errors are logged server-side rather than displayed to visitors.

## Existing database migration
If you already have a `register` table, back it up first, then widen the password column:

```sql
ALTER TABLE register MODIFY password VARCHAR(255) NOT NULL;\n-- Check for duplicate usernames before adding this constraint.\nALTER TABLE register ADD UNIQUE KEY uq_register_username (username);
```

The login code supports the old 32-character MD5 representation as a one-time transition. It upgrades each legacy hash only after the user supplies the correct password. Do not publish database dumps containing real user records or hashes.

## Before production use
Add CSRF protection, rate limiting / login throttling, password reset and email verification workflows, HTTPS-only deployment, least-privilege database credentials, security headers, and automated tests. Review all linked pages (including `index.php` and logout handling) before deployment.
