<?php
if (!isset($_SESSION)) session_start();
require_once 'config.php';
require_once ABSPATH . 'inc/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
    exit;
}

$name = trim($_POST['nome'] ?? $_POST['name'] ?? '');
$cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$email_confirm = strtolower(trim($_POST['email_confirm'] ?? ''));
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (!$name || !$cpf || !$telefone || !$email || !$email_confirm || !$password || !$password_confirm) {
    echo json_encode([
        'success' => false,
        'message' => 'Preencha todos os campos do cadastro.'
    ]);
    exit;
}

function cpfValido($cpf) {
    // Remove qualquer caractere que não seja número
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($posicao = 9; $posicao < 11; $posicao++) {
        $soma = 0;
        for ($indice = 0; $indice < $posicao; $indice++) {
            $soma += (int) $cpf[$indice] * (($posicao + 1) - $indice);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$posicao] !== $digito) {
            return false;
        }
    }
    return true;
}

if (!cpfValido($cpf)) {
    echo json_encode([
        'success' => false,
        'message' => 'Digite um CPF válido.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Digite um e-mail válido.'
    ]);
    exit;
}

if ($email !== $email_confirm) {
    echo json_encode([
        'success' => false,
        'message' => 'Os e-mails não coincidem.'
    ]);
    exit;
}

if ($password !== $password_confirm) {
    echo json_encode([
        'success' => false,
        'message' => 'As senhas não coincidem.'
    ]);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve ter no mínimo 8 caracteres.'
    ]);
    exit;
}

if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve conter pelo menos uma LETRA MAIÚSCULA.'
    ]);
    exit;
}

if (!preg_match('/[0-9]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve conter pelo menos um NÚMERO.'
    ]);
    exit;
}

if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'A senha deve conter pelo menos um CARACTERE ESPECIAL (!@#$%^&* etc).'
    ]);
    exit;
}

$database = open_database();
if (!$database) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar ao banco de dados.'
    ]);
    exit;
}

try {
    $checkEmail = $database->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $checkEmail->execute([$email]);

    if ($checkEmail->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Este e-mail já está cadastrado. Faça login ou use outro e-mail.'
        ]);
        exit;
    }

    $checkCpf = $database->prepare('SELECT id FROM usuarios WHERE cpf = ? LIMIT 1');
    $checkCpf->execute([$cpf]);

    if ($checkCpf->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Este CPF já está cadastrado. Verifique os dados e tente novamente.'
        ]);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $database->prepare('INSERT INTO usuarios (nome, email, cpf, telefone, endereco, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $insert->execute([$name, $email, $cpf, $telefone, $endereco ?: null, $password_hash, 'cliente']);

    $_SESSION['id'] = $database->lastInsertId();
    $_SESSION['nome'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['tipo'] = 'cliente';
    $_SESSION['logado'] = true;

    echo json_encode([
        'success' => true,
        'message' => 'Cadastro realizado com sucesso! Você já está logado.'
    ]);
    exit;
} catch (Exception $e) {
    error_log('Erro no cadastro: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível concluir o cadastro. Tente novamente.'
    ]);
    exit;
}

