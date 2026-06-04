<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

// Estado de autenticação limpo
$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;

// Validação e cálculo do contador de itens na sessão (somando normais + personalizados)
$itens_normais = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;
$itens_personalizados = (isset($_SESSION['cart_personalizado']) && is_array($_SESSION['cart_personalizado'])) ? count($_SESSION['cart_personalizado']) : 0;
$itens_no_carrinho = $itens_normais + $itens_personalizados;

// Sanitização e isolamento da URI atual para evitar vetores de XSS refletido no redirect do form
$redirect_uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizados - Pedacinho de Amor</title>
    <link rel="stylesheet" href="../css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tipo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 25px; }
        .tipo-card { border: 1px solid #ddd; border-radius: 12px; padding: 18px; text-align: center; cursor: pointer; transition: .2s; background: #fff; }
        .tipo-card:hover { border-color: #0d6efd; }
        .tipo-card.selected { background: #e7f1ff; border: 2px solid #0d6efd; }
        .tipo-card i { display: block; margin-bottom: 8px; font-size: 24px; }
        .field-group { display: none; }
        .field-group.visible { display: block; }
        .preview-pill { display: inline-block; padding: 6px 14px; border-radius: 30px; background: #d1e7dd; color: #0f5132; font-size: 13px; margin-bottom: 20px; }
        .section-label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #777; margin: 25px 0 10px; border-top: 1px solid #eee; padding-top: 15px; }
        .tag-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag { border: 1px solid #ccc; border-radius: 30px; padding: 8px 15px; background: #fff; cursor: pointer; font-size: 13px; transition: .2s; }
        .tag:hover { background: #f8f9fa; }
        .tag.selected { background: #0d6efd; border-color: #0d6efd; color: #fff; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-field { margin-bottom: 15px; }
        @media(max-width:768px) { .tipo-grid { grid-template-columns: 1fr; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <h1>Pedacinho de Amor</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="sobrenos.html">Sobre Nós</a></li>
                <li><a href="doces.php">Doces</a></li>
                <li><a href="salgados.php">Salgados</a></li>
                <li><a href="personalizados.php" class="active">Personalizados</a></li>
                <li>
                    <a href="carrinho.php">
                        <i class="fas fa-shopping-cart"></i>
                        Carrinho (<?php echo $itens_no_carrinho; ?>)
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
        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-info text-center container mt-3">
                <?php echo $_SESSION['cart_message']; unset($_SESSION['cart_message']); ?>
            </div>
        <?php endif; ?>

        <section class="work-section">
            <div class="work-container">
                <div class="work-text">
                    <h2>Como trabalhamos?</h2>
                    <p>Cada doce, bolo e salgado é preparado de forma artesanal, com ingredientes selecionados e muito carinho.</p>
                </div>
                <div class="work-image">
                    <img src="imagens/doce3.webp" alt="Como trabalhamos Pedacinho de Amor">
                </div>
            </div>
        </section>

        <div class="container my-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Monte seu Produto Personalizado</h2>
                <p class="text-muted">Escolha o tipo, tema, sabor e detalhes — e adicione ao carrinho!</p>
            </div>

            <?php if (!$usuario_logado): ?>
                <div class="alert alert-warning text-center col-md-8 mx-auto mb-4">
                    <i class="fas fa-lock me-2"></i>
                    Faça <a href="../index.php" class="alert-link">login</a> para adicionar produtos ao carrinho.
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    
                    <form action="add_carrinho.php" method="POST">
                        
                        <input type="hidden" name="product_id" value="personalizado">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_uri, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="tipo" id="tipoSelecionado">

                        <div class="tipo-grid">
                            <div class="tipo-card" data-tipo="bolo" onclick="selectTipo('bolo')">
                                <i class="fas fa-cake-candles"></i>
                                <span>Bolo</span>
                            </div>
                            <div class="tipo-card" data-tipo="doce" onclick="selectTipo('doce')">
                                <i class="fas fa-candy-cane"></i>
                                <span>Doce</span>
                            </div>
                            <div class="tipo-card" data-tipo="salgado" onclick="selectTipo('salgado')">
                                <i class="fas fa-bread-slice"></i>
                                <span>Salgado</span>
                            </div>
                        </div>

                        <div class="field-group" id="grupo-bolo">
                            <div class="preview-pill">🍰 Bolo Personalizado</div>
                            
                            <p class="section-label">Tamanho (Obrigatório para o Carrinho)</p>
                            <input type="hidden" name="tamanho" id="bolo_tamanho">
                            <div class="tag-row">
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_tamanho','10 Fatias')">10 Fatias</button>
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_tamanho','20 Fatias')">20 Fatias</button>
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_tamanho','30 Fatias')">30 Fatias</button>
                            </div>

                            <p class="section-label">Massa</p>
                            <input type="hidden" name="massa" id="bolo_massa">
                            <div class="tag-row">
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_massa','Chocolate')">Chocolate</button>
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_massa','Baunilha')">Baunilha</button>
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_massa','Cenoura')">Cenoura</button>
                            </div>

                            <p class="section-label">Recheio (Selecione um)</p>
                            <input type="hidden" name="recheio[]" id="bolo_recheio">
                            <div class="tag-row">
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_recheio','Brigadeiro')">Brigadeiro</button>
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_recheio','Doce de leite')">Doce de leite</button>
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_recheio','Ninho')">Ninho</button>
                            </div>

                            <p class="section-label">Cobertura (Obrigatório para o Carrinho)</p>
                            <input type="hidden" name="cobertura" id="bolo_cobertura">
                            <div class="tag-row">
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_cobertura','Chantininho')">Chantininho</button>
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_cobertura','Ganache')">Ganache</button>
                                <button type="button" class="tag" onclick="selectTag(this,'bolo_cobertura','Pasta Americana')">Pasta Americana</button>
                            </div>

                            <div class="form-row mt-4">
                                <div class="form-field">
                                    <label>Tema / Decoração</label>
                                    <input type="text" class="form-control" name="tema">
                                </div>
                                <div class="form-field">
                                    <label>Texto no bolo</label>
                                    <input type="text" class="form-control" name="texto_bolo">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label>Observações</label>
                                <textarea class="form-control" rows="4" name="obs"></textarea>
                            </div>
                        </div>

                        <div class="field-group" id="grupo-doce">
                            <div class="preview-pill">🍬 Doce Personalizado</div>

                            <p class="section-label">Tipo de Doce</p>
                            <input type="hidden" name="tipo_doce" id="doce_tipo">
                            <div class="tag-row">
                                <button type="button" class="tag" onclick="selectTag(this,'doce_tipo','Brigadeiro')">Brigadeiro</button>
                                <button type="button" class="tag" onclick="selectTag(this,'doce_tipo','Camafeu')">Camafeu</button>
                                <button type="button" class="tag" onclick="selectTag(this,'doce_tipo','Trufa')">Trufa</button>
                            </div>

                            <div class="form-row mt-4">
                                <div class="form-field">
                                    <label>Quantidade de Itens</label>
                                    <input type="number" class="form-control" name="quantity" min="1" value="1">
                                </div>
                                <div class="form-field">
                                    <label>Sabor</label>
                                    <input type="text" class="form-control" name="sabor[]" placeholder="Ex: Morango, Tradicional">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label>Observações</label>
                                <textarea class="form-control" rows="4" name="obs"></textarea>
                            </div>
                        </div>

                        <div class="field-group" id="grupo-salgado">
                            <div class="preview-pill">🥟 Salgado Personalizado</div>

                            <p class="section-label">Tipo de Salgado</p>
                            <input type="hidden" name="tipo_salgado" id="salgado_tipo">
                            <div class="tag-row">
                                <button type="button" class="tag" onclick="selectTag(this,'salgado_tipo','Coxinha')">Coxinha</button>
                                <button type="button" class="tag" onclick="selectTag(this,'salgado_tipo','Enroladinho')">Enroladinho</button>
                                <button type="button" class="tag" onclick="selectTag(this,'salgado_tipo','Quiche')">Quiche</button>
                            </div>

                            <div class="form-row mt-4">
                                <div class="form-field">
                                    <label>Quantidade de Itens</label>
                                    <input type="number" class="form-control" name="quantity" min="1" value="1">
                                </div>
                                <div class="form-field">
                                    <label>Recheio</label>
                                    <input type="text" class="form-control" name="recheio[]" placeholder="Ex: Frango com Catupiry">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label>Observações</label>
                                <textarea class="form-control" rows="4" name="obs"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-4" <?php echo !$usuario_logado ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart me-2"></i>
                            Adicionar ao Carrinho
                        </button>

                    </form>

                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bundle.min.js"></script>
    <script>
        function selectTipo(tipo){
            document.querySelectorAll('.tipo-card').forEach(card=>{ card.classList.remove('selected'); });
            document.querySelector('[data-tipo="'+tipo+'"]').classList.add('selected');
            document.querySelectorAll('.field-group').forEach(grupo=>{ grupo.classList.remove('visible'); });
            document.getElementById('grupo-'+tipo).classList.add('visible');
            document.getElementById('tipoSelecionado').value = tipo;
        }

        function selectTag(btn, campo, valor){
            const grupo = btn.parentElement;
            grupo.querySelectorAll('.tag').forEach(tag=>{ tag.classList.remove('selected'); });
            btn.classList.add('selected');
            document.getElementById(campo).value = valor;
        }
    </script>
</body>
</html>