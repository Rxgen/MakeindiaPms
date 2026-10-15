<?php
require_once __DIR__ . '/site_config.php';

$pmsClientIn = !empty($_SESSION['client_id']);
$pmsClientNm = $pmsClientIn && !empty($_SESSION['client_name']) ? $_SESSION['client_name'] : '';
if (!isset($pms_header_folio)) {
    $pms_header_folio = $pmsClientIn && !empty($_SESSION['client_folio_number']) ? $_SESSION['client_folio_number'] : '';
}
?>
  <div class="topbar">
    <div class="container">
      <div class="topbar-left">
        <a href="javascript:;" class="hamburger" id="menuToggle" aria-label="Menu"><i class="fas fa-bars"></i></a>
      </div>
      <div class="topbar-right">
        <?php if ($pmsClientIn): ?>
          <?php
          $pmsFolioNo = !empty($pms_header_folio) ? $pms_header_folio : (isset($_SESSION['client_folio_number']) ? $_SESSION['client_folio_number'] : '');
          ?>
          Welcome, <?= pms_h($pmsClientNm) ?>
          <?php if ($pmsFolioNo !== ''): ?>
            | Folio No: <?= pms_h($pmsFolioNo) ?>
          <?php endif; ?>
          | <a href="<?= pms_h(pms_url('client_logout.php')) ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
          <a href="<?= pms_h(pms_url('client_login.php')) ?>"><i class="fas fa-sign-in-alt"></i> Client Login</a> |
          <a href="<?= pms_h(pms_url('login.php')) ?>"><i class="fas fa-user-shield"></i> Admin Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <header class="main-header">
    <div class="container header-inner">
      <div class="logo">
        <a href="<?= pms_h(pms_url('index.php')) ?>">
          <img src="<?= pms_h(pms_url('img/makeindia2.png')) ?>" alt="Make in India Capital PMS" />
        </a>
      </div>
      <nav class="navbar" id="navbar">
        <ul>
          <li><a href="<?= pms_h(pms_url('index.php')) ?>">Home</a></li>
          <li class="has-submenu">
            <a href="<?= pms_h(pms_url('about.php')) ?>">About</a>
            <ul class="submenu">
              <li><a href="<?= pms_h(pms_url('about.php')) ?>">About Us</a></li>
              <li><a href="<?= pms_h(pms_url('who-should-invest.php')) ?>">Who Should Invest</a></li>
            </ul>
          </li>
          <li><a href="<?= pms_h(pms_url('investment-philosophy.php')) ?>">Investment Philosophy</a></li>
          <li><a href="<?= pms_h(pms_url('performance/')) ?>">Performance</a></li>
          <li><a href="<?= pms_h(pms_url('team/')) ?>">Team</a></li>
          <li><a href="<?= pms_h(pms_url('faq/')) ?>">FAQs</a></li>
          <li><a href="<?= pms_h(pms_url('contact-us/')) ?>">Contact</a></li>
          <?php if ($pmsClientIn): ?>
          <li><a href="<?= pms_h(pms_url('client_dashboard.php')) ?>">My Portfolio</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>
