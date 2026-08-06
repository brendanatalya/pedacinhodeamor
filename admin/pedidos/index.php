<?php 
if (!isset($_SESSION)) session_start();

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
header('Location: ' . BASEURL . 'index.php');    exit;
}

include dirname(__DIR__, 2) . '/config.php';
require_once(DBAPI);

$mensagem = '';
$tipo_mensagem = '';

// Processar mudança de status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = open_database();
        $pedido_id = $_POST['pedido_id'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        $stmt->execute([$status, $pedido_id]);
        
        close_database($conn);
        
        $mensagem = 'Status atualizado com sucesso!';
        $tipo_mensagem = 'success';
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

// Buscar todos os pedidos
$conn = open_database();
$stmt = $conn->prepare("
    SELECT 
        p.id,
        p.status,
        p.data_pedido,
        p.data_entrega, 
        p.tipo_entrega,
        p.valor_total,
        u.nome,
        u.email,
        u.telefone
    FROM pedidos p
    INNER JOIN usuarios u ON p.id_cliente = u.id
    ORDER BY p.data_pedido DESC
");
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
close_database($conn);

// Buscar detalhes do pedido se solicitado
$pedido_detalhes = null;
if (isset($_GET['ver'])) {
    $id = $_GET['ver'];
    $conn = open_database();
    
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            u.nome, u.email, u.telefone, u.endereco
        FROM pedidos p
        INNER JOIN usuarios u ON p.id_cliente = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $pedido_detalhes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pedido_detalhes) {
        $stmt = $conn->prepare("
            SELECT 
                ip.*,
                pr.nome as produto_nome,
                pr.tipo as produto_tipo
            FROM itens_pedido ip
            INNER JOIN produtos pr ON ip.id_produto = pr.id
            WHERE ip.id_pedido = ?
        ");
        $stmt->execute([$id]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pedido_detalhes['itens'] = $itens;
    }
    
    close_database($conn);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos - Admin</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>../css_pda/style_pda.css">  
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

        <?php if ($pedido_detalhes): ?>
            <!-- DETALHES DO PEDIDO -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detalhes do Pedido #<?php echo $pedido_detalhes['id']; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informações do Cliente</h6>
                            <p>
                                <strong>Nome:</strong> <?php echo htmlspecialchars($pedido_detalhes['nome']); ?><br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($pedido_detalhes['email']); ?><br>
                                <strong>Telefone:</strong> <?php echo htmlspecialchars($pedido_detalhes['telefone']); ?><br>
                                <strong>Endereço:</strong> <?php echo htmlspecialchars($pedido_detalhes['endereco']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Informações do Pedido</h6>
                            <p>
                                <strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido_detalhes['data_pedido'])); ?><br>
                                <strong>Data de Entrega:</strong> <?php echo date('d/m/Y', strtotime($pedido_detalhes['data_entrega'])); ?><br>
                                <strong>Tipo:</strong> <span class="badge bg-info"><?php echo ucfirst($pedido_detalhes['tipo_entrega']); ?></span><br>
                                <strong>Total:</strong> <span class="badge bg-success">R$ <?php echo number_format($pedido_detalhes['valor_total'], 2, ',', '.'); ?></span>
                            </p>
                        </div>
                    </div>

                    <h6>Itens do Pedido</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Produto</th>
                                    <th>Tipo</th>
                                    <th>Qtd</th>
                                    <th>Preço Unit.</th>
                                    <th>Subtotal</th>
                                    <th>Detalhes</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedido_detalhes['itens'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo ucfirst($item['produto_tipo']); ?></span></td>
                                        <td><?php echo $item['qtd']; ?></td>
                                        <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                        <td><strong>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></strong></td>
                                        <td>
                                            <?php if (!empty($item['observacao'])): ?>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($item['observacao']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $item['disponivel'] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $item['disponivel'] ? 'Disponível' : 'Indisponível'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Atualizar Status -->
                    <h6>Atualizar Status do Pedido</h6>
                    <form method="POST" class="row align-items-end">
                        <div class="col-md-6">
                            <input type="hidden" name="pedido_id" value="<?php echo $pedido_detalhes['id']; ?>">
                            <label class="form-label">Novo Status</label>
                            <select name="status" class="form-control" required>
                                <option value="pendente" <?php echo $pedido_detalhes['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="confirmado" <?php echo $pedido_detalhes['status'] === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                <option value="em_preparacao" <?php echo $pedido_detalhes['status'] === 'em_preparacao' ? 'selected' : ''; ?>>Em Preparação</option>
                                <option value="pronto" <?php echo $pedido_detalhes['status'] === 'pronto' ? 'selected' : ''; ?>>Pronto</option>
                                <option value="entregue" <?php echo $pedido_detalhes['status'] === 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                                <option value="cancelado" <?php echo $pedido_detalhes['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Status
                            </button>
                            <a href="<?php echo BASEURL; ?>admin/pedidos/" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </form>

                   
                </div>
            </div>

        <?php else: ?>
            <!-- LISTA DE PEDIDOS -->
            <h4 class="mb-4">
                <i class="fas fa-shopping-bag"></i> Gerenciar Pedidos (<?php echo count($pedidos); ?>)
            </h4>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Entrega</th>
                                <th>Tipo</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($pedido['nome']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pedido['data_entrega'])); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($pedido['tipo_entrega']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower(str_replace('_', '-', $pedido['status'])); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $pedido['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?ver=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-primary" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (empty($pedidos)): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> Nenhum pedido cadastrado ainda.
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
