<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$message = '';
$error = '';
$edit = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $mysqli->prepare('SELECT * FROM pms_payouts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $folio_id = (int) $_POST['folio_id'];
    $payout_month = trim($_POST['payout_month']);
    $payout_amount = $_POST['payout_amount'];
    $status = $_POST['status'] === 'Pending' ? 'Pending' : 'Paid';
    $payment_date = $_POST['payment_date'] !== '' ? $_POST['payment_date'] : null;
    $remarks = trim($_POST['remarks']);

    if ($folio_id < 1 || $payout_month === '' || !is_numeric($payout_amount) || (float) $payout_amount < 0) {
        $error = 'Folio, month and a valid amount are required.';
    } else {
        $folioStmt = $mysqli->prepare('SELECT id, client_id FROM pms_folios WHERE id = ? LIMIT 1');
        $folioStmt->bind_param('i', $folio_id);
        $folioStmt->execute();
        $folio = $folioStmt->get_result()->fetch_assoc();
        $folioStmt->close();

        if (!$folio) {
            $error = 'Selected folio was not found.';
        } else {
            $client_id = (int) $folio['client_id'];
            $payout_amount = number_format((float) $payout_amount, 2, '.', '');
            $txnStatus = $status === 'Paid' ? 'Completed' : 'Pending';
            $txnDate = $payment_date ? $payment_date : ($payout_month . '-01');
            $actor = pms_admin_actor();

            if ($id > 0) {
                $oldStmt = $mysqli->prepare('SELECT * FROM pms_payouts WHERE id = ? LIMIT 1');
                $oldStmt->bind_param('i', $id);
                $oldStmt->execute();
                $old = $oldStmt->get_result()->fetch_assoc();
                $oldStmt->close();

                $stmt = $mysqli->prepare('UPDATE pms_payouts SET client_id = ?, folio_id = ?, payout_month = ?, payout_amount = ?, status = ?, payment_date = ?, remarks = ? WHERE id = ?');
                $stmt->bind_param('iisdsssi', $client_id, $folio_id, $payout_month, $payout_amount, $status, $payment_date, $remarks, $id);
                if ($stmt->execute()) {
                    if ($old && !empty($old['transaction_id'])) {
                        $txnId = (int) $old['transaction_id'];
                        $upd = $mysqli->prepare('UPDATE pms_transactions SET client_id = ?, folio_id = ?, amount = ?, transaction_date = ?, remarks = ?, status = ? WHERE id = ? AND client_id = ?');
                        $upd->bind_param('iidsssii', $client_id, $folio_id, $payout_amount, $txnDate, $remarks, $txnStatus, $txnId, $old['client_id']);
                        $upd->execute();
                        $upd->close();
                    }
                    pms_audit($mysqli, $actor, 'Updated payout', 'payout', $id, json_encode($old), json_encode($_POST));
                    header('Location: admin_payouts.php?ok=1');
                    exit();
                }
                $error = 'Could not update payout.';
                $stmt->close();
            } else {
                $txn = $mysqli->prepare('INSERT INTO pms_transactions (client_id, folio_id, transaction_type, amount, transaction_date, remarks, status, created_by) VALUES (?, ?, "Monthly Payout", ?, ?, ?, ?, ?)');
                $txn->bind_param('iidssss', $client_id, $folio_id, $payout_amount, $txnDate, $remarks, $txnStatus, $actor);
                $txn->execute();
                $transactionId = $txn->insert_id;
                $txn->close();

                $stmt = $mysqli->prepare('INSERT INTO pms_payouts (client_id, folio_id, transaction_id, payout_month, payout_amount, status, payment_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('iiisdsss', $client_id, $folio_id, $transactionId, $payout_month, $payout_amount, $status, $payment_date, $remarks);
                if ($stmt->execute()) {
                    $newId = $stmt->insert_id;
                    pms_audit($mysqli, $actor, 'Recorded payout', 'payout', $newId, '', $payout_month . ' ' . $payout_amount . ' ' . $status);
                    header('Location: admin_payouts.php?ok=1');
                    exit();
                }
                $error = 'Could not record payout.';
                $stmt->close();
            }
        }
    }
}

if (isset($_GET['ok'])) {
    $message = 'Saved successfully.';
}

$preFolio = isset($_GET['folio_id']) ? (int) $_GET['folio_id'] : 0;
$folios = pms_folio_rows($mysqli);
$payouts = array();
$result = $mysqli->query('SELECT p.*, c.name AS client_name, f.folio_number
    FROM pms_payouts p
    INNER JOIN pms_clients c ON c.id = p.client_id
    INNER JOIN pms_folios f ON f.id = p.folio_id
    ORDER BY p.payout_month DESC, p.id DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $payouts[] = $row;
    }
}

$pms_admin_title = 'Manage Payouts';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<h3>Payout Management</h3>
<p class="text-muted">This is the only place to add a payout for a client. It shows on that client’s detail view and login with the same date and amount.</p>
<?php if ($message): ?><div class="alert alert-success"><?= pms_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= pms_h($error) ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><?= $edit ? 'Edit Payout' : 'Record Monthly Payout' ?></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
                    <div class="mb-3">
                        <label class="form-label">Folio</label>
                        <select class="form-select" name="folio_id" required>
                            <option value="">Select folio</option>
                            <?php foreach ($folios as $folio): ?>
                                <option value="<?= (int) $folio['id'] ?>" <?= ($edit && (int) $edit['folio_id'] === (int) $folio['id']) || (!$edit && $preFolio === (int) $folio['id']) ? 'selected' : '' ?>>
                                    <?= pms_h($folio['client_name'] . ' — ' . $folio['folio_number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Month</label>
                        <input class="form-control" type="month" name="payout_month" required value="<?= $edit ? pms_h($edit['payout_month']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payout Amount (₹)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="payout_amount" required value="<?= $edit ? pms_h($edit['payout_amount']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="Paid" <?= $edit && $edit['status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="Pending" <?= $edit && $edit['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input class="form-control" type="date" name="payment_date" value="<?= $edit ? pms_h($edit['payment_date']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input class="form-control" name="remarks" value="<?= $edit ? pms_h($edit['remarks']) : '' ?>">
                    </div>
                    <button class="btn btn-primary"><?= $edit ? 'Update Payout' : 'Save Payout' ?></button>
                    <?php if ($edit): ?><a class="btn btn-secondary" href="admin_payouts.php">Cancel</a><?php endif; ?>
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
                            <th>Month</th>
                            <th>Client</th>
                            <th>Folio</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payouts as $row): ?>
                        <tr>
                            <td><?= pms_h(pms_month_label($row['payout_month'])) ?></td>
                            <td><?= pms_h($row['client_name']) ?></td>
                            <td><?= pms_h($row['folio_number']) ?></td>
                            <td class="text-end"><?= pms_h(pms_inr($row['payout_amount'])) ?></td>
                            <td><?= pms_h($row['status']) ?></td>
                            <td><?= pms_h(pms_date($row['payment_date'])) ?></td>
                            <td><a class="btn btn-sm btn-primary" href="admin_payouts.php?id=<?= (int) $row['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
