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

// Helper: pega a primeira letra do nome para o "avatar"
function iniciais($nome) {
    return strtoupper(mb_substr(trim($nome), 0, 1));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes - Admin</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/clientes-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid p-4">

        <div class="gc-toolbar">
            <h4><i class="fas fa-users"></i> Gerenciar Clientes</h4>
            <span class="gc-total"><?php echo count($clientes); ?> cadastrados</span>
        </div>

        <?php if ($cliente_detalhes): ?>
            <!-- DETALHES DO CLIENTE -->
            <?php
                $total_pedidos_cliente = $cliente_detalhes['pedidos'] ? count($cliente_detalhes['pedidos']) : 0;
                $total_gasto_cliente = $cliente_detalhes['pedidos'] ? array_sum(array_column($cliente_detalhes['pedidos'], 'valor_total')) : 0;
            ?>
            <div class="gc-detalhes">
                <div class="gc-detalhes-head">
                    <div class="gc-detalhes-avatar"><?php echo iniciais($cliente_detalhes['nome']); ?></div>
                    <div>
                        <h5><?php echo htmlspecialchars($cliente_detalhes['nome']); ?></h5>
                        <small>Cliente</small>
                    </div>
                </div>

                <div class="gc-detalhes-body">
                    <div class="gc-detalhes-grid">
                        <div>
                            <h6 class="gc-subtitulo">Informações Pessoais</h6>
                            <div class="gc-info-list">
                                <div class="gc-info-row">
                                    <i class="fas fa-envelope"></i>
                                    <span><strong>Email:</strong> <?php echo htmlspecialchars($cliente_detalhes['email']); ?></span>
                                </div>
                                <div class="gc-info-row">
                                    <i class="fas fa-phone"></i>
                                    <span><strong>Telefone:</strong> <?php echo htmlspecialchars($cliente_detalhes['telefone'] ?? 'Não cadastrado'); ?></span>
                                </div>
                                <div class="gc-info-row">
                                    <i class="fas fa-id-card"></i>
                                    <span><strong>CPF:</strong> <?php echo htmlspecialchars($cliente_detalhes['cpf']); ?></span>
                                </div>
                                <div class="gc-info-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Endereço:</strong> <?php echo htmlspecialchars($cliente_detalhes['endereco']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h6 class="gc-subtitulo">Estatísticas</h6>
                            <div class="gc-stats-grid">
                                <div class="gc-stat-box">
                                    <div class="number"><?php echo $total_pedidos_cliente; ?></div>
                                    <small>Pedidos Realizados</small>
                                </div>
                                <div class="gc-stat-box gasto">
                                    <div class="number">R$ <?php echo number_format($total_gasto_cliente, 2, ',', '.'); ?></div>
                                    <small>Total Gasto</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="gc-hr">

                    <h6 class="gc-subtitulo">Histórico de Pedidos</h6>
                    <?php if ($cliente_detalhes['pedidos']): ?>
                        <div class="table-responsive">
                            <table class="gc-pedidos-table">
                                <thead>
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
                                            <td data-label="ID">#<?php echo $pedido['id']; ?></td>
                                            <td data-label="Data"><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                            <td data-label="Total">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                                            <td data-label="Status">
                                                <span class="badge badge-<?php echo $pedido['status']; ?>"><?php echo ucfirst($pedido['status']); ?></span>
                                            </td>
                                            <td data-label="Entrega"><?php echo date('d/m/Y', strtotime($pedido['data_entrega'])); ?></td>
                                            <td data-label="Ações">
                                                <a href="<?php echo BASEURL; ?>admin/pedidos/?ver=<?php echo $pedido['id']; ?>" class="gc-btn-abrir" target="_blank" title="Ver pedido">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="gc-vazio">
                            <i class="fas fa-receipt"></i>
                            Este cliente ainda não realizou pedidos.
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo BASEURL; ?>admin/clientes/" class="gc-btn-voltar">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- LISTA DE CLIENTES -->
            <?php if ($clientes): ?>
                <div class="gc-grid">
                    <?php foreach ($clientes as $cliente): ?>
                        <div class="cliente-card">
                            <div class="cliente-card-head">
                                <div class="cliente-avatar"><?php echo iniciais($cliente['nome']); ?></div>
                                <h6><?php echo htmlspecialchars($cliente['nome']); ?></h6>
                            </div>

                            <div class="cliente-card-info">
                                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($cliente['email']); ?></span>
                                <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($cliente['telefone'] ?? 'Não cadastrado'); ?></span>
                                <span><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($cliente['cpf']); ?></span>
                            </div>

                            <div class="cliente-card-stats">
                                <div class="cliente-stat-pill">
                                    <span class="n"><?php echo $cliente['total_pedidos']; ?></span>
                                    <span class="l">Pedidos</span>
                                </div>
                                <div class="cliente-stat-pill gasto">
                                    <span class="n">R$ <?php echo number_format($cliente['total_gasto'] ?? 0, 2, ',', '.'); ?></span>
                                    <span class="l">Gasto</span>
                                </div>
                            </div>

                            <a href="?ver=<?php echo $cliente['id']; ?>" class="gc-btn-ver">
                                <i class="fas fa-eye"></i> Ver Detalhes
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="gc-vazio">
                    <i class="fas fa-users"></i>
                    Nenhum cliente cadastrado ainda.
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>