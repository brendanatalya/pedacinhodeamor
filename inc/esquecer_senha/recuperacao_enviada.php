<?php
session_start();
include '../../config.php';
include HEADER_TEMPLATE;

// Se chegou aqui sem dados, redireciona para home
if (!isset($_GET['email'])) {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

$email = htmlspecialchars($_GET['email']);
$email_oculto = substr($email, 0, 3) . '***' . substr(strrchr($email, '@'), 0);
?>

<div style="min-height: 60vh; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
    <div style="width: 100%; max-width: 500px; padding: 20px;">

        <div style="background: #e0f4de; border: 1px solid #2e6930; border-radius: 8px; padding: 40px 30px; text-align: center;">
            
            <div style="font-size: 3rem; margin-bottom: 20px;">📧</div>
            
            <h2 style="color: #2e6930; margin-bottom: 10px;">Verifique seu e-mail!</h2>
            
            <p style="color: #555; margin-bottom: 20px; font-size: 1rem;">
                Enviamos as instruções de recuperação de senha para:<br>
                <strong><?php echo $email_oculto; ?></strong>
            </p>

            <div style="background: #fff; border: 1px solid #d4edda; border-radius: 6px; padding: 20px; margin-bottom: 25px; text-align: left; font-size: 0.95rem; color: #555;">
                <p style="margin: 0 0 12px 0;"><strong>📍 O que fazer agora:</strong></p>
                <ol style="margin: 0; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Abra seu e-mail</li>
                    <li style="margin-bottom: 8px;">Procure pela mensagem da <strong>Pedacinho de Amor</strong></li>
                    <li style="margin-bottom: 8px;">Clique no botão "Redefinir Senha" ou copie o link</li>
                    <li>Escolha uma nova senha segura (mínimo 8 caracteres)</li>
                </ol>
            </div>

            <p style="font-size: 0.85rem; color: #666; margin-bottom: 20px;">
                ⏰ O link é válido por <strong>2 horas</strong>
            </p>

            <div style="border-top: 1px solid #d4edda; padding-top: 20px;">
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px;">
                    Não recebeu o e-mail? Verifique sua pasta de spam ou:
                </p>
                <button onclick="solicitarNovamente()" class="btn-enviar" style="background: #f5a623; border: none; cursor: pointer;">
                    Enviar Novamente
                </button>
            </div>

            <p style="font-size: 0.85rem; color: #999; margin-top: 25px;">
                Voltou lembrada da senha? <a href="<?php echo BASEURL; ?>index.php" style="color: #f5a623; text-decoration: none;">Fazer login</a>
            </p>

        </div>

    </div>
</div>

<script>
function solicitarNovamente() {
    const email = '<?php echo addslashes($email); ?>';
    
    if (!email) {
        alert('E-mail não informado');
        return;
    }

    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    fetch('<?php echo BASEURL; ?>inc/esquecer_senha/esqueceu_senha.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        btn.textContent = '✓ E-mail enviado!';
        btn.style.background = '#2e6930';
        setTimeout(() => {
            btn.disabled = false;
            btn.textContent = 'Enviar Novamente';
            btn.style.background = '#f5a623';
        }, 3000);
    })
    .catch(error => {
        console.error('Erro:', error);
        btn.disabled = false;
        btn.textContent = 'Enviar Novamente';
        alert('Erro ao enviar. Tente novamente.');
    });
}
</script>

<?php include FOOTER_TEMPLATE; ?>