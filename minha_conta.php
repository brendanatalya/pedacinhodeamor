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

<div class="container">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="conta-card">

                <!-- HEADER -->
                <div class="conta-header">

                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>

                    <h2>Minha Conta</h2>

                    <p class="mb-0">
                        Gerencie suas informações pessoais
                    </p>

                </div>

                <!-- BODY -->
                <div class="conta-body">

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $type === 'danger' ? 'danger' : 'success'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="info-box">
                        <i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($email); ?>
                    </div>

                    <form action="salvar_conta.php" method="POST">

                        <div class="row">

                            <div class="col-md-6 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Nome
                                </label>

                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($nome); ?>"
                                >
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($email); ?>"
                                >
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-location-dot"></i> Endereço
                                </label>

                                <input
                                    type="text"
                                    name="endereco"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($endereco); ?>"
                                    placeholder="Digite seu endereço"
                                >
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Nova Senha
                                </label>

                                <input
                                    type="password"
                                    name="senha"
                                    class="form-control"
                                    placeholder="Digite uma nova senha"
                                >
                            </div>

                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 acoes">

                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>

                            <div class="d-flex gap-2">

                                <a href="inc/logout.php" class="btn btn-danger">
                                    <i class="fas fa-right-from-bracket"></i> Sair
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

</body>
</html>