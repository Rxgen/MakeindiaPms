<?php
$page_title = 'Contact | Make in India Capital PMS';
require dirname(__DIR__) . '/includes/site_head.php';
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <p class="section-kicker">Reach us</p>
    <h1>Contact</h1>
    <p>Speak with us about the PMS or send an enquiry.</p>
  </div>
</section>

<section class="page-content">
  <div class="page-wrap contact-layout">
    <div class="contact-cards">
      <div class="contact-card">
        <i class="fas fa-map-marker-alt"></i>
        <h3>Office</h3>
        <p>Unit No. 1402, 14th Floor, Wing B,<br>Signature Corporate Park, G-Block,<br>BKC, Bandra East, Mumbai 400051</p>
      </div>
      <div class="contact-card">
        <i class="fas fa-phone"></i>
        <h3>Phone</h3>
        <p><a href="tel:+912247437863">+91 22 4743 7863</a><br><a href="tel:18002124592">1800 212 4592</a></p>
      </div>
      <div class="contact-card">
        <i class="fas fa-envelope"></i>
        <h3>Email</h3>
        <p><a href="mailto:investor@makeindiapms.com">investor@makeindiapms.com</a></p>
      </div>
      <div class="contact-card">
        <i class="fas fa-id-card"></i>
        <h3>SEBI Registration</h3>
        <p>INP000006554</p>
      </div>
    </div>

    <div class="contact-form-card" id="enquiry">
      <h2>Send an enquiry</h2>
      <?php
      $enquiryStatus = isset($_GET['enquiry']) ? (string) $_GET['enquiry'] : '';
      if ($enquiryStatus === '1' || $enquiryStatus === 'ok'): ?>
        <p class="enquiry-status ok" role="status">Thanks for your mail. Our team will contact you shortly.</p>
      <?php elseif ($enquiryStatus === 'error'): ?>
        <p class="enquiry-status err" role="alert">Please check name, phone, email and message, then try again.</p>
      <?php endif; ?>
      <form class="pms-enquiry-form" action="<?= pms_h(pms_url('submit.php')) ?>" method="POST" novalidate>
        <input type="hidden" name="return_to" value="<?= pms_h(pms_url('contact-us/')) ?>">
        <input type="text" name="name" placeholder="Name" required minlength="2" autocomplete="name">
        <input type="tel" name="phone" placeholder="Phone" required pattern="[0-9+\-\s()]{10,18}" autocomplete="tel">
        <input type="email" name="email" placeholder="Email" required autocomplete="email">
        <textarea name="message" placeholder="Message (min. 10 characters)" required minlength="10"></textarea>
        <p class="enquiry-field-error" hidden></p>
        <button type="submit">Send enquiry</button>
      </form>
    </div>
  </div>
</section>

<?php require dirname(__DIR__) . '/includes/site_foot.php'; ?>
