<?php
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$isLocal = (strpos($host, 'localhost') === 0 || strpos($host, '127.0.0.1') === 0);

// Avoid constructor throwing before we can show a clear message
mysqli_report(MYSQLI_REPORT_OFF);

if ($isLocal) {
    // XAMPP / local development
    $mysqli = @new mysqli('127.0.0.1', 'root', '', 'pms_local_db');
} else {
    // Hostinger live — must match hPanel → MySQL Databases
    $mysqli = @new mysqli('localhost', 'u601117719_makeindiapms', 'Pms#2026', 'u601117719_pms');
}

if (!$mysqli || $mysqli->connect_error) {
    http_response_code(500);
    $msg = $mysqli ? $mysqli->connect_error : 'Could not create MySQL connection';
    die('database connection failed: ' . $msg);
}

$mysqli->set_charset('utf8mb4');
