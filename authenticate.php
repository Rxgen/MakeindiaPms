<?php
session_start();
include("database.php");

$username = $_POST['username'];
$password = md5($_POST['password']);

$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $mysqli->query($query);

if ($result->num_rows === 1) {
    $_SESSION['username'] = $username;
    header("Location: dashboard.php");
} else {
    header("Location: login.php?error=1");
}
?>