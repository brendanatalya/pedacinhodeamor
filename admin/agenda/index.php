<?php 
if (!isset($_SESSION)) session_start();

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ' . dirname(dirname(dirname(__DIR__))) . '/index.php');
    exit;
}

include '../../config.php';
require_once(DBAPI);

$conn = open_database();

// Buscar mes/ano para exibição
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

// Validar mes e ano
if ($mes < 1 || $mes > 12) $mes = date('m');
if ($ano < 2020 || $ano > 2030) $ano = date('Y');

// Buscar agendamentos do mês
$data_inicio = "$ano-$mes-01";
$data_fim = date('Y-m-t', strtotime($data_inicio));

$stmt = $conn->prepare("
    SELECT 
        a.*,
        u.nome,
        u.telefone,
        p.id as pedido_id,
        p.total
    FROM agendamentos a
    INNER JOIN usuarios u ON a.usuario_id = u.id
    INNER JOIN pedidos p ON a.pedido_id = p.id
    WHERE a.data_agendada BETWEEN ? AND ?
    ORDER BY a.data_agendada ASC
");
$stmt->execute([$data_inicio, $data_fim]);
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

close_database($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Admin - Pedacinho de Amor</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .calendar-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .calendar-nav {
            display: flex;
            gap: 10px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .calendar-cell {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            min-height: 100px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .calendar-cell:hover {
            background: #e9ecef;
        }
        .calendar-cell.other-month {
            opacity: 0.5;
        }
        .calendar-cell.today {
            background: #d4f1d4;
            border-color: #28a745;
        }
        .calendar-cell-header {
            font-weight: bold;
            text-align: center;
            background: #8b6f47;
            color: white;
            border: none !important;
            padding: 8px;
            margin-bottom: 5px;
        }
        .calendar-cell-date {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            padding: 5px 0;
        }
        .agendamento-item {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            padding: 5px;
            margin: 2px 0;
            font-size: 11px;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .agendamento-item:hover {
            background: #ffe69c;
        }
        .agendamento-retirada {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .agendamento-entrega {
            border-left-color: #007bff;
            background: #cfe2ff;
        }
        .sidebar {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .sidebar h6 {
            border-bottom: 2px solid #8b6f47;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .badge-retirada { background-color: #28a745; }
        .badge-entrega { background-color: #007bff; }
    </style>
</head>
<body style="background-color: #f8f9fa;">
    <div class="container-fluid p-4">
        
        <h4 class="mb-4">
            <i class="fas fa-calendar"></i> Agenda de Entregas e Retiradas
        </h4>

        <div class="row">
            <!-- CALENDÁRIO -->
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
                        $data_anterior = strtotime("$ano-$mes-01") - (86400 * $dia_semana_inicio);
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
                                    echo '<div class="agendamento-item ' . $classe_agend . '" title="' . htmlspecialchars($agend['nome']) . '">';
                                    echo htmlspecialchars(substr($agend['nome'], 0, 15)) . '...';
                                    echo '</div>';
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

            <!-- SIDEBAR COM RESUMO -->
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
                        foreach ($agendamentos_proximos as $agend): 
                        ?>
                            <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 8px;">
                                <small>
                                    <strong><?php echo htmlspecialchars($agend['nome']); ?></strong><br>
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
                                        <?php echo ucfirst($agend['tipo']); ?>
                                    </span>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
