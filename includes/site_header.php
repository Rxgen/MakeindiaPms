<?php
require_once __DIR__ . '/site_config.php';

$pmsClientIn = !empty($_SESSION['client_id']);
$pmsClientNm = $pmsClientIn && !empty($_SESSION['client_name']) ? $_SESSION['client_name'] : '';
if (!isset($pms_header_folio)) {
    $pms_header_folio = $pmsClientIn && !empty($_SESSION['client_folio_number']) ? $_SESSION['client_folio_number'] : '';
}
?>
  <header class="main-header">
    <div class="container header-inner">
      <div class="header-left">
        <div class="logo">
          <a href="<?= pms_h(pms_url('index.php')) ?>" aria-label="Home">
            <img src="<?= pms_h(pms_url('img/makeindia2.png')) ?>" alt="Make in India Capital PMS" />
          </a>
        </div>
        <div class="header-sebi" title="SEBI Registration">
          <span class="header-sebi-label">SEBI Registered PMS</span>
          <span class="header-sebi-code">INP000006554</span>
        </div>
      </div>

      <div class="nav-overlay" id="navOverlay" hidden></div>
      <nav class="navbar" id="navbar" aria-label="Main">
        <div class="nav-drawer-head">
          <div>
            <strong>Make in India Capital</strong>
            <span>Navigate</span>
          </div>
          <button type="button" class="nav-close" id="menuClose" aria-label="Close menu">&times;</button>
        </div>
        <ul>
          <li class="has-submenu">
            <a href="<?= pms_h(pms_url('about.php')) ?>">About</a>
            <ul class="submenu">
              <li><a href="<?= pms_h(pms_url('about.php')) ?>">About Us</a></li>
              <li><a href="<?= pms_h(pms_url('who-should-invest.php')) ?>">Who Should Invest</a></li>
            </ul>
          </li>
          <li><a href="<?= pms_h(pms_url('investment-philosophy.php')) ?>">Philosophy</a></li>
          <li><a href="<?= pms_h(pms_url('performance/')) ?>">Performance</a></li>
          <li><a href="<?= pms_h(pms_url('team/')) ?>">Team</a></li>
          <li><a href="<?= pms_h(pms_url('faq/')) ?>">FAQs</a></li>
          <li><a href="<?= pms_h(pms_url('contact-us/')) ?>">Contact</a></li>
          <?php if ($pmsClientIn): ?>
          <li><a href="<?= pms_h(pms_url('client_dashboard.php')) ?>">My Portfolio</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="header-right">
        <div class="header-auth" id="authBar">
          <?php if ($pmsClientIn): ?>
            <?php
            $pmsFolioNo = !empty($pms_header_folio) ? $pms_header_folio : (isset($_SESSION['client_folio_number']) ? $_SESSION['client_folio_number'] : '');
            ?>
            <span class="auth-welcome">Welcome, <?= pms_h($pmsClientNm) ?></span>
            <?php if ($pmsFolioNo !== ''): ?>
              <span class="auth-folio">Folio: <?= pms_h($pmsFolioNo) ?></span>
            <?php endif; ?>
            <a class="btn-auth btn-auth-outline" href="<?= pms_h(pms_url('client_logout.php')) ?>"><i class="fas fa-sign-out-alt"></i> <span class="btn-label">Logout</span></a>
          <?php else: ?>
            <a class="btn-auth btn-auth-client" href="<?= pms_h(pms_url('client_login.php')) ?>"><i class="fas fa-sign-in-alt"></i> <span class="btn-label">Client Login</span></a>
            <a class="btn-auth btn-auth-admin" href="<?= pms_h(pms_url('login.php')) ?>"><i class="fas fa-user-shield"></i> <span class="btn-label">Admin Login</span></a>
          <?php endif; ?>
        </div>
        <button type="button" class="menu-pill" id="menuToggle" aria-label="Menu" aria-expanded="false" aria-controls="navbar">
          <i class="fas fa-bars" aria-hidden="true"></i>
          <span>Menu</span>
        </button>
      </div>
    </div>
  </header>
