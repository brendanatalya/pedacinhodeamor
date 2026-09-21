<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';
require_once ABSPATH . 'inc/database.php';

function redirecionarConta($mensagem, $tipo = 'danger') {
    $_SESSION['message'] = $mensagem;
    $_SESSION['type'] = $tipo;
    header('Location: ' . BASEURL . 'minha_conta.php');
    exit;
}

function cpfValidoConta($cpf) {
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    for ($posicao = 9; $posicao < 11; $posicao++) {
        $soma = 0;
        for ($indice = 0; $indice < $posicao; $indice++) $soma += (int) $cpf[$indice] * (($posicao + 1) - $indice);
        if ((int) $cpf[$posicao] !== ((10 * $soma) % 11) % 10) return false;
    }
    return true;
}

function senhaForte($senha) {
    return strlen($senha) >= 8 && preg_match('/[A-Z]/', $senha) && preg_match('/[0-9]/', $senha) && preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $senha);
}

if (empty($_SESSION['logado']) || empty($_SESSION['id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

$acao = $_POST['acao'] ?? '';
$userId = (int) $_SESSION['id'];
$user = find('usuarios', $userId);
if (!$user) redirecionarConta('Não foi possível localizar sua conta.');

try {
    $database = open_database();
    if ($acao === 'senha') {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        if (!$senhaAtual || !$novaSenha || !$confirmarSenha) throw new Exception('Preencha todos os campos de senha.');
        if (!password_verify($senhaAtual, $user['senha'])) throw new Exception('Senha atual incorreta.');
        if ($novaSenha !== $confirmarSenha) throw new Exception('A nova senha e a confirmação não coincidem.');
        if (!senhaForte($novaSenha)) throw new Exception('Use ao menos 8 caracteres, incluindo letra maiúscula, número e símbolo.');
        update('usuarios', $userId, ['senha' => password_hash($novaSenha, PASSWORD_DEFAULT)]);
        redirecionarConta('Senha atualizada com sucesso.', 'success');
    }
    if ($acao !== 'dados') throw new Exception('Ação inválida.');

    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    if (!$nome || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$cpf || !$telefone) throw new Exception('Nome, e-mail válido, CPF e telefone são obrigatórios.');
    if (!cpfValidoConta($cpf)) throw new Exception('Digite um CPF válido.');

    $check = $database->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
    $check->execute([$email, $userId]);
    if ($check->fetch()) throw new Exception('Este e-mail já está em uso por outra conta.');

    $updateData = ['nome' => $nome, 'email' => $email, 'cpf' => $cpf, 'telefone' => $telefone, 'endereco' => $endereco ?: null];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['foto']['size'] > 5 * 1024 * 1024 || !getimagesize($_FILES['foto']['tmp_name'])) throw new Exception('Envie uma imagem válida de até 5 MB.');
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'], true)) throw new Exception('Use uma imagem JPG, JPEG, PNG ou GIF.');
        $diretorio = ABSPATH . 'imagens/uploads/usuarios';
        if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true)) throw new Exception('Não foi possível preparar o diretório da foto.');
        $caminhoRelativo = 'imagens/uploads/usuarios/perfil_' . $userId . '_' . time() . '.' . $extensao;
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], ABSPATH . $caminhoRelativo)) throw new Exception('Falha ao salvar a foto de perfil.');
        $updateData['foto'] = $caminhoRelativo;
        $_SESSION['foto'] = $caminhoRelativo;
    }
    update('usuarios', $userId, $updateData);
    $_SESSION['nome'] = $nome;
    $_SESSION['email'] = $email;
    redirecionarConta('Dados da conta atualizados com sucesso.', 'success');
} catch (Exception $e) {
    redirecionarConta($e->getMessage());
}
