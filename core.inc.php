<?php
// Session cookies are HTTP-only and SameSite=Lax. HTTPS deployments also set Secure.
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function loggedin(): bool
{
    return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid(): bool
{
    $submitted = (string) ($_POST['csrf_token'] ?? '');
    $stored = (string) ($_SESSION['csrf_token'] ?? '');
    return $submitted !== '' && $stored !== '' && hash_equals($stored, $submitted);
}

function getuserfield(mysqli $conn, string $field): ?string
{
    $allowedFields = ['username', 'firstname', 'surname'];
    $field = strtolower($field);
    if (!loggedin() || !in_array($field, $allowedFields, true)) {
        return null;
    }

    $stmt = mysqli_prepare($conn, "SELECT `$field` FROM `register` WHERE `id` = ? LIMIT 1");
    if (!$stmt) {
        error_log('Could not prepare user lookup: ' . mysqli_error($conn));
        return null;
    }
    $userId = (int) $_SESSION['user_id'];
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $row ? (string) $row[$field] : null;
}
?>