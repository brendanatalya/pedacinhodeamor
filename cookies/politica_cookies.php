<?php
require_once '../config.php';
require_once DBAPI;
include(HEADER_TEMPLATE);
?>

<!-- HERO -->
<section class="cookies-hero">
  <div class="hero-badge">🔒 LGPD — Lei 13.709/2018</div>
  <h1>Política de Cookies</h1>
  <p>Última atualização: <?= date('d/m/Y') ?></p>
</section>

<style>
  :root {
    --brand-primary: #c8793a;
    --brand-dark:    #3b1f0e;
    --brand-light:   #fdf7f2;
    --text-muted:    #6b5344;
    --border:        #f0dece;
    --radius:        14px;
  }

  .cookies-hero {
    background: linear-gradient(135deg, #3b1f0e 0%, #6b3a1f 60%, #c8793a 100%);
    color: #fff;
    padding: 64px 24px 48px;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .cookies-hero::before {
    content: '🍪';
    position: absolute;
    font-size: 180px;
    opacity: 0.06;
    top: -20px; right: -20px;
    line-height: 1;
  }
  .cookies-hero h1 {
    font-size: clamp(1.8rem, 5vw, 2.8rem);
    font-weight: 800;
    margin: 0 0 10px;
  }
  .cookies-hero p { font-size: 1rem; opacity: 0.8; margin: 0; }
  .cookies-hero .hero-badge {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    padding: 4px 14px;
    border-radius: 99px;
    font-size: 0.8rem;
    margin-bottom: 16px;
  }

  .cookies-page {
    max-width: 860px;
    margin: 0 auto;
    padding: 48px 24px 80px;
  }

  .cookies-section {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    box-shadow: 0 2px 12px rgba(59,31,14,0.04);
  }
  .cookies-section h2 {
    font-size: 1.1rem;
    color: var(--brand-dark);
    font-weight: 700;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .cookies-section h2 .section-icon {
    width: 32px; height: 32px;
    background: var(--brand-light);
    border: 1px solid var(--border);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
  }
  .cookies-section p, .cookies-section li {
    font-size: 0.92rem;
    color: var(--text-muted);
    line-height: 1.75;
  }
  .cookies-section ul { padding-left: 18px; margin: 8px 0 0; }

  .cookies-table-wrap {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid var(--border);
    margin-top: 12px;
  }
  .cookies-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
  .cookies-table thead { background: var(--brand-light); }
  .cookies-table th {
    text-align: left; padding: 10px 14px;
    color: var(--brand-dark); font-weight: 700;
    font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border);
  }
  .cookies-table td {
    padding: 10px 14px; color: var(--text-muted);
    border-bottom: 1px solid #faeee4; vertical-align: top;
  }
  .cookies-table tr:last-child td { border-bottom: none; }
  .cookies-table tr:hover td { background: #fdf7f2; }

  .badge {
    display: inline-block; padding: 2px 10px;
    border-radius: 99px; font-size: 0.72rem; font-weight: 700; white-space: nowrap;
  }
  .badge--essential  { background: #d4edda; color: #155724; }
  .badge--prefs      { background: #fff3cd; color: #856404; }
  .badge--analytics  { background: #cce5ff; color: #004085; }

  .manage-prefs-box {
    background: linear-gradient(135deg, var(--brand-light) 0%, #fff0e4 100%);
    border: 2px solid var(--brand-primary);
    border-radius: var(--radius);
    padding: 28px 32px;
    text-align: center;
    margin-bottom: 24px;
  }
  .manage-prefs-box h2 { color: var(--brand-dark); font-size: 1.1rem; margin: 0 0 8px; }
  .manage-prefs-box p  { color: var(--text-muted); font-size: 0.9rem; margin: 0 0 18px; }
  .btn-manage {
    display: inline-block;
    background: var(--brand-primary); color: #fff;
    padding: 12px 28px; border-radius: 8px;
    font-weight: 700; font-size: 0.9rem;
    text-decoration: none; border: none; cursor: pointer;
    transition: background 0.2s;
  }
  .btn-manage:hover { background: #a85e28; }

  .lgpd-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: #e8f4fd; border: 1px solid #b8d9f0;
    color: #004085; padding: 8px 16px; border-radius: 8px;
    font-size: 0.82rem; font-weight: 600; margin-bottom: 12px;
  }

  @media (max-width: 600px) {
    .cookies-section { padding: 20px 16px; }
    .manage-prefs-box { padding: 20px 16px; }
  }
</style>

<main class="cookies-page">

  <!-- GERENCIAR PREFERÊNCIAS -->
  <div class="manage-prefs-box">
    <h2>⚙️ Gerencie suas preferências</h2>
    <p>Você pode alterar suas escolhas de cookies a qualquer momento.</p>
    <button class="btn-manage" onclick="CookieConsent.showBanner(true);">
      Abrir painel de cookies
    </button>
  </div>

  <!-- O QUE SÃO COOKIES -->
  <div class="cookies-section">
    <h2><span class="section-icon">❓</span> O que são cookies?</h2>
    <p>
      Cookies são pequenos arquivos de texto armazenados no seu navegador quando você visita um site.
      Eles permitem que o site lembre informações sobre sua visita, tornando a experiência mais eficiente e personalizada.
    </p>
    <p>
      No <strong>Pedacinho de Amor</strong>, utilizamos cookies para garantir o funcionamento correto do site,
      lembrar seu carrinho de compras e entender melhor como nossos clientes utilizam a plataforma.
    </p>
  </div>

  <!-- TABELA DE COOKIES -->
  <div class="cookies-section">
    <h2><span class="section-icon">📋</span> Cookies que utilizamos</h2>
    <div class="cookies-table-wrap">
      <table class="cookies-table">
        <thead>
          <tr>
            <th>Nome</th><th>Tipo</th><th>Duração</th><th>Finalidade</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>PHPSESSID</code></td>
            <td><span class="badge badge--essential">Essencial</span></td>
            <td>Sessão</td>
            <td>Mantém a sessão do usuário ativa durante a navegação e no processo de compra.</td>
          </tr>
          <tr>
            <td><code>pda_cookie_consent</code></td>
            <td><span class="badge badge--essential">Essencial</span></td>
            <td>1 ano</td>
            <td>Salva suas preferências de consentimento de cookies (LGPD).</td>
          </tr>
          <tr>
            <td><code>pda_last_category</code></td>
            <td><span class="badge badge--prefs">Preferências</span></td>
            <td>30 dias</td>
            <td>Lembra a última categoria de produtos visitada para facilitar a navegação.</td>
          </tr>
          <tr>
            <td><code>pda_favorites</code></td>
            <td><span class="badge badge--prefs">Preferências</span></td>
            <td>30 dias</td>
            <td>Salva os produtos marcados como favoritos pelo usuário.</td>
          </tr>
          <tr>
            <td><code>pda_page_views</code></td>
            <td><span class="badge badge--analytics">Analítico</span></td>
            <td>30 dias</td>
            <td>Conta o número de páginas visitadas para análise de uso do site.</td>
          </tr>
          <tr>
            <td><code>pda_session_start</code></td>
            <td><span class="badge badge--analytics">Analítico</span></td>
            <td>Sessão</td>
            <td>Registra o início da sessão para análise de tempo de visita.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- BASE LEGAL -->
  <div class="cookies-section">
    <h2><span class="section-icon">⚖️</span> Base legal (LGPD)</h2>
    <div class="lgpd-badge">
      📄 Lei Geral de Proteção de Dados — Lei nº 13.709, de 14 de agosto de 2018
    </div>
    <p>O tratamento de dados pessoais por meio de cookies neste site se baseia em:</p>
    <ul>
      <li><strong>Legítimo interesse (Art. 7º, IX):</strong> Cookies essenciais são necessários para o funcionamento do site e execução do contrato de compra.</li>
      <li><strong>Consentimento (Art. 7º, I):</strong> Cookies de preferências e analíticos são ativados somente mediante sua autorização expressa.</li>
    </ul>
  </div>

  <!-- SEUS DIREITOS -->
  <div class="cookies-section">
    <h2><span class="section-icon">🛡️</span> Seus direitos</h2>
    <p>Como titular de dados, conforme a LGPD, você tem direito a:</p>
    <ul>
      <li>Confirmar a existência de tratamento de seus dados pessoais;</li>
      <li>Acessar os dados que possuímos sobre você;</li>
      <li>Corrigir dados incompletos, inexatos ou desatualizados;</li>
      <li>Solicitar a eliminação dos dados tratados com base no seu consentimento;</li>
      <li>Revogar o consentimento a qualquer momento.</li>
    </ul>
    <p>
      Para exercer esses direitos, entre em contato:
      <a href="mailto:ola@pedacinhodeamor.com.br" style="color: var(--brand-primary);">ola@pedacinhodeamor.com.br</a>
    </p>
  </div>

  <!-- COMO DESATIVAR -->
  <div class="cookies-section">
    <h2><span class="section-icon">🔧</span> Como desativar cookies no navegador</h2>
    <p>Além do nosso painel de preferências, você pode configurar seu navegador. Note que desativar cookies essenciais pode afetar o funcionamento do site:</p>
    <ul>
      <li><strong>Chrome:</strong> Configurações → Privacidade e segurança → Cookies</li>
      <li><strong>Firefox:</strong> Opções → Privacidade e Segurança → Cookies e dados do site</li>
      <li><strong>Safari:</strong> Preferências → Privacidade → Gerenciar dados do site</li>
      <li><strong>Edge:</strong> Configurações → Privacidade, pesquisa e serviços → Cookies</li>
    </ul>
  </div>

  <!-- CONTATO -->
  <div class="cookies-section">
    <h2><span class="section-icon">📬</span> Contato</h2>
    <p>Em caso de dúvidas sobre esta política:</p>
    <ul>
      <li><strong>E-mail:</strong> ola@pedacinhodeamor.com.br</li>
      <li><strong>Endereço:</strong> Sorocaba, São Paulo — Brasil</li>
    </ul>
  </div>

</main>

<?php include '../inc/modal.php'; ?>
<?php include(FOOTER_TEMPLATE); ?>

<script src="<?php echo BASEURL; ?>js/cookies.js"></script>