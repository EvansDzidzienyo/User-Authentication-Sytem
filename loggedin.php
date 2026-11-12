<?php
require_once __DIR__ . '/core.inc.php';
require_once __DIR__ . '/connect.php';

if (!loggedin()) {
    header('Location: login.php');
    exit;
}
$firstname = getuserfield($conn, 'firstname') ?? '';
$surname = getuserfield($conn, 'surname') ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account</title>
</head>
<body>
    <main>
        <h1>Account</h1>
        <p>Logged in as <?php echo htmlspecialchars(trim($firstname . ' ' . $surname), ENT_QUOTES, 'UTF-8'); ?>.</p>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Log out</button>
        </form>
    </main>
</body>
</html>
