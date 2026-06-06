<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;

// Carrega todos os produtos (exceto personalizados)
$todos = array_filter(find_products(null), fn($p) => $p['tipo'] !== 'personalizado');

$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

$total_carrinho = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - Pedacinho de Amor</title>
    <link rel="stylesheet" href="../css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!--brendinhaaa 
aqui ta a parte do css do cardapio ta, fiz so pra ter uma nocao de como vai ser o design, depois fica a vontade pra fazer sua magia diva rs
e o cardapio é onde eu juntei e coloquei o salgado e doce ta -->

<!---desse jeito que fiz ele filtra com base no nome do produto e no "banco de palavras" que ta guardado aq, tipo
se add um produto com o nome "bolo de chocolate", ele vai salvar e aparecer no filtro como "bolo" e assim por diante. toma cuidado
na hora de salvar quando for add um novo produto pra colocar o nome certo, pq se colocar sla, inves de bolo de chocolate e so por 
chocolate ele vai cair em outros.
fiz isso por agora, qlqr coisa eu falo com as meninas dps pra add isso no banco msm, pra ter uma subcategoria de doce e salgado pra nao ter q fazer essa gambiarra rs -->
      
    <style>
        /* ── Seção âncora ── */
        .secao-categoria {
            padding: 3rem 0 1rem;
        }
        .secao-categoria h2 {
            font-size: 2rem;
            font-weight: 900;
            color: #7a2f2f;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-bottom: 3px solid #ffb3d9;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        /* ── Filtros de subcategoria ── */
        .subcategoria-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .sub-btn {
            border: 2px solid #ffb3d9;
            background: #fff;
            color: #7a2f2f;
            padding: 7px 18px;
            border-radius: 30px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }
        .sub-btn:hover {
            background: #ffdcec;
        }
        .sub-btn.ativo {
            background: #7a2f2f;
            border-color: #7a2f2f;
            color: #fff;
        }

        /* ── Grid de produtos ── */
        .produtos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 2rem;
        }
        .produto-item[data-hidden="true"] {
            display: none;
        }

        /* ── Msg sem produtos ── */
        .sem-produtos {
            display: none;
            color: #888;
            font-style: italic;
            padding: 1rem 0 2rem;
        }
        .sem-produtos.visivel {
            display: block;
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <header>
        <h1>Pedacinho de Amor</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="sobrenos.php">Sobre Nós</a></li>
                <li><a href="cardapio.php" class="active">Cardápio</a></li>
                <li><a href="personalizados.php">Personalizados</a></li>
                <li>
                    <a href="carrinho.php">
                        <i class="fas fa-shopping-cart"></i>
                        Carrinho (<?php echo $total_carrinho; ?>)
                    </a>
                </li>
                <?php if ($usuario_logado): ?>
                    <li><a href="../inc/logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a class="btn btn-primary text-white" href="../index.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <!-- HERO -->
        <section class="doces-hero" style="background-image:url('../imagens/doce3.webp');">
            <div class="doces-hero__overlay"></div>
            <div class="doces-hero__content">
                <h1>CARDÁPIO</h1>
                <p>Tudo feito com carinho, do doce ao salgado!</p>
            </div>
        </section>

        <div class="container">

            <?php if ($cartMessage): ?>
                <div class="alert alert-success mt-3"><?php echo htmlspecialchars($cartMessage); ?></div>
            <?php endif; ?>

            <?php if (!$usuario_logado): ?>
                <div class="alert alert-warning mt-3">
                    Faça <a href="../index.php">login</a> para adicionar produtos ao carrinho.
                </div>
            <?php endif; ?>

            <!-- ════════════════════════════
                 SEÇÃO DOCES
            ════════════════════════════ -->
            <section class="secao-categoria" id="secao-doces">
                <h2>🍬 Doces</h2>

                <div class="subcategoria-bar" id="filtros-doces">
                    <button class="sub-btn ativo" data-sub="todos" onclick="filtrar('doces', 'todos', this)">Todos</button>
                    <button class="sub-btn" data-sub="cone"        onclick="filtrar('doces', 'cone', this)">Cone</button>
                    <button class="sub-btn" data-sub="trufa"       onclick="filtrar('doces', 'trufa', this)">Trufas</button>
                    <button class="sub-btn" data-sub="brigadeiro"  onclick="filtrar('doces', 'brigadeiro', this)">Brigadeiros</button>
                    <button class="sub-btn" data-sub="bolo"        onclick="filtrar('doces', 'bolo', this)">Bolos</button>
                    <button class="sub-btn" data-sub="docinho"     onclick="filtrar('doces', 'docinho', this)">Docinhos</button>
                    <button class="sub-btn" data-sub="outro"       onclick="filtrar('doces', 'outro', this)">Outros</button>
                </div>

                <p class="sem-produtos" id="sem-doces">Nenhum produto encontrado nesta categoria.</p>

                <div class="produtos-grid" id="grid-doces">
                    <?php
                    $doces = array_filter($todos, fn($p) => in_array($p['tipo'], ['doce', 'bolo']));
                    foreach ($doces as $p):
                        $sub = strtolower(extrair_subcategoria($p['nome']));
                    ?>
                    <div class="col produto-item"
                         data-secao="doces"
                         data-sub="<?php echo htmlspecialchars($sub); ?>">
                        <div class="product-card <?php echo !$p['disponivel'] ? 'unavailable' : ''; ?>">
                            <?php if (!empty($p['imagem_referencia'])): ?>
                                <img src="../<?php echo htmlspecialchars($p['imagem_referencia']); ?>"
                                     alt="<?php echo htmlspecialchars($p['nome']); ?>">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                                <p><?php echo htmlspecialchars($p['descricao'] ?? 'Delicioso produto artesanal'); ?></p>
                                <div class="product-price">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></div>
                                <?php if (!$p['disponivel']): ?>
                                    <div class="unavailable-badge">Indisponível</div>
                                <?php endif; ?>
                                <form action="add_carrinho.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect"
                                           value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES); ?>">
                                    <button type="submit" class="add-to-cart-btn"
                                            <?php echo (!$usuario_logado || !$p['disponivel']) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ════════════════════════════
                 SEÇÃO SALGADOS
            ════════════════════════════ -->
            <section class="secao-categoria" id="secao-salgados">
                <h2>🥐 Salgados</h2>

                <div class="subcategoria-bar" id="filtros-salgados">
                    <button class="sub-btn ativo" data-sub="todos"      onclick="filtrar('salgados', 'todos', this)">Todos</button>
                    <button class="sub-btn" data-sub="croissant"        onclick="filtrar('salgados', 'croissant', this)">Croissant</button>
                    <button class="sub-btn" data-sub="assado"           onclick="filtrar('salgados', 'assado', this)">Assados</button>
                    <button class="sub-btn" data-sub="pao de queijo"    onclick="filtrar('salgados', 'pao de queijo', this)">Pão de Queijo</button>
                    <button class="sub-btn" data-sub="coxinha"          onclick="filtrar('salgados', 'coxinha', this)">Coxinha</button>
                    <button class="sub-btn" data-sub="empada"           onclick="filtrar('salgados', 'empada', this)">Empada</button>
                    <button class="sub-btn" data-sub="outro"            onclick="filtrar('salgados', 'outro', this)">Outros</button>
                </div>

                <p class="sem-produtos" id="sem-salgados">Nenhum produto encontrado nesta categoria.</p>

                <div class="produtos-grid" id="grid-salgados">
                    <?php
                    $salgados = array_filter($todos, fn($p) => $p['tipo'] === 'salgado');
                    foreach ($salgados as $p):
                        $sub = strtolower(extrair_subcategoria($p['nome']));
                    ?>
                    <div class="col produto-item"
                         data-secao="salgados"
                         data-sub="<?php echo htmlspecialchars($sub); ?>">
                        <div class="product-card <?php echo !$p['disponivel'] ? 'unavailable' : ''; ?>">
                            <?php if (!empty($p['imagem_referencia'])): ?>
                                <img src="../<?php echo htmlspecialchars($p['imagem_referencia']); ?>"
                                     alt="<?php echo htmlspecialchars($p['nome']); ?>">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                                <p><?php echo htmlspecialchars($p['descricao'] ?? 'Delicioso produto artesanal'); ?></p>
                                <div class="product-price">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></div>
                                <?php if (!$p['disponivel']): ?>
                                    <div class="unavailable-badge">Indisponível</div>
                                <?php endif; ?>
                                <form action="add_carrinho.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect"
                                           value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES); ?>">
                                    <button type="submit" class="add-to-cart-btn"
                                            <?php echo (!$usuario_logado || !$p['disponivel']) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </div><!-- /container -->
    </main>

    <script src="../js/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
    function filtrar(secao, sub, btn) {
        // atualiza botão ativo
        document.querySelectorAll('#filtros-' + secao + ' .sub-btn').forEach(b => b.classList.remove('ativo'));
        btn.classList.add('ativo');

        const itens = document.querySelectorAll('#grid-' + secao + ' .produto-item');
        let visiveis = 0;

        itens.forEach(item => {
            const dataSub = item.dataset.sub || '';
            const mostrar = sub === 'todos' || dataSub.includes(sub);
            item.dataset.hidden = mostrar ? 'false' : 'true';
            if (mostrar) visiveis++;
        });

        // mostra/esconde msg de vazio
        const msg = document.getElementById('sem-' + secao);
        msg.classList.toggle('visivel', visiveis === 0);
    }
    </script>
</body>
</html>

<?php
/**
 * Tenta adivinhar a subcategoria de um produto pelo nome.
 * Você pode ajustar as palavras-chave conforme os produtos reais do banco.
 */
function extrair_subcategoria(string $nome): string {
    $nome = mb_strtolower($nome, 'UTF-8');

    $mapa = [
        'cone'        => ['cone'],
        'trufa'       => ['trufa'],
        'brigadeiro'  => ['brigadeiro'],
        'bolo'        => ['bolo'],
        'docinho'     => ['docinho', 'camafeu', 'beijinho', 'olho de sogra', 'cajuzinho', 'quindim', 'bicho de pé', 'bixo de pé'],
        'croissant'   => ['croissant'],
        'assado'      => ['assado', 'enroladinho', 'esfiha', 'esfirra'],
        'pao de queijo' => ['pão de queijo', 'pao de queijo'],
        'coxinha'     => ['coxinha'],
        'empada'      => ['empada'],
    ];

    foreach ($mapa as $sub => $palavras) {
        foreach ($palavras as $palavra) {
            if (str_contains($nome, $palavra)) return $sub;
        }
    }

    return 'outro';
}
?>