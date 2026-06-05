<?php  
    require_once "../config.php"; 
    require_once DBAPI; 
    include(HEADER_TEMPLATE);
?>
<body>
    <main>
        <section class="sobre-intro">
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
        <section class="about-section">
            <div class="about-container">
                <div class="about-image">
                    <img src="<?php echo BASEURL; ?>imagens/doce1.webp" alt="Equipe Pedacinho de Amor">
                </div>
                <div class="about-text">
                    <h2>Quem somos nós?</h2>
                    <p>A Pedacinho de Amor iniciou com a ideia de entregar o verdadeiro sabor caseiro, aquele que lembra a cozinha da família e o aconchego do lar.</p>
                    <p>Cada receita foi feita com carinho e dedicação, sempre buscando transformar momentos simples em experiências únicas e acolhedoras.</p>
                    <p>Estamos aqui para encantar seu paladar com doces e salgados preparados com amor, qualidade e aquele toque especial que só quem faz com coração sabe oferecer.</p>
                </div>
            </div>
        </section>

        <section class="work-section">
            <div class="work-container">
                <div class="work-text">
                    <h2>Como trabalhamos?</h2>
                    <p>Cada doce, bolo e salgado é preparado de forma artesanal, com ingredientes selecionados e muito carinho.</p>
                    <p>Nossos produtos buscam entregar qualidade e aconchego, cada detalhe importa em nossa produção: um recheio cremoso, a textura macia de nossas tortas e o cuidado na decoração de cada sobremesa.</p>
                    <p>Produzimos em pequenas quantidades para garantir qualidade e sabor autêntico, mantendo sempre o nosso toque caseiro.</p>
                </div>
                <div class="work-image">
                    <img src="imagens/doce3.webp" alt="Como trabalhamos Pedacinho de Amor">
                </div>
            </div>
        </section>
    </main>

    <?php include(FOOTER_TEMPLATE);?>

</body>
</html>