<?php
session_start();

if (!empty($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

$error = isset($_GET['error']) ? 'Invalid email or password.' : '';
$ok = isset($_GET['pwd']) ? 'Password updated. Please login with your new password.' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | MAKE IN INDIA CAPITAL PMS</title>
    <link rel="stylesheet" href="/client_portal.css?v=20261016c">
</head>
<body class="pms-portal">
    <div class="pms-login">
        <div class="pms-login-box">
            <h1>MAKE IN INDIA CAPITAL <span style="color:#CC0000">PMS</span></h1>
            <p>Admin login — authorised staff only.</p>

            <?php if ($error): ?>
                <div class="pms-alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($ok): ?>
                <div class="pms-ok"><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="authenticate.php">
                <label for="username">Admin Email</label>
                <input type="email" id="username" name="username" required placeholder="you@email.com" autocomplete="username">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password" autocomplete="current-password">

                <button class="pms-btn" type="submit">Login</button>
            </form>

            <a class="pms-muted" href="/index.php">Back to website</a>
        </div>
    </div>
</body>
</html>
