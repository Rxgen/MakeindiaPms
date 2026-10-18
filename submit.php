<?php
require_once __DIR__ . '/includes/pms_init.php';

function pms_enquiry_redirect($status, $anchor = 'enquiry')
{
    $back = pms_url('index.php');
    if (!empty($_POST['return_to'])) {
        $candidate = trim((string) $_POST['return_to']);
        if ($candidate !== '' && strpos($candidate, '://') === false && strpos($candidate, '//') !== 0) {
            $back = $candidate;
        }
    } elseif (!empty($_SERVER['HTTP_REFERER'])) {
        $ref = parse_url($_SERVER['HTTP_REFERER']);
        if (!empty($ref['path'])) {
            $back = $ref['path'];
            if (!empty($ref['query'])) {
                parse_str($ref['query'], $q);
                unset($q['enquiry']);
                if ($q) {
                    $back .= '?' . http_build_query($q);
                }
            }
        }
    }

    $sep = (strpos($back, '?') === false) ? '?' : '&';
    $hash = $anchor !== '' ? '#' . $anchor : '';
    header('Location: ' . $back . $sep . 'enquiry=' . rawurlencode($status) . $hash);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . pms_url('index.php'));
    exit();
}

$name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim((string) $_POST['phone']) : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';
$dob = isset($_POST['dob']) ? trim((string) $_POST['dob']) : '';
$pan = isset($_POST['pan']) ? trim((string) $_POST['pan']) : '';
$pms = isset($_POST['pms']) ? trim((string) $_POST['pms']) : '';
$city = isset($_POST['city']) ? trim((string) $_POST['city']) : '';

$phoneDigits = preg_replace('/\D+/', '', $phone);
$errors = array();

if ($name === '' || mb_strlen($name) < 2) {
    $errors[] = 'name';
}
if ($phoneDigits === '' || strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
    $errors[] = 'phone';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'email';
}
if ($message === '' || mb_strlen($message) < 10) {
    $errors[] = 'message';
}

if ($errors) {
    pms_enquiry_redirect('error');
}

$dobSql = ($dob !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) ? $dob : '';

try {
    $stmt = $mysqli->prepare(
        'INSERT INTO pms_users (name, phone, email, dob, pan_number, pms_number, city, message) VALUES (?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssssss', $name, $phone, $email, $dobSql, $pan, $pms, $city, $message);
    $stmt->execute();
    $stmt->close();
    pms_enquiry_redirect('1');
} catch (Exception $e) {
    pms_enquiry_redirect('error');
}
