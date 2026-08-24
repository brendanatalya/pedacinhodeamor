<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';
require_once ABSPATH . 'inc/subcategorias.php'; // função extrair_subcategoria() compartilhada
require_once DBAPI; 
include(HEADER_TEMPLATE);


$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;

// Carrega todos os produtos (exceto personalizados)
$todos = array_filter(find_products(null), fn($p) => $p['tipo'] !== 'personalizado');

$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

$total_carrinho = array_sum($_SESSION['cart'] ?? []);
?>
<!--brendinhaaa 
aqui ta a parte do css do cardapio ta, fiz so pra ter uma nocao de como vai ser o design, depois fica a vontade pra fazer sua magia diva rs
e o cardapio é onde eu juntei e coloquei o salgado e doce ta -->

<!---desse jeito que fiz ele filtra com base no nome do produto e no "banco de palavras" que ta guardado aq, tipo
se add um produto com o nome "bolo de chocolate", ele vai salvar e aparecer no filtro como "bolo" e assim por diante. toma cuidado
na hora de salvar quando for add um novo produto pra colocar o nome certo, pq se colocar sla, inves de bolo de chocolate e so por 
chocolate ele vai cair em outros.
fiz isso por agora, qlqr coisa eu falo com as meninas dps pra add isso no banco msm, pra ter uma subcategoria de doce e salgado pra nao ter q fazer essa gambiarra rs -->
      
    <main>
        <section class="doces-intro" style="background: url('<?php echo BASEURL; ?>/imagens/brigadeiros.jpg') no-repeat center center; background-size: cover;">

            <div class="conteudodoces">
                <div class="doces-header">
                    <h1 class="doces-titulo">Cardápio</h1>
                    <!-- linha bonitinha -->
                    <div class="doce-detalhe"></div>
                </div>
                <p class="doces-subtitulo">
                    Tudo feito com carinho, do doce ao salgado!
                </p>
            </div>
        </section>

        <div class="container">

            <?php if ($cartMessage): ?>
                <div class="alert mc-alert-success mt-3"><?php echo htmlspecialchars($cartMessage); ?></div>
            <?php endif; ?>

            <!--arruma isso bonitnho depois, transvforma em modal-->
            <?php if (!$usuario_logado): ?>
                <div class="alert alert-aviso mt-3">
                    Faça <a href="../index.php">login</a> para adicionar produtos ao carrinho.
                </div>
            <?php endif; ?>

            <!-- doce-->
            <section class="secao-categoria" id="secao-doces">
                <h2>Doces</h2>

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
                                <img src="<?php echo BASEURL . "/imagens/"; ?><?php echo htmlspecialchars($p['imagem_referencia']); ?>"
                                     alt="<?php echo htmlspecialchars($p['nome']); ?>">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                                <p><?php echo htmlspecialchars($p['descricao'] ?? 'Delicioso produto artesanal'); ?></p>
                                <div class="product-price"><?php echo number_format($p['preco'], 2, ',', '.'); ?></div>
                                <?php if (!$p['disponivel']): ?>
                                    <div class="unavailable-badge">Indisponível</div>
                                <?php endif; ?>
                                <form action="add_carrinho.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect"
                                           value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES); ?>">
                                    <button type="submit" class="add-to-carrinho-btn"
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

            <!--salgados-->
            <section class="secao-categoria" id="secao-salgados">
                <h2>Salgados</h2>

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
                                <img src="<?php echo BASEURL . "/imagens/"; ?><?php echo htmlspecialchars($p['imagem_referencia']); ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                                <p><?php echo htmlspecialchars($p['descricao'] ?? 'Delicioso produto artesanal'); ?></p>
                                <div class="product-price"><?php echo number_format($p['preco'], 2, ',', '.'); ?></div>
                                <?php if (!$p['disponivel']): ?>
                                    <div class="unavailable-badge">Indisponível</div>
                                <?php endif; ?>
                                <form action="add_carrinho.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect"
                                           value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES); ?>">
                                    <button type="submit" class="add-to-carrinho-btn"
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

        </div>
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
            const mostrar = sub === 'todos' || dataSub === sub;
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
    // A função extrair_subcategoria() agora vive em inc/subcategorias.php
    // (compartilhada com doces.php e salgados.php) — veja o require_once no topo.

    include '../inc/modal.php'; 
    include(FOOTER_TEMPLATE);
?>