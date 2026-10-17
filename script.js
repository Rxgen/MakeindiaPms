const toggleBtn = document.getElementById("menuToggle");
const closeBtn = document.getElementById("menuClose");
const nav = document.getElementById("navbar");
const overlay = document.getElementById("navOverlay");

function isMobileNav() {
  return window.matchMedia("(max-width: 992px)").matches;
}

function setMobileNav(open) {
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

function closeMobileNav() {
  setMobileNav(false);
}

if (toggleBtn && nav) {
  setMobileNav(false);

  toggleBtn.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!isMobileNav()) {
      return;
    }
    setMobileNav(!nav.classList.contains("active"));
  });

  if (closeBtn) {
    closeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      closeMobileNav();
    });
  }

  if (overlay) {
    overlay.addEventListener("click", () => {
      closeMobileNav();
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeMobileNav();
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
      closeMobileNav();
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
      closeMobileNav();
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
  const delay = 4500;
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

const authBar = document.getElementById("authBar") || document.querySelector(".header-right");
if (authBar) {
  fetch("/client_session_bar.php", { credentials: "same-origin" })
    .then((r) => (r.ok ? r.text() : ""))
    .then((html) => {
      if (!html) {
        return;
      }
      const burger = document.getElementById("menuToggle");
      authBar.innerHTML = html;
      if (burger) {
        authBar.appendChild(burger);
      }
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
