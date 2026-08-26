<script type="text/javascript" src="//www.freeprivacypolicy.com/public/cookie-consent/4.1.0/cookie-consent.js" charset="UTF-8"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>

<script src="<?php echo BASEURL; ?>js/swiper.js"></script>

<script type="text/javascript" charset="UTF-8">
document.addEventListener('DOMContentLoaded', function () {
    if (typeof cookieconsent !== 'undefined') {
        cookieconsent.run({
            "notice_banner_type": "simple",
            "consent_type": "express",
            "palette": "light",
            "language": "pt",
            "page_load_consent_levels": ["strictly-necessary"],
            "notice_banner_reject_button_hide": false,
            "preferences_center_close_button_hide": false,
            "page_refresh_confirmation_buttons": false,
            "website_name": "Pedacinho de Amor",
            "website_privacy_policy_url": "<?php echo BASEURL; ?>paginas/politica_privacidade.php"
        });
    }
});
</script>

<script>
const navToggle = document.querySelector(".nav-toggle");
const linksContainer = document.querySelector(".links-container");
const links = document.querySelector(".links");

if (navToggle && linksContainer && links) {
    navToggle.addEventListener("click", function(){
        const linksHeight = links.getBoundingClientRect().height;
        const containerHeight = linksContainer.getBoundingClientRect().height;

        if(containerHeight === 0){
            linksContainer.style.height = `${linksHeight + 80}px`;
        } else {
            linksContainer.style.height = 0;
        }
    });
}

/* deixar a navbar fixa ao rolar a pagina */
const navbar = document.getElementById("nav");

if (navbar) {
    window.addEventListener("scroll", function(){
        const scrollHeight = window.pageYOffset;
        if(scrollHeight > 80){
            navbar.classList.add("fixed-nav");
        } else {
            navbar.classList.remove("fixed-nav");
        }
    });
}
</script>

<style>
/* ── Banner de cookies (freeprivacypolicy) ── */
.freeprivacypolicy-com---nb {
    background-color: #fff5f8 !important;
    border-top: 3px solid #7a2f2f !important;
}
.cc-nb-title {
    color: #7a2f2f !important;
    font-weight: 700 !important;
}
.cc-nb-text {
    color: #444 !important;
}
button.cc-nb-okagree {
    background-color: #7a2f2f !important;
    border-color: #7a2f2f !important;
    color: #fff !important;
    border-radius: 30px !important;
}
button.cc-nb-reject {
    background-color: #fff !important;
    border: 2px solid #7a2f2f !important;
    color: #7a2f2f !important;
    border-radius: 30px !important;
}
button.cc-nb-changep {
    background-color: transparent !important;
    border: 2px solid #ccc !important;
    color: #555 !important;
    border-radius: 30px !important;
}
button.cc-nb-okagree:hover {
    background-color: #5c1e1e !important;
}
button.cc-nb-reject:hover {
    background-color: #ffdcec !important;
    color: #7a2f2f !important;
}
</style>

<script>
    // --- CARROSSEL PRINCIPAL ---
    const carrossel = document.getElementById('carrossel');
    const slides = document.querySelector('.slides');
    const images = document.querySelectorAll('.slides img');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const dotsContainer = document.querySelector('.dots');

    // Validação: Só executa se o carrossel existir na página atual
    if (carrossel && slides && images.length > 0 && prevBtn && nextBtn && dotsContainer) {
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
    }

    // --- FEEDBACKS ---
    const feedbackscarrossel = document.getElementById('feedbackscarrossel');
    const feedbackCards     = feedbackscarrossel ? feedbackscarrossel.querySelectorAll('.feedback-card') : [];
    const prevFeedbackBtn   = document.querySelector('.prev-feedback');
    const nextFeedbackBtn   = document.querySelector('.next-feedback');

    // Validação: Só executa se a estrutura de feedbacks existir na página atual
    if (feedbackscarrossel && feedbackCards.length > 0) {
        let feedbackIndex    = 0;
        let feedbackInterval = null;

        function visibleCount() { return 1; }
        function maxIndex() { return Math.max(0, feedbackCards.length - visibleCount()); }

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
            prevFeedbackBtn.addEventListener('click', () => { showFeedback(feedbackIndex - 1); resetFeedbackAutoplay(); });
        }
        if (nextFeedbackBtn) {
            nextFeedbackBtn.addEventListener('click', () => { showFeedback(feedbackIndex + 1); resetFeedbackAutoplay(); });
        }

        window.addEventListener('resize', () => showFeedback(feedbackIndex));
        showFeedback(0);
        resetFeedbackAutoplay();
    }
    
    // ── Posicionar dropdown ──
    const navDropdowns = document.querySelectorAll('.nav-dropdown');
    navDropdowns.forEach(dropdown => {
        const link = dropdown.querySelector('.nav-dropdown-link');
        const menu = dropdown.querySelector('.nav-dropdown-menu');
        
        if (link && menu) {
            function positionMenu() {
                const rect = link.getBoundingClientRect();
                menu.style.top = (rect.bottom + 5) + 'px';
                menu.style.left = rect.left + 'px';
            }
            
            dropdown.addEventListener('mouseenter', positionMenu);
            window.addEventListener('resize', positionMenu);
        }
    });
</script>

<footer>
  <div class="footer-container">

    <div class="footer-logo">
      <img src="<?php echo BASEURL; ?>imagens/logo.png" alt="Logo Pedacinho de Amor" style="max-width: 300px;">
      <p>Confeitaria artesanal, produtos feitos com o carinho de quem ama o que faz.</p>
      <p>CNPJ: 12.345.678/0001-90</p>

      <div class="footer-icones">
        <a href="https://www.instagram.com/_pedacinhodeamor_o?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank"><i class="fa-brands fa-instagram"></i></a>
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
