<?php
// Local development defaults are provided for XAMPP/WAMP. In production, set
// DB_HOST, DB_USER, DB_PASSWORD and DB_NAME in the server environment.
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'mydatabase';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('The application is temporarily unavailable. Please try again later.');
}
mysqli_set_charset($conn, 'utf8mb4');
?>
