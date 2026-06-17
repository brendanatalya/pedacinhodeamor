<?php
if (!isset($_SESSION)) session_start();
require_once '../config.php';
require_once ABSPATH . 'inc/database.php';

$usuario_logado = !empty($_SESSION['logado']) && $_SESSION['logado'] === true;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - Pedacinho de Amor</title>
    <link rel="icon" type="image/x-icon" href="../imagens/icon.png">
    <link rel="stylesheet" href="../css_pda/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css_pda/style_pda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .politica-container {
            max-width: 860px;
            margin: 3rem auto;
            padding: 0 1.5rem 4rem;
        }
        .politica-container h1 {
            font-size: 2rem;
            margin-bottom: 0.25rem;
        }
        .politica-container .atualizado {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 2.5rem;
            display: block;
        }
        .politica-container h2 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 0.5rem;
            color: #7a2f2f;
        }
        .politica-container p,
        .politica-container li {
            line-height: 1.8;
            color: #444;
        }
        .politica-container ul {
            padding-left: 1.4rem;
        }
        .politica-container a {
            color: #7a2f2f;
        }
    </style>
</head>
<body>

    <?php include_once ABSPATH . 'inc/header.php'; ?>

    <main>
        <div class="politica-container">

            <h1>Política de Privacidade</h1>
            <span class="atualizado">Última atualização: <?php echo date('d/m/Y'); ?></span>

            <p>
                A <strong>Pedacinho de Amor</strong> valoriza a privacidade dos seus clientes e está comprometida
                em proteger os dados pessoais coletados em nosso site, em conformidade com a
                <strong>Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018)</strong>.
            </p>

            <h2>1. Quais dados coletamos</h2>
            <p>Podemos coletar os seguintes dados pessoais ao utilizar nosso site:</p>
            <ul>
                <li>Nome completo</li>
                <li>Endereço de e-mail</li>
                <li>Número de telefone</li>
                <li>Endereço para entrega</li>
                <li>Dados de navegação (páginas visitadas, tempo de acesso)</li>
            </ul>

            <h2>2. Como usamos seus dados</h2>
            <p>Os dados coletados são utilizados exclusivamente para:</p>
            <ul>
                <li>Processar e entregar seus pedidos</li>
                <li>Entrar em contato sobre o status do seu pedido via WhatsApp</li>
                <li>Melhorar a experiência de navegação no site</li>
                <li>Cumprir obrigações legais</li>
            </ul>

            <h2>3. Cookies</h2>
            <p>
                Nosso site utiliza cookies para garantir o funcionamento correto das funcionalidades,
                como manter sua sessão ativa e lembrar itens no carrinho. Ao aceitar os cookies,
                você concorda com o uso descrito nesta política.
            </p>
            <p>Os cookies utilizados são:</p>
            <ul>
                <li><strong>Cookies estritamente necessários:</strong> essenciais para o funcionamento do site, como autenticação e carrinho de compras.</li>
                <li><strong>Cookies de funcionalidade:</strong> armazenam suas preferências de navegação.</li>
            </ul>
            <p>
                Você pode recusar o uso de cookies a qualquer momento clicando em "Recusar" no banner
                de consentimento exibido ao acessar o site. Note que recusar cookies pode afetar
                algumas funcionalidades.
            </p>

            <h2>4. Compartilhamento de dados</h2>
            <p>
                Não vendemos, alugamos nem compartilhamos seus dados pessoais com terceiros para
                fins comerciais. Seus dados podem ser compartilhados apenas quando exigido por lei
                ou autoridade competente.
            </p>

            <h2>5. Por quanto tempo guardamos seus dados</h2>
            <p>
                Seus dados são mantidos pelo tempo necessário para cumprir as finalidades descritas
                nesta política ou conforme exigido pela legislação vigente.
            </p>

            <h2>6. Seus direitos como titular</h2>
            <p>Conforme a LGPD, você tem direito a:</p>
            <ul>
                <li>Confirmar a existência de tratamento dos seus dados</li>
                <li>Acessar seus dados pessoais</li>
                <li>Corrigir dados incompletos ou desatualizados</li>
                <li>Solicitar a exclusão dos seus dados</li>
                <li>Revogar o consentimento a qualquer momento</li>
            </ul>
            <p>
                Para exercer qualquer um desses direitos, entre em contato conosco pelo e-mail
                <a href="mailto:ola@pedacinhodeamor.com.br">ola@pedacinhodeamor.com.br</a>
                ou pelo WhatsApp <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank">(15) 9 8832-9726</a>.
            </p>

            <h2>7. Segurança</h2>
            <p>
                Adotamos medidas técnicas para proteger seus dados contra acesso não autorizado,
                alteração, divulgação ou destruição. As senhas são armazenadas de forma criptografada
                e as comunicações são realizadas de forma segura.
            </p>

            <h2>8. Alterações nesta política</h2>
            <p>
                Esta política pode ser atualizada periodicamente. Recomendamos que você a revise
                regularmente. A data da última atualização está indicada no topo desta página.
            </p>

            <h2>9. Contato</h2>
            <p>
                Em caso de dúvidas sobre esta Política de Privacidade, entre em contato:
            </p>
            <ul>
                <li><strong>E-mail:</strong> <a href="mailto:ola@pedacinhodeamor.com.br">ola@pedacinhodeamor.com.br</a></li>
                <li><strong>WhatsApp:</strong> <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank">(15) 9 8832-9726</a></li>
                <li><strong>Endereço:</strong> Rua das Flores, 123 — Centro, São Paulo/SP</li>
            </ul>

        </div>
    </main>

    <?php include_once ABSPATH . 'inc/footer.php'; ?>

</body>
</html>