<?php
session_start();

if (!empty($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

$error = isset($_GET['error']) ? 'Invalid username or password.' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | MAKE IN INDIA CAPITAL PMS</title>
    <link rel="stylesheet" href="/client_portal.css?v=20261016a">
</head>
<body class="pms-portal">
    <div class="pms-login">
        <div class="pms-login-box">
            <h1>MAKE IN INDIA CAPITAL <span style="color:#CC0000">PMS</span></h1>
            <p>Admin login — authorised staff only.</p>

            <?php if ($error): ?>
                <div class="pms-alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="authenticate.php">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter username" autocomplete="username">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password" autocomplete="current-password">

                <button class="pms-btn" type="submit">Login</button>
            </form>

            <a class="pms-muted" href="index.html">Back to website</a>
        </div>
    </div>
</body>
</html>
