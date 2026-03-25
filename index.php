<?php
    include_once __DIR__ . '/inc/header.php';
?>
    <h1>Bem-vindo ao Pedacinho de Amor</h1>
    <p>Neste site voce vera inumeros doces e pratos de dar agua na boca, 
        com um tempero exclusivom no qual chamamos de amor.</p>

    <h2>Carrossel</h2>
    <div class="carousel" id="carousel" tabindex="0">
    <div class="slides">
        <img src="imagens/doce1.webp" alt="Imagem 1">
        <img src="imagens/doce2.webp" alt="Imagem 2">
        <img src="imagens/doce3.webp" alt="Imagem 3">
    </div>
    <button class="prev">&#10094;</button>
    <button class="next">&#10095;</button>
    <div class="dots"></div>
    </div>



    <script src="js/bootstrap/bootstrap.min.js"></script>

    <script>
    const carousel = document.getElementById('carousel');
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

    // Prev / Next
    prevBtn.addEventListener('click', () => showImage(index - 1));
    nextBtn.addEventListener('click', () => showImage(index + 1));

    // Create dots
    for (let i = 0; i < total; i++) {
        const btn = document.createElement('button');
        btn.setAttribute('aria-label', `Ir para slide ${i+1}`);
        btn.addEventListener('click', () => showImage(i));
        dotsContainer.appendChild(btn);
    }

    // Autoplay with pause on hover
    function startAutoplay() { intervalId = setInterval(() => showImage(index + 1), 4000); }
    function stopAutoplay() { if (intervalId) { clearInterval(intervalId); intervalId = null; } }

    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);

    // Keyboard accessibility
    carousel.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') showImage(index - 1);
        if (e.key === 'ArrowRight') showImage(index + 1);
    });

    // Init
    showImage(0);
    startAutoplay();
    </script>
             
<!-- MODAL -->
<div class="modal fade" id="modalLogin" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Login</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Aqui o login.html será carregado -->
        <iframe src="login.html" width="100%" height="300" style="border:none;"></iframe>

      </div>

    </div>
  </div>
</div>   
   
</body>
</html>