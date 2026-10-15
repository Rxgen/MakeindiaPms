<?php
require_once __DIR__ . '/includes/pms_init.php';
unset($_SESSION['client_id'], $_SESSION['client_name'], $_SESSION['client_email'], $_SESSION['client_folio_number'], $_SESSION['pms_otp_id'], $_SESSION['pms_otp_client_id'], $_SESSION['pms_otp_mask'], $_SESSION['pms_otp_debug'], $_SESSION['pms_otp_client_id']);
header('Location: index.html');
exit();
