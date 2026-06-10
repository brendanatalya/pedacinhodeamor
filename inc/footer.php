<script>

const navToggle = document.querySelector(".nav-toggle");
const linksContainer = document.querySelector(".links-container");
const links = document.querySelector(".links");

navToggle.addEventListener("click", function(){

    const linksHeight = links.getBoundingClientRect().height;

    const containerHeight = linksContainer.getBoundingClientRect().height;

    if(containerHeight === 0){
        linksContainer.style.height = `${linksHeight + 80}px`;
    } else {
        linksContainer.style.height = 0;
    }

});

/* deixar a navbar fixa ao rolar a pagina */

const navbar = document.getElementById("nav");

window.addEventListener("scroll", function(){

    const scrollHeight = window.pageYOffset;

    if(scrollHeight > 80){
        navbar.classList.add("fixed-nav");
    } else {
        navbar.classList.remove("fixed-nav");
    }

});

</script>

<script>
    // --- CARROSSEL PRINCIPAL ---
    const carrossel = document.getElementById('carrossel');
    const slides = document.querySelector('.slides');
    const images = document.querySelectorAll('.slides img');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const dotsContainer = document.querySelector('.dots');

    let index = 0;
    const total = images.length;
    let intervalId = null;

    function updateDots() {
        const dots = dotsContainer.querySelectorAll('button');
        dots.forEach((d, i) => d.classList.toggle('active', i === index));
    }

    function showImage(i) {
        index = (i + total) % total;
        slides.style.transform = `translateX(${-index * 100}%)`;
        updateDots();
    }

    prevBtn.addEventListener('click', () => showImage(index - 1));
    nextBtn.addEventListener('click', () => showImage(index + 1));

    for (let i = 0; i < total; i++) {
        const btn = document.createElement('button');
        btn.addEventListener('click', () => showImage(i));
        dotsContainer.appendChild(btn);
    }

    function startAutoplay() { intervalId = setInterval(() => showImage(index + 1), 4000); }
    function stopAutoplay() { clearInterval(intervalId); }

    carrossel.addEventListener('mouseenter', stopAutoplay);
    carrossel.addEventListener('mouseleave', startAutoplay);
    showImage(0);
    startAutoplay();

    // --- FEEDBACKS (2 cards visíveis, avança 1 por vez) ---
    const feedbackscarrossel = document.getElementById('feedbackscarrossel');
    const feedbackCards     = feedbackscarrossel ? feedbackscarrossel.querySelectorAll('.feedback-card') : [];
    const prevFeedbackBtn   = document.querySelector('.prev-feedback');
    const nextFeedbackBtn   = document.querySelector('.next-feedback');

    let feedbackIndex    = 0;
    let feedbackInterval = null;

    // Quantos cards ficam visíveis de uma vez (muda no mobile via JS)
    function visibleCount() {
        return 1;
    }

    function maxIndex() {
        return Math.max(0, feedbackCards.length - visibleCount());
    }

    function showFeedback(i) {
        feedbackIndex = Math.max(0, Math.min(i, maxIndex()));
        const track = feedbackscarrossel.parentElement;
        const trackWidth = track ? track.clientWidth : 0;
        const offset = feedbackIndex * trackWidth;
        feedbackscarrossel.style.transform = `translateX(-${offset}px)`;
    }

    function resetFeedbackAutoplay() {
        clearInterval(feedbackInterval);
        feedbackInterval = setInterval(() => {
            const next = feedbackIndex >= maxIndex() ? 0 : feedbackIndex + 1;
            showFeedback(next);
        }, 6000);
    }

    if (prevFeedbackBtn) {
        prevFeedbackBtn.addEventListener('click', () => {
            showFeedback(feedbackIndex - 1);
            resetFeedbackAutoplay();
        });
    }

    if (nextFeedbackBtn) {
        nextFeedbackBtn.addEventListener('click', () => {
            showFeedback(feedbackIndex + 1);
            resetFeedbackAutoplay();
        });
    }

    window.addEventListener('resize', () => showFeedback(feedbackIndex));
    showFeedback(0);
    resetFeedbackAutoplay(); 
</script>


<script src="js/bootstrap/bootstrap.bundle.min.js"></script>

<footer>
  <div class="footer-container">

    <div class="footer-logo">
      <img src="<?php echo BASEURL; ?>imagens/logo.png" alt="Logo Pedacinho de Amor" style="max-width: 300px;">
      <p>Confeitaria artesanal, produtos feitos com o carinho de quem ama o que faz.</p>
      <p>CNPJ: 12.345.678/0001-90</p>

      <div class="footer-icones">
        <a href="https://www.instagram.com/_pedacinhodeamor_o?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank"><i class="fa-brands fa-instagram"></i></a>
        <!-- <a href="#"><i class="fa-brands fa-facebook-f"></i></a> -->
      </div>
    </div>

    <div class="footer-contato">
      <h3>Contato</h3>
      <p><i class="fa-solid fa-location-dot"></i> Rua das Flores, 123<br>Centro · São Paulo</p>
      <p><i class="fa-solid fa-phone"></i> (11) 9 8765-4321</p>
      <p><i class="fa-regular fa-envelope"></i> ola@pedacinhodeamor.com.br</p>
    </div>

  </div>

  <div class="footer-copyright">
    <p>© 2026 Pedacinho de Amor. Todos os direitos reservados.</p>
    <p>Feito com <i class="fa-solid fa-heart" style="color: #8f1a5a;"></i> para adoçar seus momentos.</p>
  </div>
</footer>
<script src="js/cookies.js"></script>
