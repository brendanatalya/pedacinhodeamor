<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once 'config.php';
require_once ABSPATH . 'inc/database.php';
require_once DBAPI; 
include(HEADER_TEMPLATE);

if (empty($_SESSION['logado'])) {
    header('Location: index.php');
    exit;
}

$userId = intval($_SESSION['id'] ?? 0);
$user = find('usuarios', $userId);

if (!$user) {
    header('Location: index.php');
    exit;
}

$database = open_database();

$stmtPedidos = $database->prepare("
    SELECT *
    FROM pedidos
    WHERE id_cliente = ?
    ORDER BY data_pedido ASC
");
$stmtPedidos->execute([$userId]);
$pedidos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);

$nome = $user['nome'] ?? $_SESSION['nome'] ?? 'Cliente';
$email = $user['email'] ?? $_SESSION['email'] ?? '';
$endereco = $user['endereco'] ?? '';
$message = $_SESSION['message'] ?? '';
$type = $_SESSION['type'] ?? '';

unset($_SESSION['message'], $_SESSION['type']);
?>

<body>
    <div class="minhaconta">
        <aside class="minhaconta-sidebar">
        
            <!-- cabeçalho -->
            <div class="sidebar-header">
        
                <!-- avatar clicável (o JS abre o input file) -->
                <div class="minhaconta-perfil" id="avatarClickTrigger" title="Clique para trocar sua foto">
                    <div class="minhaconta-fotoperfil">
                        <?php
                            if (!empty($user['foto']) && file_exists($user['foto'])):
                        ?>
                            <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto de perfil" id="previewFoto">
                        <?php else: ?>
                            <img src="imagens/uploads/usuarios/usuario_basico.jpg" alt="Foto de perfil" id="previewFoto">
                        <?php endif; ?>
                    </div>
                    <div class="trocarfoto-botao" title="Alterar foto">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            
                <h3 class="minhaconta-nome"><?php echo htmlspecialchars($nome); ?></h3>
            
                <p class="minhaconta-email"><?php echo htmlspecialchars($email); ?></p>
            
                <div class="minhaconta-statuspedidos">
                    <div class="minhaconta-statuspedido">
                        <span class="minhaconta-qtdpedidos"><?php echo count($pedidos); ?></span>
                        <span class="minhaconta-statuspedido-texto">Pedidos</span>
                    </div>
                    <div class="minhaconta-statuspedido">
                        <span class="minhaconta-qtdpedidos"><?php echo count($pedidos); ?></span>
                        <span class="minhaconta-statuspedido-texto">Avaliações</span>
                    </div>
                </div>
            </div>
        
            <!-- links de navegação -->
            <div class="minhaconta-nav">
                <a href="#" class="minhaconta-btnativo" data-tab="dados">
                    <i class="fas fa-user"></i>
                    Meus Dados
                </a>
                <a href="#" data-tab="pedidos">
                    <i class="fas fa-box-open"></i>
                    Meus Pedidos
                    <!-- PHP: <span class="minhaconta-navbadge"><?= count($pedidos) ?></span> -->
                    <span class="minhaconta-navbadge"><?php echo count($pedidos); ?></span>
                </a>
                <a href="#" data-tab="senha">
                    <i class="fas fa-key"></i>
                    Alterar Senha
                </a>
                <div class="linhadivisora"></div>
                <a href="index.php">
                    <i class="fas fa-store"></i>
                    Ir para a Loja
                </a>
                <a href="inc/logout.php" class="btn-sair">
                    <i class="fas fa-right-from-bracket"></i>
                    Sair da Conta
                </a>
            </div>
        
        </aside>

        <main class="mc-main">
 

            <?php if ($message): ?>
                <div class="mc-alert <?= $type === 'danger' ? 'mc-alert-danger' : 'mc-alert-success' ?>">
                <i class="fas <?= $type === 'danger' ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
                <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="conta-dadospessoais conta-tab minhaconta-btnativo" id="panel-dados">
                <div class="conta-dadoshead">
                    <div class="conta-dadosicon"><i class="fas fa-user"></i></div>
                    <div>
                        <h2>Dados Pessoais</h2>
                        <p>Mantenha suas informações sempre atualizadas</p>
                    </div>
                </div>
            
                <div class="conta-dadosbody">
                    <form action="salvar_conta.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="acao" value="dados">
                        <input type="file"   name="foto" id="inputFoto" accept="image/*" style="display:none">
                
                        <div class="conta-dadosdiv">
                
                            <div class="conta-campo">
                                <label class="conta-campolabel" for="inp-nome">
                                    <i class="fas fa-user"></i> Nome Completo
                                </label>
                                <!-- PHP: value="<?php echo htmlspecialchars($nome); ?>" -->
                                <input type="text" id="inp-nome" name="nome" class="conta-campoinput" value="<?php echo htmlspecialchars($nome); ?>" required>
                            </div>
                
                            <div class="conta-campo">
                                <label class="conta-campolabel" for="inp-email">
                                    <i class="fas fa-envelope"></i> E-mail
                                </label>
                                <input type="email" id="inp-email" name="email" class="conta-campoinput" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                
                            <div class="conta-campo conta-ocupartudo">
                                <label class="conta-campolabel" for="inp-end">
                                    <i class="fas fa-location-dot"></i> Endereço para Entrega
                                </label>
                                <input type="text" id="inp-end" name="endereco" class="conta-campoinput" value="<?php echo htmlspecialchars($endereco); ?>" placeholder="Rua, número, bairro, cidade e CEP">
                            </div>
                
                        </div>
                
                        <div class="conta-formactions">
                            <button type="submit" class="contabotao cocontabotao-rosa">
                                <i class="fas fa-floppy-disk"></i>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        
            <div class="conta-dadospessoais conta-tab" id="panel-pedidos">
                <div class="conta-dadoshead">
                    <div class="conta-dadosicon"><i class="fas fa-box-open"></i></div>
                    <div>
                        <h2>Meus Pedidos</h2>
                        <p>Histórico completo das suas compras</p>
                    </div>
                </div>
            
                <div class="conta-dadosbody">
                    <div class="minhaconta-pedidos">

                        <?php if (!empty($pedidos)): ?>
                        <?php foreach ($pedidos as $pedido): 
                            $statusMap = [
                                'pendente'   => 'contabadge-pendente',
                                'confirmado' => 'contabadge-confirmado',
                                'preparacao' => 'contabadge-preparacao',
                                'pronto'     => 'contabadge-pronto',
                                'entregue'   => 'contabadge-entregue',
                                'concluido'  => 'contabadge-entregue',
                                'cancelado'  => 'contabadge-cancelado',
                            ];
                            $badgeClass = $statusMap[$pedido['status']] ?? 'contabadge-pendente';
                            $stmtAval = $database->prepare("SELECT id FROM avaliacoes WHERE id_pedido = ?");
                            $stmtAval->execute([$pedido['id']]);
                            $ja_avaliou = $stmtAval->fetch();
                        ?>
                        <div class="minhaconta-pedido">
                            <div class="minhaconta-pedido-id">
                                <strong><?php echo htmlspecialchars($pedido['id']); ?></strong>
                                <span>#</span>
                            </div>
                            <div class="minhaconta-pedido-info">
                                <p class="minhaconta-pedido-date">
                                    <?php echo date('d/m/Y \à\s H:i', strtotime($pedido['data_pedido'])); ?>
                                </p>
                                <p class="minhaconta-pedido-desc">Pedido #<?php echo htmlspecialchars($pedido['id']); ?></p>
                            </div>
                            <span class="contabadge <?= $badgeClass ?>">
                                <?php echo ucfirst(htmlspecialchars($pedido['status'])); ?>
                            </span>
                            <div class="minhaconta-pedidovalor">
                                R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
                            </div>
                            <div>
                                <?php if ($pedido['status'] === 'entregue' && !$ja_avaliou): ?>
                                    <a href="paginas/avaliar.php?pedido=<?= $pedido['id'] ?>" class="conta-btnavaliar">⭐ Avaliar</a>
                                <?php elseif ($pedido['status'] === 'entregue' && $ja_avaliou): ?>
                                    <span class="conta-avaliado">
                                        <i class="fas fa-circle-check"></i> Avaliado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <div class="pedidos-vazio">
                                <i class="fas fa-basket-shopping"></i>
                                <p>Você ainda não realizou nenhum pedido.<br>Que tal escolher um doce agora?</p>
                                <a href="doces.php" class="contabotao contabotao-rosa">
                                    <i class="fas fa-store"></i> Ver Cardápio
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        
            <div class="conta-dadospessoais conta-tab" id="panel-senha">
                <div class="conta-dadoshead">
                    <div class="conta-dadosicon"><i class="fas fa-key"></i></div>
                    <div>
                        <h2>Alterar Senha</h2>
                        <p>Recomendamos uma senha forte e única</p>
                    </div>
                </div>
            
                <div class="conta-dadosbody">
                    <form action="salvar_conta.php" method="POST">
                        <input type="hidden" name="acao" value="senha">
                
                        <div class="conta-dadosdiv">
                
                            <div class="conta-campo conta-ocupartudo">
                                <label class="conta-campolabel" for="senhaAtual">
                                    <i class="fas fa-lock"></i> Senha Atual
                                </label>
                                <div class="senha-wrap">
                                    <input type="password" id="senhaAtual" name="senha_atual" class="conta-campoinput" placeholder="Sua senha atual">
                                    <button type="button" class="mc-pw-eye" onclick="togglePw('senhaAtual',this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                
                            <div class="conta-campo">
                                <label class="conta-campolabel" for="novaSenha">
                                    <i class="fas fa-lock-open"></i> Nova Senha
                                </label>
                                <div class="senha-wrap">
                                    <input type="password" id="novaSenha" name="nova_senha" class="conta-campoinput" placeholder="Mínimo 6 caracteres">
                                    <button type="button" class="mc-pw-eye" onclick="togglePw('novaSenha',this)">
                                    <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                
                            <div class="conta-campo">
                                <label class="conta-campolabel" for="confirmarSenha">
                                    <i class="fas fa-check-double"></i> Confirmar Nova Senha
                                </label>
                                <div class="senha-wrap">
                                    <input type="password" id="confirmarSenha" name="confirmar_senha" class="conta-campoinput" placeholder="Repita a nova senha">
                                    <button type="button" class="mc-pw-eye" onclick="togglePw('confirmarSenha',this)">
                                    <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                
                        </div>
                
                        <div class="conta-formactions">
                            <button type="submit" class="contabotao contabotao-rosa">
                                <i class="fas fa-shield-halved"></i>
                                Atualizar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        
        </main>
    </div>

    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>



    <script>
    /* troca de tabs do minha conta */
    const tabLinks  = document.querySelectorAll('[data-tab]');
    const tabPanels = document.querySelectorAll('.conta-tab');
    
    tabLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
        e.preventDefault();
        var target = this.dataset.tab;
    
        tabLinks.forEach(function(l) { l.classList.remove('minhaconta-btnativo'); });
        tabPanels.forEach(function(p) { p.classList.remove('minhaconta-btnativo'); });
    
        this.classList.add('minhaconta-btnativo');
        document.getElementById('panel-' + target).classList.add('minhaconta-btnativo');
        });
    });
    /* add foto de usuario do minhaconta */
    document.getElementById('avatarClickTrigger').addEventListener('click', function() {
        document.getElementById('inputFoto').click();
    });
    
    document.getElementById('inputFoto').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(ev) {
        document.getElementById('previewFoto').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });
    
    /* ocultar senha ne */
    function togglePw(inputId, btn) {
        var input = document.getElementById(inputId);
        var icon  = btn.querySelector('i');
        if (input.type === 'password') {
        input.type   = 'text';
        icon.className = 'fas fa-eye-slash';
        } else {
        input.type   = 'password';
        icon.className = 'fas fa-eye';
        }
    }
    </script>

        <script>
    // Facilidade: Clicar no círculo da foto abre a seleção de arquivo
    /*
    document.getElementById('avatarClickTrigger').addEventListener('click', function() {
        document.getElementById('inputFoto').click();
    });

    const inputFoto = document.getElementById('inputFoto');
    inputFoto.addEventListener('change', function(e){
        const file = e.target.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = function(event){
                let preview = document.getElementById('previewFoto');
                if(!preview) {
                    // Caso não existisse imagem prévia, substitui a div de avatar genérico
                    const wrapper = document.getElementById('avatarClickTrigger');
                    const oldAvatar = document.getElementById('previewAvatar');
                    if(oldAvatar) oldAvatar.remove();
                    
                    preview = document.createElement('img');
                    preview.id = 'previewFoto';
                    preview.className = 'avatar-img';
                    wrapper.insertBefore(preview, wrapper.firstChild);
                }
                preview.src = event.target.result;
            }
            reader.readAsDataURL(file);
        }
    });*/
    </script>
</body>
</html>