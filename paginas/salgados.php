
<?php
if (!isset($_SESSION)) session_start();

require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

// ── Função de subcategoria ────────────────────────────────────────────────
function extrair_subcategoria_salgado(string $nome): string
{
    $nome = mb_strtolower($nome, 'UTF-8');

    $mapa = [
        'croissant'     => ['croissant'],
        'assado'        => ['assado', 'enroladinho', 'esfiha', 'esfirra'],
        'pao_de_queijo' => ['pão de queijo', 'pao de queijo'],
        'coxinha'       => ['coxinha'],
        'empada'        => ['empada'],
    ];

    foreach ($mapa as $sub => $palavras) {
        foreach ($palavras as $palavra) {
            if (str_contains($nome, $palavra)) {
                return $sub;
            }
        }
    }

    return 'outro';
}

// ── Dados ─────────────────────────────────────────────────────────────────
$usuario_logado = !empty($_SESSION['logado'])
    && $_SESSION['logado'] === true;

$todos = array_values(array_filter(
    find_products(null),
    fn($p) => $p['tipo'] === 'salgado'
));

$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

// ── Contagens por subcategoria ───────────────────────────────────────────
$contagem = ['todos' => count($todos)];

foreach ($todos as $p) {
    $sub = extrair_subcategoria_salgado($p['nome']);
    $contagem[$sub] = ($contagem[$sub] ?? 0) + 1;
}

$botoes = [
    'croissant'     => 'Croissants',
    'assado'        => 'Assados',
    'pao_de_queijo' => 'Pão de Queijo',
    'coxinha'       => 'Coxinhas',
    'empada'        => 'Empadas',
    'outro'         => 'Outros',
];

// ── Dados dos produtos para o modal ──────────────────────────────────────
$produtos_json = array_map(function ($p) {
    return [
        'id'         => (int) $p['id'],
        'nome'       => $p['nome'],
        'descricao'  => $p['descricao'] ?? 'Delicioso produto artesanal',
        'preco'      => (float) $p['preco'],
        'imagem'     => !empty($p['imagem_referencia'])
            ? BASEURL . 'imagens/' . $p['imagem_referencia']
            : '',
        'disponivel' => (bool) $p['disponivel'],
    ];
}, $todos);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Salgados - Pedacinho de Amor</title>

    <link rel="icon" type="image/x-icon"
          href="../imagens/icon.png">

    <link rel="stylesheet"
          href="../css_pda/bootstrap/bootstrap.min.css">

    <link rel="stylesheet"
          href="<?php echo BASEURL; ?>css_pda/style_pda.css">

    <link rel="stylesheet"
          href="<?php echo BASEURL; ?>css_pda/produto-modal.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
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

        .produtos-grid {
            display: grid;
            grid-template-columns: repeat(
                auto-fill,
                minmax(280px, 1fr)
            );
            gap: 24px;
            margin-bottom: 2rem;
        }

        .produto-item[data-hidden="true"] {
            display: none;
        }

        .sem-produtos {
            display: none;
            color: #888;
            font-style: italic;
            padding: 1rem 0 2rem;
        }

        .sem-produtos.visivel {
            display: block;
        }

        .nav-cardapio {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
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

    <?php include_once ABSPATH . 'inc/header.php'; ?>

    <main>
        <div class="container my-5">

            <div class="text-center mb-5">
                <h2 class="section-title">SALGADOS</h2>
            </div>

            <?php if ($cartMessage): ?>
                <div class="alert alert-success mt-3">
                    <?php echo htmlspecialchars($cartMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (!$usuario_logado): ?>
                <div class="alert alert-warning mt-3">
                    Faça <a href="../index.php">login</a>
                    para adicionar produtos ao carrinho.
                </div>
            <?php endif; ?>

            <!-- Filtros por subcategoria -->
            <div class="subcategoria-bar" id="filtros-salgados">

                <button
                    type="button"
                    class="sub-btn ativo"
                    data-sub="todos"
                    onclick="filtrar('todos', this)">

                    Todos (<?php echo $contagem['todos']; ?>)
                </button>

                <?php foreach ($botoes as $key => $label): ?>
                    <?php if (!empty($contagem[$key])): ?>

                        <button
                            type="button"
                            class="sub-btn"
                            data-sub="<?php echo htmlspecialchars($key); ?>"
                            onclick="filtrar('<?php echo $key; ?>', this)">

                            <?php echo htmlspecialchars($label); ?>
                            (<?php echo $contagem[$key]; ?>)
                        </button>

                    <?php endif; ?>
                <?php endforeach; ?>

            </div>

            <p class="sem-produtos" id="sem-produtos">
                Nenhum produto encontrado nesta categoria.
            </p>

            <!-- Produtos -->
            <div class="produtos-grid" id="grid-salgados">

                <?php foreach ($todos as $p):
                    $sub = extrair_subcategoria_salgado($p['nome']);
                ?>

                    <div
                        class="col produto-item"
                        data-sub="<?php echo htmlspecialchars($sub); ?>">

                        <div class="product-card <?php echo !$p['disponivel'] ? 'unavailable' : ''; ?>">

                            <?php if (!empty($p['imagem_referencia'])): ?>
                                <img
                                    src="<?php echo BASEURL . 'imagens/' . htmlspecialchars($p['imagem_referencia']); ?>"
                                    alt="<?php echo htmlspecialchars($p['nome']); ?>">
                            <?php endif; ?>

                            <div class="product-info">

                                <h3>
                                    <?php echo htmlspecialchars($p['nome']); ?>
                                </h3>

                                <p>
                                    <?php echo htmlspecialchars(
                                        $p['descricao'] ?? 'Delicioso produto artesanal'
                                    ); ?>
                                </p>

                                <div class="product-price">
                                    R$
                                    <?php echo number_format($p['preco'], 2, ',', '.'); ?>
                                </div>

                                <?php if (!$p['disponivel']): ?>
                                    <div class="unavailable-badge">
                                        Indisponível
                                    </div>
                                <?php endif; ?>

                                <button
                                    type="button"
                                    class="add-to-carrinho-btn"
                                    onclick="abrirModalProduto(<?php echo (int) $p['id']; ?>)"
                                    <?php echo (!$usuario_logado || !$p['disponivel']) ? 'disabled' : ''; ?>>

                                    <i class="fas fa-shopping-cart"></i>
                                    Adicionar ao Carrinho
                                </button>

                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>
    </main>

    <!-- Modal de produto -->
    <div
        class="modal fade pm-modal"
        id="modalProduto"
        tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <button
                    type="button"
                    class="pm-fechar"
                    data-bs-dismiss="modal"
                    aria-label="Fechar">

                    <i class="fas fa-times"></i>
                </button>

                <div class="pm-body">

                    <div class="pm-imagem">
                        <img id="pmImagem" src="" alt="">

                        <div
                            class="unavailable-badge"
                            id="pmIndisponivel"
                            style="display:none;">

                            Indisponível
                        </div>
                    </div>

                    <div class="pm-info">

                        <h3 class="pm-titulo" id="pmNome"></h3>

                        <p class="pm-descricao" id="pmDescricao"></p>

                        <div class="pm-preco-row">
                            <span class="pm-preco-label">
                                Preço unitário
                            </span>

                            <span class="pm-preco-unit" id="pmPrecoUnit"></span>
                        </div>

                        <div class="pm-quantidade-row">
                            <span class="pm-preco-label">
                                Quantidade
                            </span>

                            <div class="pm-stepper">

                                <button
                                    type="button"
                                    class="quantity-btn"
                                    onclick="alterarQuantidadeModal(-1)"
                                    aria-label="Diminuir quantidade">

                                    -
                                </button>

                                <input
                                    type="number"
                                    class="quantity-input"
                                    id="pmQuantidade"
                                    value="1"
                                    min="1"
                                    step="1"
                                    oninput="atualizarTotalModal()">

                                <button
                                    type="button"
                                    class="quantity-btn"
                                    onclick="alterarQuantidadeModal(1)"
                                    aria-label="Aumentar quantidade">

                                    +
                                </button>

                            </div>
                        </div>

                        <div class="pm-total-row">
                            <span class="pm-preco-label">
                                Total
                            </span>

                            <span class="pm-total-valor" id="pmTotal"></span>
                        </div>

                        <button
                            type="button"
                            class="add-to-carrinho-btn pm-btn-add"
                            id="pmBtnAdicionar"
                            onclick="adicionarAoCarrinhoModal()">

                            <i class="fas fa-shopping-cart"></i>
                            Adicionar ao Carrinho
                        </button>

                        <?php if (!$usuario_logado): ?>
                            <p class="pm-aviso-login">
                                Faça <a href="../index.php">login</a>
                                para adicionar ao carrinho.
                            </p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once ABSPATH . 'inc/footer.php'; ?>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>

    <script>
        // ── Dados dos produtos ────────────────────────────────────────────
        const PRODUTOS = <?php
            echo json_encode(
                $produtos_json,
                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG |
                JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
            );
        ?>;

        const usuarioLogado = <?php echo $usuario_logado ? 'true' : 'false'; ?>;

        let produtoModalAtual = null;

        const modalProdutoEl = document.getElementById('modalProduto');

        let modalProdutoInstance = null;

        if (modalProdutoEl && typeof bootstrap !== 'undefined') {
            modalProdutoInstance = new bootstrap.Modal(modalProdutoEl);
        } else if (modalProdutoEl) {
            console.error(
                'Bootstrap JS não carregou. O modal de produto não vai funcionar.'
            );
        }

        // ── Formatação de moeda ───────────────────────────────────────────
        function formatarMoeda(valor) {
            return valor.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // ── Abrir modal ───────────────────────────────────────────────────
        function abrirModalProduto(id) {
            const produto = PRODUTOS.find(p => p.id === id);

            if (!produto || !modalProdutoInstance) return;

            produtoModalAtual = produto;

            const imagemEl = document.getElementById('pmImagem');

            imagemEl.src = produto.imagem;
            imagemEl.alt = produto.nome;
            imagemEl.style.display = produto.imagem ? 'block' : 'none';

            document.getElementById('pmNome').textContent = produto.nome;
            document.getElementById('pmDescricao').textContent = produto.descricao;

            document.getElementById('pmPrecoUnit').textContent =
                'R$ ' + formatarMoeda(produto.preco);

            document.getElementById('pmQuantidade').value = 1;

            const indisponivelEl =
                document.getElementById('pmIndisponivel');

            const btnAdicionar =
                document.getElementById('pmBtnAdicionar');

            const quantidadeInput =
                document.getElementById('pmQuantidade');

            if (!produto.disponivel) {
                indisponivelEl.style.display = 'block';
                btnAdicionar.disabled = true;
                quantidadeInput.disabled = true;

            } else if (!usuarioLogado) {
                indisponivelEl.style.display = 'none';
                btnAdicionar.disabled = true;
                quantidadeInput.disabled = false;

            } else {
                indisponivelEl.style.display = 'none';
                btnAdicionar.disabled = false;
                quantidadeInput.disabled = false;
            }

            atualizarTotalModal();

            modalProdutoInstance.show();
        }

        // ── Alterar quantidade ────────────────────────────────────────────
        function alterarQuantidadeModal(delta) {
            const input = document.getElementById('pmQuantidade');

            let valor = parseInt(input.value, 10) || 1;

            valor = Math.max(1, valor + delta);

            input.value = valor;

            atualizarTotalModal();
        }

        // ── Atualizar total ──────────────────────────────────────────────
        function atualizarTotalModal() {
            if (!produtoModalAtual) return;

            const input = document.getElementById('pmQuantidade');

            let qtd = parseInt(input.value, 10);

            if (!qtd || qtd < 1) {
                qtd = 1;
                input.value = 1;
            }

            const total = produtoModalAtual.preco * qtd;

            document.getElementById('pmTotal').textContent =
                'R$ ' + formatarMoeda(total);
        }

        // ── Adicionar ao carrinho via AJAX ────────────────────────────────
        async function adicionarAoCarrinhoModal() {
            if (!produtoModalAtual || !usuarioLogado) return;

            const btn = document.getElementById('pmBtnAdicionar');

            const qtd = parseInt(
                document.getElementById('pmQuantidade').value,
                10
            ) || 1;

            if (qtd < 1) return;

            const htmlOriginal = btn.innerHTML;

            btn.disabled = true;

            btn.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i> Adicionando...';

            const formData = new FormData();

            formData.append('product_id', produtoModalAtual.id);
            formData.append('quantity', qtd);
            formData.append('redirect', window.location.href);

            try {
                const resp = await fetch('add_carrinho.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await resp.json();

                mostrarToast(data.mensagem, data.sucesso);

                if (data.sucesso) {
                    modalProdutoInstance.hide();
                }

            } catch (err) {
                mostrarToast(
                    'Não foi possível adicionar o produto. Tente novamente.',
                    false
                );

            } finally {
                btn.disabled = false;
                btn.innerHTML = htmlOriginal;
            }
        }

        // ── Mensagem de retorno ───────────────────────────────────────────
        function mostrarToast(mensagem, sucesso) {
            const toast = document.createElement('div');

            toast.className =
                'alert ' + (sucesso ? 'alert-success' : 'alert-danger');

            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 280px;
                max-width: calc(100vw - 40px);
                box-shadow: 0 4px 12px rgba(0,0,0,.15);
            `;

            toast.textContent = mensagem;

            document.body.appendChild(toast);

            setTimeout(() => toast.remove(), 3500);
        }

        // ── Filtrar produtos ──────────────────────────────────────────────
        function filtrar(sub, btn) {
            document.querySelectorAll(
                '#filtros-salgados .sub-btn'
            ).forEach(b => b.classList.remove('ativo'));

            btn.classList.add('ativo');

            const itens = document.querySelectorAll(
                '#grid-salgados .produto-item'
            );

            let visiveis = 0;

            itens.forEach(item => {
                const dataSub = item.dataset.sub || '';

                const mostrar = sub === 'todos' || dataSub === sub;

                item.dataset.hidden = mostrar ? 'false' : 'true';

                if (mostrar) visiveis++;
            });

            document.getElementById('sem-produtos')
                .classList.toggle('visivel', visiveis === 0);
        }
    </script>

</body>
</html>