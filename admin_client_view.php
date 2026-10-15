<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$clientId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$client = pms_client_by_id($mysqli, $clientId);
if (!$client) {
    header('Location: dashboard.php');
    exit();
}

$folios = pms_get_folios($mysqli, $clientId);
$folio = $folios ? $folios[0] : null;
$folioId = $folio ? (int) $folio['id'] : 0;

$summary = $folioId ? pms_portfolio_summary_by_folios($mysqli, array($folioId)) : pms_portfolio_summary_by_folios($mysqli, array());
$investStats = $folioId ? pms_txn_stats($mysqli, $folioId, $PMS_INVESTMENT_TYPES) : array('count' => 0, 'total' => 0);
$withdrawStats = $folioId ? pms_txn_stats($mysqli, $folioId, array('Withdrawal')) : array('count' => 0, 'total' => 0);
$payoutStats = $folioId ? pms_txn_stats($mysqli, $folioId, array('Monthly Payout', 'Dividend/Payout')) : array('count' => 0, 'total' => 0);
$investments = $folioId ? pms_txn_rows_by_types($mysqli, $folioId, $PMS_INVESTMENT_TYPES) : array();
$withdrawals = $folioId ? pms_txn_rows_by_types($mysqli, $folioId, array('Withdrawal')) : array();
$payouts = $folioId ? pms_txn_rows_by_types($mysqli, $folioId, array('Monthly Payout', 'Dividend/Payout')) : array();
$effectiveRule = $folioId ? pms_get_payout_rule($mysqli, $folioId) : null;

$holdings = array();
if ($folioId) {
    $holdStmt = $mysqli->prepare('SELECT * FROM pms_holdings WHERE folio_id = ? AND is_active = 1 ORDER BY asset_name ASC');
    $holdStmt->bind_param('i', $folioId);
    $holdStmt->execute();
    $holdResult = $holdStmt->get_result();
    while ($row = $holdResult->fetch_assoc()) {
        $holdings[] = $row;
    }
    $holdStmt->close();
}

$pms_admin_title = $client['name'];
require __DIR__ . '/includes/pms_admin_header.php';
?>

<div class="mb-3">
    <a href="dashboard.php" class="text-dark">← Dashboard</a>
    <h3 class="mt-2 mb-1"><?= pms_h($client['name']) ?></h3>
    <div class="text-muted">View only. Change values from the matching admin tab — they appear here and on the client login.</div>
</div>

<div class="pms-stat-grid">
    <div class="pms-stat">
        <label>Investments added</label>
        <strong><?= (int) $investStats['count'] ?></strong>
        <div class="text-muted"><?= pms_h(pms_inr($investStats['total'])) ?></div>
    </div>
    <div class="pms-stat">
        <label>Withdrawals taken</label>
        <strong><?= (int) $withdrawStats['count'] ?> times</strong>
        <div class="text-muted"><?= pms_h(pms_inr($withdrawStats['total'])) ?></div>
    </div>
    <div class="pms-stat">
        <label>Payouts taken</label>
        <strong><?= (int) $payoutStats['count'] ?> times</strong>
        <div class="text-muted"><?= pms_h(pms_inr($payoutStats['total'])) ?></div>
    </div>
    <div class="pms-stat">
        <label>Current valuation</label>
        <strong><?= pms_h(pms_inr($summary['valuation'])) ?></strong>
        <div class="text-muted">Rule: <?= pms_h(pms_rule_label($effectiveRule)) ?></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Client details</div>
            <div class="card-body pms-detail">
                <div><span>Name</span><strong><?= pms_h($client['name']) ?></strong></div>
                <div><span>Email</span><strong><?= pms_h($client['email']) ?></strong></div>
                <div><span>Password</span><strong><code><?= pms_h($client['login_password']) ?></code></strong></div>
                <div><span>Mobile</span><strong><?= pms_h($client['mobile']) ?></strong></div>
                <div><span>Status</span><strong><?= pms_h($client['status']) ?></strong></div>
                <div><span>Login</span><strong><?= (int) $client['is_active'] === 1 ? 'Active' : 'Inactive' ?></strong></div>
                <div><span>Password status</span><strong><?= pms_client_uses_default_password($client) ? 'Default / pending change' : 'Changed by client' ?></strong></div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">Folio details</div>
            <div class="card-body pms-detail">
                <?php if (!$folio): ?>
                    <p class="text-muted mb-0">No folio assigned.</p>
                <?php else: ?>
                    <div><span>Folio number</span><strong><?= pms_h($folio['folio_number']) ?></strong></div>
                    <div><span>Account name</span><strong><?= pms_h($folio['account_name']) ?></strong></div>
                    <div><span>Type</span><strong><?= pms_h($folio['account_type']) ?></strong></div>
                    <div><span>Folio status</span><strong><?= pms_h($folio['status']) ?></strong></div>
                    <div><span>Start date</span><strong><?= pms_h(pms_date($folio['investment_start_date'])) ?></strong></div>
                    <div><span>Remark</span><strong><?= pms_h($folio['performance_remark']) ?></strong></div>
                    <div><span>Payout rule</span><strong><?= pms_h(pms_rule_label($effectiveRule)) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="pms-tabs">
            <button type="button" class="active" data-tab-target="tab-invest">Investments <span class="pms-count"><?= (int) $investStats['count'] ?></span></button>
            <button type="button" data-tab-target="tab-withdraw">Withdrawals <span class="pms-count"><?= (int) $withdrawStats['count'] ?></span></button>
            <button type="button" data-tab-target="tab-payout">Payouts <span class="pms-count"><?= (int) $payoutStats['count'] ?></span></button>
            <button type="button" data-tab-target="tab-hold">Holdings</button>
        </div>

        <div class="pms-tab-panel active" id="tab-invest">
            <div class="card">
                <div class="card-header">Investments added for this client</div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Date</th><th>Type</th><th class="text-end">Amount</th><th>Remarks</th></tr></thead>
                        <tbody>
                        <?php if ($investments): ?>
                            <?php foreach ($investments as $row): ?>
                                <tr>
                                    <td><?= pms_h(pms_date($row['transaction_date'])) ?></td>
                                    <td><?= pms_h($row['transaction_type']) ?></td>
                                    <td class="text-end"><?= pms_h(pms_inr($row['amount'])) ?></td>
                                    <td><?= pms_h($row['remarks']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="2"><strong>Total invested</strong></td>
                                <td class="text-end"><strong><?= pms_h(pms_inr($investStats['total'])) ?></strong></td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <tr><td colspan="4">No investments recorded yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pms-tab-panel" id="tab-withdraw">
            <div class="card">
                <div class="card-header">Withdrawals — <?= (int) $withdrawStats['count'] ?> time(s)</div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Date</th><th class="text-end">Amount</th><th>Remarks</th></tr></thead>
                        <tbody>
                        <?php if ($withdrawals): ?>
                            <?php foreach ($withdrawals as $row): ?>
                                <tr>
                                    <td><?= pms_h(pms_date($row['transaction_date'])) ?></td>
                                    <td class="text-end"><?= pms_h(pms_inr($row['amount'])) ?></td>
                                    <td><?= pms_h($row['remarks']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td><strong>Total withdrawn</strong></td>
                                <td class="text-end"><strong><?= pms_h(pms_inr($withdrawStats['total'])) ?></strong></td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <tr><td colspan="3">No withdrawals recorded for this client.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pms-tab-panel" id="tab-payout">
            <div class="card">
                <div class="card-header">Payouts taken — <?= (int) $payoutStats['count'] ?> time(s)</div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Date</th><th>Type</th><th class="text-end">Amount</th><th>Remarks</th></tr></thead>
                        <tbody>
                        <?php if ($payouts): ?>
                            <?php foreach ($payouts as $row): ?>
                                <tr>
                                    <td><?= pms_h(pms_date($row['transaction_date'])) ?></td>
                                    <td><?= pms_h($row['transaction_type']) ?></td>
                                    <td class="text-end"><?= pms_h(pms_inr($row['amount'])) ?></td>
                                    <td><?= pms_h($row['remarks']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="2"><strong>Total payouts</strong></td>
                                <td class="text-end"><strong><?= pms_h(pms_inr($payoutStats['total'])) ?></strong></td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <tr><td colspan="4">No payouts recorded for this client.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pms-tab-panel" id="tab-hold">
            <div class="card">
                <div class="card-header">Current holdings</div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Asset</th><th class="text-end">Quantity</th><th class="text-end">Current value</th><th>Valuation date</th></tr></thead>
                        <tbody>
                        <?php if ($holdings): ?>
                            <?php foreach ($holdings as $row): ?>
                                <tr>
                                    <td><?= pms_h($row['asset_name']) ?></td>
                                    <td class="text-end"><?= pms_h(number_format((float) $row['quantity'], 4)) ?></td>
                                    <td class="text-end"><?= pms_h(pms_inr($row['current_value'])) ?></td>
                                    <td><?= pms_h(pms_date($row['valuation_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No holdings recorded for this client.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
