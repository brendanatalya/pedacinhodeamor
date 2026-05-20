<?php
if (!isset($_SESSION)) {
    session_start();
}

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

// Recebe e limpa as entradas textuais
$name = trim($_POST['nome'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$senhaAtual = $_POST['senha_atual'] ?? '';
$novaSenha = $_POST['nova_senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';
$address = trim($_POST['endereco'] ?? '');

if (!$name || !$email) {
    $_SESSION['message'] = 'Nome e e-mail são obrigatórios.';
    $_SESSION['type'] = 'danger';
    header('Location: minha_conta.php');
    exit;
}

try {
    $database = open_database();
    if (!$database) {
        throw new Exception('Não foi possível conectar ao banco de dados.');
    }

    // Verifica se o e-mail modificado já pertence a outro usuário
    $check = $database->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
    $check->execute([$email, $userId]);
    if ($check->fetch()) {
        throw new Exception('Este e-mail já está em uso por outro usuário.');
    }

    // Mapeia colunas existentes na tabela
    $columns = [];
    $columnResult = $database->query('SHOW COLUMNS FROM usuarios');
    if ($columnResult) {
        $columns = $columnResult->fetchAll(PDO::FETCH_COLUMN);
    }

    // Inicializa a array de dados básicos para atualização
    $updateData = [
        'nome'  => $name,
        'email' => $email,
    ];

    // Validação de alteração de Senha (movida para o local correto)
    if (!empty($novaSenha)) {
        if (empty($senhaAtual)) {
            throw new Exception('Para cadastrar uma nova senha, você precisa digitar a Senha Atual.');
        }

        if (!password_verify($senhaAtual, $user['senha'])) {
            throw new Exception('Senha atual incorreta.');
        }

        if ($novaSenha !== $confirmarSenha) {
            throw new Exception('A nova senha e a confirmação não coincidem.');
        }

        if (strlen($novaSenha) < 6) {
            throw new Exception('A nova senha deve ter no mínimo 6 caracteres.');
        }

        $updateData['senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
    }

    // Se houver coluna de endereço, inclui na atualização
    if (in_array('endereco', $columns)) {
        $updateData['endereco'] = $address;
    }

    // VALIDAÇÃO E UPLOAD DA FOTO (Segura contra erros de path vazio)
    if (in_array('foto', $columns) && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        
        // 1. Valida tamanho da imagem (Máximo 5MB)
        if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            throw new Exception('A imagem deve ter no máximo 5MB.');
        }

        // 2. Valida se o arquivo é genuinamente uma imagem através da leitura de bytes
        $checkImage = getimagesize($_FILES['foto']['tmp_name']);
        if ($checkImage === false) {
            throw new Exception('O arquivo enviado não é uma imagem válida.');
        }

        // 3. Valida extensões permitidas
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileName = $_FILES['foto']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            throw new Exception('Formato de imagem inválido. Use apenas JPG, JPEG, PNG ou GIF.');
        }

        // 4. Cria diretório caso ele não exista
        $uploadDir = 'imagens/usuarios';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 5. Define nome único e move o arquivo
        $newFileName = 'perfil_' . $userId . '_' . time() . '.' . $fileExt;
        $destination = $uploadDir . '/' . $newFileName;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
            throw new Exception('Falha ao salvar a foto de perfil no servidor.');
        }

        // 6. Apaga a foto antiga se ela existir para não acumular lixo no servidor
        if (!empty($user['foto'])) {
            $oldPath = $user['foto'];
            if (!file_exists($oldPath)) {
                $oldPath = $uploadDir . '/' . basename($user['foto']);
            }
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Adiciona a nova foto para ser salva no banco
        $updateData['foto'] = $destination;
        $_SESSION['foto'] = $destination;
    }

    // Executa a query de atualização utilizando a função global do seu projeto
    update('usuarios', $userId, $updateData);
    
    // Atualiza os dados essenciais na sessão corrente
    $_SESSION['nome'] = $name;
    $_SESSION['email'] = $email;

    $_SESSION['message'] = 'Dados da conta atualizados com sucesso.';
    $_SESSION['type'] = 'success';

} catch (Exception $e) {
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['type'] = 'danger';
}

header('Location: minha_conta.php');
exit;