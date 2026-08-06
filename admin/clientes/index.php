<?php 
if (!isset($_SESSION)) session_start();

include dirname(__DIR__, 2) . '/config.php';

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

require_once(DBAPI);

$conn = open_database();

// Buscar todos os clientes
$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.nome,
        u.email,
        u.cpf,
        u.telefone,
        u.endereco,
        COUNT(p.id) as total_pedidos,
        SUM(p.valor_total) as total_gasto
    FROM usuarios u
    LEFT JOIN pedidos p ON u.id = p.id_cliente
    WHERE u.tipo = 'cliente'
    GROUP BY u.id
    ORDER BY u.id DESC
");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar detalhes do cliente se solicitado
$cliente_detalhes = null;
if (isset($_GET['ver'])) {
    $id = (int)$_GET['ver'];
    $stmt = $conn->prepare("
        SELECT * FROM usuarios WHERE id = ? AND tipo = 'cliente'
    ");
    $stmt->execute([$id]);
    $cliente_detalhes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente_detalhes) {
        $stmt = $conn->prepare("
            SELECT * FROM pedidos 
            WHERE id_cliente = ? 
            ORDER BY data_pedido DESC
        ");
        $stmt->execute([$id]);
        $cliente_detalhes['pedidos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

close_database($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes - Admin</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body style="background-color: #f8f9fa;">
    <div class="container-fluid p-4">
        
        <h4 class="mb-4">
            <i class="fas fa-users"></i> Gerenciar Clientes (<?php echo count($clientes); ?>)
        </h4>

        <?php if ($cliente_detalhes): ?>
            <!-- DETALHES DO CLIENTE -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detalhes do Cliente: <?php echo htmlspecialchars($cliente_detalhes['nome']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações Pessoais</h6>
                            <p>
                                <strong>Nome:</strong> <?php echo htmlspecialchars($cliente_detalhes['nome']); ?><br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($cliente_detalhes['email']); ?><br>
                                <strong>Telefone:</strong> <?php echo htmlspecialchars($cliente_detalhes['telefone']); ?><br>
                                <strong>CPF:</strong> <?php echo htmlspecialchars($cliente_detalhes['cpf']); ?><br>
                                <strong>Endereço:</strong> <?php echo htmlspecialchars($cliente_detalhes['endereco']); ?><br>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Estatísticas</h6>
                            <div class="row">
                                <div class="col-6">
                                    <div style="background: #f0f0f0; padding: 15px; border-radius: 4px; text-align: center;">
                                        <div style="font-size: 24px; font-weight: bold; color: #8b6f47;">
                                            <?php echo $cliente_detalhes['pedidos'] ? count($cliente_detalhes['pedidos']) : 0; ?>
                                        </div>
                                        <small>Pedidos Realizados</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="background: #f0f0f0; padding: 15px; border-radius: 4px; text-align: center;">
                                        <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                            R$ <?php echo number_format($cliente_detalhes['pedidos'] ? array_sum(array_column($cliente_detalhes['pedidos'], 'valor_total')) : 0, 2, ',', '.'); ?>
                                        </div>
                                        <small>Total Gasto</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6>Histórico de Pedidos</h6>
                    <?php if ($cliente_detalhes['pedidos']): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Entrega</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cliente_detalhes['pedidos'] as $pedido): ?>
                                        <tr>
                                            <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                            <td>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo ucfirst($pedido['status']); ?></span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($pedido['data_entrega'])); ?></td>
                                            <td>
                                                <a href="<?php echo BASEURL; ?>admin/pedidos/?ver=<?php echo $pedido['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Este cliente ainda não realizou pedidos.
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo BASEURL; ?>admin/clientes/" class="btn btn-secondary mt-3">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- LISTA DE CLIENTES -->
            <div class="row">
                <?php foreach ($clientes as $cliente): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-cliente">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($cliente['nome']); ?>
                                </h6>
                                <small>
                                    <p class="mb-2">
                                        <strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?><br>
                                        <strong>Telefone:</strong> <?php echo htmlspecialchars($cliente['telefone']); ?><br>
                                        <strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Pedidos:</strong> 
                                        <span class="badge bg-primary"><?php echo $cliente['total_pedidos']; ?></span><br>
                                        <strong>Total Gasto:</strong> 
                                        <span class="badge bg-success">
                                            R$ <?php echo number_format($cliente['total_gasto'] ?? 0, 2, ',', '.'); ?>
                                        </span>
                                    </p>
                                </small>
                                <a href="?ver=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($clientes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhum cliente cadastrado ainda.
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>