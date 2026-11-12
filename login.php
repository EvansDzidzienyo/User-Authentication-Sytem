<?php
require_once __DIR__ . '/core.inc.php';
require_once __DIR__ . '/connect.php';

$errorMessage = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $passwordInput = (string) ($_POST['password'] ?? '');

    if (!csrf_is_valid()) {
        $errorMessage = 'Your session token is invalid. Refresh the page and try again.';
    } elseif ($username === '' || $passwordInput === '') {
        $errorMessage = 'Please enter both your username and password.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id, password FROM register WHERE username = ? LIMIT 1');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = $result ? mysqli_fetch_assoc($result) : null;
            mysqli_stmt_close($stmt);

            $valid = false;
            if ($user) {
                $stored = (string) $user['password'];
                if (password_verify($passwordInput, $stored)) {
                    $valid = true;
                } elseif (preg_match('/^[a-f0-9]{32}$/i', $stored) && hash_equals(strtolower($stored), md5($passwordInput))) {
                    // One-time compatibility migration for accounts created with the old MD5 scheme.
                    $newHash = password_hash($passwordInput, PASSWORD_DEFAULT);
                    $update = mysqli_prepare($conn, 'UPDATE register SET password = ? WHERE id = ?');
                    if ($update) {
                        mysqli_stmt_bind_param($update, 'si', $newHash, $user['id']);
                        $valid = mysqli_stmt_execute($update);
                        mysqli_stmt_close($update);
                    }
                }
            }

            if ($valid) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                header('Location: loggedin.php');
                exit;
            }
            $errorMessage = 'Invalid username or password.';
        } else {
            error_log('Login query preparation failed: ' . mysqli_error($conn));
            $errorMessage = 'Unable to sign in at the moment. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="page.css">
    <link rel="stylesheet" href="inputs.css">
</head>
<body>
<section id="wrapper">
<header><h1>Welcome back</h1></header>
<?php if ($errorMessage !== ''): ?>
    <p role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<form action="login.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <fieldset>
        <legend>Sign in</legend>
        <label for="username">Username</label><br>
        <input id="username" type="text" name="username" maxlength="30" required autocomplete="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"><br><br>
        <label for="password">Password</label><br>
        <input id="password" type="password" name="password" required autocomplete="current-password"><br><br>
        <button type="submit">Log in</button>
    </fieldset>
</form>
<p>New here? <a href="register.php">Create an account</a>.</p>
</section>
</body>
</html>
