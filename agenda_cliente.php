<?php 
if (!isset($_SESSION)) session_start();

if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: index.php');
    exit;
}

include 'config.php';
require_once(DBAPI);

$usuario_id = $_SESSION['id'];
$conn = open_database();

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
if ($mes < 1 || $mes > 12) $mes = (int)date('m');
if ($ano < 2020 || $ano > 2035) $ano = (int)date('Y');

$mesFormatado = str_pad($mes, 2, '0', STR_PAD_LEFT);
$data_inicio  = "$ano-$mesFormatado-01";
$data_fim     = date('Y-m-t', strtotime($data_inicio));

// Buscar pedidos do cliente no mês pela data de ENTREGA
$stmt = $conn->prepare("
    SELECT id, status, tipo, tipo_entrega, valor_total,
           data_entrega, hora_entrega, observacao
    FROM pedidos
    WHERE id_cliente = ?
      AND DATE(data_entrega) BETWEEN ? AND ?
    ORDER BY data_entrega ASC
");
$stmt->execute([$usuario_id, $data_inicio, $data_fim]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por data para o calendário
$pedidos_por_data = [];
foreach ($pedidos as $p) {
    $data = date('Y-m-d', strtotime($p['data_entrega']));
    $pedidos_por_data[$data][] = $p;
}

// Detalhes de um pedido específico
$pedido_detalhes = null;
if (isset($_GET['ver'])) {
    $id = (int)$_GET['ver'];
    $stmt = $conn->prepare("
        SELECT p.*, u.nome, u.telefone, u.endereco
        FROM pedidos p
        INNER JOIN usuarios u ON p.id_cliente = u.id
        WHERE p.id = ? AND p.id_cliente = ?
    ");
    $stmt->execute([$id, $usuario_id]);
    $pedido_detalhes = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pedido_detalhes) {
        $stmt = $conn->prepare("
            SELECT ip.qtd, ip.observacao, pr.nome AS produto_nome
            FROM itens_pedido ip
            INNER JOIN produtos pr ON ip.id_produto = pr.id
            WHERE ip.id_pedido = ?
        ");
        $stmt->execute([$id]);
        $pedido_detalhes['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

close_database($conn);

$meses_nomes = [
    1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
    7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Pedacinho de Amor</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cal-grid { display: grid; grid-template-columns: repeat(7,1fr); gap: 4px; }
        .cal-header { background: #FBB6CE; border-radius: 10px; text-align: center;
                      font-weight: 700; font-size: 13px; padding: 8px 0; color: #4A332D; }
        .cal-cell { min-height: 60px; border-radius: 8px; border: 1px solid #f0e9e9;
                    padding: 4px 6px; font-size: 13px; }
        .cal-cell.hoje { border: 2px solid #FBB6CE; }
        .cal-cell.outro-mes { background: #fafafa; }
        .cal-num { font-weight: 600; color: #61463B; }
        .cal-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%;
                   margin: 1px; }
        .dot-retirada { background: #DD6B20; }
        .dot-entrega  { background: #D53F8C; }
        .badge-status-pendente    { background:#FFF4E6; color:#DD6B20; }
        .badge-status-confirmado  { background:#EAF3DE; color:#3B6D11; }
        .badge-status-em_preparacao { background:#E6F1FB; color:#185FA5; }
        .badge-status-pronto      { background:#d1fae5; color:#065f46; }
        .badge-status-entregue    { background:#EAF3DE; color:#3B6D11; }
        .badge-status-cancelado   { background:#FCEBEB; color:#A32D2D; }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>

    <div class="container mt-5 mb-5">
        <h2 class="mb-4"><i class="fas fa-calendar-alt me-2" style="color:#FBB6CE"></i> Meus Agendamentos</h2>

        <?php if ($pedido_detalhes): ?>

            <!-- DETALHES DO PEDIDO -->
            <div class="card shadow-sm mb-4" style="border-radius:16px; border:none;">
                <div class="card-header" style="background:#FBB6CE; border-radius:16px 16px 0 0;">
                    <h5 class="mb-0" style="color:#4A332D;">Pedido #<?php echo $pedido_detalhes['id']; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Data de entrega/retirada:</strong><br>
                                <?php echo date('d/m/Y', strtotime($pedido_detalhes['data_entrega'])); ?>
                                às <?php echo date('H:i', strtotime($pedido_detalhes['hora_entrega'])); ?>
                            </p>
                            <p class="mb-1"><strong>Tipo:</strong>
                                <?php echo ucfirst($pedido_detalhes['tipo_entrega']); ?>
                            </p>
                            <?php if ($pedido_detalhes['tipo_entrega'] === 'entrega'): ?>
                                <p class="mb-1"><strong>Endereço:</strong> <?php echo htmlspecialchars($pedido_detalhes['endereco']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Status:</strong>
                                <span class="badge badge-status-<?php echo $pedido_detalhes['status']; ?>" style="padding:4px 10px; border-radius:8px; font-size:13px;">
                                    <?php echo ucfirst(str_replace('_',' ',$pedido_detalhes['status'])); ?>
                                </span>
                            </p>
                            <p class="mb-1"><strong>Total:</strong> R$ <?php echo number_format($pedido_detalhes['valor_total'],2,',','.'); ?></p>
                            <?php if (!empty($pedido_detalhes['observacao'])): ?>
                                <p class="mb-1"><strong>Obs:</strong> <?php echo htmlspecialchars($pedido_detalhes['observacao']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($pedido_detalhes['itens'])): ?>
                        <h6 class="mt-3">Itens</h6>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($pedido_detalhes['itens'] as $item): ?>
                                <li class="list-group-item px-0">
                                    <strong><?php echo $item['qtd']; ?>x <?php echo htmlspecialchars($item['produto_nome']); ?></strong>
                                    <?php if (!empty($item['observacao'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($item['observacao']); ?></small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <a href="agenda_cliente.php?mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>" class="btn btn-secondary mt-3">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

        <?php else: ?>

            <!-- CALENDÁRIO -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm p-4 mb-4" style="border-radius:16px; border:none;">
                        <!-- Navegação -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="?mes=<?php echo $mes==1?12:$mes-1; ?>&ano=<?php echo $mes==1?$ano-1:$ano; ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <strong><?php echo $meses_nomes[$mes].' de '.$ano; ?></strong>
                            <a href="?mes=<?php echo $mes==12?1:$mes+1; ?>&ano=<?php echo $mes==12?$ano+1:$ano; ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>

                        <!-- Cabeçalho dias -->
                        <div class="cal-grid mb-2">
                            <?php foreach (['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $d): ?>
                                <div class="cal-header"><?php echo $d; ?></div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Dias do mês -->
                        <div class="cal-grid">
                            <?php
                            $primeiroDia   = strtotime("$ano-$mesFormatado-01");
                            $inicioSemana  = (int)date('w', $primeiroDia);
                            $totalDias     = (int)date('t', $primeiroDia);
                            $hoje          = date('Y-m-d');

                            for ($i = 0; $i < $inicioSemana; $i++) echo '<div class="cal-cell outro-mes"></div>';

                            for ($dia = 1; $dia <= $totalDias; $dia++):
                                $dataLoop = "$ano-$mesFormatado-" . str_pad($dia,2,'0',STR_PAD_LEFT);
                                $eHoje = $dataLoop === $hoje ? 'hoje' : '';
                            ?>
                                <div class="cal-cell <?php echo $eHoje; ?>">
                                    <div class="cal-num"><?php echo $dia; ?></div>
                                    <?php if (!empty($pedidos_por_data[$dataLoop])): ?>
                                        <?php foreach ($pedidos_por_data[$dataLoop] as $p): ?>
                                            <a href="?ver=<?php echo $p['id']; ?>&mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>" style="text-decoration:none;">
                                                <span class="cal-dot <?php echo $p['tipo_entrega']==='entrega' ? 'dot-entrega' : 'dot-retirada'; ?>"
                                                      title="Pedido #<?php echo $p['id']; ?> — <?php echo ucfirst($p['tipo_entrega']); ?>"></span>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <!-- Legenda -->
                        <div class="d-flex gap-3 mt-3" style="font-size:12px; color:#8A736E;">
                            <span><span class="cal-dot dot-entrega"></span> Entrega</span>
                            <span><span class="cal-dot dot-retirada"></span> Retirada</span>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <div class="col-md-4">
                    <div class="card shadow-sm p-3" style="border-radius:16px; border:none;">
                        <h6 class="mb-3"><i class="fas fa-list me-1" style="color:#FBB6CE"></i> Pedidos do mês</h6>

                        <?php if (!empty($pedidos)): ?>
                            <?php foreach ($pedidos as $p): ?>
                                <a href="?ver=<?php echo $p['id']; ?>&mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>" style="text-decoration:none; color:inherit;">
                                    <div class="mb-2 p-2" style="border-radius:10px; border:1px solid #f0e9e9;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong style="font-size:13px;">Pedido #<?php echo $p['id']; ?></strong>
                                            <span class="badge badge-status-<?php echo $p['status']; ?>" style="padding:3px 8px; border-radius:6px; font-size:11px;">
                                                <?php echo ucfirst(str_replace('_',' ',$p['status'])); ?>
                                            </span>
                                        </div>
                                        <div style="font-size:12px; color:#8A736E; margin-top:2px;">
                                            <i class="fas fa-calendar-day"></i>
                                            <?php echo date('d/m', strtotime($p['data_entrega'])); ?>
                                            às <?php echo date('H:i', strtotime($p['hora_entrega'])); ?>
                                            · <?php echo ucfirst($p['tipo_entrega']); ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small text-center py-3">Nenhum pedido neste mês.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php include 'inc/footer.php'; ?>
    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>