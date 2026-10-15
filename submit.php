<?php
include 'database.php';

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
$pan = isset($_POST['pan']) ? trim($_POST['pan']) : '';
$pms = isset($_POST['pms']) ? trim($_POST['pms']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($name === '' || $phone === '' || $email === '') {
    header('Location: index.html?enquiry=error');
    exit();
}

$stmt = $mysqli->prepare('INSERT INTO pms_users (name, phone, email, dob, pan_number, pms_number, city, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssssss', $name, $phone, $email, $dob, $pan, $pms, $city, $message);

if ($stmt->execute()) {
    $back = 'index.html?enquiry=1#enquiry';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $ref = strtok($_SERVER['HTTP_REFERER'], '?');
        $back = $ref . '?enquiry=1#enquiry';
    }
    header('Location: ' . $back);
    exit();
}

echo 'Error: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
$stmt->close();
