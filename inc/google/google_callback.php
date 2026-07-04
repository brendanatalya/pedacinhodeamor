<?php
include ("../../config.php");
require_once(DBAPI);

if (!isset($_SESSION)) session_start();

// Baixa a foto de perfil do Google e salva na pasta imagens/, retornando o nome do arquivo salvo
// Baixa a foto de perfil do Google e salva na pasta imagens/uploads/usuarios/,
// retornando o CAMINHO RELATIVO completo (é isso que a coluna 'foto' guarda,
// já que minha_conta.php usa o valor direto em <img src="..."> e file_exists())
function baixarFotoGoogle($url) {
    if (!$url) {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $conteudoImagem = curl_exec($ch);
    curl_close($ch);

    if (!$conteudoImagem) {
        return null;
    }

    $nomeArquivo = 'google_' . uniqid() . '.jpg';
    $caminhoRelativo = 'imagens/uploads/usuarios/' . $nomeArquivo;
    $caminhoCompleto = ABSPATH . $caminhoRelativo;

    if (file_put_contents($caminhoCompleto, $conteudoImagem) === false) {
        return null;
    }

    return $caminhoRelativo;
}

if (!isset($_GET['code'])) {
    header("Location: " . BASEURL . "index.php?status=err&msg=" . urlencode('Login com Google cancelado ou falhou.'));
    exit;
}

$code = $_GET['code'];

// 1. Troca o code pelo access_token
$tokenParams = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code'
];

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$tokenResponse = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);

if (!isset($tokenData['access_token'])) {
    header("Location: " . BASEURL . "index.php?status=err&msg=" . urlencode('Erro ao obter token do Google.'));
    exit;
}

// 2. Usa o access_token pra buscar os dados do usuário logado no Google
$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $tokenData['access_token']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$googleUser = json_decode($userInfoResponse, true);

if (!isset($googleUser['email'])) {
    header("Location: " . BASEURL . "index.php?status=err&msg=" . urlencode('Não foi possível obter seu email do Google.'));
    exit;
}

$email = strtolower(trim($googleUser['email']));
$nome  = $googleUser['name'] ?? explode('@', $email)[0];
$fotoUrlGoogle = $googleUser['picture'] ?? null;

$database = open_database();

try {
    // 3. Verifica se o usuário já existe na tabela usuarios
    $sql = $database->prepare("SELECT id, nome, email, tipo, foto FROM usuarios WHERE email = ? LIMIT 1");
    $sql->execute([$email]);
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Usuário novo -> cria conta (senha aleatória, já que o login sempre será via Google)
        $senhaAleatoria = bin2hex(random_bytes(16));
        $senhaHash = password_hash($senhaAleatoria, PASSWORD_DEFAULT);

        // cpf é NOT NULL e UNIQUE na tabela; como não temos o CPF real do Google,
        // geramos um placeholder numérico único (não vai colidir com CPF real)
        $cpfPlaceholder = str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);

        // Baixa a foto de perfil do Google, se existir, e salva em imagens/
        $nomeFoto = baixarFotoGoogle($fotoUrlGoogle);

        $insert = $database->prepare("INSERT INTO usuarios (nome, email, senha, tipo, cpf, foto) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$nome, $email, $senhaHash, 'cliente', $cpfPlaceholder, $nomeFoto]);

        $usuario = [
            'id'    => $database->lastInsertId(),
            'nome'  => $nome,
            'email' => $email,
            'tipo'  => 'cliente',
            'foto'  => $nomeFoto
        ];
    } elseif (empty($usuario['foto']) && $fotoUrlGoogle) {
        // Usuário já existia mas ainda não tem foto -> aproveita e importa a do Google
        $nomeFoto = baixarFotoGoogle($fotoUrlGoogle);
        if ($nomeFoto) {
            $update = $database->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
            $update->execute([$nomeFoto, $usuario['id']]);
            $usuario['foto'] = $nomeFoto;
        }
    }

    // 4. Cria a sessão com as MESMAS chaves usadas pelo login normal (validacao() em database.php)
    $_SESSION['id']     = $usuario['id'];
    $_SESSION['nome']   = $usuario['nome'];
    $_SESSION['email']  = $usuario['email'];
    $_SESSION['logado'] = true;
    $_SESSION['tipo']   = $usuario['tipo'] ?? 'cliente';

} catch (Exception $e) {
    close_database($database);
    header("Location: " . BASEURL . "index.php?status=err&msg=" . urlencode('Erro ao processar login com Google.'));
    exit;
}

close_database($database);

// 5. Volta pra home já logado (o header.php vai mostrar "Olá, nome" automaticamente)
header('Location: ' . BASEURL . 'index.php');
exit;