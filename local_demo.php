<?php
require_once __DIR__ . '/includes/pms_init.php';

if (!pms_is_local()) {
    header('HTTP/1.0 404 Not Found');
    exit('Not found');
}

$allowed = array();
foreach (pms_master_account_defs() as $row) {
    $allowed[$row[1]] = $row[0];
}

$email = isset($_GET['email']) ? pms_normalize_email($_GET['email']) : 'sudhirhoshing@gmail.com';
if (!isset($allowed[$email])) {
    $email = 'sudhirhoshing@gmail.com';
}

$client = pms_find_active_client($mysqli, $email);
if (!$client) {
    exit('Demo client not found.');
}

pms_set_client_session($client, pms_default_folio(pms_get_folios($mysqli, (int) $client['id'])));
header('Location: client_dashboard.php');
exit();
