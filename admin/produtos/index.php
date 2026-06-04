<?php 
if (!isset($_SESSION)) session_start();

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ' . dirname(dirname(dirname(__DIR__))) . '/index.php');
    exit;
}

include dirname(__DIR__, 2) . '/config.php';
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
            $mensagem = 'Produto adicionado com sucesso!';
            $tipo_mensagem = 'success';
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
            $tipo_mensagem = 'success';
        }
        
        elseif ($acao === 'deletar') {
            $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $mensagem = 'Produto deletado com sucesso!';
            $tipo_mensagem = 'success';
        }
        
        elseif ($acao === 'alternar_disponibilidade') {
            $stmt = $conn->prepare("UPDATE produtos SET disponivel = NOT disponivel WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $mensagem = 'Status de disponibilidade alterado!';
            $tipo_mensagem = 'success';
        }

        elseif ($acao === 'vincular_ingrediente') {
            $stmt = $conn->prepare("
                INSERT INTO estoque_ingrediente (id, nome,unidade, qtd_estoque, qtd_minima)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE qtd_necessaria = VALUES(qtd_necessaria)
            ");
            $stmt->execute([$_POST['id_produto'], $_POST['id_ingrediente'], $_POST['qtd_necessaria']]);
            $mensagem = 'Ingrediente vinculado!';
            $tipo_mensagem = 'success';
        }

        elseif ($acao === 'remover_ingrediente') {
            $stmt = $conn->prepare("DELETE FROM estoque_ingrediente WHERE id_produto = ? AND id_ingrediente = ?");
            $stmt->execute([$_POST['id_produto'], $_POST['id_ingrediente']]);
            $mensagem = 'Ingrediente removido!';
            $tipo_mensagem = 'success';
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
            FROM estoque_ingrediente
            INNER JOIN estoque_ingredientes ei ON pi.id_ingrediente = ei.id
            WHERE pi.id_produto = ?
        ");
        $stmt->execute([$produto_edicao['id']]);
        $ingredientes_vinculados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    close_database($conn);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Admin</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8f9fa;">
<div class="container-fluid p-4">
    
    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensagem); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">

        <!-- FORMULÁRIO -->
        <div class="col-md-4">
            <div class="form-section">
                <h5><?php echo $produto_edicao ? 'Editar Produto' : 'Adicionar Novo Produto'; ?></h5>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao" value="<?php echo $produto_edicao ? 'editar' : 'adicionar'; ?>">
                    <?php if ($produto_edicao): ?>
                        <input type="hidden" name="id" value="<?php echo $produto_edicao['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nome do Produto</label>
                        <input type="text" name="nome" class="form-control" required 
                            value="<?php echo $produto_edicao ? htmlspecialchars($produto_edicao['nome']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"><?php 
                            echo $produto_edicao ? htmlspecialchars($produto_edicao['descricao']) : ''; 
                        ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Preço (R$)</label>
                        <input type="text" name="preco" id="preco" class="form-control" placeholder="R$ 0,00" required
                            value="<?php echo $produto_edicao ? 'R$ ' . number_format($produto_edicao['preco'], 2, ',', '.') : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="salgado" <?php echo ($produto_edicao && $produto_edicao['tipo'] === 'salgado') ? 'selected' : ''; ?>>Salgado</option>
                            <option value="doce" <?php echo ($produto_edicao && $produto_edicao['tipo'] === 'doce') ? 'selected' : ''; ?>>Doce</option>
                            <option value="bolo" <?php echo ($produto_edicao && $produto_edicao['tipo'] === 'bolo') ? 'selected' : ''; ?>>Bolo</option>
                            <option value="personalizado" <?php echo ($produto_edicao && $produto_edicao['tipo'] === 'personalizado') ? 'selected' : ''; ?>>Personalizado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagem do Produto</label>
                        <input type="file" name="imagem" class="form-control" accept="image/*">
                        <?php if ($produto_edicao && !empty($produto_edicao['imagem_referencia'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo BASEURL . $produto_edicao['imagem_referencia']; ?>" width="120" style="border-radius:10px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="disponivel" class="form-check-input" id="disponivel"
                            <?php echo (!$produto_edicao || $produto_edicao['disponivel']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disponivel">Disponível para venda</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save"></i> <?php echo $produto_edicao ? 'Atualizar' : 'Adicionar'; ?>
                    </button>
                    
                    <?php if ($produto_edicao): ?>
                        <a href="<?php echo BASEURL; ?>admin/produtos/" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- SEÇÃO DE INGREDIENTES (só no modo editar) -->
            <?php if ($produto_edicao): ?>
            <div class="form-section mt-3">
                <h6><i class="fas fa-mortar-pestle me-2"></i> Ingredientes do Produto</h6>

                <?php if (!empty($ingredientes_vinculados)): ?>
                    <table class="table table-sm align-middle mb-3">
                        <thead class="table-light">
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
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted small">Nenhum ingrediente vinculado ainda.</p>
                <?php endif; ?>

                <!-- Adicionar vínculo -->
                <form method="POST" class="row g-2 align-items-end mt-1">
                    <input type="hidden" name="acao" value="vincular_ingrediente">
                    <input type="hidden" name="id_produto" value="<?php echo $produto_edicao['id']; ?>">

                    <div class="col-6">
                        <label class="form-label small">Ingrediente</label>
                        <select name="id_ingrediente" class="form-control form-control-sm" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($ingredientes_disponiveis as $ing): ?>
                                <option value="<?php echo $ing['id']; ?>">
                                    <?php echo htmlspecialchars($ing['nome']); ?> (<?php echo $ing['unidade']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label small">Quantidade</label>
                        <input type="number" name="qtd_necessaria" class="form-control form-control-sm"
                            step="0.001" min="0.001" required placeholder="0,000">
                    </div>

                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- LISTA DE PRODUTOS -->
        <div class="col-md-8">
            <div class="form-section">
                <h5>Produtos Cadastrados (<?php echo count($produtos); ?>)</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover table-produtos">
                        <thead class="table-light">
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
                                <?php
                                    $corTipo = 'bg-secondary';
                                    if ($p['tipo'] == 'doce') $corTipo = 'bg-pink';
                                    elseif ($p['tipo'] == 'salgado') $corTipo = 'bg-warning text-dark';
                                    elseif ($p['tipo'] == 'bolo') $corTipo = 'bg-danger';
                                    elseif ($p['tipo'] == 'personalizado') $corTipo = 'bg-primary';
                                ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($p['imagem_referencia'])): ?>
                                            <img src="<?php echo BASEURL . $p['imagem_referencia']; ?>"
                                                width="70" height="70"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalImagem<?php echo $p['id']; ?>"
                                                style="object-fit:cover; border-radius:12px; border:2px solid #eee; cursor:pointer; transition:0.3s;"
                                                onmouseover="this.style.transform='scale(1.08)'"
                                                onmouseout="this.style.transform='scale(1)'">

                                            <div class="modal fade" id="modalImagem<?php echo $p['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content" style="background:transparent;border:none;">
                                                        <div class="text-end mb-2">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                        <img src="<?php echo BASEURL . $p['imagem_referencia']; ?>"
                                                            class="img-fluid rounded shadow"
                                                            style="max-height:80vh; object-fit:contain;">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div style="width:70px;height:70px;background:#f1f1f1;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#999;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><small>#<?php echo $p['id']; ?></small></td>
                                    <td><strong><?php echo htmlspecialchars($p['nome']); ?></strong></td>
                                    <td><span class="badge <?php echo $corTipo; ?> p-2"><?php echo ucfirst($p['tipo']); ?></span></td>
                                    <td><strong>R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></strong></td>
                                    <td>
                                        <span class="badge <?php echo $p['disponivel'] ? 'badge-disponivel' : 'badge-indisponivel'; ?>">
                                            <?php echo $p['disponivel'] ? 'Disponível' : 'Indisponível'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?editar=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?')">
                                            <input type="hidden" name="acao" value="alternar_disponibilidade">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-<?php echo $p['disponivel'] ? 'eye' : 'eye-slash'; ?>"></i>
                                            </button>
                                        </form>

                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Deletar este produto?')">
                                            <input type="hidden" name="acao" value="deletar">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($produtos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> Nenhum produto cadastrado ainda.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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