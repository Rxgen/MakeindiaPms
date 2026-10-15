<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$message = '';
$error = '';
$edit = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $mysqli->prepare('SELECT * FROM pms_transactions WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $folio_id = (int) $_POST['folio_id'];
    $transaction_type = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $transaction_date = $_POST['transaction_date'];
    $remarks = trim($_POST['remarks']);
    $status = $_POST['status'];

    if (!in_array($transaction_type, $PMS_TXN_TYPES, true)) {
        $error = 'Invalid transaction type.';
    } elseif ($folio_id < 1 || $amount === '' || $transaction_date === '') {
        $error = 'Folio, amount and date are required.';
    } elseif (!is_numeric($amount) || (float) $amount < 0) {
        $error = 'Amount must be a valid number.';
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
            $amount = number_format((float) $amount, 2, '.', '');
            $actor = pms_admin_actor();

            if ($id > 0) {
                $oldStmt = $mysqli->prepare('SELECT * FROM pms_transactions WHERE id = ? LIMIT 1');
                $oldStmt->bind_param('i', $id);
                $oldStmt->execute();
                $old = $oldStmt->get_result()->fetch_assoc();
                $oldStmt->close();

                if (!$old) {
                    $error = 'Transaction was not found.';
                } else {
                    $stmt = $mysqli->prepare('UPDATE pms_transactions SET client_id = ?, folio_id = ?, transaction_type = ?, amount = ?, transaction_date = ?, remarks = ?, status = ? WHERE id = ?');
                    $stmt->bind_param('iisdsssi', $client_id, $folio_id, $transaction_type, $amount, $transaction_date, $remarks, $status, $id);
                    if ($stmt->execute()) {
                        if ($transaction_type === 'Monthly Payout') {
                            pms_sync_payout_from_transaction($mysqli, $id, $client_id, $folio_id, $amount, $transaction_date, $remarks, $status);
                        }
                        pms_audit($mysqli, $actor, 'Updated transaction', 'transaction', $id, json_encode($old), json_encode($_POST));
                        header('Location: admin_transactions.php?ok=1');
                        exit();
                    }
                    $error = 'Could not update transaction.';
                    $stmt->close();
                }
            } else {
                $stmt = $mysqli->prepare('INSERT INTO pms_transactions (client_id, folio_id, transaction_type, amount, transaction_date, remarks, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('iisdssss', $client_id, $folio_id, $transaction_type, $amount, $transaction_date, $remarks, $status, $actor);
                    if ($stmt->execute()) {
                    $newId = $stmt->insert_id;
                    if ($transaction_type === 'Monthly Payout') {
                        pms_sync_payout_from_transaction($mysqli, $newId, $client_id, $folio_id, $amount, $transaction_date, $remarks, $status);
                    }
                    pms_audit($mysqli, $actor, 'Created transaction', 'transaction', $newId, '', $transaction_type . ' ' . $amount);
                    header('Location: admin_transactions.php?ok=1');
                    exit();
                }
                $error = 'Could not create transaction.';
                $stmt->close();
            }
        }
    }
}

if (isset($_GET['ok'])) {
    $message = 'Saved successfully.';
}

$preFolio = isset($_GET['folio_id']) ? (int) $_GET['folio_id'] : 0;
$preType = isset($_GET['type']) ? $_GET['type'] : '';
$folios = pms_folio_rows($mysqli);
$transactions = array();
$result = $mysqli->query('SELECT t.*, c.name AS client_name, f.folio_number
    FROM pms_transactions t
    INNER JOIN pms_clients c ON c.id = t.client_id
    INNER JOIN pms_folios f ON f.id = t.folio_id
    ORDER BY t.transaction_date DESC, t.id DESC
    LIMIT 200');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

$pms_admin_title = 'Manage Transactions';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<h3>Investment Management</h3>
<p class="text-muted">This is the only place to add or change investments and withdrawals. Pick the client folio, save, and the dashboard plus that client’s login update from the same record.</p>
<?php if ($message): ?><div class="alert alert-success"><?= pms_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= pms_h($error) ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><?= $edit ? 'Edit Transaction' : 'Add Transaction' ?></div>
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
                        <label class="form-label">Type</label>
                        <select class="form-select" name="transaction_type" required>
                            <?php foreach ($PMS_TXN_TYPES as $type): ?>
                                <option value="<?= pms_h($type) ?>" <?= ($edit && $edit['transaction_type'] === $type) || (!$edit && $preType === $type) ? 'selected' : '' ?>><?= pms_h($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (₹)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="amount" required value="<?= $edit ? pms_h($edit['amount']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input class="form-control" type="date" name="transaction_date" required value="<?= $edit ? pms_h($edit['transaction_date']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input class="form-control" name="remarks" value="<?= $edit ? pms_h($edit['remarks']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <?php foreach (array('Completed', 'Pending', 'Cancelled') as $st): ?>
                                <option value="<?= $st ?>" <?= $edit && $edit['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary"><?= $edit ? 'Update Transaction' : 'Save Transaction' ?></button>
                    <?php if ($edit): ?><a class="btn btn-secondary" href="admin_transactions.php">Cancel</a><?php endif; ?>
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
                            <th>Date</th>
                            <th>Client</th>
                            <th>Folio</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($transactions as $row): ?>
                        <tr>
                            <td><?= pms_h(pms_date($row['transaction_date'])) ?></td>
                            <td><?= pms_h($row['client_name']) ?></td>
                            <td><?= pms_h($row['folio_number']) ?></td>
                            <td><?= pms_h($row['transaction_type']) ?></td>
                            <td class="text-end"><?= pms_h(pms_inr($row['amount'])) ?></td>
                            <td><?= pms_h($row['status']) ?></td>
                            <td><a class="btn btn-sm btn-primary" href="admin_transactions.php?id=<?= (int) $row['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
