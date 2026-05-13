<?php 
    include ("../config.php");
    require_once(DBAPI);
    include (HEADER_TEMPLATE);
    //verifica se houve post e se o usuario ou a senha sao vazios
    if(!empty($_POST) AND (empty($_POST['login']) OR empty($_POST['senha']))){
        header("Location: ". BASEURL . "index.php"); //redireciona o usuario para a pagina de login
        exit;
    }

    //tenta se conectar a um banco de dados

    //$bd = open_database();
    try {
        //selecionando o bd, caso ele nao consiga

        //$bd -> select_db(DB_NAME);

        //pegando o login e senha do form
        $usuario = $_POST['login'];
        $senha = $_POST['senha'];

        validacao($usuario, $senha);
        //vendo se nao estao vazios
        /*
        if (!empty($usuario) AND !empty($senha)) {
            //criptografando a senha para comparar com o banco
            //$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

            //validação de usuario e senha

            $sql = "SELECT id, nome, user, password FROM usuarios WHERE user = '" . $usuario . "' LIMIT 1";
            $query = $bd -> query($sql);
            
            if ($query->num_rows > 0) {
                //coletando os dados
                $dados = $query-> fetch_assoc();
                echo "<b>";
                //var_dump($dados);
                echo "</b>";
                $id = $dados['id'];
                $nome = $dados['nome'];
                $user = $dados['user'];
                $password = $dados['password'];
                //var_dump($user);

                //verifica se o user nao esta vazio
                if(password_verify($senha, $password)){
                        if(!isset($_SESSION)) session_start();
                        $_SESSION['message'] = "Bem vindo " . $nome . "!";
                        $_SESSION['type'] = "info";
                        $_SESSION['id'] = $id;
                        $_SESSION['nome'] = $nome;
                        $_SESSION['user'] = $user;
                        echo "<b>";
                        //var_dump($user);
                        echo "</b>";
                }
                else {
                    throw new Exception("Não foi possivel se conectar!<br>Verifique seu usuario e senha!");
                }
                header("Location: ". BASEURL . "index.php"); //redireciona o usuario para a pagina de login
            } else {
                throw new Exception("Não foi possivel se conectar!<br>Verifique seu usuario e senha!");
            }
        } else {
            throw new Exception("Não foi possivel se conectar!<br>Verifique seu usuario e senha!");
        }*/
    } catch (Exception $e) {
        $_SESSION['message'] = "Ocorreu um erro: " . $e->getMessage();
        $_SESSION['type'] = 'danger';
    }
?>

<?php if (!empty($_SESSION['message'])) : ?>
    <div class="alert alert-<?php echo $_SESSION['type']; ?> alert-dismissible" role="alert" id="actions">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>        
    </div>
    <?php 
        clear_messages();
    ?>
<?php endif; ?>
<header>
    <a href="<?php echo BASEURL?>index.php" class="btn btn-dark"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</header>

<?php include (FOOTER_TEMPLATE); ?>