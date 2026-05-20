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
    ORDER BY data_pedido DESC
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

<title>Minha Conta</title>

<link rel="stylesheet" href="css_pda/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="css_pda/style_pda.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="conta-card">

                <!-- HEADER -->
                <div class="conta-header">

                    <div class="avatar-wrapper">

                        <?php if (!empty($user['foto']) && file_exists($user['foto'])): ?>

                            <img 
                                src="<?php echo htmlspecialchars($user['foto']); ?>" 
                                class="avatar-img"
                                id="previewFoto"
                            >

                        <?php else: ?>

                            <div class="avatar" id="previewAvatar">
                                <i class="fas fa-user"></i>
                            </div>

                        <?php endif; ?>

                    </div>

                    <h2 class="mt-3">Minha Conta</h2>

                    <p class="mb-0">
                        Gerencie suas informações pessoais
                    </p>

                </div>

                <!-- BODY -->
                <div class="conta-body p-4">

                    <?php if ($message): ?>

                        <div class="alert alert-<?php echo $type === 'danger' ? 'danger' : 'success'; ?>">

                            <?php echo htmlspecialchars($message); ?>

                        </div>

                    <?php endif; ?>

                    <div class="info-box mb-4">

                        <i class="fas fa-envelope"></i>

                        <?php echo htmlspecialchars($email); ?>

                    </div>

                    <form 
                        action="salvar_conta.php" 
                        method="POST"
                        enctype="multipart/form-data"
                    >
                    <div class="pedidos-box mt-5">

    <h4 class="mb-4">

        <i class="fas fa-bag-shopping"></i>
        Histórico de Pedidos

    </h4>

    <?php if (!empty($pedidos)): ?>

        <?php foreach ($pedidos as $pedido): ?>

            <div class="pedido-item">

                <div>

                    <strong>
                        Pedido #<?php echo $pedido['id']; ?>
                    </strong>

                    <br>

                    <small>
                        <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?>
                    </small>

                </div>

                <div>

                    <span class="badge bg-primary">

                        <?php echo ucfirst($pedido['status']); ?>

                    </span>

                </div>

                <div>

                    R$
                    <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="alert alert-light">

            Você ainda não possui pedidos.

        </div>

    <?php endif; ?>

</div>

                        <!-- FOTO -->
                        <div class="mb-4">

                            <label class="form-label">

                                <i class="fas fa-camera"></i>
                                Foto de Perfil

                            </label>

                            <input 
                                type="file"
                                name="foto"
                                class="form-control"
                                accept="image/*"
                                id="inputFoto"
                            >

                        </div>

                        <div class="row">

                            <!-- NOME -->
                            <div class="col-md-6 mb-4">

                                <label class="form-label">

                                    <i class="fas fa-user"></i>
                                    Nome

                                </label>

                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($nome); ?>"
                                    required
                                >

                            </div>

                            <!-- EMAIL -->
                            <div class="col-md-6 mb-4">

                                <label class="form-label">

                                    <i class="fas fa-envelope"></i>
                                    Email

                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($email); ?>"
                                    required
                                >

                            </div>

                            <!-- ENDEREÇO -->
                            <div class="col-12 mb-4">

                                <label class="form-label">

                                    <i class="fas fa-location-dot"></i>
                                    Endereço

                                </label>

                                <input
                                    type="text"
                                    name="endereco"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($endereco); ?>"
                                    placeholder="Digite seu endereço"
                                >

                            </div>

                            <!-- SENHA -->
                            <div class="col-md-6 mb-4">

                                <label class="form-label">

                                    <i class="fas fa-lock"></i>
                                    Nova Senha

                                </label>

                                <input
                                    type="password"
                                    name="senha"
                                    class="form-control"
                                    placeholder="Digite uma nova senha"
                                >

                            </div>

                        </div>
                        <div class="row mt-3">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Senha Atual
                            </label>

                            <input
                                type="password"
                                name="senha_atual"
                                class="form-control"
                            >

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Nova Senha
                            </label>

                            <input
                                type="password"
                                name="nova_senha"
                                class="form-control"
                            >

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Confirmar Nova Senha
                            </label>

                            <input
                                type="password"
                                name="confirmar_senha"
                                class="form-control"
                            >

                        </div>

                    </div>

                        <!-- AÇÕES -->
                        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">

                            <a href="index.php" class="btn btn-secondary">

                                <i class="fas fa-arrow-left"></i>
                                Voltar

                            </a>

                            <div class="d-flex gap-2 flex-wrap">

                                <a href="inc/logout.php" class="btn btn-danger">

                                    <i class="fas fa-right-from-bracket"></i>
                                    Sair

                                </a>

                                <button type="submit" class="btn btn-salvar">

                                    <i class="fas fa-floppy-disk"></i>
                                    Salvar Alterações

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="js/bootstrap/bootstrap.bundle.min.js"></script>

<script>

const inputFoto = document.getElementById('inputFoto');

inputFoto.addEventListener('change', function(e){

    const file = e.target.files[0];

    if(file){

        const reader = new FileReader();

        reader.onload = function(event){

            let preview = document.getElementById('previewFoto');

            if(preview){

                preview.src = event.target.result;

            }

        }

        reader.readAsDataURL(file);

    }

});

</script>

</body>
</html>