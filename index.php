<?php
$page_title = 'Make in India Capital PMS';
$body_class = 'page-home';
require __DIR__ . '/includes/site_head.php';
?>

  <div class="slider" id="homeSlider" aria-roledescription="carousel">
    <div class="slides">
      <div class="slide active">
        <picture>
          <source media="(max-width: 767px)" srcset="<?= pms_h(pms_url('img/hero-mobile-1.jpg')) ?>">
          <img src="<?= pms_h(pms_url('img/hero-desktop-1.jpg')) ?>" alt="Make in India Capital PMS" loading="eager">
        </picture>
      </div>
      <div class="slide">
        <picture>
          <source media="(max-width: 767px)" srcset="<?= pms_h(pms_url('img/hero-mobile-2.jpg')) ?>">
          <img src="<?= pms_h(pms_url('img/hero-desktop-2.jpg')) ?>" alt="Portfolio management" loading="lazy">
        </picture>
      </div>
      <div class="slide">
        <picture>
          <source media="(max-width: 767px)" srcset="<?= pms_h(pms_url('img/hero-mobile-3.jpg')) ?>">
          <img src="<?= pms_h(pms_url('img/hero-desktop-3.jpg')) ?>" alt="Long-term investing" loading="lazy">
        </picture>
      </div>
    </div>
    <div class="slider-scrim"></div>
    <div class="slider-copy">
      <p class="slider-kicker">SEBI Registered PMS · INP000006554</p>
      <h1>Make in India Capital <em>PMS</em></h1>
      <p class="slider-lead">Patient small-cap investing built on research, promoter skin in the game, and long-term compounding.</p>
      <div class="slider-actions">
        <a class="btn-hero" href="<?= pms_h(pms_url('client_login.php')) ?>">Client Login</a>
        <a class="btn-hero-ghost" href="<?= pms_h(pms_url('about.php')) ?>">About us</a>
      </div>
    </div>
    <button type="button" class="prev" id="slidePrev" aria-label="Previous slide">&#10094;</button>
    <button type="button" class="next" id="slideNext" aria-label="Next slide">&#10095;</button>
    <div class="slider-dots" id="sliderDots" aria-hidden="true"></div>
  </div>

  <section class="stats-section" style="--stats-bg: url('<?= pms_h(pms_url('img/hero-desktop-3.jpg')) ?>')">
    <div class="stats-section-inner">
      <div class="stats-band-head">
        <p class="section-kicker light">At a glance</p>
        <h2 class="stats-title">Capital + Knowledge + Patience = Fortune</h2>
      </div>
      <div class="stats-row" role="list">
        <article class="stat-card" role="listitem">
          <span class="stat-icon" aria-hidden="true"><i class="fas fa-coins"></i></span>
          <p class="stat-value"><span class="currency">₹</span>770<span class="unit">Cr</span></p>
          <p class="stat-label">Assets under management</p>
        </article>
        <article class="stat-card" role="listitem">
          <span class="stat-icon" aria-hidden="true"><i class="fas fa-chart-line"></i></span>
          <p class="stat-value">100<span class="unit">%</span></p>
          <p class="stat-label">Equity focused</p>
        </article>
        <article class="stat-card" role="listitem">
          <span class="stat-icon" aria-hidden="true"><i class="fas fa-users"></i></span>
          <p class="stat-value">557</p>
          <p class="stat-label">Investors</p>
        </article>
      </div>
      <p class="as-on">*As on 20 July 2025</p>
    </div>
  </section>

  <section class="home-intro">
    <div class="home-intro-grid">
      <div>
        <p class="section-kicker">SEBI registered PMS</p>
        <h2>About the company</h2>
        <p>Ni Make in India Investments is a SEBI-registered Portfolio Management Service (INP000006554) since July 2019. We invest in small companies with the aim of generating long-term, market-beating returns.</p>
        <p>Our work is concentrated on spotting, evaluating and holding promising small-capitalisation businesses — bought at a discount to intrinsic value, held with patience, and reviewed when fundamentals change.</p>
        <a class="text-link" href="<?= pms_h(pms_url('about.php')) ?>">Read the full about page →</a>
      </div>
      <ul class="home-highlights">
        <li><i class="fas fa-check-circle"></i> Focused small-cap research and on-ground diligence</li>
        <li><i class="fas fa-check-circle"></i> No upfront management fee; performance fee after 10% p.a.</li>
        <li><i class="fas fa-check-circle"></i> Promoters have around 20 years of small-cap experience</li>
        <li><i class="fas fa-check-circle"></i> Honesty and forthrightness as operating policy</li>
      </ul>
    </div>
  </section>

  <section class="home-cards-section">
    <div class="home-cards-wrap">
      <p class="section-kicker">Why investors work with us</p>
      <h2>Our USP</h2>
      <div class="home-card-grid">
        <article class="home-card">
          <span class="card-num">01</span>
          <h3>Research first</h3>
          <p>Plant visits, AGMs and channel checks across the country, not a model-portfolio approach.</p>
        </article>
        <article class="home-card">
          <span class="card-num">02</span>
          <h3>Skin in the game</h3>
          <p>Over a third of AUM belongs to the promoters. Incentives stay aligned with clients.</p>
        </article>
        <article class="home-card">
          <span class="card-num">03</span>
          <h3>Bespoke portfolios</h3>
          <p>Every investor has a unique portfolio. We do not run one model book for everyone.</p>
        </article>
        <article class="home-card">
          <span class="card-num">04</span>
          <h3>Simple fee idea</h3>
          <p>No upfront fixed fee, entry load or exit load. We charge after investors earn over 10% p.a.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="home-who">
    <div class="home-cards-wrap">
      <p class="section-kicker">Suitability</p>
      <h2>Who should invest</h2>
      <div class="home-card-grid">
        <article class="home-card">
          <h3>5-year patience</h3>
          <p>Business profits take time to compound. This product is for investors who can stay invested.</p>
        </article>
        <article class="home-card">
          <h3>Risk awareness</h3>
          <p>Small caps carry market risk and liquidity risk. Volatility is normal, not an exception.</p>
        </article>
        <article class="home-card">
          <h3>Reasonable return</h3>
          <p>Think 15–25% CAGR over time. There is no promise of linear or guaranteed returns.</p>
        </article>
        <article class="home-card">
          <h3>Existing equity capital</h3>
          <p>Best suited to investors reallocating from existing equity, not adding fresh market risk.</p>
        </article>
      </div>
      <p class="section-more"><a class="text-link" href="<?= pms_h(pms_url('who-should-invest.php')) ?>">See the full suitability note →</a></p>
    </div>
  </section>

  <div class="note-section">
    <strong>Note</strong>
    Due to expensive valuations, we are currently accepting funds only from those investors who are reallocating funds from existing equity holdings (like equity mutual funds, PMS/AIF, direct stock holdings) to our PMS, wherein there is no additional <span class="highlight">MARKET RISK</span> as their net equity exposure remains unchanged.
  </div>

  <section class="home-cta">
    <div class="home-cta-inner">
      <div>
        <h2>Existing clients</h2>
        <p>View your folio, valuation and transaction history in the client portal.</p>
      </div>
      <a class="cta-btn" href="<?= pms_h(pms_url('client_login.php')) ?>">Client Login</a>
    </div>
  </section>

<?php require __DIR__ . '/includes/site_foot.php'; ?>
