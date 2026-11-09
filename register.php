<?php
require_once __DIR__ . '/core.inc.php';
require_once __DIR__ . '/connect.php';

$errorMessage = '';
$username = trim((string) ($_POST['username'] ?? ''));
$firstname = trim((string) ($_POST['firstname'] ?? ''));
$surname = trim((string) ($_POST['surname'] ?? ''));

if (loggedin()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordInput = (string) ($_POST['password'] ?? '');
    $passwordAgain = (string) ($_POST['password_again'] ?? '');

    if ($username === '' || $firstname === '' || $surname === '' || $passwordInput === '' || $passwordAgain === '') {
        $errorMessage = 'All fields are required.';
    } elseif (mb_strlen($username) > 30 || mb_strlen($firstname) > 40 || mb_strlen($surname) > 40) {
        $errorMessage = 'One or more fields exceed the allowed length.';
    } elseif (strlen($passwordInput) < 10) {
        $errorMessage = 'Use a password with at least 10 characters.';
    } elseif ($passwordInput !== $passwordAgain) {
        $errorMessage = 'The passwords do not match.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT id FROM register WHERE username = ? LIMIT 1');
        if ($check) {
            mysqli_stmt_bind_param($check, 's', $username);
            mysqli_stmt_execute($check);
            $existing = mysqli_stmt_get_result($check);
            $taken = $existing && mysqli_num_rows($existing) > 0;
            mysqli_stmt_close($check);

            if ($taken) {
                $errorMessage = 'That username is already in use.';
            } else {
                $hash = password_hash($passwordInput, PASSWORD_DEFAULT);
                $insert = mysqli_prepare($conn, 'INSERT INTO register (username, password, firstname, surname) VALUES (?, ?, ?, ?)');
                if ($insert) {
                    mysqli_stmt_bind_param($insert, 'ssss', $username, $hash, $firstname, $surname);
                    if (mysqli_stmt_execute($insert)) {
                        mysqli_stmt_close($insert);
                        header('Location: login.php?registered=1');
                        exit;
                    }
                    error_log('Registration insert failed: ' . mysqli_stmt_error($insert));
                    mysqli_stmt_close($insert);
                }
                $errorMessage = 'Registration could not be completed. Please try again.';
            }
        } else {
            error_log('Registration lookup preparation failed: ' . mysqli_error($conn));
            $errorMessage = 'Registration is temporarily unavailable.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create an account</title>
    <link rel="stylesheet" href="page.css">
    <link rel="stylesheet" href="inputs.css">
</head>
<body>
<section id="wrapper">
<header><h1>Create an account</h1></header>
<?php if ($errorMessage !== ''): ?>
    <p role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<form action="register.php" method="post">
    <fieldset>
        <legend>Registration details</legend>
        <label for="username">Username</label><br>
        <input id="username" type="text" name="username" maxlength="30" required autocomplete="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"><br><br>
        <label for="password">Password (at least 10 characters)</label><br>
        <input id="password" type="password" name="password" minlength="10" required autocomplete="new-password"><br><br>
        <label for="password_again">Confirm password</label><br>
        <input id="password_again" type="password" name="password_again" minlength="10" required autocomplete="new-password"><br><br>
        <label for="firstname">First name</label><br>
        <input id="firstname" type="text" name="firstname" maxlength="40" required value="<?php echo htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8'); ?>"><br><br>
        <label for="surname">Surname</label><br>
        <input id="surname" type="text" name="surname" maxlength="40" required value="<?php echo htmlspecialchars($surname, ENT_QUOTES, 'UTF-8'); ?>"><br><br>
        <button type="submit">Register</button>
    </fieldset>
</form>
<p>Already registered? <a href="login.php">Log in</a>.</p>
</section>
</body>
</html>
