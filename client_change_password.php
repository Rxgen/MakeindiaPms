<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_client();

$clientId = (int) $_SESSION['client_id'];
$client = pms_client_by_id($mysqli, $clientId);
if (!$client || (int) $client['is_active'] !== 1) {
    unset($_SESSION['client_id'], $_SESSION['client_name'], $_SESSION['client_email'], $_SESSION['client_folio_number']);
    header('Location: client_login.php');
    exit();
}

$error = '';
$info = '';
$firstLogin = isset($_GET['first']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = isset($_POST['current_password']) ? (string) $_POST['current_password'] : '';
    $new = isset($_POST['new_password']) ? trim((string) $_POST['new_password']) : '';
    $confirm = isset($_POST['confirm_password']) ? trim((string) $_POST['confirm_password']) : '';

    if ($current === '' || $new === '' || $confirm === '') {
        $error = 'Please fill all password fields.';
    } elseif ((string) $client['login_password'] !== $current) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirm password do not match.';
    } elseif ($new === $current) {
        $error = 'New password must be different from the current password.';
    } else {
        if (pms_set_client_password($mysqli, $clientId, $new, 0)) {
            pms_audit($mysqli, $client['email'], 'Changed login password', 'client', $clientId, $current, $new);
            $client = pms_client_by_id($mysqli, $clientId);
            $info = 'Password updated successfully. Admin can see your new password in the client list.';
            if ($firstLogin) {
                header('Location: client_dashboard.php?pwd=1');
                exit();
            }
        } else {
            $error = 'Could not update password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | MAKE IN INDIA CAPITAL PMS</title>
    <link rel="stylesheet" href="/client_portal.css?v=20261016a">
</head>
<body class="pms-portal">
    <div class="pms-login">
        <div class="pms-login-box">
            <h1>Change Password</h1>
            <?php if ($firstLogin): ?>
                <p>You are using the default password. Please set your own password to continue.</p>
            <?php else: ?>
                <p>Update the password used for your client login.</p>
            <?php endif; ?>

            <?php if ($error): ?><div class="pms-alert"><?= pms_h($error) ?></div><?php endif; ?>
            <?php if ($info): ?><div class="pms-ok"><?= pms_h($info) ?></div><?php endif; ?>

            <form method="POST" action="client_change_password.php<?= $firstLogin ? '?first=1' : '' ?>">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">

                <button class="pms-btn" type="submit">Save Password</button>
            </form>

            <?php if (!$firstLogin): ?>
                <a class="pms-muted" href="client_dashboard.php">Back to portfolio</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
