<?php
// Use secure cookie settings when HTTPS is enabled by the hosting environment.
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

function getuserfield(mysqli $conn, string $field): ?string
{
    $allowedFields = ['username', 'firstname', 'surname'];
    if (!loggedin() || !in_array(strtolower($field), $allowedFields, true)) {
        return null;
    }

    $field = strtolower($field);
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
