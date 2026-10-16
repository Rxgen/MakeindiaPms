<?php
session_start();
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
$password = isset($_POST['password']) ? (string) $_POST['password'] : '';

if ($username === '' || $password === '') {
    header('Location: login.php?error=1');
    exit();
}

$username = strtolower($username);
$passwordHash = md5($password);

$stmt = $mysqli->prepare('SELECT id, username FROM users WHERE username = ? AND password = ? LIMIT 1');
$stmt->bind_param('ss', $username, $passwordHash);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    $_SESSION['username'] = $row['username'];
    $_SESSION['admin_id'] = (int) $row['id'];
    header('Location: dashboard.php');
    exit();
}

header('Location: login.php?error=1');
exit();
