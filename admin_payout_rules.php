<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$message = '';
$error = '';
$edit = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $mysqli->prepare('SELECT * FROM pms_payout_rules WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $folio_id = $_POST['folio_id'] === '' ? null : (int) $_POST['folio_id'];
    $payout_percent = $_POST['payout_percent'];
    $payout_label = trim($_POST['payout_label']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!is_numeric($payout_percent) || (float) $payout_percent < 0 || $payout_label === '') {
        $error = 'A valid payout percent and label are required.';
    } elseif ($folio_id && !$mysqli->query('SELECT id FROM pms_folios WHERE id = ' . (int) $folio_id)->num_rows) {
        $error = 'Selected folio was not found.';
    } else {
        if ($id > 0) {
            if ($folio_id === null) {
                $stmt = $mysqli->prepare('UPDATE pms_payout_rules SET folio_id = NULL, payout_percent = ?, payout_label = ?, is_active = ? WHERE id = ?');
                $stmt->bind_param('dsii', $payout_percent, $payout_label, $is_active, $id);
            } else {
                $stmt = $mysqli->prepare('UPDATE pms_payout_rules SET folio_id = ?, payout_percent = ?, payout_label = ?, is_active = ? WHERE id = ?');
                $stmt->bind_param('idsii', $folio_id, $payout_percent, $payout_label, $is_active, $id);
            }
            if ($stmt->execute()) {
                pms_audit($mysqli, pms_admin_actor(), 'Updated payout rule', 'payout_rule', $id, '', $payout_percent . '% ' . $payout_label);
                header('Location: admin_payout_rules.php?ok=1');
                exit();
            }
            $error = 'Could not update payout rule.';
            $stmt->close();
        } else {
            if ($folio_id === null) {
                $stmt = $mysqli->prepare('INSERT INTO pms_payout_rules (folio_id, payout_percent, payout_label, is_active) VALUES (NULL, ?, ?, ?)');
                $stmt->bind_param('dsi', $payout_percent, $payout_label, $is_active);
            } else {
                $stmt = $mysqli->prepare('INSERT INTO pms_payout_rules (folio_id, payout_percent, payout_label, is_active) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('idsi', $folio_id, $payout_percent, $payout_label, $is_active);
            }
            if ($stmt->execute()) {
                pms_audit($mysqli, pms_admin_actor(), 'Created payout rule', 'payout_rule', $stmt->insert_id, '', $payout_percent . '% ' . $payout_label);
                header('Location: admin_payout_rules.php?ok=1');
                exit();
            }
            $error = 'Could not create payout rule.';
            $stmt->close();
        }
    }
}

if (isset($_GET['ok'])) {
    $message = 'Saved successfully.';
}

$preFolio = isset($_GET['folio_id']) ? (int) $_GET['folio_id'] : 0;
$folios = pms_folio_rows($mysqli);
$rules = array();
$result = $mysqli->query('SELECT r.*, f.folio_number, c.name AS client_name
    FROM pms_payout_rules r
    LEFT JOIN pms_folios f ON f.id = r.folio_id
    LEFT JOIN pms_clients c ON c.id = f.client_id
    ORDER BY r.id DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rules[] = $row;
    }
}

$pms_admin_title = 'Payout Rules';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<h3>Payout Rule Configuration</h3>
<p class="text-muted">The percentage shown on the client dashboard comes from these rules. It is not hardcoded in the website pages.</p>
<?php if ($message): ?><div class="alert alert-success"><?= pms_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= pms_h($error) ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><?= $edit ? 'Edit Rule' : 'Add Rule' ?></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
                    <div class="mb-3">
                        <label class="form-label">Folio</label>
                        <select class="form-select" name="folio_id">
                            <option value="">Global default</option>
                            <?php foreach ($folios as $folio): ?>
                                <option value="<?= (int) $folio['id'] ?>" <?= ($edit && (int) $edit['folio_id'] === (int) $folio['id']) || (!$edit && $preFolio === (int) $folio['id']) ? 'selected' : '' ?>>
                                    <?= pms_h($folio['client_name'] . ' — ' . $folio['folio_number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payout Percent</label>
                        <input class="form-control" type="number" step="0.0001" min="0" name="payout_percent" required value="<?= $edit ? pms_h($edit['payout_percent']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Label</label>
                        <input class="form-control" name="payout_label" required value="<?= $edit ? pms_h($edit['payout_label']) : 'Monthly Payout' ?>">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= !$edit || (int) $edit['is_active'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <button class="btn btn-primary"><?= $edit ? 'Update Rule' : 'Save Rule' ?></button>
                    <?php if ($edit): ?><a class="btn btn-secondary" href="admin_payout_rules.php">Cancel</a><?php endif; ?>
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
                            <th>Scope</th>
                            <th>Percent</th>
                            <th>Label</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rules as $row): ?>
                        <tr>
                            <td><?= $row['folio_number'] ? pms_h($row['client_name'] . ' / ' . $row['folio_number']) : 'Global default' ?></td>
                            <td><?= pms_h(rtrim(rtrim(number_format((float) $row['payout_percent'], 4), '0'), '.')) ?>%</td>
                            <td><?= pms_h($row['payout_label']) ?></td>
                            <td><?= (int) $row['is_active'] === 1 ? 'Yes' : 'No' ?></td>
                            <td><a class="btn btn-sm btn-primary" href="admin_payout_rules.php?id=<?= (int) $row['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
