// Scroll-animatie voor elementen met .scroll-animate
function onScrollAnimate() {
  const elements = document.querySelectorAll('.scroll-animate');
  const triggerBottom = window.innerHeight * 0.92;
  elements.forEach(el => {
    const boxTop = el.getBoundingClientRect().top;
    if (boxTop < triggerBottom) {
      el.classList.add('visible');
    } else {
      el.classList.remove('visible');
    }
  });
}
window.addEventListener('scroll', onScrollAnimate);
window.addEventListener('load', onScrollAnimate);
