<?php 
if (!isset($_SESSION)) session_start();

// Verificar se usuário está logado e é admin
if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ' . dirname(dirname(dirname(__DIR__))) . '/index.php');
    exit;
}

include '../../config.php';
require_once(DBAPI);

// Processar ações (adicionar, editar, deletar, alternar disponibilidade)
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    try {
        $conn = open_database();
        
        if ($acao === 'adicionar') {
            $nome = $_POST['nome'];
            $descricao = $_POST['descricao'];
            $preco = str_replace('R$ ', '', $_POST['preco']);
            $preco = str_replace('.', '', $preco);
            $preco = str_replace(',', '.', $preco);

            $tipo = $_POST['tipo'];
            $disponivel = isset($_POST['disponivel']) ? 1 : 0;
           $imagem = null;

if (!empty($_FILES['imagem']['name'])) {

    $pasta = '../../uploads/produtos/';

    if (!file_exists($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $nomeImagem = time() . '_' . $_FILES['imagem']['name'];

    $caminhoImagem = $pasta . $nomeImagem;

    move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem);

    $imagem = 'uploads/produtos/' . $nomeImagem;
}
            
            $stmt = $conn->prepare("
                INSERT INTO produtos (nome, descricao, preco, tipo, disponivel, imagem_referencia)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nome, $descricao, $preco, $tipo, $disponivel, $imagem]);
            
            $mensagem = 'Produto adicionado com sucesso!';
            $tipo_mensagem = 'success';
        }
        
        elseif ($acao === 'editar') {

    $id = $_POST['id'];

    $nome = $_POST['nome'];

    $descricao = $_POST['descricao'];

    $preco = str_replace('R$ ', '', $_POST['preco']);
    $preco = str_replace('.', '', $preco);
    $preco = str_replace(',', '.', $preco);

    $tipo = $_POST['tipo'];

    $disponivel = isset($_POST['disponivel']) ? 1 : 0;

    // Buscar imagem atual
    $stmt = $conn->prepare("SELECT imagem_referencia FROM produtos WHERE id = ?");
    $stmt->execute([$id]);

    $produtoAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    $imagem = $produtoAtual['imagem_referencia'];

    // Nova imagem
    if (!empty($_FILES['imagem']['name'])) {

        $pasta = '../../uploads/produtos/';

        if (!file_exists($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $nomeImagem = time() . '_' . $_FILES['imagem']['name'];

        $caminhoImagem = $pasta . $nomeImagem;

        move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem);

        $imagem = 'uploads/produtos/' . $nomeImagem;
    }

    $stmt = $conn->prepare("
        UPDATE produtos 
        SET nome = ?, descricao = ?, preco = ?, tipo = ?, disponivel = ?, imagem_referencia = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $nome,
        $descricao,
        $preco,
        $tipo,
        $disponivel,
        $imagem,
        $id
    ]);

    $mensagem = 'Produto atualizado com sucesso!';
    $tipo_mensagem = 'success';
}
        
        elseif ($acao === 'deletar') {
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$id]);
            
            $mensagem = 'Produto deletado com sucesso!';
            $tipo_mensagem = 'success';
        }
        
        elseif ($acao === 'alternar_disponibilidade') {
            $id = $_POST['id'];
            $stmt = $conn->prepare("UPDATE produtos SET disponivel = NOT disponivel WHERE id = ?");
            $stmt->execute([$id]);
            
            $mensagem = 'Status de disponibilidade alterado!';
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

// Buscar produto para edição (se houver)
$produto_edicao = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $conn = open_database();
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .badge-disponivel { background-color: #28a745; }
    .badge-indisponivel { background-color: #dc3545; }

    .bg-pink{
        background-color:#ff69b4;
        color:white;
    }

    .table-produtos { font-size: 14px; }

    .form-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
</style>
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

                            <input 
                                type="text"
                                name="preco"
                                id="preco"
                                class="form-control"
                                placeholder="R$ 0,00"
                                required

                                value="<?php 
                                    echo $produto_edicao 
                                        ? 'R$ ' . number_format($produto_edicao['preco'], 2, ',', '.') 
                                        : ''; 
                                ?>">
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

                            <input type="file"
                                name="imagem"
                                class="form-control"
                                accept="image/*">

                            <?php if ($produto_edicao && !empty($produto_edicao['imagem_referencia'])): ?>

                                <div class="mt-2">
                                    <img src="<?php echo BASEURL . $produto_edicao['imagem_referencia']; ?>"
                                        width="120"
                                        style="border-radius:10px;">
                                </div>

                            <?php endif; ?>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="disponivel" class="form-check-input" id="disponivel"
                                <?php echo (!$produto_edicao || $produto_edicao['disponivel']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="disponivel">
                                Disponível para venda
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> 
                            <?php echo $produto_edicao ? 'Atualizar' : 'Adicionar'; ?>
                        </button>
                        
                        <?php if ($produto_edicao): ?>
                            <a href="<?php echo BASEURL; ?>admin/produtos/" class="btn btn-secondary w-100">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
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
            // Cor do tipo
            $corTipo = 'bg-secondary';

            if($p['tipo'] == 'doce'){
                $corTipo = 'bg-pink';
            }

            elseif($p['tipo'] == 'salgado'){
                $corTipo = 'bg-warning text-dark';
            }

            elseif($p['tipo'] == 'bolo'){
                $corTipo = 'bg-danger';
            }

            elseif($p['tipo'] == 'personalizado'){
                $corTipo = 'bg-primary';
            }
        ?>

        <tr>

            <!-- IMAGEM -->
            <td>

    <?php if(!empty($p['imagem_referencia'])): ?>

        <img 
            src="<?php echo BASEURL . $p['imagem_referencia']; ?>"
            width="70"
            height="70"

            data-bs-toggle="modal"
            data-bs-target="#modalImagem<?php echo $p['id']; ?>"

            style="
                object-fit: cover;
                border-radius: 12px;
                border: 2px solid #eee;
                cursor:pointer;
                transition:0.3s;
            "

            onmouseover="this.style.transform='scale(1.08)'"
            onmouseout="this.style.transform='scale(1)'"
        >

        <!-- MODAL -->
        <div class="modal fade" id="modalImagem<?php echo $p['id']; ?>" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered modal-lg">

                <div class="modal-content" style="background:transparent;border:none;">

                    <div class="text-end mb-2">

                        <button 
                            type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">

                            <i class="fas fa-times"></i>

                        </button>

                    </div>

                    <img 
                        src="<?php echo BASEURL . $p['imagem_referencia']; ?>"
                        class="img-fluid rounded shadow"
                        style="
                            max-height:80vh;
                            object-fit:contain;
                        "
                    >

                </div>

            </div>

        </div>

    <?php else: ?>

        <div style="
            width:70px;
            height:70px;
            background:#f1f1f1;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#999;
        ">
            <i class="fas fa-image"></i>
        </div>

    <?php endif; ?>

</td>

            <!-- ID -->
            <td>
                <small>#<?php echo $p['id']; ?></small>
            </td>

            <!-- NOME -->
            <td>
                <strong>
                    <?php echo htmlspecialchars($p['nome']); ?>
                </strong>
            </td>

            <!-- TIPO -->
            <td>
                <span class="badge <?php echo $corTipo; ?> p-2">
                    <?php echo ucfirst($p['tipo']); ?>
                </span>
            </td>

            <!-- PREÇO -->
            <td>
                <strong>
                    R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?>
                </strong>
            </td>

            <!-- STATUS -->
            <td>
                <span class="badge <?php echo $p['disponivel'] ? 'badge-disponivel' : 'badge-indisponivel'; ?>">
                    <?php echo $p['disponivel'] ? 'Disponível' : 'Indisponível'; ?>
                </span>
            </td>

            <!-- AÇÕES -->
            <td>

                <a href="?editar=<?php echo $p['id']; ?>" 
                class="btn btn-sm btn-warning"
                title="Editar">

                    <i class="fas fa-edit"></i>

                </a>

                <form method="POST"
                    style="display:inline;"
                    onclick="return confirm('Tem certeza?')">

                    <input type="hidden" name="acao" value="alternar_disponibilidade">

                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                    <button type="submit"
                        class="btn btn-sm btn-outline-secondary">

                        <i class="fas fa-<?php echo $p['disponivel'] ? 'eye' : 'eye-slash'; ?>"></i>

                    </button>

                </form>

                <form method="POST"
                    style="display:inline;"
                    onclick="return confirm('Deletar este produto?')">

                    <input type="hidden" name="acao" value="deletar">

                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                    <button type="submit"
                        class="btn btn-sm btn-danger">

                        <i class="fas fa-trash"></i>

                    </button>

                </form>

            </td>

        </tr>

    <?php endforeach; ?>
</tbody>
                        </table>
                    </div>

                    <?php if (empty($produtos)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum produto cadastrado ainda.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>

   
<script>

const campoPreco = document.getElementById('preco');

campoPreco.addEventListener('input', function(e){

    let valor = e.target.value;

    // remove tudo que não for número
    valor = valor.replace(/\D/g, '');

    // transforma em centavos
    valor = (valor / 100).toFixed(2) + '';

    valor = valor.replace(".", ",");

    valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');

    e.target.value = 'R$ ' + valor;

});

</script>

</body>
</html>
