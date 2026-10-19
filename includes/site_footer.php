<?php
require_once __DIR__ . '/site_config.php';
?>
  <footer class="footer" id="enquiry">
    <div class="footer-grid">
      <div class="footer-col">
        <a href="<?= pms_h(pms_url('index.php')) ?>">
          <img class="footer-logo-img" src="<?= pms_h(pms_url('img/makeindia2.png')) ?>" alt="Make in India Capital PMS">
        </a>
        <h3>MAKE IN INDIA CAPITAL PMS</h3>
        <p>SEBI-registered Portfolio Management Service focused on small-cap investing.</p>
        <p class="footer-sebi">SEBI Registration: INP000006554</p>
      </div>
      <div class="footer-col footer-col-links">
        <h4>Quick links</h4>
        <ul class="footer-links-grid">
          <li><a href="<?= pms_h(pms_url('index.php')) ?>">Home</a></li>
          <li><a href="<?= pms_h(pms_url('about.php')) ?>">About Us</a></li>
          <li><a href="<?= pms_h(pms_url('who-should-invest.php')) ?>">Who Should Invest</a></li>
          <li><a href="<?= pms_h(pms_url('investment-philosophy.php')) ?>">Investment Philosophy</a></li>
          <li><a href="<?= pms_h(pms_url('performance/')) ?>">Performance</a></li>
          <li><a href="<?= pms_h(pms_url('team/')) ?>">Team</a></li>
          <li><a href="<?= pms_h(pms_url('faq/')) ?>">FAQs</a></li>
          <li><a href="<?= pms_h(pms_url('contact-us/')) ?>">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <p>Unit No. 1402, 14th Floor, Wing B,<br>Signature Corporate Park, G-Block,<br>BKC, Bandra East, Mumbai 400051</p>
        <p><a href="tel:+912247437863">+91 22 4743 7863</a><br><a href="tel:18002124592">1800 212 4592</a></p>
        <p><a href="mailto:investor@makeindiapms.com">investor@makeindiapms.com</a></p>
      </div>
      <div class="footer-col callback-form">
        <h4>Enquire</h4>
        <form class="pms-enquiry-form" action="<?= pms_h(pms_url('submit.php')) ?>" method="POST" novalidate>
          <input type="hidden" name="return_to" value="<?= pms_h(pms_url('index.php')) ?>">
          <input type="hidden" name="ajax" value="1">
          <input type="text" name="name" placeholder="Name" required minlength="2" autocomplete="name">
          <input type="tel" name="phone" placeholder="Phone" required pattern="[0-9+\-\s()]{10,18}" autocomplete="tel">
          <input type="email" name="email" placeholder="Email" required autocomplete="email">
          <textarea name="message" placeholder="Message (min. 10 characters)" required minlength="10"></textarea>
          <p class="enquiry-field-error" hidden></p>
          <button type="submit">Send enquiry</button>
          <p class="enquiry-status enquiry-form-status" hidden role="status"></p>
        </form>
      </div>
    </div>
    <div class="copyright">
      <p>© <?= date('Y') ?> Make in India Capital PMS. All rights reserved.</p>
    </div>
  </footer>
  <a href="#top" class="back-to-top" id="backToTop" aria-label="Back to top" title="Back to top">
    <i class="fas fa-arrow-up" aria-hidden="true"></i>
    <span class="back-to-top-text">TOP</span>
  </a>
