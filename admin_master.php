<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_admin();

$folios = pms_folio_rows($mysqli);
$rows = array();
$totalInvested = 0;
$totalWithdrawn = 0;
$totalValue = 0;
foreach ($folios as $folio) {
    $summary = pms_portfolio_summary_by_folios($mysqli, array((int) $folio['id']));
    $rows[] = array('folio' => $folio, 'summary' => $summary);
    $totalInvested += $summary['invested'];
    $totalWithdrawn += $summary['withdrawn'];
    $totalValue += $summary['valuation'];
}
$totalReturn = $totalValue + $totalWithdrawn - $totalInvested;
$totalPct = $totalInvested > 0 ? ($totalReturn / $totalInvested) * 100 : 0;

$pms_admin_title = 'Master Folio Register';
require __DIR__ . '/includes/pms_admin_header.php';
?>

<h3>Master Folio Range Reference</h3>
<p class="text-muted">Live totals only. To change a folio name use Folios. To change money use Investments &amp; Withdrawals, Payouts or Holdings.</p>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Client / Entity Name</th>
                    <th>Login Email</th>
                    <th>Folio Number</th>
                    <th class="text-end">Investment (₹)</th>
                    <th class="text-end">Withdrawn / Payouts (₹)</th>
                    <th class="text-end">Current Valuation (₹)</th>
                    <th>Performance Remark</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= pms_h($row['folio']['account_name']) ?></td>
                    <td><?= pms_h($row['folio']['client_email'] ?? '') ?></td>
                    <td><?= pms_h($row['folio']['folio_number']) ?></td>
                    <td class="text-end"><?= pms_h(pms_inr($row['summary']['invested'])) ?></td>
                    <td class="text-end"><?= pms_h(pms_inr($row['summary']['withdrawn'])) ?></td>
                    <td class="text-end"><?= pms_h(pms_inr($row['summary']['valuation'])) ?></td>
                    <td><?= pms_h($row['folio']['performance_remark']) ?></td>
                </tr>
            <?php endforeach; ?>
                <tr class="table-secondary">
                    <td colspan="3"><strong>TOTAL PORTFOLIO</strong></td>
                    <td class="text-end"><strong><?= pms_h(pms_inr($totalInvested)) ?></strong></td>
                    <td class="text-end"><strong><?= pms_h(pms_inr($totalWithdrawn)) ?></strong></td>
                    <td class="text-end"><strong><?= pms_h(pms_inr($totalValue)) ?></strong></td>
                    <td><strong>Total Profit: <?= pms_h(pms_inr($totalReturn)) ?> (<?= pms_h(number_format($totalPct, 1)) ?>%)</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/pms_admin_footer.php'; ?>
