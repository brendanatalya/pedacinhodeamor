<?php 
if (!isset($_SESSION)) session_start();

// Verificar se usuário está logado como cliente
if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: ' . dirname(__DIR__) . '/index.php');
    exit;
}

include 'config.php';
require_once(DBAPI);

$usuario_id = $_SESSION['id'];
$conn = open_database();

// Buscar mes/ano para exibição
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

// Validar mes e ano
if ($mes < 1 || $mes > 12) $mes = date('m');
if ($ano < 2020 || $ano > 2030) $ano = date('Y');

// Buscar agendamentos do cliente do mês
$data_inicio = "$ano-$mes-01";
$data_fim = date('Y-m-t', strtotime($data_inicio));

$stmt = $conn->prepare("
    SELECT 
        a.*,
        p.id as pedido_id,
        p.total
    FROM agendamentos a
    INNER JOIN pedidos p ON a.pedido_id = p.id
    WHERE a.usuario_id = ? AND a.data_agendada BETWEEN ? AND ?
    ORDER BY a.data_agendada ASC
");
$stmt->execute([$usuario_id, $data_inicio, $data_fim]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar agendamentos por data
$agendamentos_por_data = [];
foreach ($agendamentos as $agendamento) {
    $data = $agendamento['data_agendada'];
    if (!isset($agendamentos_por_data[$data])) {
        $agendamentos_por_data[$data] = [];
    }
    $agendamentos_por_data[$data][] = $agendamento;
}

// Buscar detalhes do agendamento se solicitado
$agendamento_detalhes = null;
if (isset($_GET['ver'])) {
    $agend_id = $_GET['ver'];
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            p.id as pedido_id,
            p.total,
            p.observacoes
        FROM agendamentos a
        INNER JOIN pedidos p ON a.pedido_id = p.id
        WHERE a.id = ? AND a.usuario_id = ?
    ");
    $stmt->execute([$agend_id, $usuario_id]);
    $agendamento_detalhes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($agendamento_detalhes) {
        $stmt = $conn->prepare("
            SELECT * FROM itens_pedido 
            WHERE pedido_id = ?
        ");
        $stmt->execute([$agendamento_detalhes['pedido_id']]);
        $agendamento_detalhes['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

close_database($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Agenda - Pedacinho de Amor</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>
    <?php include 'inc/header.php'; ?>

    <div class="container mt-5 mb-5">
        
        <h2 class="mb-4">
            <i class="fas fa-calendar"></i> Minha Agenda de Entregas/Retiradas
        </h2>

        <?php if ($agendamento_detalhes): ?>
            <!-- DETALHES DO AGENDAMENTO -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #f5c2d6;">
                    <h5 class="mb-0">Detalhes do Agendamento</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações do Agendamento</h6>
                            <p>
                                <strong>Data Agendada:</strong> <?php echo date('d/m/Y', strtotime($agendamento_detalhes['data_agendada'])); ?><br>
                                <strong>Tipo:</strong> 
                                <span class="badge <?php echo $agendamento_detalhes['tipo'] === 'entrega' ? 'badge-entrega' : 'badge-retirada'; ?>">
                                    <?php echo ucfirst($agendamento_detalhes['tipo']); ?>
                                </span><br>
                                <strong>Status:</strong> 
                                <span class="badge bg-info"><?php echo ucfirst($agendamento_detalhes['status']); ?></span><br>
                                
                                <?php if ($agendamento_detalhes['tipo'] === 'retirada'): ?>
                                    <strong>Hora da Retirada:</strong> 
                                    <?php echo $agendamento_detalhes['hora_retirada'] ? date('H:i', strtotime($agendamento_detalhes['hora_retirada'])) : 'A confirmar'; ?><br>
                                <?php else: ?>
                                    <strong>Hora da Entrega:</strong> 
                                    <?php echo $agendamento_detalhes['hora_entrega'] ? date('H:i', strtotime($agendamento_detalhes['hora_entrega'])) : 'A confirmar'; ?><br>
                                    <strong>Local:</strong> 
                                    <?php echo htmlspecialchars($agendamento_detalhes['localizacao'] ?? 'Seu endereço cadastrado'); ?><br>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Informações do Pedido</h6>
                            <p>
                                <strong>ID do Pedido:</strong> #<?php echo $agendamento_detalhes['pedido_id']; ?><br>
                                <strong>Total:</strong> <span class="badge bg-success">R$ <?php echo number_format($agendamento_detalhes['total'], 2, ',', '.'); ?></span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h6>Itens do Pedido</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agendamento_detalhes['itens'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['produto_id']); ?></td>
                                        <td><?php echo $item['quantidade']; ?></td>
                                        <td>
                                            <small>
                                                <?php if ($item['sabor_massa']): ?>
                                                    <br><strong>Massa:</strong> <?php echo htmlspecialchars($item['sabor_massa']); ?>
                                                <?php endif; ?>
                                                <?php if ($item['sabor_recheio']): ?>
                                                    <br><strong>Recheio:</strong> <?php echo htmlspecialchars($item['sabor_recheio']); ?>
                                                <?php endif; ?>
                                                <?php if ($item['topping']): ?>
                                                    <br><strong>Topping:</strong> <?php echo htmlspecialchars($item['topping']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($agendamento_detalhes['observacoes']): ?>
                        <div class="alert alert-info">
                            <strong>Observações:</strong> <?php echo htmlspecialchars($agendamento_detalhes['observacoes']); ?>
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo BASEURL; ?>agenda_cliente.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- CALENDÁRIO -->
            <div class="row">
                <div class="col-md-9">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <div>
                                <h5><?php echo strftime('%B de %Y', strtotime("$ano-$mes-01")); ?></h5>
                            </div>
                            <div class="calendar-nav">
                                <a href="?mes=<?php echo $mes == 1 ? 12 : $mes - 1; ?>&ano=<?php echo $mes == 1 ? $ano - 1 : $ano; ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-chevron-left"></i> Anterior
                                </a>
                                <a href="?mes=<?php echo date('m'); ?>&ano=<?php echo date('Y'); ?>" class="btn btn-sm btn-outline-secondary">
                                    Hoje
                                </a>
                                <a href="?mes=<?php echo $mes == 12 ? 1 : $mes + 1; ?>&ano=<?php echo $mes == 12 ? $ano + 1 : $ano; ?>" class="btn btn-sm btn-secondary">
                                    Próximo <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="calendar-grid">
                            <!-- Cabeçalho dos dias da semana -->
                            <?php 
                            $dias_semana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                            foreach ($dias_semana as $dia): 
                            ?>
                                <div class="calendar-cell calendar-cell-header"><?php echo substr($dia, 0, 3); ?></div>
                            <?php endforeach; ?>

                            <!-- Dias do mês -->
                            <?php
                            $primeiro_dia = strtotime("$ano-$mes-01");
                            $ultimo_dia = strtotime(date('Y-m-t', $primeiro_dia));
                            $dia_semana_inicio = date('w', $primeiro_dia);
                            $num_dias = date('d', $ultimo_dia);

                            // Preencher dias do mês anterior
                            for ($i = 0; $i < $dia_semana_inicio; $i++) {
                                echo '<div class="calendar-cell other-month"></div>';
                            }

                            // Preencher dias do mês
                            for ($dia = 1; $dia <= $num_dias; $dia++) {
                                $data_atual = "$ano-$mes-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
                                $class = date('Y-m-d') === $data_atual ? 'today' : '';
                                echo '<div class="calendar-cell ' . $class . '">';
                                echo '<div class="calendar-cell-date">' . $dia . '</div>';
                                
                                // Mostrar agendamentos do dia
                                if (isset($agendamentos_por_data[$data_atual])) {
                                    foreach ($agendamentos_por_data[$data_atual] as $agend) {
                                        $classe_agend = $agend['tipo'] === 'entrega' ? 'agendamento-entrega' : '';
                                        echo '<a href="?ver=' . $agend['id'] . '" class="agendamento-item ' . $classe_agend . '" style="text-decoration: none; color: inherit;">';
                                        echo '<i class="fas fa-' . ($agend['tipo'] === 'entrega' ? 'truck' : 'shopping-bag') . '"></i> ';
                                        echo ucfirst($agend['tipo']);
                                        echo '</a>';
                                    }
                                }
                                echo '</div>';
                            }

                            // Preencher dias do próximo mês
                            $dias_restantes = (7 - (($dia_semana_inicio + $num_dias) % 7)) % 7;
                            for ($i = 0; $i < $dias_restantes; $i++) {
                                echo '<div class="calendar-cell other-month"></div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <div class="col-md-3">
                    <div class="sidebar">
                        <h6><i class="fas fa-info-circle"></i> Resumo</h6>
                        
                        <div class="mb-3">
                            <p>
                                <strong>Total de Agendamentos:</strong><br>
                                <?php echo count($agendamentos); ?>
                            </p>
                        </div>

                        <h6 class="mt-4"><i class="fas fa-tasks"></i> Por Tipo</h6>
                        <p>
                            <span class="badge badge-retirada">
                                Retiradas: <?php echo count(array_filter($agendamentos, function($a) { return $a['tipo'] === 'retirada'; })); ?>
                            </span>
                        </p>
                        <p>
                            <span class="badge badge-entrega">
                                Entregas: <?php echo count(array_filter($agendamentos, function($a) { return $a['tipo'] === 'entrega'; })); ?>
                            </span>
                        </p>

                        <h6 class="mt-4"><i class="fas fa-clock"></i> Próximos Agendamentos</h6>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php 
                            $agendamentos_proximos = array_slice($agendamentos, 0, 5);
                            if ($agendamentos_proximos):
                                foreach ($agendamentos_proximos as $agend): 
                            ?>
                                <a href="?ver=<?php echo $agend['id']; ?>" style="text-decoration: none; color: inherit;">
                                    <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 8px;">
                                        <small>
                                            <strong><?php echo ucfirst($agend['tipo']); ?></strong><br>
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($agend['data_agendada'])); ?><br>
                                            <i class="fas fa-clock"></i> 
                                            <?php 
                                            if ($agend['tipo'] === 'entrega') {
                                                echo $agend['hora_entrega'] ? date('H:i', strtotime($agend['hora_entrega'])) : 'Sem horário';
                                            } else {
                                                echo $agend['hora_retirada'] ? date('H:i', strtotime($agend['hora_retirada'])) : 'Sem horário';
                                            }
                                            ?>
                                            <br>
                                            <span class="badge <?php echo $agend['tipo'] === 'entrega' ? 'badge-entrega' : 'badge-retirada'; ?>">
                                                <?php echo ucfirst($agend['status']); ?>
                                            </span>
                                        </small>
                                    </div>
                                </a>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <div class="alert alert-info" style="font-size: 12px;">
                                    Você ainda não tem agendamentos.
                                </div>
                            <?php 
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php include 'inc/footer.php'; ?>
    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
