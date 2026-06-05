<?php  
    require_once "config.php"; 
    require_once DBAPI; 
    include(HEADER_TEMPLATE);
?>

    <body>
        <main>
            <section class="index-bemvindo">
                <!-- bloco de bem vindo do site -->
                <div class="bemvindo-fundo"></div>


                <div class="conteudobemvindo">
                    <p class="bemvindo-subtitulo">Confeitaria artesanal</p>
                    <h1 class="bemvindo-titulo">
                        Feito com<br>
                        <em>amor e cuidado</em>
                    </h1>
                    <p class="bemvindo-subtitulo2">
                        Cada doce é um pedaço de aconchego<br>
                        e carinho que buscamos levar até você.
                    </p>
                    <div class="bemvindo-botoes">
                        <a href="<?php echo BASEURL; ?>paginas/personalizados.php" class="botaoclaro">Encomendar</a>
                        <a href="<?php echo BASEURL; ?>paginas/sobrenos.php" class="botaoescuro">Sobre nós</a>
                    </div>
                </div>

                <!-- linha bonitinha -->
                <div class="detalhe">
                    <span></span>
                </div>
            </section>

            <section class="carrossel">
                <!--carrossel-->
                <div class="container-xxl">
                    <div style="margin-bottom: 40px;">
                        <p class="carrossel-subtitulo">Ficou com curiosidade?</p>
                        <h2 class="carrossel-titulo">Tenha um <em>gostinho</em> do que temos</h2>
                    </div>
                    <div class="carrosselofc" id="carrossel" tabindex="0">
                        <div class="slides">
                            <img src="<?php echo BASEURL; ?>imagens/doce1.webp" alt="Imagem 1">
                            <img src="<?php echo BASEURL; ?>imagens/doce2.webp" alt="Imagem 2">
                            <img src="<?php echo BASEURL; ?>imagens/doce3.webp" alt="Imagem 3">
                        </div>
                        <button class="prev carrossel-btnprev">
                            <i class="fa-solid fa-angle-left"></i>
                        </button>
                        <button class="next carrossel-btnnext">
                            <i class="fa-solid fa-angle-right"></i>
                        </button>
                        <div class="dots"></div>
                    </div>
                </div>
            </section>

            <!-- transição de cor-->
            <div style="height: 100px; background: linear-gradient(to bottom, #fdf2f4, #fde0e5);"></div>

            <section class="cards py-5">
                <!-- PArte dos carss fofinhos rs para desktop aff -->
                <div class="container-xxl" style="overflow: visible;">
                    <div>
                        <p class="carrossel-subtitulo">Campeões de Vendas</p>
                        <h2 class="carrossel-titulo">Esses fazem <em>sucesso</em> por aqui!</h2>
                    </div>
                    

                    <div class="product-slider d-none d-lg-flex">
                        
                        <div class="product-card" style="background-image: url('<?php echo BASEURL; ?>imagens/doce1.webp');">
                            <div class="product-card-body">
                                <span>Tortas</span>
                                <button class="btn-confira" src="<?php echo BASEURL; ?>paginas/doces.php">Confira</button>
                            </div>
                        </div>
                        
                        <div class="product-card" style="background-image: url('<?php echo BASEURL; ?>imagens/doce2.webp');">
                            <div class="product-card-body">
                                <span>Salgados</span>
                                <button class="btn-confira" src="<?php echo BASEURL; ?>paginas/salgados.php">Confira</button>
                            </div>
                        </div>
                    
                        <div class="product-card" style="background-image: url('<?php echo BASEURL; ?>imagens/doce2.webp');">
                            <div class="product-card-body">
                                <span>Bolos</span>
                                <button class="btn-confira" src="<?php echo BASEURL; ?>paginas/doces.php">Confira</button>
                            </div>
                        </div>
                    
                        <div class="product-card" style="background-image: url('<?php echo BASEURL; ?>imagens/doce3.webp');">
                            <div class="product-card-body">
                                <span>Cones</span>
                                <button class="btn-confira" src="<?php echo BASEURL; ?>paginas/personalizados.php">Confira</button>
                            </div>
                        </div>
                    </div>

                    <!-- carrossel aparece em tela pequena
                    <div id="cards" class="carousel slide product-slider d-lg-none" data-bs-ride="false">
                        
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="product-card" style="background-image: url('imagens/doce1.webp');">
                                    <div class="product-card-body">
                                        <span>Tortas</span>
                                        <button class="btn-confira" src="./paginas/doces.php">Confira</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="carousel-item">
                                <div class="product-card" style="background-image: url('imagens/doce2.webp');">
                                    <div class="product-card-body">
                                        <span>Salgados</span>
                                        <button class="btn-confira">Confira</button>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="product-card" style="background-image: url('imagens/doce2.webp');">
                                    <div class="product-card-body">
                                        <span>Bolos</span>
                                        <button class="btn-confira">Confira</button>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="product-card" style="background-image: url('imagens/doce3.webp');">
                                    <div class="product-card-body">
                                        <span>Cones</span>
                                        <button class="btn-confira">Confira</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="carrossel-btnprev carousel-control-prev" type="button" data-bs-target="#cards" data-bs-slide="prev">
                            <i class="fa-solid fa-angle-left"></i>
                        </button>
                        <button class="carrossel-btnnext carousel-control-next" type="button" data-bs-target="#cards" data-bs-slide="next">
                            <i class="fa-solid fa-angle-right"></i>
                        </button>
                    </div>
                     -->
                    
                </div>
            </section>

            <!-- transição de cor-->
            <div style="height: 100px; background: linear-gradient(to top, #fdf2f4, #fde0e5);"></div>

            <section class="feedbacks py-5">
                <!-- feedbacks que so da b.o-->
                <div class="container-xxl">
                    <div style="margin-bottom: 40px;">
                        <p class="carrossel-subtitulo">Depoimentos</p>
                        <h2 class="carrossel-titulo">O que nossos <em>clientes</em> dizem sobre nós?</h2>
                    </div>

                    <div class="feedbacks-wrapper">
                        <button class="feedback-arrow prev-feedback" type="button" aria-label="Feedback anterior">&#10094;</button>
                        <div class="feedbacks-carrossel-track">
                            <div class="feedbacks-carrossel" id="feedbackscarrossel">
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

        </main>

        <?php include 'inc/modal.php'; 
        include(FOOTER_TEMPLATE);?>
        
    </body>
</html>