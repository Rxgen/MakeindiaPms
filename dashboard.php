<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$clients = pms_client_rows($mysqli);
$cards = array();
$totalInvested = 0;
$totalWithdrawn = 0;
$totalValue = 0;
$totalPayouts = 0;

foreach ($clients as $client) {
    $folios = pms_get_folios($mysqli, (int) $client['id']);
    $folio = $folios ? $folios[0] : null;
    $folioId = $folio ? (int) $folio['id'] : 0;
    $summary = $folioId ? pms_portfolio_summary_by_folios($mysqli, array($folioId)) : pms_portfolio_summary_by_folios($mysqli, array());
    $invest = $folioId ? pms_txn_stats($mysqli, $folioId, $PMS_INVESTMENT_TYPES) : array('count' => 0, 'total' => 0);
    $withdraw = $folioId ? pms_txn_stats($mysqli, $folioId, array('Withdrawal')) : array('count' => 0, 'total' => 0);
    $payouts = $folioId ? pms_txn_stats($mysqli, $folioId, array('Monthly Payout', 'Dividend/Payout')) : array('count' => 0, 'total' => 0);
    $rule = $folioId ? pms_get_payout_rule($mysqli, $folioId) : null;

    $totalInvested += $summary['invested'];
    $totalWithdrawn += $summary['withdrawn'];
    $totalValue += $summary['valuation'];
    $totalPayouts += $payouts['count'];

    $cards[] = array(
        'client' => $client,
        'folio' => $folio,
        'summary' => $summary,
        'invest' => $invest,
        'withdraw' => $withdraw,
        'payouts' => $payouts,
        'rule' => $rule,
    );
}

$pmsQuery = $mysqli->query('SHOW TABLES LIKE "pms_entries"');
$legacyRows = array();
if ($pmsQuery && $pmsQuery->num_rows > 0) {
    $legacy = $mysqli->query('SELECT * FROM pms_entries ORDER BY id DESC LIMIT 8');
    if ($legacy) {
        while ($row = $legacy->fetch_assoc()) {
            $legacyRows[] = $row;
        }
    }
}

$pms_admin_title = 'Admin Dashboard';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<div class="pms-stat-grid">
    <div class="pms-stat">
        <label>Active Clients</label>
        <strong><?= count($cards) ?></strong>
    </div>
    <div class="pms-stat">
        <label>Total Invested</label>
        <strong><?= pms_h(pms_inr($totalInvested)) ?></strong>
    </div>
    <div class="pms-stat">
        <label>Total Withdrawn / Payouts</label>
        <strong><?= pms_h(pms_inr($totalWithdrawn)) ?></strong>
    </div>
    <div class="pms-stat">
        <label>Current Valuation</label>
        <strong><?= pms_h(pms_inr($totalValue)) ?></strong>
    </div>
</div>

<div class="pms-toolbar">
    <div>
        <h3 class="mb-1">Client Portfolios</h3>
        <div class="text-muted">Click View details to see that client’s investments, withdrawals and payouts. Edit only from the matching tab.</div>
    </div>
    <input type="search" id="clientSearch" placeholder="Search client, email or folio">
</div>

<div class="pms-client-grid">
<?php foreach ($cards as $card): ?>
    <?php
    $c = $card['client'];
    $f = $card['folio'];
    $search = strtolower($c['name'] . ' ' . $c['email'] . ' ' . ($f ? $f['folio_number'] . ' ' . $f['account_name'] : ''));
    ?>
    <article class="pms-client-card" data-client-card data-search="<?= pms_h($search) ?>">
        <header>
            <div>
                <h3><?= pms_h($c['name']) ?></h3>
                <div class="meta"><?= pms_h($c['email']) ?></div>
                <div class="meta">Folio: <strong><?= $f ? pms_h($f['folio_number']) : 'Not assigned' ?></strong></div>
            </div>
            <div>
                <span class="pms-pill <?= (int) $c['is_active'] === 1 ? 'live' : 'off' ?>"><?= (int) $c['is_active'] === 1 ? 'Login Active' : 'Inactive' ?></span>
            </div>
        </header>
        <div class="pms-mini-metrics">
            <div>
                <span>Investments</span>
                <b><?= (int) $card['invest']['count'] ?> entries</b>
                <div class="meta"><?= pms_h(pms_inr($card['invest']['total'])) ?></div>
            </div>
            <div>
                <span>Withdrawals</span>
                <b><?= (int) $card['withdraw']['count'] ?> times</b>
                <div class="meta"><?= pms_h(pms_inr($card['withdraw']['total'])) ?></div>
            </div>
            <div>
                <span>Payouts taken</span>
                <b><?= (int) $card['payouts']['count'] ?> times</b>
                <div class="meta"><?= pms_h(pms_inr($card['payouts']['total'])) ?></div>
            </div>
        </div>
        <footer>
            <div class="meta">Rule: <strong><?= pms_h(pms_rule_label($card['rule'])) ?></strong></div>
            <div>
                <a class="btn-india" href="admin_client_view.php?id=<?= (int) $c['id'] ?>">View details</a>
            </div>
        </footer>
    </article>
<?php endforeach; ?>
</div>

<?php if ($legacyRows): ?>
<div class="card mt-4">
    <div class="card-header">Legacy PMS Entries</div>
    <div class="card-body table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>PMS No</th>
                    <th class="text-end">Principal</th>
                    <th class="text-end">Monthly Payout</th>
                    <th class="text-end">Current Value</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($legacyRows as $row): ?>
                <tr>
                    <td><?= pms_h($row['client_name']) ?></td>
                    <td><?= pms_h($row['pms_no']) ?></td>
                    <td class="text-end"><?= pms_h(pms_inr($row['principal_investment'])) ?></td>
                    <td class="text-end"><?= pms_h(pms_inr($row['monthly_payout'])) ?></td>
                    <td class="text-end"><?= pms_h(pms_inr($row['current_value'])) ?></td>
                    <td><a class="btn btn-sm btn-dark" href="edit_pms.php?id=<?= (int) $row['id'] ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a href="add_pms.php" class="btn-ghost">+ Add Legacy PMS Entry</a>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
