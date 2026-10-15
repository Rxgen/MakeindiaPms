<?php
/**
 * PMS client portal helpers.
 * Reuses existing database.php ($mysqli) and PHP sessions.
 * Does not replace pms_entries, pms_users, or admin users.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    require_once dirname(__DIR__) . '/database.php';
}

$mysqli->set_charset('utf8mb4');
date_default_timezone_set('Asia/Kolkata');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

define('PMS_OTP_EXPIRY_MINUTES', 5);
define('PMS_OTP_MAX_REQUESTS', 3);
define('PMS_OTP_WINDOW_MINUTES', 10);
define('PMS_OTP_MAX_ATTEMPTS', 5);
define('PMS_DEFAULT_PASSWORD', 'Pms@1234');

$PMS_TXN_TYPES = array(
    'Investment',
    'Additional Investment',
    'Withdrawal',
    'Monthly Payout',
    'Dividend/Payout',
    'Adjustment',
    'Fee',
    'Reinvestment',
);

$PMS_INVESTMENT_TYPES = array('Investment', 'Additional Investment', 'Reinvestment');
$PMS_WITHDRAWAL_TYPES = array('Withdrawal', 'Monthly Payout', 'Dividend/Payout');

function pms_ensure_schema($mysqli)
{
    static $done = false;
    if ($done) {
        return;
    }

    $queries = array(
        "CREATE TABLE IF NOT EXISTS pms_clients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            mobile VARCHAR(20) NOT NULL,
            login_password VARCHAR(100) NOT NULL DEFAULT 'Pms@1234',
            must_change_password TINYINT(1) NOT NULL DEFAULT 1,
            status VARCHAR(80) NOT NULL DEFAULT 'Active & Verified',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_pms_client_email (email),
            UNIQUE KEY unique_pms_client_mobile (mobile)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS pms_folios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            folio_number VARCHAR(50) NOT NULL,
            account_name VARCHAR(150) NOT NULL,
            account_type VARCHAR(80) NOT NULL DEFAULT 'Individual',
            status VARCHAR(50) NOT NULL DEFAULT 'Active',
            investment_start_date DATE DEFAULT NULL,
            performance_remark TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_pms_folio_number (folio_number),
            KEY idx_pms_folio_client (client_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS pms_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            folio_id INT NOT NULL,
            transaction_type VARCHAR(50) NOT NULL,
            amount DECIMAL(18,2) NOT NULL,
            transaction_date DATE NOT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'Completed',
            created_by VARCHAR(100) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_pms_txn_client (client_id),
            KEY idx_pms_txn_folio (folio_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS pms_holdings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            folio_id INT NOT NULL,
            asset_name VARCHAR(150) NOT NULL,
            quantity DECIMAL(18,4) NOT NULL DEFAULT 0,
            average_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
            current_price DECIMAL(18,2) NOT NULL DEFAULT 0,
            current_value DECIMAL(18,2) NOT NULL DEFAULT 0,
            valuation_date DATE DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_pms_hold_client (client_id),
            KEY idx_pms_hold_folio (folio_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS pms_payouts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            folio_id INT NOT NULL,
            transaction_id INT DEFAULT NULL,
            payout_month VARCHAR(7) NOT NULL,
            payout_amount DECIMAL(18,2) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'Paid',
            payment_date DATE DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_pms_pay_client (client_id),
            KEY idx_pms_pay_folio (folio_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS pms_payout_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            folio_id INT DEFAULT NULL,
            payout_percent DECIMAL(8,4) NOT NULL DEFAULT 2.0000,
            payout_label VARCHAR(100) NOT NULL DEFAULT 'Monthly Payout',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_pms_rule_folio (folio_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS pms_otp (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            identifier VARCHAR(150) NOT NULL,
            otp_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            attempts INT NOT NULL DEFAULT 0,
            is_used TINYINT(1) NOT NULL DEFAULT 0,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_pms_otp_client (client_id),
            KEY idx_pms_otp_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS pms_audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            actor VARCHAR(100) NOT NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INT DEFAULT NULL,
            old_value TEXT,
            new_value TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS pms_folio_access (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            folio_id INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_pms_folio_access (client_id, folio_id),
            KEY idx_pms_access_client (client_id),
            KEY idx_pms_access_folio (folio_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    foreach ($queries as $sql) {
        $mysqli->query($sql);
    }

    pms_ensure_client_password_columns($mysqli);
    pms_backfill_client_passwords($mysqli);

    $done = true;
}

function pms_ensure_client_password_columns($mysqli)
{
    $check = $mysqli->query("SHOW COLUMNS FROM pms_clients LIKE 'login_password'");
    if (!$check || $check->num_rows === 0) {
        $mysqli->query("ALTER TABLE pms_clients ADD COLUMN login_password VARCHAR(100) NOT NULL DEFAULT 'Pms@1234' AFTER mobile");
    }
    $check2 = $mysqli->query("SHOW COLUMNS FROM pms_clients LIKE 'must_change_password'");
    if (!$check2 || $check2->num_rows === 0) {
        $mysqli->query("ALTER TABLE pms_clients ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 1 AFTER login_password");
    }
}

function pms_backfill_client_passwords($mysqli)
{
    $default = PMS_DEFAULT_PASSWORD;
    $stmt = $mysqli->prepare("UPDATE pms_clients SET login_password = ? WHERE login_password IS NULL OR login_password = ''");
    $stmt->bind_param('s', $default);
    $stmt->execute();
    $stmt->close();
}

function pms_default_password()
{
    return PMS_DEFAULT_PASSWORD;
}

function pms_set_client_password($mysqli, $client_id, $password, $must_change = 0)
{
    $client_id = (int) $client_id;
    $password = trim((string) $password);
    $must_change = $must_change ? 1 : 0;
    if ($client_id < 1 || $password === '') {
        return false;
    }
    $stmt = $mysqli->prepare('UPDATE pms_clients SET login_password = ?, must_change_password = ? WHERE id = ?');
    $stmt->bind_param('sii', $password, $must_change, $client_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function pms_client_uses_default_password($client)
{
    if (!$client) {
        return false;
    }
    $password = isset($client['login_password']) ? (string) $client['login_password'] : '';
    return $password === '' || $password === PMS_DEFAULT_PASSWORD || !empty($client['must_change_password']);
}

function pms_inr($amount)
{
    $amount = (float) $amount;
    $negative = $amount < 0;
    $amount = abs($amount);
    $formatted = number_format($amount, 2, '.', '');
    $parts = explode('.', $formatted);
    $int = $parts[0];
    $dec = isset($parts[1]) ? $parts[1] : '00';
    $last3 = substr($int, -3);
    $rest = substr($int, 0, -3);
    if ($rest !== '') {
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $int = $rest . ',' . $last3;
    }
    return ($negative ? '-' : '') . '₹' . $int . '.' . $dec;
}

function pms_inr_short($amount)
{
    $amount = (float) $amount;
    $sign = $amount < 0 ? '-' : '';
    $amount = abs($amount);
    if ($amount >= 10000000) {
        return $sign . '₹' . number_format($amount / 10000000, 2) . ' Cr';
    }
    if ($amount >= 100000) {
        return $sign . '₹' . number_format($amount / 100000, 2) . ' L';
    }
    return pms_inr($sign === '-' ? -$amount : $amount);
}

function pms_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function pms_is_local()
{
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    return strpos($host, 'localhost') === 0 || strpos($host, '127.0.0.1') === 0;
}

function pms_date($date)
{
    if (!$date || $date === '0000-00-00') {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date('d M Y', $ts) : '—';
}

function pms_month_label($ym)
{
    $dt = DateTime::createFromFormat('Y-m', $ym);
    return $dt ? $dt->format('F Y') : $ym;
}

function pms_normalize_mobile($mobile)
{
    return preg_replace('/\D+/', '', (string) $mobile);
}

function pms_normalize_email($email)
{
    return strtolower(trim((string) $email));
}

function pms_require_admin()
{
    if (empty($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }
}

function pms_require_client()
{
    if (empty($_SESSION['client_id'])) {
        header('Location: client_login.php');
        exit();
    }
}

function pms_set_client_session($client, $folio = null)
{
    $_SESSION['client_id'] = (int) $client['id'];
    $_SESSION['client_name'] = $client['name'];
    $_SESSION['client_email'] = $client['email'];
    if ($folio && !empty($folio['folio_number'])) {
        $_SESSION['client_folio_number'] = $folio['folio_number'];
    }
}

function pms_default_folio($folios)
{
    foreach ((array) $folios as $folio) {
        if (!empty($folio['folio_number']) && $folio['folio_number'] === '50192834/88') {
            return $folio;
        }
    }
    return $folios ? $folios[0] : null;
}

function pms_ledger_remark($txn, $folio_number, $rule = null)
{
    $remark = trim((string) (isset($txn['remarks']) ? $txn['remarks'] : ''));
    $type = isset($txn['transaction_type']) ? $txn['transaction_type'] : '';
    if ($remark === '' || stripos($remark, 'Monthly payout as per configured') !== false) {
        if ($type === 'Monthly Payout') {
            $pct = $rule ? rtrim(rtrim(number_format((float) $rule['payout_percent'], 4), '0'), '.') : '2';
            $remark = 'Automated ' . $pct . '% Monthly Payout';
        } elseif ($type === 'Investment' && $remark === '') {
            $remark = 'Initial Capital Deployment (Make in India PMS)';
        }
    }
    if ($folio_number !== '' && stripos($remark, $folio_number) === false) {
        $remark = ($remark === '' ? 'Folio ' . $folio_number : $remark . ' (Folio ' . $folio_number . ')');
    }
    return $remark;
}

function pms_audit($mysqli, $actor, $action, $entity_type, $entity_id, $old_value, $new_value)
{
    $stmt = $mysqli->prepare('INSERT INTO pms_audit_log (actor, action, entity_type, entity_id, old_value, new_value) VALUES (?, ?, ?, ?, ?, ?)');
    $entity_id = $entity_id ? (int) $entity_id : 0;
    $old = $old_value === null ? '' : (string) $old_value;
    $new = $new_value === null ? '' : (string) $new_value;
    $stmt->bind_param('sssiss', $actor, $action, $entity_type, $entity_id, $old, $new);
    $stmt->execute();
    $stmt->close();
}

function pms_admin_actor()
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : 'admin';
}

function pms_client_by_id($mysqli, $client_id)
{
    $stmt = $mysqli->prepare('SELECT * FROM pms_clients WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function pms_find_active_client($mysqli, $identifier)
{
    $email = pms_normalize_email($identifier);
    $mobile = pms_normalize_mobile($identifier);

    $stmt = $mysqli->prepare('SELECT * FROM pms_clients WHERE is_active = 1 AND (email = ? OR mobile = ? OR REPLACE(REPLACE(REPLACE(mobile, " ", ""), "-", ""), "+", "") = ?) LIMIT 1');
    $stmt->bind_param('sss', $email, $identifier, $mobile);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function pms_client_can_access_folio($mysqli, $folio_id, $client_id)
{
    $stmt = $mysqli->prepare('SELECT id FROM pms_folios WHERE id = ? AND client_id = ? LIMIT 1');
    $stmt->bind_param('ii', $folio_id, $client_id);
    $stmt->execute();
    $ok = $stmt->get_result()->num_rows === 1;
    $stmt->close();
    return $ok;
}

function pms_folio_belongs_to_client($mysqli, $folio_id, $client_id)
{
    return pms_client_can_access_folio($mysqli, $folio_id, $client_id);
}

function pms_get_folio($mysqli, $folio_id, $client_id)
{
    $stmt = $mysqli->prepare('SELECT * FROM pms_folios WHERE id = ? AND client_id = ? LIMIT 1');
    $stmt->bind_param('ii', $folio_id, $client_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function pms_get_folios($mysqli, $client_id)
{
    $stmt = $mysqli->prepare('SELECT * FROM pms_folios WHERE client_id = ? ORDER BY id ASC');
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function pms_sync_folio_ownership($mysqli, $folio_id, $client_id)
{
    foreach (array('pms_transactions', 'pms_holdings', 'pms_payouts') as $table) {
        $stmt = $mysqli->prepare("UPDATE {$table} SET client_id = ? WHERE folio_id = ?");
        $stmt->bind_param('ii', $client_id, $folio_id);
        $stmt->execute();
        $stmt->close();
    }
}

function pms_int_ids($ids)
{
    $clean = array();
    foreach ((array) $ids as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $clean[] = $id;
        }
    }
    return array_values(array_unique($clean));
}

function pms_sum_types($mysqli, $client_id, $folio_id, $types)
{
    $folio_ids = $folio_id ? array((int) $folio_id) : array_map(function ($row) {
        return (int) $row['id'];
    }, pms_get_folios($mysqli, $client_id));
    return pms_sum_types_by_folios($mysqli, $folio_ids, $types);
}

function pms_sum_types_by_folios($mysqli, $folio_ids, $types)
{
    global $PMS_TXN_TYPES;
    $folio_ids = pms_int_ids($folio_ids);
    $clean = array();
    foreach ($types as $type) {
        if (in_array($type, $PMS_TXN_TYPES, true)) {
            $clean[] = $type;
        }
    }
    if (!$folio_ids || !$clean) {
        return 0.0;
    }

    $folioPh = implode(',', array_fill(0, count($folio_ids), '?'));
    $typePh = implode(',', array_fill(0, count($clean), '?'));
    $sql = "SELECT COALESCE(SUM(amount), 0) AS total
            FROM pms_transactions
            WHERE folio_id IN ($folioPh)
              AND status IN ('Completed', 'Paid')
              AND transaction_type IN ($typePh)";
    $params = array_merge($folio_ids, $clean);
    $bind = str_repeat('i', count($folio_ids)) . str_repeat('s', count($clean));
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($bind, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float) $row['total'];
}

function pms_sum_holdings($mysqli, $client_id, $folio_id)
{
    $folio_ids = $folio_id ? array((int) $folio_id) : array_map(function ($row) {
        return (int) $row['id'];
    }, pms_get_folios($mysqli, $client_id));
    return pms_sum_holdings_by_folios($mysqli, $folio_ids);
}

function pms_sum_holdings_by_folios($mysqli, $folio_ids)
{
    $folio_ids = pms_int_ids($folio_ids);
    if (!$folio_ids) {
        return 0.0;
    }
    $folioPh = implode(',', array_fill(0, count($folio_ids), '?'));
    $sql = "SELECT COALESCE(SUM(current_value), 0) AS total FROM pms_holdings WHERE is_active = 1 AND folio_id IN ($folioPh)";
    $bind = str_repeat('i', count($folio_ids));
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($bind, ...$folio_ids);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float) $row['total'];
}

function pms_last_valuation_date($mysqli, $client_id, $folio_id)
{
    $folio_ids = $folio_id ? array((int) $folio_id) : array_map(function ($row) {
        return (int) $row['id'];
    }, pms_get_folios($mysqli, $client_id));
    return pms_last_valuation_by_folios($mysqli, $folio_ids);
}

function pms_last_valuation_by_folios($mysqli, $folio_ids)
{
    $folio_ids = pms_int_ids($folio_ids);
    if (!$folio_ids) {
        return null;
    }
    $folioPh = implode(',', array_fill(0, count($folio_ids), '?'));
    $sql = "SELECT MAX(valuation_date) AS last_date FROM pms_holdings WHERE is_active = 1 AND folio_id IN ($folioPh)";
    $bind = str_repeat('i', count($folio_ids));
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($bind, ...$folio_ids);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row && $row['last_date'] ? $row['last_date'] : null;
}

/**
 * Backend calculation rules:
 * Total Invested     = SUM(Investment + Additional Investment + Reinvestment) where status is Completed/Paid
 * Total Withdrawn    = SUM(Withdrawal + Monthly Payout + Dividend/Payout) where status is Completed/Paid
 * Current Valuation  = SUM(current_value of active holdings)
 * Net Portfolio Value = Current Valuation
 * Total Return       = Current Valuation + Total Withdrawn - Total Invested
 * Return Percentage  = Total Return / Total Invested * 100
 */
function pms_portfolio_summary_by_folios($mysqli, $folio_ids)
{
    global $PMS_INVESTMENT_TYPES, $PMS_WITHDRAWAL_TYPES;
    $folio_ids = pms_int_ids($folio_ids);
    $invested = pms_sum_types_by_folios($mysqli, $folio_ids, $PMS_INVESTMENT_TYPES);
    $withdrawn = pms_sum_types_by_folios($mysqli, $folio_ids, $PMS_WITHDRAWAL_TYPES);
    $valuation = pms_sum_holdings_by_folios($mysqli, $folio_ids);
    $last_valuation = pms_last_valuation_by_folios($mysqli, $folio_ids);
    $total_return = $valuation + $withdrawn - $invested;
    $return_pct = $invested > 0 ? ($total_return / $invested) * 100 : 0;
    return array(
        'invested' => $invested,
        'withdrawn' => $withdrawn,
        'valuation' => $valuation,
        'net_value' => $valuation,
        'total_return' => $total_return,
        'return_pct' => $return_pct,
        'last_valuation' => $last_valuation,
    );
}

function pms_portfolio_summary($mysqli, $client_id, $folio_id = null)
{
    if ($folio_id) {
        return pms_portfolio_summary_by_folios($mysqli, array($folio_id));
    }
    $ids = array();
    foreach (pms_get_folios($mysqli, $client_id) as $folio) {
        $ids[] = (int) $folio['id'];
    }
    return pms_portfolio_summary_by_folios($mysqli, $ids);
}

function pms_ensure_client($mysqli, $name, $email, $mobile, $status)
{
    $email = pms_normalize_email($email);
    $defaultPassword = PMS_DEFAULT_PASSWORD;
    $stmt = $mysqli->prepare('SELECT id, login_password FROM pms_clients WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $id = (int) $row['id'];
        $upd = $mysqli->prepare('UPDATE pms_clients SET name = ?, mobile = ?, status = ?, is_active = 1 WHERE id = ?');
        $upd->bind_param('sssi', $name, $mobile, $status, $id);
        $upd->execute();
        $upd->close();
        if (empty($row['login_password'])) {
            pms_set_client_password($mysqli, $id, $defaultPassword, 1);
        }
        return $id;
    }
    $ins = $mysqli->prepare('INSERT INTO pms_clients (name, email, mobile, login_password, must_change_password, status, is_active) VALUES (?, ?, ?, ?, 1, ?, 1)');
    $ins->bind_param('sssss', $name, $email, $mobile, $defaultPassword, $status);
    $ins->execute();
    $id = (int) $ins->insert_id;
    $ins->close();
    return $id;
}

function pms_sync_payout_from_transaction($mysqli, $txn_id, $client_id, $folio_id, $amount, $transaction_date, $remarks, $txn_status)
{
    $txn_id = (int) $txn_id;
    if ($txn_id < 1) {
        return;
    }
    $status = ($txn_status === 'Pending') ? 'Pending' : 'Paid';
    $month = substr((string) $transaction_date, 0, 7);
    $find = $mysqli->prepare('SELECT id FROM pms_payouts WHERE transaction_id = ? LIMIT 1');
    $find->bind_param('i', $txn_id);
    $find->execute();
    $existing = $find->get_result()->fetch_assoc();
    $find->close();
    if ($existing) {
        $upd = $mysqli->prepare('UPDATE pms_payouts SET client_id = ?, folio_id = ?, payout_month = ?, payout_amount = ?, status = ?, payment_date = ?, remarks = ? WHERE id = ?');
        $upd->bind_param('iisdsssi', $client_id, $folio_id, $month, $amount, $status, $transaction_date, $remarks, $existing['id']);
        $upd->execute();
        $upd->close();
        return;
    }
    $ins = $mysqli->prepare('INSERT INTO pms_payouts (client_id, folio_id, transaction_id, payout_month, payout_amount, status, payment_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $ins->bind_param('iiisdsss', $client_id, $folio_id, $txn_id, $month, $amount, $status, $transaction_date, $remarks);
    $ins->execute();
    $ins->close();
}

function pms_grant_folio_access($mysqli, $client_id, $folio_id)
{
    $stmt = $mysqli->prepare('INSERT IGNORE INTO pms_folio_access (client_id, folio_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $client_id, $folio_id);
    $stmt->execute();
    $stmt->close();
}

function pms_folio_viewer_emails($mysqli, $folio_id)
{
    $emails = array();
    $stmt = $mysqli->prepare('SELECT c.email
        FROM pms_clients c
        INNER JOIN pms_folios f ON f.client_id = c.id
        WHERE f.id = ?
        UNION
        SELECT c.email
        FROM pms_clients c
        INNER JOIN pms_folio_access a ON a.client_id = c.id
        WHERE a.folio_id = ?');
    $stmt->bind_param('ii', $folio_id, $folio_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row['email'];
    }
    $stmt->close();
    sort($emails);
    return $emails;
}

function pms_master_account_defs()
{
    return array(
        array('Aparna Hoshing', 'aparna.hoshing@example.com', '9876543211', '10482910/42', 'Aparna Hoshing', 'Individual', '2025-06-01', 'Stable & Healthy'),
        array('Sudhir Hoshing HUF (A1)', 'sudhir.hufa1@makeindiapms.local', '9876543212', '39182049/11', 'Sudhir Hoshing HUF (A1)', 'HUF', '2025-01-10', 'Strong Compounding'),
        array('Sudhir Rao Hoshing', 'sudhirhoshing@gmail.com', '8828040322', '50192834/88', 'Sudhir Hoshing (A2)', 'Individual', '2025-03-18', 'Excellent Growth'),
        array('Sudhir Hoshing (A3)', 'sudhir.a3@makeindiapms.local', '9876543213', '77281903/05', 'Sudhir Hoshing (A3)', 'Individual', '2025-04-02', 'Strong Alpha'),
        array('Sudhir Hoshing HUF (A4)', 'sudhir.hufa4@makeindiapms.local', '9876543214', '88391024/90', 'Sudhir Hoshing HUF (A4)', 'HUF', '2025-02-14', 'High Risk / Rebalancing'),
    );
}

function pms_sync_master_register($mysqli)
{
    foreach (pms_master_account_defs() as $row) {
        $email = pms_normalize_email($row[1]);
        $findClient = $mysqli->prepare('SELECT id FROM pms_clients WHERE email = ? LIMIT 1');
        $findClient->bind_param('s', $email);
        $findClient->execute();
        $clientRow = $findClient->get_result()->fetch_assoc();
        $findClient->close();
        if ($clientRow) {
            $clientId = (int) $clientRow['id'];
        } else {
            $clientId = pms_ensure_client($mysqli, $row[0], $row[1], $row[2], 'Active & Verified');
        }

        $folioNumber = $row[3];
        $find = $mysqli->prepare('SELECT id FROM pms_folios WHERE folio_number = ? LIMIT 1');
        $find->bind_param('s', $folioNumber);
        $find->execute();
        $existing = $find->get_result()->fetch_assoc();
        $find->close();
        if ($existing) {
            $folioId = (int) $existing['id'];
        } else {
            $ins = $mysqli->prepare('INSERT INTO pms_folios (client_id, folio_number, account_name, account_type, status, investment_start_date, performance_remark) VALUES (?, ?, ?, ?, "Active", ?, ?)');
            $ins->bind_param('isssss', $clientId, $folioNumber, $row[4], $row[5], $row[6], $row[7]);
            $ins->execute();
            $folioId = (int) $ins->insert_id;
            $ins->close();
            pms_sync_folio_ownership($mysqli, $folioId, $clientId);
        }
    }
}

function pms_get_payout_rule($mysqli, $folio_id)
{
    $stmt = $mysqli->prepare('SELECT * FROM pms_payout_rules WHERE is_active = 1 AND folio_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('i', $folio_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        return $row;
    }

    $result = $mysqli->query('SELECT * FROM pms_payout_rules WHERE is_active = 1 AND folio_id IS NULL ORDER BY id DESC LIMIT 1');
    return $result ? $result->fetch_assoc() : null;
}

function pms_otp_recent_count($mysqli, $identifier)
{
    $window = (int) PMS_OTP_WINDOW_MINUTES;
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM pms_otp WHERE identifier = ? AND created_at >= (NOW() - INTERVAL {$window} MINUTE)");
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) $row['total'];
}

function pms_send_otp_mail($toEmail, $otp, $clientName)
{
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'nimakeindia.com';
    $subject = 'Your MAKE IN INDIA CAPITAL PMS login OTP';
    $body = "Dear {$clientName},\n\nYour One-Time Password (OTP) is: {$otp}\n\nThis OTP is valid for " . PMS_OTP_EXPIRY_MINUTES . " minutes. Do not share it with anyone.\n\nMAKE IN INDIA CAPITAL PMS\n";
    $headers = 'From: MAKE IN INDIA CAPITAL PMS <noreply@' . $host . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return @mail($toEmail, $subject, $body, $headers);
}

function pms_client_rows($mysqli)
{
    $rows = array();
    $result = $mysqli->query('SELECT * FROM pms_clients ORDER BY name ASC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function pms_folio_rows($mysqli)
{
    $rows = array();
    $sql = "SELECT f.*, c.name AS client_name, c.email AS client_email
            FROM pms_folios f
            INNER JOIN pms_clients c ON c.id = f.client_id
            ORDER BY FIELD(f.folio_number, '10482910/42', '39182049/11', '50192834/88', '77281903/05', '88391024/90'), f.id ASC";
    $result = $mysqli->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function pms_txn_stats($mysqli, $folio_id, $types)
{
    global $PMS_TXN_TYPES;
    $folio_id = (int) $folio_id;
    $clean = array();
    foreach ((array) $types as $type) {
        if (in_array($type, $PMS_TXN_TYPES, true)) {
            $clean[] = $type;
        }
    }
    if ($folio_id < 1 || !$clean) {
        return array('count' => 0, 'total' => 0.0);
    }
    $typePh = implode(',', array_fill(0, count($clean), '?'));
    $sql = "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS total
            FROM pms_transactions
            WHERE folio_id = ?
              AND status IN ('Completed', 'Paid')
              AND transaction_type IN ($typePh)";
    $stmt = $mysqli->prepare($sql);
    $bind = 'i' . str_repeat('s', count($clean));
    $params = array_merge(array($folio_id), $clean);
    $stmt->bind_param($bind, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return array(
        'count' => (int) $row['cnt'],
        'total' => (float) $row['total'],
    );
}

function pms_txn_rows_by_types($mysqli, $folio_id, $types)
{
    global $PMS_TXN_TYPES;
    $folio_id = (int) $folio_id;
    $clean = array();
    foreach ((array) $types as $type) {
        if (in_array($type, $PMS_TXN_TYPES, true)) {
            $clean[] = $type;
        }
    }
    $rows = array();
    if ($folio_id < 1 || !$clean) {
        return $rows;
    }
    $typePh = implode(',', array_fill(0, count($clean), '?'));
    $sql = "SELECT * FROM pms_transactions
            WHERE folio_id = ? AND transaction_type IN ($typePh)
            ORDER BY transaction_date DESC, id DESC";
    $stmt = $mysqli->prepare($sql);
    $bind = 'i' . str_repeat('s', count($clean));
    $params = array_merge(array($folio_id), $clean);
    $stmt->bind_param($bind, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function pms_get_folio_payout_rule($mysqli, $folio_id)
{
    $folio_id = (int) $folio_id;
    $stmt = $mysqli->prepare('SELECT * FROM pms_payout_rules WHERE folio_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('i', $folio_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function pms_rule_label($rule)
{
    if (!$rule) {
        return 'Not set';
    }
    $pct = rtrim(rtrim(number_format((float) $rule['payout_percent'], 4), '0'), '.');
    return $pct . '% ' . $rule['payout_label'];
}

function pms_seed_demo_if_empty($mysqli)
{
    $check = $mysqli->query('SELECT COUNT(*) AS total FROM pms_clients');
    $count = $check ? (int) $check->fetch_assoc()['total'] : 0;
    if ($count > 0) {
        return;
    }

    $mysqli->begin_transaction();
    try {
    $clients = array(
        array('Sudhir Rao Hoshing', 'sudhirhoshing@gmail.com', '8828040322', 'Active & Verified'),
        array('Aparna Hoshing', 'aparna.hoshing@example.com', '9876543211', 'Active & Verified'),
    );

    $clientIds = array();
    $defaultPassword = PMS_DEFAULT_PASSWORD;
    $stmt = $mysqli->prepare('INSERT INTO pms_clients (name, email, mobile, login_password, must_change_password, status, is_active) VALUES (?, ?, ?, ?, 1, ?, 1)');
    foreach ($clients as $client) {
        $stmt->bind_param('sssss', $client[0], $client[1], $client[2], $defaultPassword, $client[3]);
        $stmt->execute();
        $clientIds[$client[1]] = $stmt->insert_id;
    }
    $stmt->close();

    $sudhirId = $clientIds['sudhirhoshing@gmail.com'];
    $aparnaId = $clientIds['aparna.hoshing@example.com'];

    $folios = array(
        array($sudhirId, '39182049/11', 'Sudhir Hoshing HUF A1', 'HUF', '2025-01-10'),
        array($sudhirId, '50192834/88', 'Sudhir Hoshing A2', 'Individual', '2025-03-18'),
        array($sudhirId, '77281903/05', 'Sudhir Hoshing A3', 'Individual', '2025-04-02'),
        array($sudhirId, '88391024/90', 'Sudhir Hoshing HUF A4', 'HUF', '2025-02-14'),
        array($aparnaId, '10482910/42', 'Aparna Hoshing', 'Individual', '2025-06-01'),
    );

    $folioIds = array();
    $folioStmt = $mysqli->prepare('INSERT INTO pms_folios (client_id, folio_number, account_name, account_type, status, investment_start_date, performance_remark) VALUES (?, ?, ?, ?, "Active", ?, ?)');
    foreach ($folios as $folio) {
        $remark = 'Valuation as per latest mark-to-market provided by the investment team.';
        $folioStmt->bind_param('isssss', $folio[0], $folio[1], $folio[2], $folio[3], $folio[4], $remark);
        $folioStmt->execute();
        $folioIds[$folio[1]] = $folioStmt->insert_id;
    }
    $folioStmt->close();

    $txStmt = $mysqli->prepare('INSERT INTO pms_transactions (client_id, folio_id, transaction_type, amount, transaction_date, remarks, status, created_by) VALUES (?, ?, ?, ?, ?, ?, "Completed", "system")');
    $transactions = array(
        array($sudhirId, $folioIds['50192834/88'], 'Investment', 57300000.00, '2025-03-18', 'Initial Capital Deployment (Make in India PMS)'),
        array($sudhirId, $folioIds['50192834/88'], 'Withdrawal', 4500000.00, '2026-01-15', 'Requested Liquidity Payout'),
        array($sudhirId, $folioIds['50192834/88'], 'Monthly Payout', 1260000.00, '2026-08-31', 'Automated 2% Monthly Payout'),
        array($sudhirId, $folioIds['39182049/11'], 'Investment', 10300000.00, '2025-01-10', 'Initial Capital Deployment'),
        array($sudhirId, $folioIds['77281903/05'], 'Investment', 40000000.00, '2025-04-02', 'Initial Capital Deployment'),
        array($sudhirId, $folioIds['77281903/05'], 'Withdrawal', 3200000.00, '2026-03-12', 'Requested Liquidity Payout'),
        array($sudhirId, $folioIds['88391024/90'], 'Investment', 25000000.00, '2025-02-14', 'Initial Capital Deployment'),
        array($sudhirId, $folioIds['88391024/90'], 'Monthly Payout', 3360000.00, '2026-08-31', 'Cumulative monthly payouts'),
        array($aparnaId, $folioIds['10482910/42'], 'Investment', 10000000.00, '2025-06-01', 'Initial Capital Deployment'),
        array($aparnaId, $folioIds['10482910/42'], 'Monthly Payout', 300000.00, '2026-08-31', 'Monthly payout as per configured rule'),
    );
    foreach ($transactions as $txn) {
        $txStmt->bind_param('iisdss', $txn[0], $txn[1], $txn[2], $txn[3], $txn[4], $txn[5]);
        $txStmt->execute();
    }
    $txStmt->close();

    $holdStmt = $mysqli->prepare('INSERT INTO pms_holdings (client_id, folio_id, asset_name, quantity, average_cost, current_price, current_value, valuation_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
    $holdings = array(
        array($sudhirId, $folioIds['50192834/88'], 'Large Cap Equity Portfolio', 10000.0000, 4500.00, 4500.00, 45000000.00, '2026-09-20'),
        array($sudhirId, $folioIds['50192834/88'], 'Mid Cap Equity Portfolio', 5000.0000, 4000.00, 4000.00, 20000000.00, '2026-09-20'),
        array($sudhirId, $folioIds['50192834/88'], 'Debt / Liquid Allocation', 6365.2130, 1000.00, 1000.00, 6365213.00, '2026-09-20'),
        array($sudhirId, $folioIds['39182049/11'], 'Managed Equity Portfolio', 1074.2890, 10000.00, 10000.00, 10742890.00, '2026-09-20'),
        array($sudhirId, $folioIds['77281903/05'], 'Managed Equity Portfolio', 4387.1205, 10000.00, 10000.00, 43871205.00, '2026-09-20'),
        array($sudhirId, $folioIds['88391024/90'], 'Managed Equity Portfolio', 2228.9814, 10000.00, 10000.00, 22289814.00, '2026-09-20'),
        array($aparnaId, $folioIds['10482910/42'], 'Managed Equity Portfolio', 1029.8201, 10000.00, 10000.00, 10298201.00, '2026-09-20'),
    );
    foreach ($holdings as $holding) {
        $holdStmt->bind_param('iisdddds', $holding[0], $holding[1], $holding[2], $holding[3], $holding[4], $holding[5], $holding[6], $holding[7]);
        $holdStmt->execute();
    }
    $holdStmt->close();

    $payStmt = $mysqli->prepare('INSERT INTO pms_payouts (client_id, folio_id, payout_month, payout_amount, status, payment_date, remarks) VALUES (?, ?, ?, ?, "Paid", ?, ?)');
    $payouts = array(
        array($sudhirId, $folioIds['50192834/88'], '2026-08', 1260000.00, '2026-08-31', 'Automated 2% Monthly Payout'),
        array($sudhirId, $folioIds['88391024/90'], '2026-08', 3360000.00, '2026-08-31', 'Monthly payout as per configured rule'),
        array($aparnaId, $folioIds['10482910/42'], '2026-08', 300000.00, '2026-08-31', 'Monthly payout as per configured rule'),
    );
    foreach ($payouts as $payout) {
        $payStmt->bind_param('iisdss', $payout[0], $payout[1], $payout[2], $payout[3], $payout[4], $payout[5]);
        $payStmt->execute();
    }
    $payStmt->close();

    $mysqli->query("INSERT INTO pms_payout_rules (folio_id, payout_percent, payout_label, is_active) VALUES (NULL, 2.0000, 'Monthly Payout', 1)");
    $a2 = (int) $folioIds['50192834/88'];
    $mysqli->query("INSERT INTO pms_payout_rules (folio_id, payout_percent, payout_label, is_active) VALUES ({$a2}, 2.0000, 'Monthly Payout', 1)");

    pms_audit($mysqli, 'system', 'Seeded demo portfolio data', 'system', 0, '', 'Initial PMS portal tables populated');
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
    }
}

function pms_ensure_folio_record($mysqli, $client_id, $folio_number, $account_name, $account_type, $start_date, $remark)
{
    $find = $mysqli->prepare('SELECT id FROM pms_folios WHERE folio_number = ? LIMIT 1');
    $find->bind_param('s', $folio_number);
    $find->execute();
    $existing = $find->get_result()->fetch_assoc();
    $find->close();
    if ($existing) {
        $folioId = (int) $existing['id'];
        $upd = $mysqli->prepare('UPDATE pms_folios SET client_id = ?, account_name = ?, account_type = ?, status = "Active", investment_start_date = ?, performance_remark = ? WHERE id = ?');
        $upd->bind_param('issssi', $client_id, $account_name, $account_type, $start_date, $remark, $folioId);
        $upd->execute();
        $upd->close();
        pms_sync_folio_ownership($mysqli, $folioId, $client_id);
        return $folioId;
    }
    $ins = $mysqli->prepare('INSERT INTO pms_folios (client_id, folio_number, account_name, account_type, status, investment_start_date, performance_remark) VALUES (?, ?, ?, ?, "Active", ?, ?)');
    $ins->bind_param('isssss', $client_id, $folio_number, $account_name, $account_type, $start_date, $remark);
    $ins->execute();
    $folioId = (int) $ins->insert_id;
    $ins->close();
    pms_sync_folio_ownership($mysqli, $folioId, $client_id);
    return $folioId;
}

function pms_ensure_folio_investment_package($mysqli, $client_id, $folio_id, $invest_amount, $payout_amount, $start_date)
{
    $check = $mysqli->prepare("SELECT id FROM pms_transactions WHERE folio_id = ? AND transaction_type = 'Investment' LIMIT 1");
    $check->bind_param('i', $folio_id);
    $check->execute();
    $hasInvest = $check->get_result()->fetch_assoc();
    $check->close();
    if (!$hasInvest) {
        $tx = $mysqli->prepare('INSERT INTO pms_transactions (client_id, folio_id, transaction_type, amount, transaction_date, remarks, status, created_by) VALUES (?, ?, "Investment", ?, ?, "Initial Capital Deployment", "Completed", "admin")');
        $tx->bind_param('iids', $client_id, $folio_id, $invest_amount, $start_date);
        $tx->execute();
        $tx->close();
    }

    $payCheck = $mysqli->prepare("SELECT id FROM pms_transactions WHERE folio_id = ? AND transaction_type = 'Monthly Payout' LIMIT 1");
    $payCheck->bind_param('i', $folio_id);
    $payCheck->execute();
    $hasPayout = $payCheck->get_result()->fetch_assoc();
    $payCheck->close();
    if (!$hasPayout && $payout_amount > 0) {
        $payDate = '2026-09-30';
        $tx = $mysqli->prepare('INSERT INTO pms_transactions (client_id, folio_id, transaction_type, amount, transaction_date, remarks, status, created_by) VALUES (?, ?, "Monthly Payout", ?, ?, "Monthly payout 2% as per mandate", "Completed", "admin")');
        $tx->bind_param('iids', $client_id, $folio_id, $payout_amount, $payDate);
        $tx->execute();
        $txnId = (int) $tx->insert_id;
        $tx->close();
        pms_sync_payout_from_transaction($mysqli, $txnId, $client_id, $folio_id, $payout_amount, $payDate, 'Monthly payout 2% as per mandate', 'Completed');
    }

    $holdCheck = $mysqli->prepare('SELECT id FROM pms_holdings WHERE folio_id = ? AND is_active = 1 LIMIT 1');
    $holdCheck->bind_param('i', $folio_id);
    $holdCheck->execute();
    $hasHold = $holdCheck->get_result()->fetch_assoc();
    $holdCheck->close();
    if (!$hasHold) {
        $qty = $invest_amount / 10000.0;
        $valDate = '2026-09-30';
        $asset = 'Managed Equity Portfolio';
        $avg = 10000.00;
        $hold = $mysqli->prepare('INSERT INTO pms_holdings (client_id, folio_id, asset_name, quantity, average_cost, current_price, current_value, valuation_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
        $hold->bind_param('iisdddds', $client_id, $folio_id, $asset, $qty, $avg, $avg, $invest_amount, $valDate);
        $hold->execute();
        $hold->close();
    }

    $ruleCheck = $mysqli->prepare('SELECT id FROM pms_payout_rules WHERE folio_id = ? AND is_active = 1 LIMIT 1');
    $ruleCheck->bind_param('i', $folio_id);
    $ruleCheck->execute();
    $hasRule = $ruleCheck->get_result()->fetch_assoc();
    $ruleCheck->close();
    if (!$hasRule) {
        $rule = $mysqli->prepare('INSERT INTO pms_payout_rules (folio_id, payout_percent, payout_label, is_active) VALUES (?, 2.0000, "Monthly Payout", 1)');
        $rule->bind_param('i', $folio_id);
        $rule->execute();
        $rule->close();
    }
}

/**
 * Import client intake from Data.txt (Junaid only from Shaikh family + remaining clients).
 * Idempotent — safe to run on every request bootstrap.
 */
function pms_import_intake_clients($mysqli)
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    // Keep Sudhir contact in sync with latest intake sheet.
    $sudhirEmail = 'sudhirhoshing@gmail.com';
    $sudhirMobile = '8828040322';
    $stmt = $mysqli->prepare('UPDATE pms_clients SET mobile = ? WHERE email = ?');
    $stmt->bind_param('ss', $sudhirMobile, $sudhirEmail);
    $stmt->execute();
    $stmt->close();

    $remark = 'Valuation as per latest mark-to-market provided by the investment team.';
    $accounts = array(
        // Only Junaid from the Shaikh family (shared contact on the sheet).
        array(
            'name' => 'Junaid Shaikh',
            'email' => 'shaikh.naved79@gmail.com',
            'mobile' => '9137483417',
            'folios' => array(
                array('JUNA9137/35', 'Junaid Shaikh', 'Individual', '2026-04-01', 3500000.00, 70000.00),
            ),
        ),
        array(
            'name' => 'Fatema Tarawala',
            'email' => 'hftarawala@hotmail.com',
            'mobile' => '9619500185',
            'folios' => array(
                array('FATE9619/20', 'Fatema Tarawala', 'Individual', '2026-04-01', 2000000.00, 40000.00),
            ),
        ),
        array(
            'name' => 'Vinita Abhishek Gupta',
            'email' => 'vinigupta@gmail.com',
            'mobile' => '8104548180',
            'folios' => array(
                array('VINI8104/01', 'Vinita Abhishek Gupta (F1)', 'Individual', '2026-04-01', 1250000.00, 25000.00),
                array('VINI8104/02', 'Vinita Abhishek Gupta (F2)', 'Individual', '2026-04-01', 200000.00, 4000.00),
                array('VINI8104/03', 'Vinita Abhishek Gupta (F3)', 'Individual', '2026-04-01', 1600000.00, 32000.00),
            ),
        ),
        array(
            'name' => 'Keki Jal Guard',
            'email' => 'kjguard2010@gmail.com',
            'mobile' => '9821410759',
            'folios' => array(
                array('KEKI9821/18', 'Keki Jal Guard', 'Individual', '2026-04-01', 1800000.00, 36000.00),
            ),
        ),
        array(
            'name' => 'Gautam G Mallapur',
            'email' => 'drgphoto@gmail.com',
            'mobile' => '9820427170',
            'folios' => array(
                array('GAUT9820/20', 'Gautam G Mallapur', 'Individual', '2026-04-01', 2000000.00, 40000.00),
            ),
        ),
    );

    foreach ($accounts as $account) {
        $clientId = pms_ensure_client($mysqli, $account['name'], $account['email'], $account['mobile'], 'Active & Verified');
        foreach ($account['folios'] as $folio) {
            $folioId = pms_ensure_folio_record($mysqli, $clientId, $folio[0], $folio[1], $folio[2], $folio[3], $remark);
            pms_ensure_folio_investment_package($mysqli, $clientId, $folioId, $folio[4], $folio[5], $folio[3]);
        }
    }
}

pms_ensure_schema($mysqli);
pms_seed_demo_if_empty($mysqli);
pms_sync_master_register($mysqli);
pms_import_intake_clients($mysqli);
