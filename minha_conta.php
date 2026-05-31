<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once 'config.php';
require_once ABSPATH . 'inc/database.php';

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

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta | Doce Sabor</title>

    <link rel="stylesheet" href="css_pda/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-confeitaria">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="conta-card shadow-sm">
                <div class="conta-header text-center">
                            <?php 
                    // 1. Verifica se o cliente tem uma foto personalizada cadastrada E se ela existe na pasta
                    if (!empty($user['foto']) && file_exists($user['foto'])): 
                    ?>
                        <img src="<?php echo htmlspecialchars($user['foto']); ?>" class="avatar-img" id="previewFoto">
                    
                    <?php else: ?>
                        <img src="imagens/uploads/usuarios/usuario_basico.jpg" class="avatar-img" id="previewFoto">
                    
                    <?php endif; ?>

                    <div class="avatar-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>

                    <h2 class="mt-3 title-confeitaria">Olá, <?php echo htmlspecialchars($nome); ?>!</h2>
                    <p class="text-muted">Acompanhe seus pedidos e gerencie seu perfil doce.</p>
                </div>

                <div class="conta-body p-4 p-md-5">
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $type === 'danger' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                            <i class="fas <?php echo $type === 'danger' ? 'fa-circle-xmark' : 'fa-circle-check'; ?> me-2"></i>
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="salvar_conta.php" method="POST" enctype="multipart/form-data">
                        
                        <input type="file" name="foto" class="d-none" accept="image/*" id="inputFoto">

                        <h5 class="section-title mb-4"><i class="fas fa-user-cookie me-2"></i>Seus Dados Pessoais</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label-custom"><i class="fas fa-user me-2"></i>Nome Completo</label>
                                <input type="text" name="nome" class="form-control form-control-custom" value="<?php echo htmlspecialchars($nome); ?>" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label-custom"><i class="fas fa-envelope me-2"></i>E-mail Cadastrado</label>
                                <input type="email" name="email" class="form-control form-control-custom" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label-custom"><i class="fas fa-location-dot : me-2"></i>Endereço para Entrega</label>
                                <input type="text" name="endereco" class="form-control form-control-custom" value="<?php echo htmlspecialchars($endereco); ?>" placeholder="Rua, número, bairro, cidade e CEP">
                            </div>
                        </div>

                        <hr class="my-4 custom-hr">

                        <h5 class="section-title mb-4"><i class="fas fa-key me-2"></i>Alterar Senha <small class="text-muted fs-6">(Opcional)</small></h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label-custom">Senha Atual</label>
                                <input type="password" name="senha_atual" class="form-control form-control-custom" placeholder="Sua senha vigente">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label-custom">Nova Senha</label>
                                <input type="password" name="nova_senha" class="form-control form-control-custom" placeholder="Mínimo 6 caracteres">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label-custom">Confirmar Nova Senha</label>
                                <input type="password" name="confirmar_senha" class="form-control form-control-custom" placeholder="Repita a nova senha">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-2 flex-wrap gap-3">
                            <a href="index.php" class="btn btn-custom-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Ir para a Loja
                            </a>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="inc/logout.php" class="btn btn-custom-danger">
                                    <i class="fas fa-right-from-bracket me-2"></i>Sair
                                </a>
                                <button type="submit" class="btn btn-custom-primary">
                                    <i class="fas fa-floppy-disk me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="pedidos-box mt-5 pt-4 border-top custom-border">
                        <h5 class="section-title mb-4">
                            <i class="fas fa-cake-candles me-2"></i>Seus Pedidos Recentes
                        </h5>

                        <?php if (!empty($pedidos)): ?>
                            <div class="table-responsive-md">
                                <table class="table table-custom align-middle">
                                    <thead>
                                        <tr>
                                            <th>Pedido</th>
                                            <th>Data</th>
                                            <th>Status</th>
                                            <th class="text-end">Total</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pedidos as $pedido): ?>
                                            <tr>
                                                <td><span class="fw-bold text-pink">#<?php echo $pedido['id']; ?></span></td>
                                                <td><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></small></td>
                                                <td>
                                                    <?php 
                                                        $statusClass = 'bg-warning text-dark';
                                                        if($pedido['status'] == 'entregue' || $pedido['status'] == 'concluido') $statusClass = 'bg-success text-white';
                                                        if($pedido['status'] == 'cancelado') $statusClass = 'bg-danger text-white';
                                                    ?>
                                                    <span class="badge rounded-pill <?php echo $statusClass; ?>">
                                                        <?php echo ucfirst($pedido['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold text-dark">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <?php
                                                        $stmtAval = $database->prepare("SELECT id FROM avaliacoes WHERE id_pedido = ?");
                                                        $stmtAval->execute([$pedido['id']]);
                                                        $ja_avaliou = $stmtAval->fetch();

                                                        if ($pedido['status'] === 'entregue' && !$ja_avaliou): ?>
                                                            <a href="paginas/avaliar.php?pedido=<?= $pedido['id'] ?>
                                                            " class="btn btn-sm btn-warning">
                                                                ⭐ Avaliar
                                                            </a>
                                                        <?php elseif ($pedido['status'] === 'entregue' && $ja_avaliou): ?>
                                                            <span class="text-success small">✔ Avaliado</span>
                                                        <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4 rounded-3 bg-light-pink">
                                <i class="fas fa-basket-shopping fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Você ainda não realizou nenhum pedido. Que tal escolher um doce agora?</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="js/bootstrap/bootstrap.bundle.min.js"></script>
<script>
// Facilidade: Clicar no círculo da foto abre a seleção de arquivo
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
});
</script>
</body>
</html>