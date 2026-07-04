<?php 
    include ("../config.php");
    include (HEADER_TEMPLATE);

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

// Validação básica
if (!$email || !$password) {
    $msg = urlencode('Preencha email e senha.');
    header("Location: index.html?status=err&msg={$msg}");
    exit;
}

// Busca usuário
$stmt = $conn->prepare('SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1');

if (!$stmt) {
    $msg = urlencode('Erro interno no servidor.');
    header("Location: index.html?status=err&msg={$msg}");
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

// Verifica login
if (!$user || !password_verify($password, $user['password_hash'])) {
    $msg = urlencode('Email ou senha inválidos.');
    header("Location: index.html?status=err&msg={$msg}");
    exit;
}

// Login OK
$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $email
];

$msg = urlencode("Bem-vindo, {$user['name']}!");
header("Location: index.html?status=ok&msg={$msg}");
exit;
?>

  <?php 
    include ("../config.php");
    include (HEADER_TEMPLATE);
?>
    <div id="actions" class="mt-5 mb-5">
        <form action="valida.php" method="post">
            <div class="row">
                <div class="form-floating col-12 mb-2">
                    <input type="text" class="form-control" id="log" name="login" placeholder="Usuário">
                    <label for="log">Usuário</label>
                </div>
                <div class="form-floating col-12 mb-2">
                    <input type="password" class="form-control" id="pass" name="senha" placeholder="Senha">
                    <label for="pass">Senha</label>
                </div>
                <div class="col-12 mb-2">
                    <button type="submit" class="btn btn-info mb-4"><i class="fa-solid fa-user"></i>Entrar</button>
                    <a href="<?php echo BASEURL;?>" class="btn btn-dark mb-4"><i class="fa-solid fa-x"></i> Cancelar</a>
                </div>
                  <a href="google_login.php">Entrar com Google</a>

            </div>
        </form>
    </div>
<?php include (FOOTER_TEMPLATE); ?>
