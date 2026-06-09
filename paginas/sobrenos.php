<?php  
    if (!isset($_SESSION)) session_start();
    require_once '../config.php';
    require_once ABSPATH . 'inc/database.php';
    require_once DBAPI; 
    include(HEADER_TEMPLATE);
?>

<body>
    <main>
        <section class="sobre-intro" style="background: url('<?php echo BASEURL; ?>/imagens/boloframboesa.jpg') no-repeat center center; background-size: cover;">
            <!-- bloco de bem vindo do site -->
            <div class="bemvindo-fundo"></div>


            <div class="conteudobemvindo">
                <h1 class="bemvindo-titulo">
                    Quer saber<br>
                    <em>quem somos nós?</em>
                </h1>
                <p class="bemvindo-subtitulo2">
                    Venha ver um pouquinho de nossa história e <br>
                    o que nos move a criar delícias para você!
                </p>
            </div>

            <!-- linha bonitinha -->
            <div class="detalhe">
                <span></span>
            </div>
        </section>
        
        <section class="sobre-nos">
            <div class="sobre">
                <div class="sobre-foto">
                    <img src="<?php echo BASEURL; ?>imagens/sobrenos.jpg" alt="Quem somos">
                </div>
                <div class="sobre-texto">
                    <h2>Quem Somos?</h2>
                    <p>A Pedacinho de Amor iniciou com uma simples ideia: entregar o verdadeiro sabor caseiro, aquele que lembra a cozinha aconchegante da vovó e o calor do lar.</p>
                    <p>Nascemos da paixão por criar experiências doces e memoráveis. Cada receita foi desenvolvida com carinho infinito e dedicação, sempre buscando transformar momentos simples em experiências únicas e acolhedoras que tocam o coração.</p>
                    <p>Estamos aqui para encantar seu paladar com doces e salgados preparados com amor genuíno, ingredientes de qualidade superior e aquele toque especial que só quem faz com coração sabe oferecer. Para nós, cada cliente é parte da nossa família.</p>
                </div>
            </div>
        </section>

         <section class="sobre-trabalho">
            <div class="trabalho">
                <div class="trabalho-texto">
                    <h2>Como Trabalhamos?</h2>
                    <p>Cada doce, bolo e salgado que sai da nossa cozinha é preparado de forma 100% artesanal, com ingredientes selecionados cuidadosamente e muito carinho em cada detalhe.</p>
                    <p>Acreditamos que a qualidade está nos detalhes: um recheio cremoso que derrete na boca, a textura macia e perfeita de nossas tortas, o cuidado delicado na decoração de cada sobremesa e a apresentação que transmite o amor com o qual foi feito.</p>
                    <p>Produzimos em pequenas quantidades para garantir qualidade excepcional e sabor autêntico, mantendo sempre o nosso toque caseiro que nos diferencia. Nenhum produto em nossa cozinha é feito por máquina - tudo é feito à mão, com atenção e carinho.</p>
                </div>
                <div class="trabalho-foto">
                    <img src="<?php echo BASEURL; ?>imagens/comotrabalhamos.jpg" alt="Como trabalhamos">
                </div>
            </div>
        </section>

        <section class="sobre-valores">
            <div class="valores">
                <p class="valores-titulo">O que nos move em cada dia</p>
                <div class="valores-cards">
                    <div class="valores-card">
                        <div class="valores-icon"><i class="fa-solid fa-heart"></i></div>
                        <h3>Amor e Carinho</h3>
                        <p>Cada receita é feita com dedicação genuína, como se preparássemos para a pessoa que mais amamos.</p>
                    </div>
                    <div class="valores-card">
                        <div class="valores-icon"><i class="fa-solid fa-ranking-star"></i></div>
                        <h3>Qualidade Premium</h3>
                        <p>Selecionamos apenas os melhores ingredientes para garantir um sabor incomparável em cada produto.</p>
                    </div>
                    <div class="valores-card">
                        <div class="valores-icon"><i class="fa-solid fa-house"></i></div>
                        <h3>Tradição Artesanal</h3>
                        <p>Mantemos vivas as receitas tradicionais com técnicas artesanais passadas de geração em geração.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="sobre-escolha">
            <h2 class="valores-titulo">Por que nos escolher?</h2>
            <div class="escolha-cards">
                <div class="escolha-card">
                    <div class="numero">1</div>
                    <div class="escolha-texto">
                        <h4>Ingredientes Selecionados</h4>
                        <p>Apenas os melhores ingredientes, sem conservantes desnecessários. Sabor puro e genuíno.</p>
                    </div>
                </div>
                <div class="escolha-card">
                    <div class="numero">2</div>
                    <div class="escolha-texto">
                        <h4>Produção Artesanal</h4>
                        <p>Cada item é feito manualmente, garantindo qualidade e autenticidade em todos os nossos produtos.</p>
                    </div>
                </div>
                <div class="escolha-card">
                    <div class="numero">3</div>
                    <div class="escolha-texto">
                        <h4>Customização Personalizada</h4>
                        <p>Criamos confeitarias especiais sob encomenda para seus momentos únicos e inesquecíveis.</p>
                    </div>
                </div>
                <div class="escolha-card">
                    <div class="numero">4</div>
                    <div class="escolha-texto">
                        <h4>Embalagem Cuidadosa</h4>
                        <p>Cada pedido é embalado com cuidado especial para chegar perfeito até à sua mesa.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
        include '../inc/modal.php'; 
        include(FOOTER_TEMPLATE);
 
    ?>

</body>
</html>