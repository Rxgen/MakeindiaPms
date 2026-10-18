<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

if (isset($_GET['id'])) {
    $userId = (int) $_GET['id'];
    if ($userId > 0) {
        $stmt = $mysqli->prepare('DELETE FROM pms_users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }
}

header('Location: admin_enquiries.php?ok=1');
exit();
