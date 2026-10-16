<?php
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$isLocal = (strpos($host, 'localhost') === 0 || strpos($host, '127.0.0.1') === 0);

if ($isLocal) {
    $mysqli = new mysqli("localhost", "u601117719_makeindiapms", "Pms#2026", "u601117719_pms");
} else {
    $mysqli = new mysqli("127.0.0.1", "root", "", "pms_local_db");
}

if ($mysqli->connect_error) {
    die("database connection failed: " . $mysqli->connect_error);
}

?>
