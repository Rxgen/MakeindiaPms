<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$message = '';
$error = '';
$edit = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $mysqli->prepare('SELECT * FROM pms_holdings WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $folio_id = (int) $_POST['folio_id'];
    $asset_name = trim($_POST['asset_name']);
    $quantity = $_POST['quantity'];
    $average_cost = $_POST['average_cost'];
    $current_price = $_POST['current_price'];
    $current_value = trim($_POST['current_value']);
    $valuation_date = $_POST['valuation_date'] !== '' ? $_POST['valuation_date'] : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($folio_id < 1 || $asset_name === '' || !is_numeric($quantity) || !is_numeric($average_cost) || !is_numeric($current_price)) {
        $error = 'Folio, asset, quantity, average cost and current price are required.';
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
            if ($current_value === '' || !is_numeric($current_value)) {
                $current_value = (float) $quantity * (float) $current_price;
            }
            $current_value = number_format((float) $current_value, 2, '.', '');

            if ($id > 0) {
                $oldStmt = $mysqli->prepare('SELECT * FROM pms_holdings WHERE id = ? LIMIT 1');
                $oldStmt->bind_param('i', $id);
                $oldStmt->execute();
                $old = $oldStmt->get_result()->fetch_assoc();
                $oldStmt->close();

                $stmt = $mysqli->prepare('UPDATE pms_holdings SET client_id = ?, folio_id = ?, asset_name = ?, quantity = ?, average_cost = ?, current_price = ?, current_value = ?, valuation_date = ?, is_active = ? WHERE id = ?');
                $stmt->bind_param('iisddddsii', $client_id, $folio_id, $asset_name, $quantity, $average_cost, $current_price, $current_value, $valuation_date, $is_active, $id);
                if ($stmt->execute()) {
                    $oldVal = $old ? $old['current_value'] : '';
                    pms_audit($mysqli, pms_admin_actor(), 'Updated holding valuation', 'holding', $id, 'Old: ' . pms_inr($oldVal), 'New: ' . pms_inr($current_value));
                    header('Location: admin_holdings.php?ok=1');
                    exit();
                }
                $error = 'Could not update holding.';
                $stmt->close();
            } else {
                $stmt = $mysqli->prepare('INSERT INTO pms_holdings (client_id, folio_id, asset_name, quantity, average_cost, current_price, current_value, valuation_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('iisddddsi', $client_id, $folio_id, $asset_name, $quantity, $average_cost, $current_price, $current_value, $valuation_date, $is_active);
                if ($stmt->execute()) {
                    $newId = $stmt->insert_id;
                    pms_audit($mysqli, pms_admin_actor(), 'Created holding', 'holding', $newId, '', $asset_name . ' ' . pms_inr($current_value));
                    header('Location: admin_holdings.php?ok=1');
                    exit();
                }
                $error = 'Could not create holding.';
                $stmt->close();
            }
        }
    }
}

if (isset($_GET['ok'])) {
    $message = 'Saved successfully.';
}

$folios = pms_folio_rows($mysqli);
$holdings = array();
$result = $mysqli->query('SELECT h.*, c.name AS client_name, f.folio_number
    FROM pms_holdings h
    INNER JOIN pms_clients c ON c.id = h.client_id
    INNER JOIN pms_folios f ON f.id = h.folio_id
    ORDER BY h.id DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $holdings[] = $row;
    }
}

$pms_admin_title = 'Manage Holdings';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<h3>Portfolio Management</h3>
<?php if ($message): ?><div class="alert alert-success"><?= pms_h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= pms_h($error) ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><?= $edit ? 'Edit Holding' : 'Add Holding' ?></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
                    <div class="mb-3">
                        <label class="form-label">Folio</label>
                        <select class="form-select" name="folio_id" required>
                            <option value="">Select folio</option>
                            <?php foreach ($folios as $folio): ?>
                                <option value="<?= (int) $folio['id'] ?>" <?= $edit && (int) $edit['folio_id'] === (int) $folio['id'] ? 'selected' : '' ?>>
                                    <?= pms_h($folio['client_name'] . ' — ' . $folio['folio_number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asset</label>
                        <input class="form-control" name="asset_name" required value="<?= $edit ? pms_h($edit['asset_name']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input class="form-control" type="number" step="0.0001" name="quantity" required value="<?= $edit ? pms_h($edit['quantity']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Average Cost</label>
                        <input class="form-control" type="number" step="0.01" name="average_cost" required value="<?= $edit ? pms_h($edit['average_cost']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Price</label>
                        <input class="form-control" type="number" step="0.01" name="current_price" required value="<?= $edit ? pms_h($edit['current_price']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Valuation</label>
                        <input class="form-control" type="number" step="0.01" name="current_value" value="<?= $edit ? pms_h($edit['current_value']) : '' ?>" placeholder="Leave blank to use quantity × price">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valuation Date</label>
                        <input class="form-control" type="date" name="valuation_date" value="<?= $edit ? pms_h($edit['valuation_date']) : '' ?>">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= !$edit || (int) $edit['is_active'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active holding</label>
                    </div>
                    <button class="btn btn-primary"><?= $edit ? 'Update Holding' : 'Save Holding' ?></button>
                    <?php if ($edit): ?><a class="btn btn-secondary" href="admin_holdings.php">Cancel</a><?php endif; ?>
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
                            <th>Client</th>
                            <th>Folio</th>
                            <th>Asset</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Current Value</th>
                            <th>Valuation</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($holdings as $row): ?>
                        <tr>
                            <td><?= pms_h($row['client_name']) ?></td>
                            <td><?= pms_h($row['folio_number']) ?></td>
                            <td><?= pms_h($row['asset_name']) ?></td>
                            <td class="text-end"><?= pms_h(number_format((float) $row['quantity'], 4)) ?></td>
                            <td class="text-end"><?= pms_h(pms_inr($row['current_value'])) ?></td>
                            <td><?= pms_h(pms_date($row['valuation_date'])) ?></td>
                            <td><a class="btn btn-sm btn-primary" href="admin_holdings.php?id=<?= (int) $row['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
