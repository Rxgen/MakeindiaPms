<?php
require_once __DIR__ . '/includes/site_config.php';

if (!empty($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

$error = isset($_GET['error']) ? 'Invalid email or password.' : '';
$ok = isset($_GET['pwd']) ? 'Password updated. Please login with your new password.' : '';

$page_title = 'Admin Login | Make in India Capital PMS';
$body_class = 'pms-portal';
$extra_css = array(pms_asset('client_portal.css'));
require __DIR__ . '/includes/site_head.php';
?>

<section class="page-content login-page">
  <div class="pms-login pms-login-embedded">
    <div class="pms-login-box">
      <h1>MAKE IN INDIA CAPITAL <span style="color:#CC0000">PMS</span></h1>
      <p>Admin login — authorised staff only.</p>

      <?php if ($error): ?>
        <div class="pms-alert"><?= pms_h($error) ?></div>
      <?php endif; ?>
      <?php if ($ok): ?>
        <div class="pms-ok"><?= pms_h($ok) ?></div>
      <?php endif; ?>

      <form method="POST" action="authenticate.php">
        <label for="username">Admin Email</label>
        <input type="email" id="username" name="username" required placeholder="you@email.com" autocomplete="username">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Enter password" autocomplete="current-password">

        <button class="pms-btn" type="submit">Login</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/site_foot.php'; ?>
