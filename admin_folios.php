<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$message = '';
$error = '';
$edit = null;

if (isset($_GET['id'])) {
    $stmt = $mysqli->prepare('SELECT * FROM pms_folios WHERE id = ? LIMIT 1');
    $id = (int) $_GET['id'];
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $client_id = (int) $_POST['client_id'];
    $folio_number = trim($_POST['folio_number']);
    $account_name = trim($_POST['account_name']);
    $account_type = trim($_POST['account_type']);
    $status = trim($_POST['status']);
    $investment_start_date = $_POST['investment_start_date'] !== '' ? $_POST['investment_start_date'] : null;
    $performance_remark = trim($_POST['performance_remark']);

    if ($client_id < 1 || $folio_number === '' || $account_name === '') {
        $error = 'Client, folio number and account name are required.';
    } elseif (!pms_client_by_id($mysqli, $client_id)) {
        $error = 'Selected client was not found.';
    } else {
        if ($id > 0) {
            $oldStmt = $mysqli->prepare('SELECT * FROM pms_folios WHERE id = ? LIMIT 1');
            $oldStmt->bind_param('i', $id);
            $oldStmt->execute();
            $old = $oldStmt->get_result()->fetch_assoc();
            $oldStmt->close();

            $stmt = $mysqli->prepare('UPDATE pms_folios SET client_id = ?, folio_number = ?, account_name = ?, account_type = ?, status = ?, investment_start_date = ?, performance_remark = ? WHERE id = ?');
            $stmt->bind_param('issssssi', $client_id, $folio_number, $account_name, $account_type, $status, $investment_start_date, $performance_remark, $id);
            if ($stmt->execute()) {
                pms_sync_folio_ownership($mysqli, $id, $client_id);
                pms_audit($mysqli, pms_admin_actor(), 'Updated folio', 'folio', $id, json_encode($old), json_encode($_POST));
                header('Location: admin_folios.php?ok=1');
                exit();
            }
            $error = 'Could not update folio. Folio number may already exist.';
            $stmt->close();
        } else {
            $stmt = $mysqli->prepare('INSERT INTO pms_folios (client_id, folio_number, account_name, account_type, status, investment_start_date, performance_remark) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('issssss', $client_id, $folio_number, $account_name, $account_type, $status, $investment_start_date, $performance_remark);
            if ($stmt->execute()) {
                $newId = $stmt->insert_id;
                pms_sync_folio_ownership($mysqli, $newId, $client_id);
                pms_audit($mysqli, pms_admin_actor(), 'Created folio', 'folio', $newId, '', $folio_number);
                header('Location: admin_folios.php?ok=1');
                exit();
            }
            $error = 'Could not create folio. Folio number may already exist.';
            $stmt->close();
        }
    }
}

if (isset($_GET['ok'])) {
    $message = 'Saved successfully.';
}

$clients = pms_client_rows($mysqli);
$folios = pms_folio_rows($mysqli);
$pms_admin_title = 'Manage Folios';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<h3>Folio Management</h3>
<p class="text-muted">This is the only place to edit folio number and account name. Saved values show on the dashboard, Master Register and the client login.</p>
<?php if ($message): ?><div class="alert alert-success"><?= pms_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= pms_h($error) ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><?= $edit ? 'Edit Folio' : 'Create Folio' ?></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
                    <div class="mb-3">
                        <label class="form-label">Assign to Client</label>
                        <select class="form-select" name="client_id" required>
                            <option value="">Select client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= (int) $client['id'] ?>" <?= $edit && (int) $edit['client_id'] === (int) $client['id'] ? 'selected' : '' ?>><?= pms_h($client['name'] . ' (' . $client['email'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Folio Number</label>
                        <input class="form-control" name="folio_number" required placeholder="50192834/88" value="<?= $edit ? pms_h($edit['folio_number']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input class="form-control" name="account_name" required value="<?= $edit ? pms_h($edit['account_name']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Type</label>
                        <input class="form-control" name="account_type" value="<?= $edit ? pms_h($edit['account_type']) : 'Individual' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <input class="form-control" name="status" value="<?= $edit ? pms_h($edit['status']) : 'Active' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Investment Start Date</label>
                        <input class="form-control" type="date" name="investment_start_date" value="<?= $edit ? pms_h($edit['investment_start_date']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Performance Remark</label>
                        <textarea class="form-control" name="performance_remark" rows="3"><?= $edit ? pms_h($edit['performance_remark']) : '' ?></textarea>
                    </div>
                    <button class="btn btn-primary"><?= $edit ? 'Update Folio' : 'Create Folio' ?></button>
                    <?php if ($edit): ?><a class="btn btn-secondary" href="admin_folios.php">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Folio</th>
                            <th>Client</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>Remark</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($folios as $row): ?>
                        <tr>
                            <td><?= pms_h($row['folio_number']) ?></td>
                            <td><?= pms_h($row['client_name']) ?></td>
                            <td><?= pms_h($row['account_name']) ?></td>
                            <td><?= pms_h($row['account_type']) ?></td>
                            <td><?= pms_h($row['status']) ?></td>
                            <td><?= pms_h(pms_date($row['investment_start_date'])) ?></td>
                            <td><?= pms_h($row['performance_remark']) ?></td>
                            <td><a class="btn btn-sm btn-primary" href="admin_folios.php?id=<?= (int) $row['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
