<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

// ── Função de subcategoria ────────────────────────────────────────────────────
function extrair_subcategoria_salgado(string $nome): string {
    $nome = mb_strtolower($nome, 'UTF-8');

    $mapa = [
        'croissant'    => ['croissant'],
        'assado'       => ['assado', 'enroladinho', 'esfiha', 'esfirra'],
        'pao de queijo'=> ['pão de queijo', 'pao de queijo'],
        'coxinha'      => ['coxinha'],
        'empada'       => ['empada'],
    ];

    foreach ($mapa as $sub => $palavras) {
        foreach ($palavras as $palavra) {
            if (str_contains($nome, $palavra)) return $sub;
        }
    }

    return 'outro';
}

// ── Dados ─────────────────────────────────────────────────────────────────────
$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;

$todos = array_values(array_filter(
    find_products(null),
    fn($p) => $p['tipo'] === 'salgado'
));

$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

// ── Contagens por subcategoria ────────────────────────────────────────────────
$contagem = ['todos' => count($todos)];
foreach ($todos as $p) {
    $sub = extrair_subcategoria_salgado($p['nome']);
    $contagem[$sub] = ($contagem[$sub] ?? 0) + 1;
}

$botoes = [
    'croissant'     => '🥐 Croissants',
    'assado'        => '🫓 Assados',
    'pao de queijo' => '🧀 Pão de Queijo',
    'coxinha'       => '🍗 Coxinhas',
    'empada'        => '🥟 Empadas',
    'outro'         => '✨ Outros',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salgados - Pedacinho de Amor</title>
    <link rel="icon" type="image/x-icon" href="../imagens/icon.png">
    <link rel="stylesheet" href="../css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .subcategoria-bar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; }
        .sub-btn {
            border: 2px solid #ffb3d9; background: #fff; color: #7a2f2f;
            padding: 7px 18px; border-radius: 30px; font-size: 0.88rem;
            font-weight: 700; cursor: pointer; transition: 0.2s;
        }
        .sub-btn:hover  { background: #ffdcec; }
        .sub-btn.ativo  { background: #7a2f2f; border-color: #7a2f2f; color: #fff; }

        .produtos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px; margin-bottom: 2rem;
        }
        .produto-item[data-hidden="true"] { display: none; }

        .sem-produtos { display: none; color: #888; font-style: italic; padding: 1rem 0 2rem; }
        .sem-produtos.visivel { display: block; }

        .nav-cardapio { display: flex; flex-wrap: wrap; gap: 0.75rem; margin: 2rem 0 1rem; }
        .nav-cardapio a {
            padding: 10px 24px; border-radius: 30px; font-weight: 700;
            font-size: 0.95rem; text-decoration: none;
            border: 2px solid #7a2f2f; color: #7a2f2f; transition: 0.2s;
        }
        .nav-cardapio a.ativo,
        .nav-cardapio a:hover { background: #7a2f2f; color: #fff; }
    </style>
</head>
<body>

    <?php include_once ABSPATH . 'inc/header.php'; ?>

    <main>
        <section class="doces-hero" style="background-image:url('../imagens/doce3.webp');">
            <div class="doces-hero__overlay"></div>
            <div class="doces-hero__content">
                <h1>🥐 SALGADOS</h1>
                <p>Assados e fritos com muito carinho!</p>
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

            <div class="nav-cardapio">
                <a href="doces.php">🍬 Doces</a>
                <a href="salgados.php" class="ativo">🥐 Salgados</a>
                <a href="personalizados.php">🎨 Personalizados</a>
            </div>

            <div class="subcategoria-bar" id="filtros-salgados">
                <button class="sub-btn ativo" data-sub="todos" onclick="filtrar('todos', this)">
                    Todos (<?php echo $contagem['todos']; ?>)
                </button>
                <?php foreach ($botoes as $key => $label): ?>
                    <?php if (!empty($contagem[$key])): ?>
                        <button class="sub-btn" data-sub="<?php echo $key; ?>" onclick="filtrar('<?php echo $key; ?>', this)">
                            <?php echo $label; ?> (<?php echo $contagem[$key]; ?>)
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <p class="sem-produtos" id="sem-produtos">Nenhum produto encontrado nesta categoria.</p>

            <div class="produtos-grid" id="grid-salgados">
                <?php foreach ($todos as $p):
                    $sub = extrair_subcategoria_salgado($p['nome']);
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
                                <input type="hidden" name="quantity"   value="1">
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

        </div>
    </main>

    <?php include_once ABSPATH . 'inc/footer.php'; ?>

    <script>
    function filtrar(sub, btn) {
        document.querySelectorAll('#filtros-salgados .sub-btn').forEach(b => b.classList.remove('ativo'));
        btn.classList.add('ativo');

        const itens = document.querySelectorAll('#grid-salgados .produto-item');
        let visiveis = 0;

        itens.forEach(item => {
            const dataSub = item.dataset.sub || '';
            const mostrar = sub === 'todos' || dataSub === sub;
            item.dataset.hidden = mostrar ? 'false' : 'true';
            if (mostrar) visiveis++;
        });

        document.getElementById('sem-produtos').classList.toggle('visivel', visiveis === 0);
    }
    </script>
</body>
</html>