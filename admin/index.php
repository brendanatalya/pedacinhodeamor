<?php 
if (!isset($_SESSION)) session_start();
date_default_timezone_set('America/Sao_Paulo');

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
    <title>Admin</title>
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/dashboard-admin.css">
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
                
                <div class="align-items-start d-flex flex-column gap-2" style="padding: 0 10px;">
                    <a href="#dashboard" onclick="showSection('dashboard', event)" class="menu-link active">
                        <i class="fas fa-chart-line"></i> Visão Geral
                    </a>
                    <a href="#produtos" onclick="showSection('produtos', event)" class="menu-link">
                        <i class="fas fa-box"></i> Produtos
                    </a>
                    <a href="#estoque" onclick="showSection('estoque', event)" class="menu-link">
                        <i class="fas fa-boxes"></i> Estoque
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
                </div>

                
                <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
                
                <a href="<?php echo BASEURL; ?>index.php" style="color: #FBB6CE;">
                    <i class="fas fa-home"></i> Voltar ao site
                </a>
            </div>

            <div class="col-md-10 main-content">

                <div class="header-admin">
                    <div>
                        <h1>
                            Painel de Administração
                        </h1>
                        <small>
                            Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!
                        </small>
                    </div>
                    
                    <div style="display:flex; gap:20px; align-items:center; justify-content:flex-end;">
                        <div style="color: #61463B; text-align:right; font-size: 14px;">
                            <small style="font-weight: 600; color: #8A736E;">
                                <?php echo date('d/m/Y'); ?>
                            </small>
                            <br>
                            <strong style="font-size: 18px; font-weight: 800; color: #8C475E;">
                                <?php echo date('H:i'); ?>
                            </strong>
                        </div>

                        <div class="botoesheaderadmin">
                            <a href="<?php echo BASEURL; ?>minha_conta.php"><span><i class="fa-regular fa-circle-user"></i>  Minha Conta</span></a>
                            <a href="<?php echo BASEURL; ?>inc/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                        </div>
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

<div id="estoque" class="section">
    <iframe src="<?php echo BASEURL; ?>admin/estoque/index.php"
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

    <!-- Modal Pedido -->
    <div class="modal fade" id="modalPedido" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="mp-header">
                    <h5><i class="fas fa-shopping-bag"></i> Pedido <span id="modalPedidoId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="mp-body">

                    <div class="mp-grid">
                        <div>
                            <span class="mp-subtitulo">Cliente</span>
                            <span class="mp-valor" id="modalCliente"></span>
                        </div>
                        <div>
                            <span class="mp-subtitulo">Email</span>
                            <span class="mp-valor" id="modalEmail"></span>
                        </div>
                    </div>

                    <div class="mp-grid mp-grid-4">
                        <div>
                            <span class="mp-subtitulo">Status</span>
                            <span id="modalStatus"></span>
                        </div>
                        <div>
                            <span class="mp-subtitulo">Tipo</span>
                            <span class="mp-valor" id="modalTipo"></span>
                        </div>
                        <div>
                            <span class="mp-subtitulo">Pagamento</span>
                            <span class="mp-valor" id="modalPagamento"></span>
                        </div>
                        <div>
                            <span class="mp-subtitulo">Entrega</span>
                            <span id="modalEntrega"></span>
                        </div>
                    </div>

                    <div class="mp-grid">
                        <div>
                            <span class="mp-subtitulo">Data do Pedido</span>
                            <span class="mp-valor" id="modalDataPedido"></span>
                        </div>
                        <div>
                            <span class="mp-subtitulo">Data de Entrega</span>
                            <span class="mp-valor" id="modalDataEntrega"></span>
                        </div>
                    </div>

                    <div id="modalObsBox" class="mp-obs-box" style="display:none;">
                        <span class="mp-subtitulo">Observação</span>
                        <p id="modalObs"></p>
                    </div>

                    <h6 class="mp-itens-titulo"><i class="fas fa-list"></i> Itens do Pedido</h6>
                    <table class="mp-tabela">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Unitário</th>
                                <th class="text-end">Subtotal</th>
                                <th>Obs</th>
                            </tr>
                        </thead>
                        <tbody id="modalItens"></tbody>
                        <tfoot>
                            <tr class="mp-total-row">
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end mp-total-valor" id="modalTotal"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASEURL; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
    let currentActiveSection = 'dashboard';

    // Rótulo e classe de badge de cada status (as classes já existem no style_pda.css)
    const STATUS_INFO = {
        pendente:       { label: 'Pendente',       classe: 'badge-pendente' },
        confirmado:     { label: 'Confirmado',     classe: 'badge-confirmado' },
        em_preparacao:  { label: 'Em Preparação',  classe: 'badge-preparacao' },
        pronto:         { label: 'Pronto',         classe: 'badge-pronto' },
        entregue:       { label: 'Entregue',       classe: 'badge-entregue' },
        cancelado:      { label: 'Cancelado',      classe: 'badge-cancelado' },
    };

    function badgeStatusHtml(status) {
        const info = STATUS_INFO[status] || { label: status, classe: 'badge-pendente' };
        return `<span class="badge ${info.classe}">${info.label}</span>`;
    }

    function badgeEntregaHtml(tipoEntrega) {
        const chave = (tipoEntrega || '').toLowerCase();
        const rotulo = chave.charAt(0).toUpperCase() + chave.slice(1);
        return `<span class="badge badge-${chave}">${rotulo}</span>`;
    }

    function viewPedido(id) {
        fetch('<?php echo BASEURL; ?>admin/api/pedido.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return alert('Erro ao carregar pedido.');

                const p = data.pedido;

                document.getElementById('modalPedidoId').textContent = '#' + p.id;
                document.getElementById('modalCliente').textContent = p.cliente_nome;
                document.getElementById('modalEmail').textContent = p.cliente_email;
                document.getElementById('modalStatus').innerHTML = badgeStatusHtml(p.status);
                document.getElementById('modalTipo').textContent = p.tipo;
                document.getElementById('modalPagamento').textContent = p.forma_pagamento || '—';
                document.getElementById('modalEntrega').innerHTML = badgeEntregaHtml(p.tipo_entrega);
                document.getElementById('modalDataPedido').textContent = new Date(p.data_pedido).toLocaleString('pt-BR');
                document.getElementById('modalDataEntrega').textContent = new Date(p.data_entrega).toLocaleDateString('pt-BR') + ' às ' + p.hora_entrega;
                document.getElementById('modalTotal').textContent = 'R$ ' + parseFloat(p.valor_total).toFixed(2).replace('.', ',');

                const obsBox = document.getElementById('modalObsBox');
                if (p.observacao) {
                    obsBox.style.display = 'block';
                    document.getElementById('modalObs').textContent = p.observacao;
                } else {
                    obsBox.style.display = 'none';
                }

                const tbody = document.getElementById('modalItens');
                tbody.innerHTML = '';
                data.itens.forEach(item => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${item.produto_nome}</td>
                            <td class="text-center">${item.qtd}</td>
                            <td class="text-end">R$ ${parseFloat(item.preco_unitario).toFixed(2).replace('.', ',')}</td>
                            <td class="text-end">R$ ${parseFloat(item.subtotal).toFixed(2).replace('.', ',')}</td>
                            <td>${item.observacao || '—'}</td>
                        </tr>
                    `;
                });

                new bootstrap.Modal(document.getElementById('modalPedido')).show();
            })
            .catch(() => alert('Erro de conexão.'));
    }

    function showSection(sectionId, event) {
        currentActiveSection = sectionId;

        document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.menu-link').forEach(el => el.classList.remove('active'));

        document.getElementById(sectionId).classList.add('active');

        if (event) event.currentTarget.classList.add('active');
    }

    function refreshIframe(id) {
        const iframe = document.getElementById(id);
        if (iframe) iframe.src = iframe.src;
    }

    function loadDashboardData() {
        if (currentActiveSection !== 'dashboard') return;

        fetch('<?php echo BASEURL; ?>admin/api/dashboard.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;

                const d = data.dashboard;

                document.getElementById('totalPedidos').textContent = d.pedidos_hoje;
                document.getElementById('pedidosPendentes').textContent = d.pedidos_pendentes;
                document.getElementById('totalProdutos').textContent = d.total_produtos;
                document.getElementById('totalClientes').textContent = d.total_clientes;

                const tbody = document.getElementById('ultimosPedidos');
                tbody.innerHTML = '';

                d.ultimos_pedidos.forEach(pedido => {
                    tbody.innerHTML += `
                        <tr>
                            <td class="fw-bold">#${pedido.id}</td>
                            <td>${pedido.nome}</td>
                            <td>${new Date(pedido.data_pedido).toLocaleString('pt-BR')}</td>
                            <td>${badgeStatusHtml(pedido.status)}</td>
                            <td class="fw-bold" style="color:#2e8f5c;">R$ ${parseFloat(pedido.total).toFixed(2).replace('.', ',')}</td>
                            <td>
                                <a href="javascript:void(0)" class="dash-btn-ver" onclick="viewPedido(${pedido.id})" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error('Erro ao carregar dashboard:', error));
    }

    // Carrega ao abrir a página
    document.addEventListener('DOMContentLoaded', loadDashboardData);

    // Atualiza quando volta para a aba
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState !== 'visible') return;
        loadDashboardData();
        if (currentActiveSection === 'pedidos') refreshIframe('iframePedidos');
        if (currentActiveSection === 'agenda')  refreshIframe('iframeAgenda');
    });

    // Polling a cada 30 segundos
    setInterval(() => {
        if (document.visibilityState !== 'visible') return;
        loadDashboardData();
        if (currentActiveSection === 'pedidos') refreshIframe('iframePedidos');
        if (currentActiveSection === 'agenda')  refreshIframe('iframeAgenda');
    }, 30000);
    </script>
</body>
</html>