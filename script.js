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

let currentSlide = 0;
const slides = document.querySelectorAll(".slide");

function showSlide(index) {
  if (!slides.length) {
    return;
  }
  const track = document.querySelector(".slides");
  slides.forEach((slide) => {
    slide.classList.remove("active");
    slide.style.display = "";
  });
  slides[index].classList.add("active");
  if (track) {
    track.style.transform = "translateX(-" + index * 100 + "%)";
  }
}

function nextSlide() {
  if (!slides.length) {
    return;
  }
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}

function prevSlide() {
  if (!slides.length) {
    return;
  }
  currentSlide = (currentSlide - 1 + slides.length) % slides.length;
  showSlide(currentSlide);
}

if (slides.length) {
  showSlide(currentSlide);
  setInterval(nextSlide, 2000);
}

const topbarRight = document.querySelector(".topbar-right");
if (topbarRight) {
  fetch("/client_session_bar.php", { credentials: "same-origin" })
    .then((r) => (r.ok ? r.text() : ""))
    .then((html) => {
      if (!html) {
        return;
      }
      topbarRight.innerHTML = html;
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
