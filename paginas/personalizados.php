<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;

$itens_normais = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;
$itens_pers    = (isset($_SESSION['cart_personalizado']) && is_array($_SESSION['cart_personalizado'])) ? count($_SESSION['cart_personalizado']) : 0;
$itens_no_carrinho = $itens_normais + $itens_pers;

$redirect_uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizados - Pedacinho de Amor</title>
    <link rel="icon" type="image/x-icon" href="../imagens/icon.png">
    <link rel="stylesheet" href="../css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include_once ABSPATH . 'inc/header.php'; ?>

    <main>
        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-info text-center container mt-3">
                <?php echo htmlspecialchars($_SESSION['cart_message']); unset($_SESSION['cart_message']); ?>
            </div>
        <?php endif; ?>

        <section class="doces-hero" style="background-image:url('../imagens/doce3.webp');">
            <div class="doces-hero__overlay"></div>
            <div class="doces-hero__content">
                <h1>🎨 PERSONALIZADOS</h1>
                <p>Monte seu produto do jeito que você quiser!</p>
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

                    <ul class="nav nav-tabs mb-4" id="tabPersonalizado" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-doce" data-bs-toggle="tab" data-bs-target="#pane-doce" type="button" role="tab">
                                🍬 Doce
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-salgado" data-bs-toggle="tab" data-bs-target="#pane-salgado" type="button" role="tab">
                                🥐 Salgado
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-bolo" data-bs-toggle="tab" data-bs-target="#pane-bolo" type="button" role="tab">
                                🎂 Bolo
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="tabPersonalizadoContent">

                        <!-- ABA DOCE -->
                        <div class="tab-pane fade show active" id="pane-doce" role="tabpanel">
                            <form action="add_carrinho.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="product_id" value="personalizado">
                                <input type="hidden" name="tipo" value="doce">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_uri, ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Sabor Principal *</label>
                                        <input type="text" class="form-control custom-input" name="sabor" placeholder="Ex: Brigadeiro, Beijinho, Morango" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Quantidade *</label>
                                        <input type="number" class="form-control custom-input" name="quantity" min="1" value="1" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tema</label>
                                        <input type="text" class="form-control custom-input" name="tema" placeholder="Ex: Festa junina, Natal, Aniversário">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Quantidade de Camadas</label>
                                        <input type="number" class="form-control custom-input" name="cor" placeholder="Ex: 2">
                                    </div>                          
                                    
                                      <!-- arrumar essa parte pra quando o usuario selecionar as quantidades de camads,aparecer sozinho a quantidade no sabor camadas
                                       sem precisar especificar cada sabor("camada 1 = morango, camada 2 = chocolate ), aparecer um botao para ja pronto, tp ("botao camada 1 = dai o usuario coloca o sabor , camada 2 = sabor 2") -->
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Sabor das Camadas </label>
                                        <input type="text" class="form-control custom-input" name="cor" placeholder="Ex: ">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="custom-label d-block">Restrições Alimentares</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_gluten" id="doce_sg">
                                        <label class="form-check-label" for="doce_sg">Sem glúten</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_lactose" id="doce_sl">
                                        <label class="form-check-label" for="doce_sl">Sem lactose</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="vegano" id="doce_vg">
                                        <label class="form-check-label" for="doce_vg">Vegano</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Data Desejada</label>
                                        <input type="date" class="form-control custom-input" name="data_desejada" min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
                                        <small class="text-muted">Mínimo 2 dias de antecedência.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Imagem de Referência</label>
                                        <input type="file" class="form-control custom-input" name="imagem_referencia" accept="image/jpeg,image/png,image/webp">
                                        <small class="text-muted">JPG, PNG ou WEBP. Máx. 5MB.</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="custom-label">Detalhes Especiais</label>
                                    <textarea class="form-control custom-input" name="detalhes" rows="3" placeholder="Decorações, embalagem, mensagem..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-custom-submit" <?php echo !$usuario_logado ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-cart me-2"></i> Adicionar ao Carrinho
                                </button>
                            </form>
                        </div>

                        <!-- ABA SALGADO -->
                        <div class="tab-pane fade" id="pane-salgado" role="tabpanel">
                            <form action="add_carrinho.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="product_id" value="personalizado">
                                <input type="hidden" name="tipo" value="salgado">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_uri, ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tipo de Salgado *</label>
                                        <select class="form-select custom-input" name="sabor" required>
                                            <option value="">Selecione...</option>
                                            <option value="coxinha">Coxinha</option>
                                            <option value="esfiha">Esfiha</option>
                                            <option value="empada">Empada</option>
                                            <option value="bolinha_queijo">Bolinha de Queijo</option>
                                            <option value="enroladinho">Enroladinho</option>
                                            <option value="outro">Outro (descrever nos detalhes)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Recheio *</label>
                                        <input type="text" class="form-control custom-input" name="tema" placeholder="Ex: Frango, Carne, Queijo e presunto" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Quantidade *</label>
                                        <input type="number" class="form-control custom-input" name="quantity" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tamanho</label>
                                        <select class="form-select custom-input" name="tamanho">
                                            <option value="">Selecione...</option>
                                            <option value="mini">Mini (coquetel)</option>
                                            <option value="medio">Médio</option>
                                            <option value="grande">Grande</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="custom-label d-block">Restrições Alimentares</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_gluten" id="salg_sg">
                                        <label class="form-check-label" for="salg_sg">Sem glúten</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_lactose" id="salg_sl">
                                        <label class="form-check-label" for="salg_sl">Sem lactose</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="vegano" id="salg_vg">
                                        <label class="form-check-label" for="salg_vg">Vegano</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Data Desejada</label>
                                        <input type="date" class="form-control custom-input" name="data_desejada" min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
                                        <small class="text-muted">Mínimo 2 dias de antecedência.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Imagem de Referência</label>
                                        <input type="file" class="form-control custom-input" name="imagem_referencia" accept="image/jpeg,image/png,image/webp">
                                        <small class="text-muted">JPG, PNG ou WEBP. Máx. 5MB.</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="custom-label">Detalhes Especiais</label>
                                    <textarea class="form-control custom-input" name="detalhes" rows="3" placeholder="Evento, embalagem, quantidade por sabor..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-custom-submit" <?php echo !$usuario_logado ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-cart me-2"></i> Adicionar ao Carrinho
                                </button>
                            </form>
                        </div>

                        <!-- ABA BOLO -->
                        <div class="tab-pane fade" id="pane-bolo" role="tabpanel">
                            <form action="add_carrinho.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="product_id" value="personalizado">
                                <input type="hidden" name="tipo" value="bolo">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_uri, ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Tema / Ocasião *</label>
                                        <input type="text" class="form-control custom-input" name="tema" placeholder="Ex: Aniversário 1 ano, Casamento, Formatura" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Sabor da Massa *</label>
                                        <select class="form-select custom-input" name="sabor" required>
                                            <option value="">Selecione...</option>
                                            <option value="chocolate">Chocolate</option>
                                            <option value="baunilha">Baunilha</option>
                                            <option value="red_velvet">Red Velvet</option>
                                            <option value="cenoura">Cenoura</option>
                                            <option value="limao">Limão</option>
                                            <option value="outro">Outro (descrever nos detalhes)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Recheio</label>
                                        <input type="text" class="form-control custom-input" name="cor" placeholder="Ex: Ninho com morango, Brigadeiro, Doce de leite">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Número de Andares</label>
                                        <select class="form-select custom-input" name="tamanho">
                                            <option value="1">1 andar</option>
                                            <option value="2">2 andares</option>
                                            <option value="3">3 andares</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Número de Pessoas</label>
                                        <select class="form-select custom-input" name="quantity">
                                            <option value="10">Até 10 pessoas</option>
                                            <option value="20">Até 20 pessoas</option>
                                            <option value="30">Até 30 pessoas</option>
                                            <option value="50">Até 50 pessoas</option>
                                            <option value="80">Até 80 pessoas</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Cobertura</label>
                                        <select class="form-select custom-input" name="cobertura">
                                            <option value="">Selecione...</option>
                                            <option value="chantilly">Chantilly</option>
                                            <option value="pasta_americana">Pasta Americana</option>
                                            <option value="ganache">Ganache</option>
                                            <option value="naked_cake">Naked Cake (sem cobertura)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="custom-label d-block">Restrições Alimentares</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_gluten" id="bolo_sg">
                                        <label class="form-check-label" for="bolo_sg">Sem glúten</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="sem_lactose" id="bolo_sl">
                                        <label class="form-check-label" for="bolo_sl">Sem lactose</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="restricoes[]" value="vegano" id="bolo_vg">
                                        <label class="form-check-label" for="bolo_vg">Vegano</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Data Desejada</label>
                                        <input type="date" class="form-control custom-input" name="data_desejada" min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
                                        <small class="text-muted">Mínimo 2 dias de antecedência.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="custom-label">Imagem de Referência</label>
                                        <input type="file" class="form-control custom-input" name="imagem_referencia" accept="image/jpeg,image/png,image/webp">
                                        <small class="text-muted">JPG, PNG ou WEBP. Máx. 5MB.</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="custom-label">Detalhes Especiais</label>
                                    <textarea class="form-control custom-input" name="detalhes" rows="3" placeholder="Mensagem no bolo, cores da decoração, topo personalizado..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-custom-submit" <?php echo !$usuario_logado ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-cart me-2"></i> Adicionar ao Carrinho
                                </button>
                            </form>
                        </div>

                    </div><!-- /tab-content -->
                </div>
            </div>
        </div>
    </main>

    <?php include_once ABSPATH . 'inc/footer.php'; ?>

</body>
</html>