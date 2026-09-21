<?php 
if (!isset($_SESSION)) session_start();

include dirname(__DIR__, 2) . '/config.php';

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

require_once(DBAPI);

// Únicos status aceitos pelo fluxo do pedido
const STATUS_VALIDOS = ['pendente', 'confirmado', 'em_preparacao', 'pronto', 'entregue', 'cancelado'];

// Rótulo e classe de badge de cada status (a classe já existe no style_pda.css)
const STATUS_LABELS = [
    'pendente'      => 'Pendente',
    'confirmado'    => 'Confirmado',
    'em_preparacao' => 'Em Preparação',
    'pronto'        => 'Pronto',
    'entregue'      => 'Entregue',
    'cancelado'     => 'Cancelado',
];
const STATUS_BADGE = [
    'pendente'      => 'badge-pendente',
    'confirmado'    => 'badge-confirmado',
    'em_preparacao' => 'badge-preparacao',
    'pronto'        => 'badge-pronto',
    'entregue'      => 'badge-entregue',
    'cancelado'     => 'badge-cancelado',
];

$mensagem = '';
$tipo_mensagem = '';

// Processar mudança de status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pedido_id = (int)$_POST['pedido_id'];
        $status = $_POST['status'] ?? '';

        if (!in_array($status, STATUS_VALIDOS, true)) {
            throw new Exception('Status inválido.');
        }

        $conn = open_database();

        $stmt = $conn->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        $stmt->execute([$status, $pedido_id]);
        
        close_database($conn);
        
        $mensagem = 'Status atualizado com sucesso!';
        $tipo_mensagem = 'sucesso';
    } catch (Exception $e) {
        error_log('admin/pedidos: ' . $e->getMessage());
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
    $id = (int)$_GET['ver'];
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
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/pedidos-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid p-4">
        
        <?php if ($mensagem): ?>
            <div class="gp-alert mc-alert alert-<?php echo $tipo_mensagem; ?>" role="alert">
                <span><?php echo htmlspecialchars($mensagem); ?></span>
                <button type="button" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($pedido_detalhes): ?>
            <!-- DETALHES DO PEDIDO -->
            <div class="gp-detalhes">
                <div class="gp-detalhes-head">
                    <div>
                        <h5>Pedido #<?php echo $pedido_detalhes['id']; ?></h5>
                        <small><?php echo htmlspecialchars($pedido_detalhes['nome']); ?></small>
                    </div>
                    <span class="badge <?php echo STATUS_BADGE[$pedido_detalhes['status']] ?? 'badge-pendente'; ?>">
                        <?php echo STATUS_LABELS[$pedido_detalhes['status']] ?? ucfirst($pedido_detalhes['status']); ?>
                    </span>
                </div>

                <div class="gp-detalhes-body">
                    <div class="gp-detalhes-grid">
                        <div>
                            <h6 class="gp-subtitulo">Informações do Cliente</h6>
                            <div class="gp-info-list">
                                <div class="gp-info-row">
                                    <i class="fas fa-user"></i>
                                    <span><strong>Nome:</strong> <?php echo htmlspecialchars($pedido_detalhes['nome']); ?></span>
                                </div>
                                <div class="gp-info-row">
                                    <i class="fas fa-envelope"></i>
                                    <span><strong>Email:</strong> <?php echo htmlspecialchars($pedido_detalhes['email']); ?></span>
                                </div>
                                <div class="gp-info-row">
                                    <i class="fas fa-phone"></i>
                                    <span><strong>Telefone:</strong> <?php echo htmlspecialchars($pedido_detalhes['telefone']); ?></span>
                                </div>
                                <div class="gp-info-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Endereço:</strong> <?php echo htmlspecialchars($pedido_detalhes['endereco']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h6 class="gp-subtitulo">Informações do Pedido</h6>
                            <div class="gp-info-list">
                                <div class="gp-info-row">
                                    <i class="fas fa-calendar"></i>
                                    <span><strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido_detalhes['data_pedido'])); ?></span>
                                </div>
                                <div class="gp-info-row">
                                    <i class="fas fa-truck"></i>
                                    <span><strong>Data de Entrega:</strong> <?php echo date('d/m/Y', strtotime($pedido_detalhes['data_entrega'])); ?></span>
                                </div>
                                <div class="gp-info-row">
                                    <i class="fas fa-box"></i>
                                    <span><strong>Tipo:</strong>
                                        <span class="badge badge-<?php echo $pedido_detalhes['tipo_entrega']; ?>">
                                            <?php echo ucfirst($pedido_detalhes['tipo_entrega']); ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="gp-info-row">
                                    <i class="fas fa-sack-dollar"></i>
                                    <span><strong>Total:</strong> R$ <?php echo number_format($pedido_detalhes['valor_total'], 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="gp-hr">

                    <h6 class="gp-subtitulo">Itens do Pedido</h6>
                    <div class="table-responsive mb-4">
                        <table class="table-custom w-100">
                            <thead>
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
                                        <td><span class="gp-tag"><?php echo ucfirst($item['produto_tipo']); ?></span></td>
                                        <td><?php echo $item['qtd']; ?></td>
                                        <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                        <td><strong>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></strong></td>
                                        <td>
                                            <?php if (!empty($item['observacao'])): ?>
                                                <small style="color:#8A736E;">
                                                    <?php echo htmlspecialchars($item['observacao']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $item['disponivel'] ? 'badge-disponivel' : 'badge-indisponivel'; ?>">
                                                <?php echo $item['disponivel'] ? 'Disponível' : 'Indisponível'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Atualizar Status -->
                    <h6 class="gp-subtitulo">Atualizar Status do Pedido</h6>
                    <form method="POST" class="gp-status-form">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido_detalhes['id']; ?>">
                        <div class="gp-campo">
                            <label class="form-label-custom">Novo Status</label>
                            <select name="status" class="form-control-custom" required>
                                <?php foreach (STATUS_LABELS as $valor => $label): ?>
                                    <option value="<?php echo $valor; ?>" <?php echo $pedido_detalhes['status'] === $valor ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="gp-acoes">
                            <button type="submit" class="gp-btn-salvar">
                                <i class="fas fa-save"></i> Atualizar Status
                            </button>
                            <a href="<?php echo BASEURL; ?>admin/pedidos/" class="contabotao contabotao-ghost">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- LISTA DE PEDIDOS -->
            <div class="gp-toolbar">
                <h4><i class="fas fa-shopping-bag"></i> Gerenciar Pedidos</h4>
                <span class="gp-total"><?php echo count($pedidos); ?> pedidos</span>
            </div>

            <?php if ($pedidos): ?>
                <div class="gp-table-card">
                    <table class="gp-table">
                        <thead>
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
                                    <td>#<?php echo $pedido['id']; ?></td>
                                    <td><?php echo htmlspecialchars($pedido['nome']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pedido['data_entrega'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $pedido['tipo_entrega']; ?>">
                                            <?php echo ucfirst($pedido['tipo_entrega']); ?>
                                        </span>
                                    </td>
                                    <td class="gp-valor">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge <?php echo STATUS_BADGE[$pedido['status']] ?? 'badge-pendente'; ?>">
                                            <?php echo STATUS_LABELS[$pedido['status']] ?? ucfirst(str_replace('_', ' ', $pedido['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?ver=<?php echo $pedido['id']; ?>" class="gp-btn-abrir" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="gp-vazio">
                    <i class="fas fa-shopping-bag"></i>
                    Nenhum pedido cadastrado ainda.
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>