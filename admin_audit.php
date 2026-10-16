<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$logs = array();
$result = $mysqli->query('SELECT * FROM pms_audit_log ORDER BY id DESC LIMIT 200');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

$pms_admin_title = 'Audit Log';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<h3>Audit Log</h3>
<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date / Time</th>
                    <th>Who</th>
                    <th>Action</th>
                    <th>Record</th>
                    <th>Previous Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($logs): ?>
                <?php foreach ($logs as $row): ?>
                    <tr>
                        <td><?= pms_h($row['created_at']) ?></td>
                        <td><?= pms_h($row['actor']) ?></td>
                        <td><?= pms_h($row['action']) ?></td>
                        <td><?= pms_h($row['entity_type']) ?> #<?= (int) $row['entity_id'] ?></td>
                        <td><small><?= pms_h($row['old_value']) ?></small></td>
                        <td><small><?= pms_h($row['new_value']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No audit records yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
