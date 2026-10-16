const slides = document.querySelectorAll('.slide');
let currentSlide = 0;
const slideInterval = 4000; // 4 seconds

function showSlide(index) {
  slides.forEach(slide => slide.classList.remove('active'));
  slides[index].classList.add('active');
  document.querySelector('.slides').style.transform = `translateX(-${index * 100}%)`;
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}

function prevSlide() {
  currentSlide = (currentSlide - 1 + slides.length) % slides.length;
  showSlide(currentSlide);
}

// Auto Slide
setInterval(nextSlide, slideInterval);

// Initialize first slide (if needed)
document.addEventListener("DOMContentLoaded", () => {
  showSlide(currentSlide);
});
