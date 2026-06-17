<?php


/** O nome do banco de dados */
define("DB_NAME", "pedacinhodeamor");

/** Usuário do banco de dados MySQL */
define('DB_USER', 'root');

/** Senha do banco de dados MySQL */
define('DB_PASSWORD', '');

/** nome do host do MySQL */
define('DB_HOST', 'localhost');

/** DSN para conexão PDO */
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8');

/** caminho absoluto para a pasta do sistema **/
if (!defined('ABSPATH'))
    define('ABSPATH', dirname(__FILE__) . '/');

/** caminho no server para o sistema **/
if (!defined('BASEURL'))
    define('BASEURL', '/pedacinhodeamor/');

/** caminho do arquivo de banco de dados **/
if (!defined('DBAPI'))
    define('DBAPI', ABSPATH . 'inc/database.php');

/** caminhos dos templates de header e footer **/
define('HEADER_TEMPLATE', ABSPATH . 'inc/header.php');
define('FOOTER_TEMPLATE', ABSPATH . 'inc/footer.php');

/** caminhos para o modal do cookie **/
 const COOKIE_TEMPLATE = "inc/cookiemodal.php";
 
/** número WhatsApp da confeitaria (só números, com DDI) **/
define('WHATSAPP_NUMBER', '5515988329726');

/** caminhos para o modal do cookie **/
define('COOKIE_TEMPLATE', ABSPATH . 'cookies/cookiemodal.php');
 

?>