<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;

// Carrega só os produtos doces/bolos
$todos = array_filter(find_products(null), fn($p) => in_array($p['tipo'], ['doce', 'bolo']));

$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

$total_carrinho = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doces - Pedacinho de Amor</title>
    <link rel="icon" type="image/x-icon" href="../imagens/icon.png">
    <link rel="stylesheet" href="../css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
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

        /* ── Link de navegação entre páginas ── */
        .nav-cardapio {
            display: flex;
            gap: 1rem;
            margin: 2rem 0 1rem;
        }
        .nav-cardapio a {
            padding: 10px 24px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.95rem;
            text-decoration: none;
            border: 2px solid #7a2f2f;
            color: #7a2f2f;
            transition: 0.2s;
        }
        .nav-cardapio a.ativo,
        .nav-cardapio a:hover {
            background: #7a2f2f;
            color: #fff;
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <?php include_once ABSPATH . 'inc/header.php'; ?>

    <main>
        <!-- HERO -->
        <section class="doces-hero" style="background-image:url('../imagens/doce3.webp');">
            <div class="doces-hero__overlay"></div>
            <div class="doces-hero__content">
                <h1>🍬 DOCES</h1>
                <p>Tudo feito com carinho para adoçar seus momentos!</p>
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

            <!-- Navegação entre Doces e Salgados -->
            <div class="nav-cardapio">
                <a href="doces.php" class="ativo">🍬 Doces</a>
                <a href="salgados.php">🥐 Salgados</a>
            </div>

            <!-- Filtros -->
            <div class="subcategoria-bar" id="filtros-doces">
                <button class="sub-btn ativo" data-sub="todos"       onclick="filtrar('todos', this)">Todos</button>
                <button class="sub-btn" data-sub="cone"              onclick="filtrar('cone', this)">Cone</button>
                <button class="sub-btn" data-sub="trufa"             onclick="filtrar('trufa', this)">Trufas</button>
                <button class="sub-btn" data-sub="brigadeiro"        onclick="filtrar('brigadeiro', this)">Brigadeiros</button>
                <button class="sub-btn" data-sub="bolo"              onclick="filtrar('bolo', this)">Bolos</button>
                <button class="sub-btn" data-sub="docinho"           onclick="filtrar('docinho', this)">Docinhos</button>
                <button class="sub-btn" data-sub="outro"             onclick="filtrar('outro', this)">Outros</button>
            </div>

            <p class="sem-produtos" id="sem-produtos">Nenhum produto encontrado nesta categoria.</p>

            <div class="produtos-grid" id="grid-doces">
                <?php foreach ($todos as $p):
                    $sub = strtolower(extrair_subcategoria($p['nome']));
                ?>
                <div class="col produto-item" data-sub="<?php echo htmlspecialchars($sub); ?>">
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

        </div><!-- /container -->
    </main>

    <?php include_once ABSPATH . 'inc/footer.php'; ?>

    <script src="../js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASEURL; ?>js/cookies.js"></script>

    <script>
    function filtrar(sub, btn) {
        document.querySelectorAll('#filtros-doces .sub-btn').forEach(b => b.classList.remove('ativo'));
        btn.classList.add('ativo');

        const itens = document.querySelectorAll('#grid-doces .produto-item');
        let visiveis = 0;

        itens.forEach(item => {
            const dataSub = item.dataset.sub || '';
            const mostrar = sub === 'todos' || dataSub.includes(sub);
            item.dataset.hidden = mostrar ? 'false' : 'true';
            if (mostrar) visiveis++;
        });

        document.getElementById('sem-produtos').classList.toggle('visivel', visiveis === 0);
    }
    </script>
</body>
</html>

<?php
function extrair_subcategoria(string $nome): string {
    $nome = mb_strtolower($nome, 'UTF-8');

    $mapa = [
        'cone'          => ['cone'],
        'trufa'         => ['trufa'],
        'brigadeiro'    => ['brigadeiro'],
        'bolo'          => ['bolo'],
        'docinho'       => ['docinho', 'camafeu', 'beijinho', 'olho de sogra', 'cajuzinho', 'quindim', 'bicho de pé', 'bixo de pé'],
    ];

    foreach ($mapa as $sub => $palavras) {
        foreach ($palavras as $palavra) {
            if (str_contains($nome, $palavra)) return $sub;
        }
    }

    return 'outro';
}
?>