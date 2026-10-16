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

    <div class="contact-form-card">
      <h2>Send an enquiry</h2>
      <form action="<?= pms_h(pms_url('submit.php')) ?>" method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <input type="tel" name="phone" placeholder="Phone" required>
        <input type="email" name="email" placeholder="Email" required>
        <textarea name="message" placeholder="Message" required></textarea>
        <button type="submit">Send enquiry</button>
      </form>
    </div>
  </div>
</section>

<?php require dirname(__DIR__) . '/includes/site_foot.php'; ?>
