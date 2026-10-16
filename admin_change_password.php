<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$error = '';
$info = '';
$adminUser = isset($_SESSION['username']) ? (string) $_SESSION['username'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = isset($_POST['current_password']) ? (string) $_POST['current_password'] : '';
    $new = isset($_POST['new_password']) ? trim((string) $_POST['new_password']) : '';
    $confirm = isset($_POST['confirm_password']) ? trim((string) $_POST['confirm_password']) : '';

    if ($adminUser === '') {
        $error = 'Admin session is invalid. Please login again.';
    } elseif ($current === '' || $new === '' || $confirm === '') {
        $error = 'Please fill all password fields.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirm password do not match.';
    } elseif ($new === $current) {
        $error = 'New password must be different from the current password.';
    } else {
        $currentHash = md5($current);
        $stmt = $mysqli->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $adminUser);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || (string) $row['password'] !== $currentHash) {
            $error = 'Current password is incorrect.';
        } else {
            $newHash = md5($new);
            $id = (int) $row['id'];
            $upd = $mysqli->prepare('UPDATE users SET password = ? WHERE id = ?');
            $upd->bind_param('si', $newHash, $id);
            if ($upd->execute()) {
                pms_audit($mysqli, $adminUser, 'Changed admin password', 'admin', $id, 'updated', 'password_changed');
                $upd->close();
                // Force re-login with the new password
                session_destroy();
                header('Location: login.php?pwd=1');
                exit();
            }
            $upd->close();
            $error = 'Could not update password. Please try again.';
        }
    }
}

$pms_admin_title = 'Change Password';
require __DIR__ . '/includes/pms_admin_header.php';
?>
<div class="card shadow-sm border-0" style="max-width:520px;">
    <div class="card-body">
        <h3 class="mb-2">Change admin password</h3>
        <p class="text-muted mb-3">Logged in as <strong><?= pms_h($adminUser) ?></strong>. The new password is saved in the database immediately.</p>

        <?php if ($error): ?><div class="alert alert-danger"><?= pms_h($error) ?></div><?php endif; ?>
        <?php if ($info): ?><div class="alert alert-success"><?= pms_h($info) ?></div><?php endif; ?>

        <form method="POST" action="admin_change_password.php">
            <div class="mb-3">
                <label class="form-label" for="current_password">Current Password</label>
                <input class="form-control" type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="mb-3">
                <label class="form-label" for="new_password">New Password</label>
                <input class="form-control" type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label" for="confirm_password">Confirm New Password</label>
                <input class="form-control" type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
            </div>
            <button class="btn btn-primary" type="submit">Update Password</button>
            <a class="btn btn-outline-secondary ms-2" href="dashboard.php">Cancel</a>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
