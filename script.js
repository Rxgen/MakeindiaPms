const toggleBtn = document.getElementById("menuToggle");
const nav = document.getElementById("navbar");

if (toggleBtn && nav) {
  toggleBtn.addEventListener("click", () => {
    nav.classList.toggle("active");
  });
}

let currentSlide = 0;
const slides = document.querySelectorAll(".slide");

function showSlide(index) {
  if (!slides.length) {
    return;
  }
  slides.forEach((slide, i) => {
    slide.classList.remove("active");
    slide.style.display = "none";
  });

  slides[index].classList.add("active");
  slides[index].style.display = "block";
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
