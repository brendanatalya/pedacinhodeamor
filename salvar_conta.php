<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';
require_once ABSPATH . 'inc/database.php';

if (empty($_SESSION['logado'])) {
    header('Location: minha_conta.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: minha_conta.php');
    exit;
}

$userId = intval($_SESSION['id'] ?? 0);
if (!$userId) {
    header('Location: minha_conta.php');
    exit;
}

$user = find('usuarios', $userId);
if (!$user) {
    header('Location: minha_conta.php');
    exit;
}

$name = trim($_POST['nome'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$senhaAtual = $_POST['senha_atual'] ?? '';
$novaSenha = $_POST['nova_senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';
$address = trim($_POST['endereco'] ?? '');

if (!$name || !$email) {
    $_SESSION['message'] = 'Nome e email são obrigatórios.';
    $_SESSION['type'] = 'danger';
    header('Location: minha_conta.php');
    exit;
}

try {
    $database = open_database();
    if (!$database) {
        throw new Exception('Não foi possível conectar ao banco de dados.');
    }

    $check = $database->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
    $check->execute([$email, $userId]);
    if ($check->fetch()) {
        throw new Exception('Este email já está em uso por outro usuário.');
    }

    $columns = [];
    $columnResult = $database->query('SHOW COLUMNS FROM usuarios');
    if ($columnResult) {
        $columns = $columnResult->fetchAll(PDO::FETCH_COLUMN);
    }

    $updateData = [
        'nome' => $name,
        'email' => $email,
    ];

    if (!empty($password)) {
        $updateData['senha'] = password_hash($password, PASSWORD_DEFAULT);
    }

    if (in_array('endereco', $columns)) {
        $updateData['endereco'] = $address;
    }

    if (in_array('foto', $columns) && !empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileName = $_FILES['foto']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            throw new Exception('Formato de imagem inválido. Use jpg, png ou gif.');
        }

        $uploadDir = 'imagens/usuarios';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = 'perfil_' . $userId . '_' . time() . '.' . $fileExt;
        $destination = $uploadDir . '/' . $newFileName;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
            throw new Exception('Falha ao enviar a foto de perfil.');
        }

        if (!empty($user['foto'])) {
            $oldPath = $user['foto'];
            if (!file_exists($oldPath)) {
                $oldPath = $uploadDir . '/' . basename($user['foto']);
            }
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $updateData['foto'] = $destination;
    }

    update('usuarios', $userId, $updateData);
    $_SESSION['nome'] = $name;
    $_SESSION['email'] = $email;
    if (!empty($password)) {
        // Mantém o login atual, não força logout
    }
    if (!empty($updateData['foto'])) {
        $_SESSION['foto'] = $updateData['foto'];
    }
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
    throw new Exception('A imagem deve ter no máximo 5MB.');
}
$checkImage = getimagesize($_FILES['foto']['tmp_name']);

if ($checkImage === false) {
    throw new Exception('Arquivo inválido.');
}

    $_SESSION['message'] = 'Dados da conta atualizados com sucesso.';
    $_SESSION['type'] = 'success';
} catch (Exception $e) {
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['type'] = 'danger';
}
if (!empty($novaSenha)) {

    if (empty($senhaAtual)) {
        throw new Exception('Digite sua senha atual.');
    }

    if (!password_verify($senhaAtual, $user['senha'])) {
        throw new Exception('Senha atual incorreta.');
    }

    if ($novaSenha !== $confirmarSenha) {
        throw new Exception('As novas senhas não coincidem.');
    }

    if (strlen($novaSenha) < 6) {
        throw new Exception('A nova senha deve ter no mínimo 6 caracteres.');
    }

    $updateData['senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
}
header('Location: minha_conta.php');
exit;
