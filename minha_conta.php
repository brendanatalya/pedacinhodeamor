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

    <link rel="stylesheet" href="css_pda/style_pda.css">
    <link rel="stylesheet" href="css_pda/bootstrap/css/bootstrap.min.css">

   
</head>
<body>

<div class="container">
    <div class="conta-box">

        <div class="text-center mb-4">

            <h2 class="mt-3 titulo">
                Olá, <?php echo htmlspecialchars($nome); ?>
            </h2>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $type === 'danger' ? 'danger' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="salvar_conta.php" method="POST" enctype="multipart/form-data">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nome</label>

                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        value="<?php echo htmlspecialchars($nome); ?>"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?php echo htmlspecialchars($email); ?>"
                    >
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Endereço</label>

                    <input
                        type="text"
                        name="endereco"
                        class="form-control"
                        value="<?php echo htmlspecialchars($endereco); ?>"
                        placeholder="Digite seu endereço"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nova Senha</label>

                    <input
                        type="password"
                        name="senha"
                        class="form-control"
                        placeholder="Digite uma nova senha"
                    >
                </div>

            </div>
            
            <div class="d-flex justify-content-between mt-4">

                <a href="index.php" class="btn btn-secondary">
                    Voltar
                </a>

                <div>
                    <a href="inc/logout.php" class="btn btn-danger">
                        Sair
                    </a>

                    <button type="submit" class="btn btn-salvar">
                        Salvar Alterações
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>

<script src="js/bootstrap/bootstrap.bundle.min.js"></script>

</body>
</html>

