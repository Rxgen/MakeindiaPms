<?php
require_once __DIR__ . '/includes/pms_init.php';

if (!empty($_SESSION['client_id'])) {
    header('Location: client_dashboard.php');
    exit();
}

$error = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = pms_normalize_email(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your registered email and password.';
    } else {
        $client = pms_find_active_client($mysqli, $email);
        if (!$client) {
            $error = 'No active client account was found for this email.';
        } elseif ((string) $client['login_password'] !== $password) {
            $error = 'Invalid email or password.';
            pms_audit($mysqli, $email, 'Failed client login', 'client', (int) $client['id'], '', 'Wrong password');
        } else {
            pms_set_client_session($client, pms_default_folio(pms_get_folios($mysqli, (int) $client['id'])));
            pms_audit($mysqli, $client['email'], 'Client login', 'client', (int) $client['id'], '', 'Password login');
            if (pms_client_uses_default_password($client)) {
                header('Location: client_change_password.php?first=1');
            } else {
                header('Location: client_dashboard.php');
            }
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login | MAKE IN INDIA CAPITAL PMS</title>
    <link rel="stylesheet" href="client_portal.css">
</head>
<body class="pms-portal">
    <div class="pms-login">
        <div class="pms-login-box">
            <h1>MAKE IN INDIA CAPITAL <span style="color:#CC0000">PMS</span></h1>
            <p>Login with your registered email and password.</p>

            <?php if ($error): ?><div class="pms-alert"><?= pms_h($error) ?></div><?php endif; ?>
            <?php if ($info): ?><div class="pms-ok"><?= pms_h($info) ?></div><?php endif; ?>

            <form method="POST" action="client_login.php">
                <label for="email">Registered Email</label>
                <input type="email" id="email" name="email" required placeholder="your@email.com" autocomplete="username" value="<?= isset($_POST['email']) ? pms_h($_POST['email']) : '' ?>">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password" autocomplete="current-password">

                <button class="pms-btn" type="submit">Login</button>
            </form>

            <p class="pms-muted" style="margin-top:14px; text-align:left;">
                First-time login password: <strong><?= pms_h(pms_default_password()) ?></strong><br>
                After login you can change this password. Your admin can also view the current password.
            </p>
            <a class="pms-muted" href="index.html">Back to website</a>
        </div>
    </div>
</body>
</html>
