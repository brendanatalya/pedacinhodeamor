<?php
if (!isset($_SESSION)) session_start();

// Segurança do Painel
if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

include dirname(__DIR__, 2) . '/config.php';
require_once(DBAPI);

$conn = open_database();

/*| FILTROS DE DATA*/
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

if (isset($_GET['dia'])) {
    $diaSelecionado = str_pad((int)$_GET['dia'], 2, '0', STR_PAD_LEFT);
    $dataFiltro = "$ano-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-$diaSelecionado";
} else {
    $dataFiltro = ($mes == date('m') && $ano == date('Y')) ? date('Y-m-d') : "$ano-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
}

$mesFormatado = str_pad($mes, 2, '0', STR_PAD_LEFT);

/*| 1. PEDIDOS DO MÊS (Para bolinhas do calendário e cards de resumo)*/
$stmtMes = $conn->prepare("
    SELECT p.*, u.nome as cliente_nome 
    FROM pedidos p
    INNER JOIN usuarios u ON p.id_cliente = u.id
    WHERE MONTH(p.data_entrega) = ? AND YEAR(p.data_entrega) = ?
    ORDER BY p.data_entrega ASC
");
$stmtMes->execute([$mes, $ano]);
$pedidosDoMes = $stmtMes->fetchAll(PDO::FETCH_ASSOC);

$eventosPorDia = [];

foreach ($pedidosDoMes as $p) {

    $d = (int)date('j', strtotime($p['data_entrega']));

    if (!isset($eventosPorDia[$d])) {
        $eventosPorDia[$d] = [
            'normal' => 0,
            'personalizado' => 0
        ];
    }

    $tipo = $p['tipo'] ?? 'normal';

    // evita erro caso venha vazio ou inválido
    if (!in_array($tipo, ['normal', 'personalizado'])) {
        $tipo = 'normal';
    }

    $eventosPorDia[$d][$tipo]++;
}

/*| 2. PEDIDOS DO DIA SELECIONADO (Coluna da Esquerda)*/
$stmtDia = $conn->prepare("
    SELECT p.*, u.nome, u.telefone, u.endereco 
    FROM pedidos p
    INNER JOIN usuarios u ON p.id_cliente = u.id
    WHERE DATE(p.data_entrega) = ?
    ORDER BY p.data_entrega ASC
");
$stmtDia->execute([$dataFiltro]);
$pedidosDoDia = $stmtDia->fetchAll(PDO::FETCH_ASSOC);

close_database($conn);

$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Agenda</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #FDF9F9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #5A433D;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR FIXA NA ESQUERDA */
        .sidebar {
            width: 240px;
            background-color: #61463B;
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            padding: 25px 0;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-item a {
            color: #E2D9D7;
            text-decoration: none;
            padding: 14px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .sidebar-item.active a, .sidebar-item a:hover {
            background-color: #503930;
            color: #FFF;
            border-left: 4px solid #FBB6CE;
        }
        .sidebar-logout { margin-top: auto; }
        .sidebar-logout a { color: #FBB6CE; }

        /* CONTEÚDO PRINCIPAL */
        .main-content {
            flex: 1;
            padding: 40px;
            max-width: 1400px;
        }

        .header-agenda {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header-titulo { font-size: 24px; font-weight: 800; display: flex; align-items: center; gap: 10px; color: #4A332D; }
        .header-titulo i { color: #FBB6CE; }

        .btn-nav-data {
            background: #FFF;
            border: 1px solid #F5EDED;
            padding: 8px 16px;
            border-radius: 10px;
            color: #61463B;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .btn-nav-data:hover { background: #FBB6CE; color: #FFF; }

        /* GRID PRINCIPAL: 2 COLUNAS LADO A LADO */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 30px;
            align-items: start; /* Impede que uma coluna force a outra a esticar verticalmente */
        }

        /* COLUNA DA DIREITA (Agrupa Calendário em cima e a Grid Inferior embaixo) */
        .coluna-direita-flex {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* NOVA MINI GRID: Coloca o Resumo e as Próximas Entregas lado a lado */
        .grid-inferior-lado-a-lado {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* CARDS GERAIS BRANCOS */
        .painel-card {
            background: #FFFFFF;
            border-radius: 24px;
            border: 1px solid #FAF2F2;
            box-shadow: 0 10px 30px rgba(97, 70, 59, 0.04);
            padding: 22px;
            height: auto; /* Garante tamanho dinâmico baseado no conteúdo */
        }

        /* PEDIDOS (ESQUERDA) */
        .titulo-card-pedidos {
            background: #FBB6CE;
            color: #4A332D;
            text-align: center;
            padding: 15px;
            border-radius: 18px;
            font-weight: 700;
            margin-bottom: 25px;
        }
        .titulo-card-pedidos span { font-size: 18px; display: block; margin-top: 2px; }
        
        .bloco-pedido-entrega {
            border-bottom: 1px dashed #F3EBEB;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .bloco-pedido-entrega:last-child { border: none; }
        
        .hora-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .txt-hora { font-size: 20px; font-weight: 800; color: #8C475E; }
        
        .badge-tipo {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-tipo.normal { background: #FFEAF1; color: #D53F8C; }
        .badge-tipo.personalizado { background: #FFF4E6; color: #DD6B20; }

        .id-pedido-txt { font-weight: 700; font-size: 15px; margin-bottom: 10px; color: #4A332D; }
        .itens-lista-produtos { list-style: none; padding-left: 0; margin-bottom: 15px; }
        .itens-lista-produtos li { font-size: 14px; font-weight: 600; color: #705853; margin-bottom: 5px; position: relative; padding-left: 15px;}
        .itens-lista-produtos li::before { content: "•"; position: absolute; left: 0; color: #CBD5E0; font-size: 18px; top: -3px; }
        .itens-lista-produtos .obs-item { display: block; font-size: 12px; color: #A0AEC0; font-weight: 400; margin-top: 1px; }

        .cliente-info-box p { font-size: 13px; margin-bottom: 4px; color: #8A736E; display: flex; align-items: center; gap: 8px; }
        .cliente-info-box p i { color: #FBB6CE; width: 14px; }
        .preco-total-txt { font-size: 15px; font-weight: 700; color: #D53F8C; margin-top: 10px; }

        /* CALENDÁRIO */
        .mes-ano-banner {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #4A332D;
            margin-bottom: 25px;
        }
        .grid-dias-nomes {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            color: #4A332D;
            background: #FBB6CE;
            padding: 10px 0;
            border-radius: 14px;
            margin-bottom: 15px;
        }
        .grid-dias-numeros {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            row-gap: 14px;
            text-align: center;
        }
        .btn-dia-calendario {
            width: 38px;
            height: 38px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border-radius: 50%;
            font-size: 14px;
            font-weight: 600;
            color: #4A332D;
            text-decoration: none;
            position: relative;
            transition: background 0.2s;
        }
        .btn-dia-calendario:hover { background: #FFF0F5; }
        .dia-selecionado { background: #FBB6CE !important; font-weight: 700; }
        
        .dot-status { width: 5px; height: 5px; border-radius: 50%; position: absolute; bottom: 2px; }
        .dot-status.pink { background-color: #D53F8C; }
        .dot-status.orange { background-color: #DD6B20; }
        .dot-status.purple { background-color: #805AD5; }

        .legendas-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 25px;
            font-size: 12px;
            font-weight: 600;
        }
        .legenda-item { display: flex; align-items: center; gap: 6px; }
        .legenda-item .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

        /* BLOCOS DE CONTEÚDO INFERIOR */
        .subtitulo-lateral { font-size: 14px; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; color: #4A332D;}
        
        .box-info-qtd {
            background: #FFF8F9;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .box-info-qtd:last-child { margin-bottom: 0; }
        .box-info-qtd h5 { font-size: 13px; color: #8A736E; margin: 0; font-weight: 600; }
        .box-info-qtd .num { font-size: 18px; font-weight: 800; color: #D53F8C; }

        /* LISTA PRÓXIMOS AGENDAMENTOS */
        .lista-proximos-flex {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .card-proximo-mini {
            background: #FFF8F9;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 12px;
        }
        .card-proximo-mini .data-hora { font-weight: 700; color: #4A332D; }
        .card-proximo-mini .nome-cli { color: #8A736E; margin-top: 1px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}

        @media (max-width: 1100px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .main-content { margin-left: 0; }
        }

        @media (max-width: 600px) {
            .grid-inferior-lado-a-lado { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<main class="main-content">
    
    <header class="header-agenda">
        <h1 class="header-titulo"><i class="fas fa-calendar-alt"></i> Agenda de Entregas e Retiradas</h1>
        <div class="d-flex gap-2">
            <a class="btn-nav-data" href="?mes=<?php echo $mes == 1 ? 12 : $mes - 1; ?>&ano=<?php echo $mes == 1 ? $ano - 1 : $ano; ?>"><i class="fas fa-chevron-left"></i> Anterior</a>
            <a class="btn-nav-data" href="?mes=<?php echo date('m'); ?>&ano=<?php echo date('Y'); ?>">Hoje</a>
            <a class="btn-nav-data" href="?mes=<?php echo $mes == 12 ? 1 : $mes + 1; ?>&ano=<?php echo $mes == 12 ? $ano + 1 : $ano; ?>">Próximo <i class="fas fa-chevron-right"></i></a>
        </div>
    </header>

    <div class="dashboard-grid">

        <div class="painel-card">
            <div class="titulo-card-pedidos">
                PEDIDOS:
                <span><?php echo date('d/m/Y', strtotime($dataFiltro)); ?></span>
            </div>

            <?php if (!empty($pedidosDoDia)): ?>
                <?php foreach ($pedidosDoDia as $pedido): ?>
                    <div class="bloco-pedido-entrega">
                        <div class="hora-header">
                            <span class="txt-hora"><?php echo date('H:i', strtotime($pedido['data_entrega'])); ?></span>
                            <span class="badge-tipo <?php echo $pedido['tipo']; ?>">
                                <i class="fas <?php echo $pedido['tipo'] == 'personalizado' ? 'fa-store' : 'fa-truck'; ?>"></i> 
                                <?php echo $pedido['tipo'] == 'personalizado' ? 'Retirada' : 'Entrega'; ?>
                            </span>
                        </div>

                        <div class="id-pedido-txt">Pedido #<?php echo $pedido['id']; ?></div>

                        <ul class="itens-lista-produtos">
                            <?php
                            $dbItens = open_database();
                            $stmtItens = $dbItens->prepare("SELECT ip.qtd, pr.nome FROM itens_pedido ip INNER JOIN produtos pr ON ip.id_produto = pr.id WHERE ip.id_pedido = ?");
                            $stmtItens->execute([$pedido['id']]);
                            $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                            close_database($dbItens);

                            foreach ($itens as $item): ?>
                                <li>
                                    <?php echo $item['qtd']; ?>x <?php echo htmlspecialchars($item['nome']); ?>
                                </li>
                            <?php endforeach; ?>
                            
                            <?php if(!empty($pedido['observacao'])): ?>
                                <span class="obs-item"><?php echo htmlspecialchars($pedido['observacao']); ?></span>
                            <?php endif; ?>
                        </ul>

                        <div class="cliente-info-box">
                            <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($pedido['nome']); ?></p>
                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($pedido['telefone']); ?></p>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($pedido['endereco']); ?></p>
                        </div>
                        <div class="preco-total-txt">Total: R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted py-5">Sem agendamentos para esta data.</p>
            <?php endif; ?>
        </div>

        <div class="coluna-direita-flex">
            
            <div class="painel-card">
                <div class="mes-ano-banner"><?php echo $meses[$mes] . " de " . $ano; ?></div>

                <div class="grid-dias-nomes">
                    <div>D</div><div>S</div><div>T</div><div>Q</div><div>Q</div><div>S</div><div>S</div>
                </div>

                <div class="grid-dias-numeros">
                    <?php
                    $primeiroDia = strtotime("$ano-$mesFormatado-01");
                    $diaSemanaInicial = date('w', $primeiroDia);
                    $totalDiasMes = date('t', $primeiroDia);

                    for ($i = 0; $i < $diaSemanaInicial; $i++) {
                        echo '<div></div>';
                    }

                    for ($dia = 1; $dia <= $totalDiasMes; $dia++) {
                        $dataLoop = "$ano-$mesFormatado-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
                        $classeDia = 'btn-dia-calendario';
                        
                        if ($dataLoop === $dataFiltro) {
                            $classeDia .= ' dia-selecionado';
                        }

                        echo "<a href='?mes=$mes&ano=$ano&dia=$dia' class='$classeDia'>$dia";

                        if (isset($eventosPorDia[$dia])) {
                            $evt = $eventosPorDia[$dia];
                            if ($evt['normal'] > 0 && $evt['personalizado'] > 0) {
                                echo '<span class="dot-status purple"></span>';
                            } elseif ($evt['normal'] > 0) {
                                echo '<span class="dot-status pink"></span>';
                            } elseif ($evt['personalizado'] > 0) {
                                echo '<span class="dot-status orange"></span>';
                            }
                        }
                        echo "</a>";
                    }
                    ?>
                </div>

                <div class="legendas-container">
                    <div class="legenda-item"><span class="dot" style="background:#D53F8C;"></span> Entrega</div>
                    <div class="legenda-item"><span class="dot" style="background:#DD6B20;"></span> Retirada</div>
                    <div class="legenda-item"><span class="dot" style="background:#805AD5;"></span> Ambos</div>
                </div>
            </div>

            <div class="grid-inferior-lado-a-lado">
                
                <div class="painel-card">
                    <div class="subtitulo-lateral"><i class="fas fa-info-circle"></i> Resumo do Mês</div>
                    <div class="box-info-qtd">
                        <h5>Total Agendado</h5>
                        <div class="num"><?php echo count($pedidosDoMes); ?></div>
                    </div>
                    <div class="box-info-qtd">
                        <h5><span class="dot" style="background:#D53F8C; width:6px; height:6px; display:inline-block; border-radius:50%; margin-right:4px;"></span> Entregas</h5>
                        <div class="num"><?php echo count(array_filter($pedidosDoMes, function($p){ return $p['tipo'] == 'normal'; })); ?></div>
                    </div>
                    <div class="box-info-qtd">
                        <h5><span class="dot" style="background:#DD6B20; width:6px; height:6px; display:inline-block; border-radius:50%; margin-right:4px;"></span> Retiradas</h5>
                        <div class="num"><?php echo count(array_filter($pedidosDoMes, function($p){ return $p['tipo'] == 'personalizado'; })); ?></div>
                    </div>
                </div>

                <div class="painel-card">
                    <div class="subtitulo-lateral"><i class="fas fa-clock"></i> Próximos do Mês</div>
                    <div class="lista-proximos-flex">
                        <?php 
                        $proximos = array_slice($pedidosDoMes, 0, 3); 
                        if (!empty($proximos)):
                            foreach ($proximos as $prox): 
                            ?>
                                <div class="card-proximo-mini">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="data-hora"><?php echo date('d/m - H:i', strtotime($prox['data_entrega'])); ?></span>
                                        <span class="badge-tipo <?php echo $prox['tipo']; ?>" style="font-size:9px; padding:1px 6px;">
                                            <?php echo $prox['tipo'] == 'personalizado' ? 'Ret' : 'Ent'; ?>
                                        </span>
                                    </div>
                                    <div class="nome-cli" title="<?php echo htmlspecialchars($prox['cliente_nome']); ?>">
                                        <?php echo htmlspecialchars($prox['cliente_nome']); ?>
                                    </div>
                                </div>
                            <?php 
                            endforeach; 
                        else: ?>
                            <span class="text-muted small py-3 text-center">Sem próximos agendamentos.</span>
                        <?php endif; ?>
                    </div>
                </div>

            </div> 

        </div> 

    </div>
</main>

</body>
</html>