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
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            background-color: #8b6f47;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(0, 0, 0, 0.1);
            border-left-color: #f5c2d6;
            color: #f5c2d6;
        }
        .main-content {
            padding: 30px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .header-admin {
            background-color: #f5c2d6;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-admin h1 {
            margin: 0;
            color: #8b6f47;
            font-size: 28px;
        }
        .card-stat {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-stat .number {
            font-size: 32px;
            font-weight: bold;
            color: #8b6f47;
        }
        .card-stat .label {
            color: #666;
            margin-top: 10px;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR -->
            <div class="col-md-2 sidebar">
                <div class="text-white p-3" style="border-bottom: 2px solid rgba(255,255,255,0.2); margin-bottom: 20px;">
                    <h5 style="margin: 0;"><i class="fas fa-user-circle"></i> Admin</h5>
                    <small><?php echo htmlspecialchars($_SESSION['nome']); ?></small>
                </div>
                
                <a href="#dashboard" onclick="showSection('dashboard')" class="menu-link active">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="#produtos" onclick="showSection('produtos')" class="menu-link">
                    <i class="fas fa-box"></i> Produtos
                </a>
                <a href="#pedidos" onclick="showSection('pedidos')" class="menu-link">
                    <i class="fas fa-shopping-bag"></i> Pedidos
                </a>
                <a href="#agenda" onclick="showSection('agenda')" class="menu-link">
                    <i class="fas fa-calendar"></i> Agenda
                </a>
                <a href="#clientes" onclick="showSection('clientes')" class="menu-link">
                    <i class="fas fa-users"></i> Clientes
                </a>
                
                <hr style="border-color: rgba(255,255,255,0.2);">
                
                <a href="<?php echo BASEURL; ?>inc/logout.php" style="color: #ff6b6b;">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>

            <!-- MAIN CONTENT -->
            <div class="col-md-10 main-content">
                
                <?php
                date_default_timezone_set('America/Sao_Paulo');
                ?>

                <!-- HEADER -->
                <div class="header-admin">
                    <div>
                        <h1>
                            <i class="fas fa-crown"></i>
                            Painel de Administração
                        </h1>

                        <small>
                            Bem-vindo de volta,
                            <?php echo htmlspecialchars($_SESSION['nome']); ?>!
                        </small>
                    </div>
                    
                    <div style="color: #8b6f47; text-align:right;">
                        <small>
                            <?php echo date('d/m/Y'); ?>
                        </small>
                        <br>
                        <strong>
                            <?php echo date('H:i'); ?>
                        </strong>
                    </div>
                </div>

                <!-- DASHBOARD -->
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
                            <div style="background: white; padding: 20px; border-radius: 8px;">
                                <h5>Últimos Pedidos</h5>
                                <table class="table table-sm">
                                    <thead>
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
                                        <!-- Preenchido via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRODUTOS -->
                <div id="produtos" class="section">
                    <iframe src="<?php echo BASEURL; ?>admin/produtos/index.php" 
                            style="width: 100%; height: 800px; border: none;"></iframe>
                </div>

                <!-- PEDIDOS -->
                <div id="pedidos" class="section">
                    <iframe src="<?php echo BASEURL; ?>admin/pedidos/index.php" 
                            style="width: 100%; height: 800px; border: none;"></iframe>
                </div>

                <!-- AGENDA -->
                <div id="agenda" class="section">
                    <iframe src="<?php echo BASEURL; ?>admin/agenda/index.php" 
                            style="width: 100%; height: 800px; border: none;"></iframe>
                </div>

                <!-- CLIENTES -->
                <div id="clientes" class="section">
                    <iframe src="<?php echo BASEURL; ?>admin/clientes/index.php" 
                            style="width: 100%; height: 800px; border: none;"></iframe>
                </div>

            </div>
        </div>
    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        function showSection(sectionId) {
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
            
            // Adicionar classe active ao link clicado
            event.target.closest('.menu-link').classList.add('active');
        }

        // Carregar dados do dashboard
        function loadDashboardData() {
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
                                    <td>#${pedido.id}</td>
                                    <td>${pedido.nome}</td>
                                    <td>${pedido.data_pedido}</td>
                                    <td><span class="badge bg-info">${pedido.status}</span></td>
                                    <td>R$ ${parseFloat(pedido.total).toFixed(2)}</td>
                                    <td>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-primary" onclick="viewPedido(${pedido.id})">
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
        setInterval(() => {

            if(document.visibilityState === 'visible') {

                loadDashboardData();

            }

        }, 5000);




    </script>
</body>
</html>
