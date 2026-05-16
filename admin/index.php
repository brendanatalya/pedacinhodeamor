<?php 
if (!isset($_SESSION)) session_start();

// Verificar se usuário está logado e é admin
if (empty($_SESSION['logado']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ' . dirname(__DIR__) . '/index.php');
    exit;
}

include '../config.php';
require_once(DBAPI);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Pedacinho de Amor</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>/css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <div class="text-white p-3 mb-4" style="border-bottom: 1px dashed rgba(255,255,255,0.2);">
                    <h5 class="d-flex align-items-center gap-2" style="margin: 0; font-weight: 700;"><i class="fas fa-user-circle"></i> Admin</h5>
                    <small style="color: #E2D9D7;"><?php echo htmlspecialchars($_SESSION['nome']); ?></small>
                </div>
                
                <a href="#dashboard" onclick="showSection('dashboard', event)" class="menu-link active">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="#produtos" onclick="showSection('produtos', event)" class="menu-link">
                    <i class="fas fa-box"></i> Produtos
                </a>
                <a href="#pedidos" onclick="showSection('pedidos', event)" class="menu-link">
                    <i class="fas fa-shopping-bag"></i> Pedidos
                </a>
                <a href="#agenda" onclick="showSection('agenda', event)" class="menu-link">
                    <i class="fas fa-calendar"></i> Agenda
                </a>
                <a href="#clientes" onclick="showSection('clientes', event)" class="menu-link">
                    <i class="fas fa-users"></i> Clientes
                </a>
                
                <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
                
                <a href="<?php echo BASEURL; ?>inc/logout.php" style="color: #FBB6CE;">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>

            <div class="col-md-10 main-content">
                
                <?php date_default_timezone_set('America/Sao_Paulo'); ?>

                <div class="header-admin">
                    <div>
                        <h1>
                            <i class="fas fa-crown"></i>
                            Painel de Administração
                        </h1>
                        <small>
                            Bem-vindo de volta, <?php echo htmlspecialchars($_SESSION['nome']); ?>!
                        </small>
                    </div>
                    
                    <div style="color: #61463B; text-align:right; font-size: 14px;">
                        <small style="font-weight: 600; color: #8A736E;">
                            <?php echo date('d/m/Y'); ?>
                        </small>
                        <br>
                        <strong style="font-size: 18px; font-weight: 800; color: #8C475E;">
                            <?php echo date('H:i'); ?>
                        </strong>
                    </div>
                </div>

                <div id="dashboard" class="section active">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card-stat">
                                <div class="number" id="totalPedidos">0</div>
                                <div class="label">Pedidos Hoje</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-stat">
                                <div class="number" id="pedidosPendentes">0</div>
                                <div class="label">Pendentes</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-stat">
                                <div class="number" id="totalProdutos">0</div>
                                <div class="label">Produtos</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-stat">
                                <div class="number" id="totalClientes">0</div>
                                <div class="label">Clientes</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card-table-box">
                                <h5><i class="fas fa-history text-muted me-2"></i> Últimos Pedidos</h5>
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Data</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ultimosPedidos">
                                        </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="produtos" class="section">
                    <iframe src="<?php echo BASEURL; ?>admin/produtos/index.php" 
                            style="width: 100%; height: 85vh; border: none; border-radius: 16px;"></iframe>
                </div>

               <div id="pedidos" class="section">
                    <iframe 
                        id="iframePedidos"
                        src="<?php echo BASEURL; ?>admin/pedidos/index.php"
                        style="width: 100%; height: 85vh; border: none; border-radius: 16px;">
                    </iframe>
                </div>

                <div id="agenda" class="section">
                    <iframe 
                        id="iframeAgenda"
                        src="<?php echo BASEURL; ?>admin/agenda/index.php"
                        style="width: 100%; height: 85vh; border: none; border-radius: 16px;">
                    </iframe>
                </div>

                <div id="clientes" class="section">
                    <iframe src="<?php echo BASEURL; ?>admin/clientes/index.php" 
                            style="width: 100%; height: 85vh; border: none; border-radius: 16px;"></iframe>
                </div>

            </div>
        </div>
    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        let currentActiveSection = 'dashboard';

        function showSection(sectionId, event) {
            currentActiveSection = sectionId;

            // Esconder todas as seções
            document.querySelectorAll('.section').forEach(el => {
                el.classList.remove('active');
            });
            
            // Remover classe active de todos os links
            document.querySelectorAll('.menu-link').forEach(el => {
                el.classList.remove('active');
            });
            
            // Mostrar seção selecionada
            document.getElementById(sectionId).classList.add('active');
            
            // Adicionar classe active ao link clicado de forma segura
            if(event) {
                event.currentTarget.classList.add('active');
            }
        }

        // Carregar dados do dashboard
        function loadDashboardData() {
            // Só executa o fetch se a aba do painel principal (dashboard) estiver visível
            if (currentActiveSection !== 'dashboard') return;

            fetch('<?php echo BASEURL; ?>admin/api/dashboard.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalPedidos').textContent = data.pedidos_hoje || 0;
                    document.getElementById('pedidosPendentes').textContent = data.pedidos_pendentes || 0;
                    document.getElementById('totalProdutos').textContent = data.total_produtos || 0;
                    document.getElementById('totalClientes').textContent = data.total_clientes || 0;
                    
                    // Carregar últimos pedidos
                    if (data.ultimos_pedidos) {
                        const tbody = document.getElementById('ultimosPedidos');
                        tbody.innerHTML = '';
                        data.ultimos_pedidos.forEach(pedido => {
                            const row = `
                                <tr>
                                    <td class="fw-bold">#${pedido.id}</td>
                                    <td>${pedido.nome}</td>
                                    <td>${pedido.data_pedido}</td>
                                    <td><span class="badge bg-info text-dark rounded-pill" style="background-color: #FFEAF1 !important; color: #D53F8C !important;">${pedido.status}</span></td>
                                    <td class="fw-bold text-secondary">R$ ${parseFloat(pedido.total).toFixed(2).replace('.', ',')}</td>
                                    <td>
                                        <a href="javascript:void(0)" class="btn btn-sm text-white" style="background-color: #61463B" onclick="viewPedido(${pedido.id})">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                    }
                })
                .catch(error => console.error('Erro ao carregar dashboard:', error));
        }

        // Carregar dados ao abrir a página
        document.addEventListener('DOMContentLoaded', loadDashboardData);
        
        // Polling inteligente de 5 segundos
        setInterval(() => {

    if(document.visibilityState !== 'visible') return;

    // Dashboard principal
    loadDashboardData();

    // Atualiza iframe ativo
    if (currentActiveSection === 'pedidos') {
        refreshIframe('iframePedidos');
    }

    if (currentActiveSection === 'agenda') {
        refreshIframe('iframeAgenda');
    }

}, 5000);
    </script>
</body>
</html>