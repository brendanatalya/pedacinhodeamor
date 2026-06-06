
<?php  
    require_once "config.php"; 
    require_once DBAPI; 
    include(HEADER_TEMPLATE);
?>

<?php 
if(!isset($_SESSION)) session_start();

// Verificar se cliente tem pedido entregue sem avaliação
if (!empty($_SESSION['logado']) && $_SESSION['tipo'] === 'cliente') {
    require_once 'config.php';
    require_once ABSPATH . 'inc/database.php';

    $conn = open_database();
    $stmt = $conn->prepare("
        SELECT p.id 
        FROM pedidos p
        LEFT JOIN avaliacoes a ON a.id_pedido = p.id
        WHERE p.id_cliente = ? 
          AND p.status = 'entregue'
          AND a.id IS NULL
        ORDER BY p.data_pedido DESC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['id']]);
    $pendente = $stmt->fetch();
    close_database($conn);

    $_SESSION['pedido_avaliar_id'] = $pendente ? $pendente['id'] : null;
}
?>
<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';
require_once ABSPATH . 'inc/database.php';

// Verificar se cliente tem pedido entregue sem avaliação
if (!empty($_SESSION['logado']) && $_SESSION['tipo'] === 'cliente') {
    $conn = open_database();
    $stmt = $conn->prepare("
        SELECT p.id
        FROM pedidos p
        LEFT JOIN avaliacoes a ON a.id_pedido = p.id
        WHERE p.id_cliente = ?
          AND p.status = 'entregue'
          AND a.id IS NULL
        ORDER BY p.data_pedido DESC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['id']]);
    $pendente = $stmt->fetch();
    close_database($conn);
    $_SESSION['pedido_avaliar_id'] = $pendente ? $pendente['id'] : null;
}

// Buscar as últimas 10 avaliações de todos os clientes
$conn = open_database();
$stmt = $conn->prepare("
    SELECT a.nota_produto, a.nota_atend, a.comentario, a.criado_em, u.nome
    FROM avaliacoes a
    INNER JOIN usuarios u ON u.id = a.id_cliente
    ORDER BY a.criado_em DESC
    LIMIT 10
");
$stmt->execute();
$avaliacoes_home = $stmt->fetchAll(PDO::FETCH_ASSOC);
close_database($conn);
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

                    <div class="campeoes-slider d-none d-lg-flex">
                        
                        <div class="campeoes-card" style="background-image: url('<?php echo BASEURL; ?>imagens/torta.jpg');">
                            <div class="campeoes-card-body">
                                <span>Tortas</span>
                                <button class="btn-confira" src="<?php echo BASEURL; ?>paginas/doces.php">Confira</button>
                            </div>
                        </div>
                        
                        <div class="campeoes-card" style="background-image: url('<?php echo BASEURL; ?>imagens/salgados.jpg');">
                            <div class="campeoes-card-body">
                                <span>Salgados</span>
                                <button class="btn-confira" src="<?php echo BASEURL; ?>paginas/salgados.php">Confira</button>
                            </div>
                        </div>
                    
                        <div class="campeoes-card" style="background-image: url('<?php echo BASEURL; ?>imagens/bolos.jpg');">
                            <div class="campeoes-card-body">
                                <span>Bolos</span>
                                <button class="btn-confira" src="<?php echo BASEURL; ?>paginas/doces.php">Confira</button>
                            </div>
                        </div>
                    
                        <div class="campeoes-card" style="background-image: url('<?php echo BASEURL; ?>imagens/cones.jpg');">
                            <div class="campeoes-card-body">
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
                    
                    <?php if (!empty($avaliacoes_home)): ?>

                    <div class="feedbacks-wrapper">
                        <button class="feedback-arrow prev-feedback" type="button" aria-label="Feedback anterior">&#10094;</button>
                        <div class="feedbacks-carrossel-track">
                            <div class="feedbacks-carrossel" id="feedbackscarrossel">

                                <?php foreach ($avaliacoes_home as $av): ?>
                                <?php
                                    $nota_media = round(($av['nota_produto'] + $av['nota_atend']) / 2);
                                    $estrelas   = str_repeat('★', $nota_media) . str_repeat('☆', 5 - $nota_media);
                                    $primeiro_nome = explode(' ', trim($av['nome']))[0];
                                    $inicial = mb_strtoupper(mb_substr($av['nome'], 0, 1, 'UTF-8'), 'UTF-8');
                                ?>
                                <div class="feedback-card feedback-card-large">
                                    <div class="feedback-user">
                                        <div class="feedback-avatar" style="width:46px;height:46px;border-radius:50%;background:#e8d5f0;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:#a855f7;flex-shrink:0;">
                                            <?php echo htmlspecialchars($inicial); ?>
                                        </div>
                                        <div class="feedback-info">
                                            <h4><?php echo htmlspecialchars($primeiro_nome); ?></h4>
                                            <p style="color:#f5a623;font-size:1rem;letter-spacing:1px;margin:0;"><?php echo $estrelas; ?></p>
                                        </div>
                                    </div>
                                    <p class="feedback-text"><?php echo htmlspecialchars($av['comentario']); ?></p>
                                </div>
                                <?php endforeach; ?>

                            </div>
                        </div>
                        <button class="feedback-arrow next-feedback" type="button" aria-label="Próximo feedback">&#10095;</button>
                    </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">Ainda não há avaliações — seja o primeiro! 🎂</p>
                    <?php endif; ?>
                </div>
            </section>

        </main>

        <?php include 'inc/modal.php'; 
        include(FOOTER_TEMPLATE);?>
        
    </body>
</html>