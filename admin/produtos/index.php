<?php 
if (!isset($_SESSION)) session_start();

include dirname(__DIR__, 2) . '/config.php';

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

require_once(DBAPI);

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    try {
        $conn = open_database();
        
        if ($acao === 'adicionar') {
            $nome = $_POST['nome'];
            $descricao = $_POST['descricao'];
            $preco = str_replace(['R$ ', '.', ','], ['', '', '.'], $_POST['preco']);
            $tipo = $_POST['tipo'];
            $disponivel = isset($_POST['disponivel']) ? 1 : 0;
            $imagem = null;

            if (!empty($_FILES['imagem']['name'])) {
                $pasta = '../../imagens/uploads/produtos/';
                if (!file_exists($pasta)) mkdir($pasta, 0777, true);
                $nomeImagem = time() . '_' . $_FILES['imagem']['name'];
                move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $nomeImagem);
                $imagem = 'imagens/uploads/produtos/' . $nomeImagem;
            }

            $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, tipo, disponivel, imagem_referencia) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $descricao, $preco, $tipo, $disponivel, $imagem]);
            $novoId = $conn->lastInsertId();
            close_database($conn);
            header('Location: ?editar=' . $novoId . '&novo=1');
            exit;
        }
        
        elseif ($acao === 'editar') {
            $id = $_POST['id'];
            $nome = $_POST['nome'];
            $descricao = $_POST['descricao'];
            $preco = str_replace(['R$ ', '.', ','], ['', '', '.'], $_POST['preco']);
            $tipo = $_POST['tipo'];
            $disponivel = isset($_POST['disponivel']) ? 1 : 0;

            $stmt = $conn->prepare("SELECT imagem_referencia FROM produtos WHERE id = ?");
            $stmt->execute([$id]);
            $produtoAtual = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagem = $produtoAtual['imagem_referencia'];
            
            if (!empty($_FILES['imagem']['name'])) {
                $pasta = $_SERVER['DOCUMENT_ROOT'] . '/pedacinhodeamor/imagens/uploads/produtos/';
                if (!file_exists($pasta)) mkdir($pasta, 0777, true);
                if (!empty($produtoAtual['imagem_referencia'])) {
                    $imagemAntiga = $_SERVER['DOCUMENT_ROOT'] . '/pedacinhodeamor/' . $produtoAtual['imagem_referencia'];
                    if (file_exists($imagemAntiga)) unlink($imagemAntiga);
                }
                $nomeImagem = uniqid() . '_' . basename($_FILES['imagem']['name']);
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $nomeImagem)) {
                    $imagem = 'imagens/uploads/produtos/' . $nomeImagem;
                }
            }

            $stmt = $conn->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ?, tipo = ?, disponivel = ?, imagem_referencia = ? WHERE id = ?");
            $stmt->execute([$nome, $descricao, $preco, $tipo, $disponivel, $imagem, $id]);
            $mensagem = 'Produto atualizado com sucesso!';
            $tipo_mensagem = 'sucesso';
        }
        
        elseif ($acao === 'deletar') {
            $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $mensagem = 'Produto deletado com sucesso!';
            $tipo_mensagem = 'sucesso';
        }
        
        elseif ($acao === 'alternar_disponibilidade') {
            $stmt = $conn->prepare("UPDATE produtos SET disponivel = NOT disponivel WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $mensagem = 'Status de disponibilidade alterado!';
            $tipo_mensagem = 'sucesso';
        }

        elseif ($acao === 'vincular_ingrediente') {
            $stmt = $conn->prepare("
                INSERT INTO produto_ingrediente (id_produto, id_ingrediente, qtd_necessaria)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE qtd_necessaria = VALUES(qtd_necessaria)
            ");
            $stmt->execute([$_POST['id_produto'], $_POST['id_ingrediente'], $_POST['qtd_necessaria']]);
            $mensagem = 'Ingrediente vinculado!';
            $tipo_mensagem = 'sucesso';
        }

        elseif ($acao === 'remover_ingrediente') {
            $stmt = $conn->prepare("DELETE FROM produto_ingrediente WHERE id_produto = ? AND id_ingrediente = ?");
            $stmt->execute([$_POST['id_produto'], $_POST['id_ingrediente']]);
            $mensagem = 'Ingrediente removido!';
            $tipo_mensagem = 'sucesso';
        }
        
        close_database($conn);
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

// Buscar todos os produtos
$conn = open_database();
$stmt = $conn->prepare("SELECT * FROM produtos ORDER BY id DESC");
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
close_database($conn);

// Buscar produto para edição
$produto_edicao = null;
$ingredientes_disponiveis = [];
$ingredientes_vinculados = [];

if (isset($_GET['editar'])) {
    $conn = open_database();

    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $produto_edicao = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto_edicao) {
        $stmt = $conn->prepare("SELECT * FROM estoque_ingredientes ORDER BY nome ASC");
        $stmt->execute();
        $ingredientes_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("
            SELECT pi.*, ei.nome, ei.unidade
            FROM produto_ingrediente pi
            INNER JOIN estoque_ingredientes ei ON pi.id_ingrediente = ei.id
            WHERE pi.id_produto = ?
        ");
        $stmt->execute([$produto_edicao['id']]);
        $ingredientes_vinculados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    close_database($conn);
}

// Cor de badge por tipo de produto (classes definidas no CSS do admin)
const TIPO_BADGE = [
    'doce'          => 'bg-pink',
    'salgado'       => 'bg-gold',
    'bolo'          => 'bg-wine',
    'personalizado' => 'bg-plum',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Admin</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/produtos-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container-fluid p-4">

    <div class="gpr-toolbar">
        <h4><i class="fas fa-birthday-cake"></i> Gerenciar Produtos</h4>
        <span class="gpr-total"><?php echo count($produtos); ?> cadastrados</span>
    </div>

    <?php if ($mensagem): ?>
        <div class="gpr-alert mc-alert alert-<?php echo $tipo_mensagem; ?>" role="alert">
            <span><?php echo htmlspecialchars($mensagem); ?></span>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['novo']) && $produto_edicao): ?>
        <div class="gpr-onboarding">
            <div class="emoji">🎉</div>
            <div>
                <strong>Produto "<?php echo htmlspecialchars($produto_edicao['nome']); ?>" criado com sucesso!</strong>
                <span>Agora vincule os ingredientes que ele consome para o desconto automático do estoque funcionar.</span>
            </div>
        </div>
        <div class="gpr-steps">
            <div class="gpr-step feito"><i class="fas fa-check-circle"></i> 1. Produto criado</div>
            <div class="gpr-step-linha"></div>
            <div class="gpr-step atual"><i class="fas fa-mortar-pestle"></i> 2. Vincular ingredientes</div>
            <div class="gpr-step-linha"></div>
            <div class="gpr-step futuro"><i class="fas fa-check"></i> 3. Pronto para vender</div>
        </div>
    <?php endif; ?>

    <div class="gpr-grid">

        <!-- FORMULÁRIO -->
        <div>
            <div class="gpr-card">
                <h5><?php echo $produto_edicao ? 'Editar Produto' : 'Adicionar Novo Produto'; ?></h5>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao" value="<?php echo $produto_edicao ? 'editar' : 'adicionar'; ?>">
                    <?php if ($produto_edicao): ?>
                        <input type="hidden" name="id" value="<?php echo $produto_edicao['id']; ?>">
                    <?php endif; ?>

                    <div class="gpr-campo">
                        <label class="form-label-custom">Nome do Produto</label>
                        <input type="text" name="nome" class="form-control-custom" required 
                            value="<?php echo $produto_edicao ? htmlspecialchars($produto_edicao['nome']) : ''; ?>">
                    </div>

                    <div class="gpr-campo">
                        <label class="form-label-custom">Descrição</label>
                        <textarea name="descricao" class="form-control-custom" rows="3"><?php 
                            echo $produto_edicao ? htmlspecialchars($produto_edicao['descricao']) : ''; 
                        ?></textarea>
                    </div>

                    <div class="gpr-campo">
                        <label class="form-label-custom">Preço (R$)</label>
                        <input type="text" name="preco" id="preco" class="form-control-custom" placeholder="R$ 0,00" required
                            value="<?php echo $produto_edicao ? 'R$ ' . number_format($produto_edicao['preco'], 2, ',', '.') : ''; ?>">
                    </div>

                    <div class="gpr-campo">
                        <label class="form-label-custom">Tipo</label>
                        <select name="tipo" class="form-control-custom" required>
                            <option value="">Selecione...</option>
                            <option value="salgado" <?php echo ($produto_edicao && $produto_edicao['tipo'] === 'salgado') ? 'selected' : ''; ?>>Salgado</option>
                            <option value="doce" <?php echo ($produto_edicao && $produto_edicao['tipo'] === 'doce') ? 'selected' : ''; ?>>Doce</option>
                            <option value="bolo" <?php echo ($produto_edicao && $produto_edicao['tipo'] === 'bolo') ? 'selected' : ''; ?>>Bolo</option>
                            <option value="personalizado" <?php echo ($produto_edicao && $produto_edicao['tipo'] === 'personalizado') ? 'selected' : ''; ?>>Personalizado</option>
                        </select>
                    </div>

                    <div class="gpr-campo">
                        <label class="form-label-custom">Imagem do Produto</label>
                        <input type="file" name="imagem" class="form-control-custom" accept="image/*">
                        <?php if ($produto_edicao && !empty($produto_edicao['imagem_referencia'])): ?>
                            <div class="gpr-imagem-preview">
                                <img src="<?php echo BASEURL . "imagens/" . $produto_edicao['imagem_referencia']; ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="gpr-check">
                        <input type="checkbox" name="disponivel" id="disponivel"
                            <?php echo (!$produto_edicao || $produto_edicao['disponivel']) ? 'checked' : ''; ?>>
                        <label for="disponivel">Disponível para venda</label>
                    </div>

                    <button type="submit" class="gpr-btn-primary">
                        <i class="fas fa-save"></i> <?php echo $produto_edicao ? 'Atualizar' : 'Adicionar'; ?>
                    </button>
                    
                    <?php if ($produto_edicao): ?>
                        <?php if (isset($_GET['novo'])): ?>
                            <a href="<?php echo BASEURL; ?>admin/produtos/" class="gpr-btn-concluir">
                                <i class="fas fa-check"></i> Concluir e voltar à lista
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASEURL; ?>admin/produtos/" class="contabotao contabotao-ghost">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </form>
            </div>

            <!-- SEÇÃO DE INGREDIENTES (só no modo editar) -->
            <?php if ($produto_edicao): ?>
            <div class="gpr-card <?php echo isset($_GET['novo']) ? 'gpr-card-destaque' : ''; ?>">
                <h6>
                    <i class="fas fa-mortar-pestle"></i>
                    Ingredientes da Receita
                    <?php if (isset($_GET['novo'])): ?>
                        <span class="gpr-tag-suave">Configure agora</span>
                    <?php else: ?>
                        <span class="gpr-tag-neutra"><?php echo count($ingredientes_vinculados); ?> vinculado(s)</span>
                    <?php endif; ?> 
                </h6>

                <?php if (!empty($ingredientes_vinculados)): ?>
                    <table class="table-custom w-100 mb-3">
                        <thead>
                            <tr>
                                <th>Ingrediente</th>
                                <th>Qtd</th>
                                <th>Unid.</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ingredientes_vinculados as $iv): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($iv['nome']); ?></td>
                                    <td><?php echo number_format($iv['qtd_necessaria'], 3, ',', '.'); ?></td>
                                    <td><?php echo $iv['unidade']; ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Remover vínculo?')">
                                            <input type="hidden" name="acao" value="remover_ingrediente">
                                            <input type="hidden" name="id_produto" value="<?php echo $produto_edicao['id']; ?>">
                                            <input type="hidden" name="id_ingrediente" value="<?php echo $iv['id_ingrediente']; ?>">
                                            <button type="submit" class="gpr-btn-remover" title="Remover">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="gpr-vazio-texto">Nenhum ingrediente vinculado ainda.</p>
                <?php endif; ?>

                <!-- Adicionar vínculo -->
                <form method="POST" class="gpr-ing-form">
                    <input type="hidden" name="acao" value="vincular_ingrediente">
                    <input type="hidden" name="id_produto" value="<?php echo $produto_edicao['id']; ?>">

                    <div>
                        <label class="form-label-custom" style="font-size:.75rem;">Ingrediente</label>
                        <select name="id_ingrediente" class="gpr-input-sm" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($ingredientes_disponiveis as $ing): ?>
                                <option value="<?php echo $ing['id']; ?>">
                                    <?php echo htmlspecialchars($ing['nome']); ?> (<?php echo $ing['unidade']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label-custom" style="font-size:.75rem;">Quantidade</label>
                        <input type="number" name="qtd_necessaria" class="gpr-input-sm"
                            step="0.001" min="0.001" required placeholder="0,000">
                    </div>

                    <button type="submit" class="gpr-btn-add" title="Vincular">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- LISTA DE PRODUTOS -->
        <div>
            <div class="gpr-table-card">
                <h5 style="padding-top:14px;">Produtos Cadastrados</h5>

                <?php if ($produtos): ?>
                    <table class="gpr-table">
                        <thead>
                            <tr>
                                <th>Imagem</th>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Preço</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $p): ?>
                                <?php $corTipo = TIPO_BADGE[$p['tipo']] ?? 'gpr-tag-neutra'; ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($p['imagem_referencia'])): ?>
                                            <img src="<?php echo BASEURL . "imagens/" . $p['imagem_referencia']; ?>"
                                                class="gpr-thumb"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalImagem<?php echo $p['id']; ?>">

                                            <div class="modal fade" id="modalImagem<?php echo $p['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content" style="background:transparent;border:none;">
                                                        <div class="text-end mb-2">
                                                            <button type="button" class="gpr-modal-fechar" data-bs-dismiss="modal">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                        <img src="<?php echo BASEURL . "imagens/" . $p['imagem_referencia']; ?>"
                                                            class="img-fluid rounded shadow"
                                                            style="max-height:80vh; object-fit:contain;">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="gpr-thumb-vazio">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>#<?php echo $p['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($p['nome']); ?></strong></td>
                                    <td><span class="badge <?php echo $corTipo; ?>"><?php echo ucfirst($p['tipo']); ?></span></td>
                                    <td class="gpr-preco">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $p['disponivel'] ? 'badge-disponivel' : 'badge-indisponivel'; ?>">
                                            <?php echo $p['disponivel'] ? 'Disponível' : 'Indisponível'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="gpr-acoes">
                                            <a href="?editar=<?php echo $p['id']; ?>" class="gpr-btn-icon editar" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form method="POST" onsubmit="return confirm('Tem certeza?')">
                                                <input type="hidden" name="acao" value="alternar_disponibilidade">
                                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                <button type="submit" class="gpr-btn-icon alternar" title="Alternar disponibilidade">
                                                    <i class="fas fa-<?php echo $p['disponivel'] ? 'eye' : 'eye-slash'; ?>"></i>
                                                </button>
                                            </form>

                                            <form method="POST" onsubmit="return confirm('Deletar este produto?')">
                                                <input type="hidden" name="acao" value="deletar">
                                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                <button type="submit" class="gpr-btn-icon excluir" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="gpr-vazio">
                        <i class="fas fa-info-circle"></i>
                        Nenhum produto cadastrado ainda.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
<script>
const campoPreco = document.getElementById('preco');
if (campoPreco) {
    campoPreco.addEventListener('input', function(e) {
        let valor = e.target.value.replace(/\D/g, '');
        valor = (valor / 100).toFixed(2) + '';
        valor = valor.replace(".", ",");
        valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        e.target.value = 'R$ ' + valor;
    });
}
</script>
</body>
</html>