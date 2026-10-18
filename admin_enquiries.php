<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$enquiries = array();
$result = $mysqli->query('SELECT id, name, phone, email, message, created_at FROM pms_users ORDER BY id DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $enquiries[] = $row;
    }
}

$pms_admin_title = 'Enquiries';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<?php if (isset($_GET['ok'])): ?>
<div class="alert alert-success">Enquiry deleted successfully.</div>
<?php endif; ?>

<div class="pms-toolbar">
    <div>
        <h3 class="mb-1">Website Enquiries</h3>
        <div class="text-muted">Messages submitted from the footer Enquire form and Contact page.</div>
    </div>
    <div class="text-muted"><?= count($enquiries) ?> total</div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$enquiries): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No enquiries yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($enquiries as $row): ?>
                <tr>
                    <td><?= (int) $row['id'] ?></td>
                    <td><strong><?= pms_h($row['name']) ?></strong></td>
                    <td><a href="tel:<?= pms_h($row['phone']) ?>"><?= pms_h($row['phone']) ?></a></td>
                    <td><a href="mailto:<?= pms_h($row['email']) ?>"><?= pms_h($row['email']) ?></a></td>
                    <td style="max-width:320px;white-space:pre-wrap;"><?= pms_h($row['message']) ?></td>
                    <td class="text-nowrap"><?= pms_h($row['created_at']) ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-danger" href="delete_enquiry.php?id=<?= (int) $row['id'] ?>" onclick="return confirm('Delete this enquiry?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
