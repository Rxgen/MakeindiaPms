<?php
if (!isset($pms_admin_title)) {
    $pms_admin_title = 'PMS Admin';
}
$pmsAdminPage = basename(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
$pmsAdminUser = isset($_SESSION['username']) ? $_SESSION['username'] : 'admin';
$pmsNav = array(
    'dashboard.php' => 'Dashboard',
    'admin_master.php' => 'Master Register',
    'admin_clients.php' => 'Clients',
    'admin_folios.php' => 'Folios',
    'admin_transactions.php' => 'Investments & Withdrawals',
    'admin_holdings.php' => 'Holdings',
    'admin_payouts.php' => 'Payouts',
    'admin_payout_rules.php' => 'Payout Rules',
    'admin_audit.php' => 'Audit Log',
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= pms_h($pms_admin_title) ?> | MAKE IN INDIA CAPITAL PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="pms-admin">
<div class="pms-admin-shell">
    <aside class="pms-admin-sidebar">
        <div class="pms-admin-brand">
            <img src="img/makeindia2.png" alt="MAKE IN INDIA CAPITAL PMS">
            <strong>MAKE IN INDIA CAPITAL</strong>
            <span>Admin Console</span>
        </div>
        <nav class="pms-admin-nav">
            <?php foreach ($pmsNav as $href => $label): ?>
                <a href="<?= pms_h($href) ?>" class="<?= $pmsAdminPage === $href || ($href === 'admin_clients.php' && $pmsAdminPage === 'admin_client_view.php') ? 'active' : '' ?>"><?= pms_h($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="pms-admin-main">
        <div class="pms-admin-top">
            <h1><?= pms_h($pms_admin_title) ?> <em>PMS</em></h1>
            <div>
                Welcome, <?= pms_h($pmsAdminUser) ?>
                <a class="btn-ghost ms-3" href="logout.php">Logout</a>
            </div>
        </div>
        <div class="pms-admin-content">
