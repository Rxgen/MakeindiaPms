const toggleBtn = document.getElementById("menuToggle");
const closeBtn = document.getElementById("menuClose");
const nav = document.getElementById("navbar");
const overlay = document.getElementById("navOverlay");
const siteHeader = document.querySelector(".main-header");

function updateHeaderScroll() {
  if (!siteHeader) {
    return;
  }
  siteHeader.classList.toggle("is-scrolled", window.scrollY > 12);
}

updateHeaderScroll();
window.addEventListener("scroll", updateHeaderScroll, { passive: true });

function isMobileNav() {
  return window.matchMedia("(max-width: 992px)").matches;
}

function setNavOpen(open) {
  if (!nav) {
    return;
  }
  nav.classList.toggle("active", open);
  if (overlay) {
    overlay.classList.toggle("active", open);
    if (open) {
      overlay.removeAttribute("hidden");
    } else {
      overlay.setAttribute("hidden", "");
    }
  }
  document.body.classList.toggle("nav-open", open);
  if (toggleBtn) {
    toggleBtn.setAttribute("aria-expanded", open ? "true" : "false");
    const icon = toggleBtn.querySelector("i");
    if (icon) {
      icon.className = open ? "fas fa-times" : "fas fa-bars";
    }
  }
}

function closeNav() {
  setNavOpen(false);
}

if (toggleBtn && nav) {
  setNavOpen(false);

  toggleBtn.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!isMobileNav()) {
      return;
    }
    setNavOpen(!nav.classList.contains("active"));
  });

  if (closeBtn) {
    closeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      closeNav();
    });
  }

  if (overlay) {
    overlay.addEventListener("click", () => {
      closeNav();
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeNav();
    }
  });

  nav.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      if (!isMobileNav()) {
        return;
      }
      const parent = link.parentElement;
      if (parent && parent.classList.contains("has-submenu") && parent.querySelector(".submenu")) {
        return;
      }
      closeNav();
    });
  });

  nav.querySelectorAll(".has-submenu > a").forEach((parentLink) => {
    parentLink.addEventListener("click", (e) => {
      if (!isMobileNav()) {
        return;
      }
      const parent = parentLink.parentElement;
      if (parent && parent.querySelector(".submenu")) {
        e.preventDefault();
        parent.classList.toggle("open");
      }
    });
  });

  window.addEventListener("resize", () => {
    if (!isMobileNav()) {
      closeNav();
    }
  });
}

/* Smooth homepage slider */
(function initHomeSlider() {
  const slider = document.getElementById("homeSlider") || document.querySelector(".slider");
  if (!slider) {
    return;
  }

  const track = slider.querySelector(".slides");
  const slides = slider.querySelectorAll(".slide");
  const prevBtn = document.getElementById("slidePrev") || slider.querySelector(".prev");
  const nextBtn = document.getElementById("slideNext") || slider.querySelector(".next");
  const dotsWrap = document.getElementById("sliderDots") || slider.querySelector(".slider-dots");
  if (!track || !slides.length) {
    return;
  }

  let current = 0;
  let timer = null;
  const delay = 2800;
  const dots = [];

  if (dotsWrap) {
    dotsWrap.innerHTML = "";
    slides.forEach((_, i) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.setAttribute("aria-label", "Go to slide " + (i + 1));
      btn.addEventListener("click", () => {
        goTo(i);
        restart();
      });
      dotsWrap.appendChild(btn);
      dots.push(btn);
    });
  }

  function goTo(index) {
    current = (index + slides.length) % slides.length;
    track.style.transform = "translateX(-" + current * 100 + "%)";
    slides.forEach((slide, i) => {
      slide.classList.toggle("active", i === current);
    });
    dots.forEach((dot, i) => {
      dot.classList.toggle("active", i === current);
    });
  }

  function next() {
    goTo(current + 1);
  }

  function prev() {
    goTo(current - 1);
  }

  function restart() {
    clearInterval(timer);
    timer = setInterval(next, delay);
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", () => {
      prev();
      restart();
    });
  }
  if (nextBtn) {
    nextBtn.addEventListener("click", () => {
      next();
      restart();
    });
  }

  slider.addEventListener("mouseenter", () => clearInterval(timer));
  slider.addEventListener("mouseleave", restart);

  let touchStartX = 0;
  slider.addEventListener(
    "touchstart",
    (e) => {
      touchStartX = e.changedTouches[0].screenX;
    },
    { passive: true }
  );
  slider.addEventListener(
    "touchend",
    (e) => {
      const dx = e.changedTouches[0].screenX - touchStartX;
      if (Math.abs(dx) < 40) {
        return;
      }
      if (dx < 0) {
        next();
      } else {
        prev();
      }
      restart();
    },
    { passive: true }
  );

  goTo(0);
  restart();
})();

const authBar = document.getElementById("authBar");
if (authBar) {
  fetch("/client_session_bar.php", { credentials: "same-origin" })
    .then((r) => (r.ok ? r.text() : ""))
    .then((html) => {
      if (!html) {
        return;
      }
      authBar.innerHTML = html;
      const loggedIn = html.indexOf("client_logout.php") !== -1;
      const navUl = document.querySelector("#navbar > ul");
      if (navUl) {
        const existing = navUl.querySelector("#navPortfolio, a[href='client_dashboard.php'], a[href='/client_dashboard.php']");
        if (loggedIn) {
          if (!existing) {
            const li = document.createElement("li");
            li.id = "navPortfolio";
            li.innerHTML = '<a href="/client_dashboard.php">My Portfolio</a>';
            navUl.appendChild(li);
          }
        } else if (existing) {
          const li = existing.closest("li") || existing;
          if (li && li.parentNode === navUl) {
            li.remove();
          }
        }
      }
    })
    .catch(() => {});
}

/* Fixed back-to-top button */
(function initBackToTop() {
  const btn = document.getElementById("backToTop");
  if (!btn) {
    return;
  }
  function update() {
    btn.classList.toggle("is-visible", window.scrollY > 280);
  }
  update();
  window.addEventListener("scroll", update, { passive: true });
})();

/* Enquiry form basic field validation (footer + contact) */
(function initEnquiryForms() {
  const forms = document.querySelectorAll(".pms-enquiry-form");
  if (!forms.length) {
    return;
  }

  function digits(value) {
    return String(value || "").replace(/\D+/g, "");
  }

  function showError(form, message) {
    const box = form.querySelector(".enquiry-field-error");
    if (!box) {
      return;
    }
    box.hidden = !message;
    box.textContent = message || "";
  }

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const name = form.querySelector('[name="name"]');
      const phone = form.querySelector('[name="phone"]');
      const email = form.querySelector('[name="email"]');
      const message = form.querySelector('[name="message"]');
      const fields = [name, phone, email, message].filter(Boolean);

      fields.forEach((el) => el.classList.remove("is-invalid"));
      showError(form, "");

      const nameVal = name ? name.value.trim() : "";
      const phoneVal = phone ? digits(phone.value) : "";
      const emailVal = email ? email.value.trim() : "";
      const messageVal = message ? message.value.trim() : "";
      let err = "";

      if (nameVal.length < 2) {
        err = "Please enter your full name.";
        if (name) name.classList.add("is-invalid");
      } else if (phoneVal.length < 10 || phoneVal.length > 15) {
        err = "Please enter a valid phone number (10–15 digits).";
        if (phone) phone.classList.add("is-invalid");
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
        err = "Please enter a valid email address.";
        if (email) email.classList.add("is-invalid");
      } else if (messageVal.length < 10) {
        err = "Please enter a message of at least 10 characters.";
        if (message) message.classList.add("is-invalid");
      }

      if (err) {
        e.preventDefault();
        showError(form, err);
      }
    });
  });
})();
