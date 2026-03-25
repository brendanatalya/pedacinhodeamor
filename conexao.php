
<?php
//inicio da conexão com o banco de dados utilizando PDO
$host = 'localhost';
$user = 'root';
$pass = "";
$dbname = "bancofull";
//$port = 3306;

try {
    //conexão com a porta 
    //$conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);

    //conexão sem a porta
     $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    //echo "conexão com banco de dados realizada com sucesso";
} catch (PDOException $err) {
    die ("Erro conexão com banco de dados não realizada com sucesso. 
    Erro gerado " . $err->getMessage());
}
    //fim da conexão com o banco de dados utilizando PDO
?>