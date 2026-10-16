<?php
require_once __DIR__ . '/includes/pms_init.php';
pms_require_client();

$clientId = (int) $_SESSION['client_id'];
$client = pms_client_by_id($mysqli, $clientId);

if (!$client || (int) $client['is_active'] !== 1) {
    unset($_SESSION['client_id'], $_SESSION['client_name'], $_SESSION['client_email'], $_SESSION['client_folio_number']);
    header('Location: client_login.php');
    exit();
}

$folios = pms_get_folios($mysqli, $clientId);
$requestedFolioId = isset($_GET['folio_id']) ? (int) $_GET['folio_id'] : 0;
$selectedFolio = null;

if ($requestedFolioId > 0) {
    $selectedFolio = pms_get_folio($mysqli, $requestedFolioId, $clientId);
    if (!$selectedFolio) {
        header('Location: client_dashboard.php');
        exit();
    }
} else {
    $selectedFolio = pms_default_folio($folios);
}

$folioId = $selectedFolio ? (int) $selectedFolio['id'] : 0;
$folioNumber = $selectedFolio ? $selectedFolio['folio_number'] : '';
pms_set_client_session($client, $selectedFolio);
$pms_header_folio = $folioNumber;

$summary = pms_portfolio_summary($mysqli, $clientId, $folioId ?: null);
$rule = $folioId ? pms_get_payout_rule($mysqli, $folioId) : null;

$transactions = array();
if ($folioId) {
    $txnStmt = $mysqli->prepare('SELECT * FROM pms_transactions WHERE folio_id = ? ORDER BY transaction_date DESC, id DESC');
    $txnStmt->bind_param('i', $folioId);
    $txnStmt->execute();
    $txnResult = $txnStmt->get_result();
    while ($row = $txnResult->fetch_assoc()) {
        $transactions[] = $row;
    }
    $txnStmt->close();
}

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

$holdingLabels = array();
$holdingValues = array();
foreach ($holdings as $holding) {
    $holdingLabels[] = $holding['asset_name'];
    $holdingValues[] = (float) $holding['current_value'];
}

$stillDeployed = max($summary['invested'] - $summary['withdrawn'], 0);
$chartPayload = array(
    'holdings' => array(
        'labels' => $holdingLabels,
        'values' => $holdingValues,
    ),
    'capital' => array(
        'labels' => array('Capital still deployed', 'Withdrawn / Payouts'),
        'values' => array($stillDeployed, (float) $summary['withdrawn']),
    ),
);
$chartJson = json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Portfolio Portal | MAKE IN INDIA CAPITAL PMS</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="client_portal.css">
    <script defer src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="pms-portal">
    <?php require __DIR__ . '/includes/site_header.php'; ?>

    <header class="pms-header">
        <div class="pms-brand">
            MAKE IN INDIA CAPITAL PMS
            <span class="pms-brand-sep">|</span>
            <em>CLIENT PORTFOLIO PORTAL</em>
        </div>
        <div class="pms-header-right">
            Welcome, <?= pms_h($client['name']) ?>
            <span class="pms-sep">|</span>
            Folio No: <?= $folioNumber !== '' ? pms_h($folioNumber) : '—' ?>
            <span class="pms-sep">|</span>
            <a class="pms-logout-inline" href="client_change_password.php">Change Password</a>
            <span class="pms-sep">|</span>
            <a class="pms-logout-inline" href="client_logout.php">Logout</a>
        </div>
        <div class="pms-mi">MI</div>
    </header>

    <div class="pms-wrap">
        <?php if (isset($_GET['pwd'])): ?>
            <div class="pms-ok" style="margin-bottom:14px;">Password updated. You can continue using your portfolio.</div>
        <?php elseif (pms_client_uses_default_password($client)): ?>
            <div class="pms-alert" style="margin-bottom:14px;">
                You are still using the default password.
                <a href="client_change_password.php" style="color:#8a0000;font-weight:700;">Change it here</a>.
            </div>
        <?php endif; ?>
        <div class="pms-identity pms-identity-hero">
            <div>
                <span>Welcome</span>
                <strong><?= pms_h($client['name']) ?></strong>
            </div>
            <div>
                <span>Registered Email</span>
                <strong><?= pms_h($client['email']) ?></strong>
            </div>
            <div>
                <span>Folio Number</span>
                <strong><?= $folioNumber !== '' ? pms_h($folioNumber) : '—' ?></strong>
            </div>
            <div>
                <span>Account Status</span>
                <strong><?= pms_h($client['status']) ?></strong>
            </div>
        </div>

        <?php if (count($folios) > 1): ?>
            <form class="folio-switch" method="GET" action="client_dashboard.php">
                <label for="folio_id">Switch Folio</label>
                <select id="folio_id" name="folio_id" onchange="this.form.submit()">
                    <?php foreach ($folios as $folio): ?>
                        <option value="<?= (int) $folio['id'] ?>" <?= $folioId === (int) $folio['id'] ? 'selected' : '' ?>>
                            <?= pms_h($folio['account_name'] . ' — ' . $folio['folio_number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>

        <section class="pms-section">
            <h2>Portfolio Metrics</h2>
            <div class="pms-metrics">
                <div class="pms-metric">
                    <h3>Total Invested Principal</h3>
                    <div class="value"><?= pms_h(pms_inr($summary['invested'])) ?></div>
                    <div class="hint">Capital Deployed</div>
                </div>
                <div class="pms-metric">
                    <h3>Total Withdrawn / Payouts</h3>
                    <div class="value"><?= pms_h(pms_inr($summary['withdrawn'])) ?></div>
                    <div class="hint">Cumulative Monthly Payouts</div>
                </div>
                <div class="pms-metric">
                    <h3>Current Valuation</h3>
                    <div class="value"><?= pms_h(pms_inr($summary['valuation'])) ?></div>
                    <div class="hint">Mark-to-Market Value</div>
                </div>
            </div>
        </section>

        <section class="pms-section">
            <h2>Portfolio Insights</h2>
            <div class="body pms-insight-grid">
                <div class="pms-chart-card">
                    <h3>Holdings Allocation</h3>
                    <?php if ($holdingValues): ?>
                        <div class="pms-chart-wrap"><canvas id="holdingsChart"></canvas></div>
                    <?php else: ?>
                        <p class="pms-chart-empty">Holdings will appear here after they are added for this folio.</p>
                    <?php endif; ?>
                </div>
                <div class="pms-chart-card">
                    <h3>Capital Movement</h3>
                    <?php if ($summary['invested'] > 0 || $summary['withdrawn'] > 0): ?>
                        <div class="pms-chart-wrap"><canvas id="capitalChart"></canvas></div>
                    <?php else: ?>
                        <p class="pms-chart-empty">Investment and withdrawal figures will appear here.</p>
                    <?php endif; ?>
                </div>
                <div class="pms-chart-card pms-insight-facts">
                    <h3>Performance Snapshot</h3>
                    <div class="pms-fact">
                        <span>Total Return</span>
                        <strong><?= pms_h(pms_inr($summary['total_return'])) ?></strong>
                    </div>
                    <div class="pms-fact">
                        <span>Return</span>
                        <strong><?= pms_h(number_format($summary['return_pct'], 2)) ?>%</strong>
                    </div>
                    <div class="pms-fact">
                        <span>Payout Rule</span>
                        <strong><?= $rule ? pms_h(pms_rule_label($rule)) : 'Not set' ?></strong>
                    </div>
                    <div class="pms-fact">
                        <span>Last Valuation</span>
                        <strong><?= pms_h(pms_date($summary['last_valuation'])) ?></strong>
                    </div>
                    <div class="pms-fact">
                        <span>Remark</span>
                        <strong><?= $selectedFolio && $selectedFolio['performance_remark'] ? pms_h($selectedFolio['performance_remark']) : '—' ?></strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="pms-section">
            <h2>Investment &amp; Withdrawal Ledger</h2>
            <div class="body table-wrap">
                <table class="pms-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="num">Amount</th>
                            <th>Remarks / Folio Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($transactions): ?>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?= pms_h(pms_date($txn['transaction_date'])) ?></td>
                                <td><?= pms_h($txn['transaction_type']) ?></td>
                                <td class="num"><?= pms_h(pms_inr($txn['amount'])) ?></td>
                                <td><?= pms_h(pms_ledger_remark($txn, $folioNumber, $rule)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No transactions found for this folio.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($holdings): ?>
        <section class="pms-section">
            <h2>Current Holdings</h2>
            <div class="body table-wrap">
                <table class="pms-table">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th class="num">Quantity</th>
                            <th class="num">Avg Cost</th>
                            <th class="num">Current Price</th>
                            <th class="num">Current Value</th>
                            <th>Valuation Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($holdings as $holding): ?>
                            <tr>
                                <td><?= pms_h($holding['asset_name']) ?></td>
                                <td class="num"><?= pms_h(number_format((float) $holding['quantity'], 4)) ?></td>
                                <td class="num"><?= pms_h(pms_inr($holding['average_cost'])) ?></td>
                                <td class="num"><?= pms_h(pms_inr($holding['current_price'])) ?></td>
                                <td class="num"><?= pms_h(pms_inr($holding['current_value'])) ?></td>
                                <td><?= pms_h(pms_date($holding['valuation_date'])) ?></td>
                                <td>
                                    <button type="button" class="pms-withdraw-btn" data-open-withdraw>Withdraw</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <div class="pms-modal" id="withdrawModal" hidden>
        <div class="pms-modal-backdrop" data-close-withdraw></div>
        <div class="pms-modal-box" role="dialog" aria-modal="true" aria-labelledby="withdrawModalTitle">
            <button type="button" class="pms-modal-close" data-close-withdraw aria-label="Close">&times;</button>
            <div class="pms-modal-icon" aria-hidden="true">!</div>
            <h3 id="withdrawModalTitle">Withdrawal Not Allowed</h3>
            <p class="pms-modal-lead">
                You have invested in an ELSS (Equity Linked Savings Scheme) fund.
                As per tax regulations, there is a <strong class="pms-lockin">3 years lock-in period</strong> for ELSS investments.
            </p>
            <div class="pms-modal-note">
                From the investment start date to the last day, till the investment becomes 3 years older in the system, you cannot withdraw the money.
            </div>
            <p class="pms-modal-assist">
                For better assistance, please connect your Relationship Manager on the numbers given below or write to us.
            </p>
            <div class="pms-modal-contacts">
                <div class="pms-modal-contact">
                    <i class="fas fa-phone"></i>
                    <span>+91 2247437863 / 1800 212 4592</span>
                </div>
                <a class="pms-modal-contact pms-modal-email" href="mailto:investor@makeindiapms.com">
                    <i class="fas fa-envelope"></i>
                    <span>investor@makeindiapms.com</span>
                </a>
            </div>
            <button type="button" class="pms-modal-ok" data-close-withdraw>OK, Got It</button>
        </div>
    </div>

    <script>
    window.PMS_CHARTS = <?= $chartJson ?>;
    </script>
    <script src="client_charts.js"></script>
    <script>
    (function () {
        var modal = document.getElementById('withdrawModal');
        if (!modal) {
            return;
        }
        function openModal() {
            modal.hidden = false;
            document.body.classList.add('pms-modal-open');
        }
        function closeModal() {
            modal.hidden = true;
            document.body.classList.remove('pms-modal-open');
        }
        document.querySelectorAll('[data-open-withdraw]').forEach(function (btn) {
            btn.addEventListener('click', openModal);
        });
        modal.querySelectorAll('[data-close-withdraw]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    })();
    </script>
</body>
</html>
