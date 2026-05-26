<?php 
if(!isset($_SESSION)) session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedacinho de Amor</title>
    <link rel="stylesheet" href="css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!--<link rel="stylesheet" href="css_pda/">-->
</head>

<body>
<header>
    
    <h1>Pedacinho de Amor</h1>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="paginas/sobrenos.html">Sobre Nós</a></li>
            <li><a href="paginas/doces.php">Doces</a></li>
            <li><a href="paginas/salgados.php">Salgados</a></li>
            <li><a href="paginas/personalizados.php">Personalizados</a></li>
            
          <?php if(!empty($_SESSION['logado']) && $_SESSION['logado'] === true): ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            👤 Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>
        </a>

        <ul class="dropdown-menu" aria-labelledby="userDropdown">
            <li>
                <a class="dropdown-item" href="minha_conta.php">
                    Gerenciar Conta
                </a>
            </li>

            <li>
                <a class="dropdown-item" href="inc/logout.php">
                    Sair
                </a>
            </li>
        </ul>
    </li>
<?php else: ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLogin">
        Login
    </button>
<?php endif; ?>
   
                <li><a href="paginas/carrinho.php"><i class="fas fa-shopping-cart"></i> Carrinho (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</a></li>

            
        </ul>
    </nav>
</header>
<main>
    <section class="index-bemvindo">
        <!-- primeira foto do site -->
        <div class="bemvindo-fundo"></div>


        <div class="conteudobemvindo">
            <p class="subtitulo">Confeitaria artesanal</p>
            <h1 class="bemvindo-titulo">
                Feito com<br>
                <em>amor e cuidado</em>
            </h1>
            <p class="bemvindo-subttitulo">
                Cada doce é um pedaço de aconchego<br>
                e carinho que buscamos levar até você.
            </p>
            <div class="bemvindo-botoes">
                <a href="doces.php" class="botaoclaro">Encomendar</a>
                <a href="sobrenos.html" class="botaoescuro">Sobre nós</a>
            </div>
        </div>

        <!-- linha bonitinha -->
        <div class="detalhe">
            <span></span>
        </div>
    </section>


</main>

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

<!-- PArte dos carss fofinhos rs -->
    <section class="popular-products py-5">
        <div class="container position-relative">
            <h2 class="text-center mb-4">Esses fazem sucesso por aqui!</h2>
            <div class="product-slider-wrapper">
                <div class="product-slider">
                    
                    <div class="product-card" style="background-image: url('imagens/doce1.webp');">
                       
                        <div class="product-card-body">
                            <span>Croissants</span>
                            <button class="btn btn-confira" src="./paginas/doces.php">Confira</button>
                        </div>
                    </div>
                    
                    <div class="product-card" style="background-image: url('imagens/doce2.webp');">
                        <div class="product-card-body">
                            <span>Cookies</span>
                            <button class="btn btn-confira">Confira</button>
                        </div>
                    </div>
                  
                    <div class="product-card" style="background-image: url('imagens/doce2.webp');">
                        <div class="product-card-body">
                            <span>Bolos &nbsp;</span>
                            <button class="btn btn-confira">Confira</button>
                        </div>
                    </div>
                  
                    <div class="product-card" style="background-image: url('imagens/doce3.webp');">
                        <div class="product-card-body">
                            <span>Brownies</span>
                            <button class="btn btn-confira">Confira</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEEDBACKS SECTION -->
    <section class="feedbacks-section py-5">
        <div class="container">
            <div class="feedbacks-header">
                <h2>Eles amam! Feedbacks que amamos!</h2>
            </div>
            <div class="feedbacks-wrapper">
                <button class="feedback-arrow prev-feedback" type="button" aria-label="Feedback anterior">&#10094;</button>
                <div class="feedbacks-carousel-track">
                    <div class="feedbacks-carousel" id="feedbacksCarousel">
                        <div class="feedback-card feedback-card-large">
                            <div class="feedback-user">
                                <svg class="feedback-avatar" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="23" cy="23" r="23" fill="#e8d5f0"/><circle cx="23" cy="18" r="8" fill="#a855f7"/><ellipse cx="23" cy="36" rx="13" ry="8" fill="#a855f7"/></svg>
                                <div class="feedback-info">
                                    <h4>Gabriela Ruiz</h4>
                                    <p>@gabrielaclientefiel</p>
                                </div>
                            </div>
                            <p class="feedback-text">adoro a qualidade e rapidez de entrega. Pedi um bolo pro aniversario do meu filho, e chegou rapidinho, estava impecável, todo mundo amou!!</p>
                        </div>

                        <div class="feedback-card feedback-card-large">
                            <div class="feedback-user">
                                <svg class="feedback-avatar" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="23" cy="23" r="23" fill="#e8d5f0"/><circle cx="23" cy="18" r="8" fill="#a855f7"/><ellipse cx="23" cy="36" rx="13" ry="8" fill="#a855f7"/></svg>
                                <div class="feedback-info">
                                    <h4>Gabriela Ruiz</h4>
                                    <p>@gabrielaclientefiel</p>
                                </div>
                            </div>
                            <p class="feedback-text">adoro a qualidade e rapidez de entrega. Pedi um bolo pro aniversario do meu filho, e chegou rapidinho, estava impecável, todo mundo amou!!</p>
                        </div>

                        <div class="feedback-card feedback-card-large">
                            <div class="feedback-user">
                                <svg class="feedback-avatar" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="23" cy="23" r="23" fill="#e8d5f0"/><circle cx="23" cy="18" r="8" fill="#a855f7"/><ellipse cx="23" cy="36" rx="13" ry="8" fill="#a855f7"/></svg>
                                <div class="feedback-info">
                                    <h4>Gabriela Ruiz</h4>
                                    <p>@gabrielaclientefiel</p>
                                </div>
                            </div>
                            <p class="feedback-text">adoro a qualidade e rapidez de entrega. Pedi um bolo pro aniversario do meu filho, e chegou rapidinho, estava impecável, todo mundo amou!!</p>
                        </div>

                       <div class="feedback-card feedback-card-large">
                            <div class="feedback-user">
                                <svg class="feedback-avatar" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="23" cy="23" r="23" fill="#e8d5f0"/><circle cx="23" cy="18" r="8" fill="#a855f7"/><ellipse cx="23" cy="36" rx="13" ry="8" fill="#a855f7"/></svg>
                                <div class="feedback-info">
                                    <h4>Gabriela Ruiz</h4>
                                    <p>@gabrielaclientefiel</p>
                                </div>
                            </div>
                            <p class="feedback-text">adoro a qualidade e rapidez de entrega. Pedi um bolo pro aniversario do meu filho, e chegou rapidinho, estava impecável, todo mundo amou!!</p>
                        </div>
                    </div>
                </div>
                <button class="feedback-arrow next-feedback" type="button" aria-label="Próximo feedback">&#10095;</button>
            </div>
        </div>
    </section>



    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
<?php include 'inc/modal.php'; ?>
<script>
    // --- CARROSSEL PRINCIPAL ---
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

    prevBtn.addEventListener('click', () => showImage(index - 1));
    nextBtn.addEventListener('click', () => showImage(index + 1));

    for (let i = 0; i < total; i++) {
        const btn = document.createElement('button');
        btn.addEventListener('click', () => showImage(i));
        dotsContainer.appendChild(btn);
    }

    function startAutoplay() { intervalId = setInterval(() => showImage(index + 1), 4000); }
    function stopAutoplay() { clearInterval(intervalId); }

    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    showImage(0);
    startAutoplay();

    // --- FEEDBACKS (2 cards visíveis, avança 1 por vez) ---
    const feedbacksCarousel = document.getElementById('feedbacksCarousel');
    const feedbackCards     = feedbacksCarousel ? feedbacksCarousel.querySelectorAll('.feedback-card') : [];
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
        const track = feedbacksCarousel.parentElement;
        const trackWidth = track ? track.clientWidth : 0;
        const offset = feedbackIndex * trackWidth;
        feedbacksCarousel.style.transform = `translateX(-${offset}px)`;
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
</body>
</html>