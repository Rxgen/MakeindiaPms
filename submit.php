<?php
// Clean JSON for AJAX — avoid BOM/whitespace breaking fetch().
if (function_exists('ob_get_level')) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

require_once __DIR__ . '/includes/pms_init.php';
require_once __DIR__ . '/includes/site_config.php';

function pms_enquiry_len($value)
{
    $value = (string) $value;
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function pms_enquiry_wants_json()
{
    if (!empty($_POST['ajax'])) {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    $accept = isset($_SERVER['HTTP_ACCEPT']) ? (string) $_SERVER['HTTP_ACCEPT'] : '';
    return stripos($accept, 'application/json') !== false;
}

function pms_enquiry_json($ok, $message, $http = 200)
{
    if (function_exists('ob_get_level')) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
    if (!headers_sent()) {
        http_response_code($http);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
    }
    echo json_encode(array(
        'ok' => (bool) $ok,
        'message' => (string) $message,
    ), JSON_UNESCAPED_UNICODE);
    exit();
}

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

function pms_enquiry_respond($ok, $status, $message)
{
    if (pms_enquiry_wants_json()) {
        pms_enquiry_json($ok, $message, $ok ? 200 : 422);
    }
    pms_enquiry_redirect($status);
}

function pms_enquiry_ensure_table($mysqli)
{
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS pms_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) DEFAULT NULL,
            phone VARCHAR(30) DEFAULT NULL,
            email VARCHAR(150) DEFAULT NULL,
            dob DATE DEFAULT NULL,
            pan_number VARCHAR(20) DEFAULT NULL,
            pms_number VARCHAR(50) DEFAULT NULL,
            city VARCHAR(80) DEFAULT NULL,
            message TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (pms_enquiry_wants_json()) {
        pms_enquiry_json(false, 'Invalid request.', 405);
    }
    header('Location: ' . pms_url('index.php'));
    exit();
}

$name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim((string) $_POST['phone']) : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';

$phoneDigits = preg_replace('/\D+/', '', $phone);
$errors = array();

if ($name === '' || pms_enquiry_len($name) < 2) {
    $errors[] = 'name';
}
if ($phoneDigits === '' || strlen($phoneDigits) !== 10) {
    $errors[] = 'phone';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'email';
}
if ($message === '' || pms_enquiry_len($message) < 10) {
    $errors[] = 'message';
}

if ($errors) {
    pms_enquiry_respond(false, 'error', 'Please check name, phone, email and message, then try again.');
}

try {
    global $mysqli;
    pms_enquiry_ensure_table($mysqli);
    $stmt = $mysqli->prepare(
        'INSERT INTO pms_users (name, phone, email, message) VALUES (?, ?, ?, ?)'
    );
    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $mysqli->error);
    }
    $stmt->bind_param('ssss', $name, $phone, $email, $message);
    $stmt->execute();
    $stmt->close();
    pms_enquiry_respond(true, '1', 'Thanks for your mail. Our team will contact you shortly.');
} catch (Throwable $e) {
    error_log('Enquiry submit failed: ' . $e->getMessage());
    pms_enquiry_respond(false, 'error', 'Could not save enquiry. Please try again or email investor@makeindiapms.com.');
}
