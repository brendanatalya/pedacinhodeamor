<?php
session_start();

// Importa config e o database
require_once '../../config.php';
require_once DBAPI;

// Headers
header('Content-Type: application/json; charset=utf-8');

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Método não permitido']));
}

// Valida email
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(200); // Retorna sucesso mesmo assim (segurança)
    exit(json_encode(['success' => true]));
}

try {
    $database = open_database();

    // 1. Verifica se o email existe no banco
    $sql = $database->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $sql->execute([$email]);
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    // Segurança: retorna sucesso mesmo se email não existir (evita enumeração de usuários)
    if (!$usuario) {
        http_response_code(200);
        exit(json_encode(['success' => true]));
    }

    // 2. Gera token único (32 bytes = 64 caracteres em hex)
    $token = bin2hex(random_bytes(32));
    $expiracao = date('Y-m-d H:i:s', strtotime('+2 hours')); // 2 horas de validade

    // 3. Salva token no banco
    $update = $database->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expiracao = ? WHERE id = ?");
    $update->execute([$token, $expiracao, $usuario['id']]);

    close_database($database);

    // 4. Prepara PHPMailer
    require_once 'PHPMailer/Exception.php';
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mail = new PHPMailer(true);

    // Configure usando variáveis de ambiente ou constantes do config.php
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST ?? 'smtp.gmail.com';  // Ex: smtp.gmail.com
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER ?? '';  // Ex: seu-email@gmail.com
    $mail->Password   = SMTP_PASS ?? '';  // Senha de app do Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Remetente
    $mail->setFrom(SMTP_USER ?? 'noreply@pedacinhodeamor.com', 'Pedacinho de Amor');
    $mail->addAddress($email);

    // Monta o link de recuperação
    $linkRecuperacao = BASEURL . 'paginas/redefinir_senha.php?token=' . urlencode($token);

    // 5. Conteúdo do email
    $mail->isHTML(true);
    $mail->Subject = '🔑 Recuperação de Senha - Pedacinho de Amor';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; color: #333;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h2 style='color: #7a2f2f; margin: 0;'>Recuperação de Senha</h2>
            </div>
            
            <p>Oi!</p>
            <p>Recebemos uma solicitação para redefinir sua senha na <strong>Pedacinho de Amor</strong>.</p>
            
            <p style='margin: 30px 0;'>
                <a href='{$linkRecuperacao}' style='display: inline-block; background: #f5a623; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: bold;'>
                    Redefinir Senha
                </a>
            </p>
            
            <p style='font-size: 0.9rem; color: #666;'>
                <strong>Ou copie e cole este link no seu navegador:</strong><br>
                <code style='background: #f5f5f5; padding: 5px; border-radius: 3px;'>$linkRecuperacao</code>
            </p>
            
            <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
            
            <p style='font-size: 0.85rem; color: #999;'>
                ⏰ <strong>Este link é válido por 2 horas.</strong><br>
                Se você não solicitou esta alteração, ignore este e-mail e sua senha permanecerá a mesma.
            </p>
            
            <p style='text-align: center; font-size: 0.85rem; color: #999; margin-top: 30px;'>
                Pedacinho de Amor 💝
            </p>
        </div>
    ";

    $mail->send();
    
    http_response_code(200);
    exit(json_encode(['success' => true]));

} catch (Exception $e) {
    // Log do erro (não expõe ao usuário)
    error_log('Erro ao enviar email de recuperação: ' . $e->getMessage());
    
    // Retorna sucesso mesmo assim (segurança)
    http_response_code(200);
    exit(json_encode(['success' => true]));
}