/**
 * Pedacinho de Amor — Sistema de Consentimento de Cookies (LGPD)
 * Gerencia o banner, preferências e salvamento do consentimento
 */

const CookieConsent = {
  COOKIE_NAME: 'pda_cookie_consent',
  COOKIE_EXPIRY_DAYS: 365,

  // Categorias de cookies disponíveis
  categories: {
    essential: {
      label: 'Essenciais',
      description: 'Necessários para o funcionamento do site (sessão, carrinho). Não podem ser desativados.',
      required: true,
      default: true
    },
    preferences: {
      label: 'Preferências',
      description: 'Lembram suas escolhas (ex: itens favoritos, última categoria visitada).',
      required: false,
      default: false
    },
    analytics: {
      label: 'Analíticos',
      description: 'Nos ajudam a entender como o site é usado para melhorá-lo.',
      required: false,
      default: false
    }
  },

  // Lê o consentimento salvo
  getConsent() {
    const raw = this.getCookie(this.COOKIE_NAME);
    if (!raw) return null;
    try {
      return JSON.parse(decodeURIComponent(raw));
    } catch {
      return null;
    }
  },

  // Salva o consentimento como cookie
  saveConsent(preferences) {
    const data = {
      timestamp: new Date().toISOString(),
      version: '1.0',
      preferences
    };
    const value = encodeURIComponent(JSON.stringify(data));
    const expires = new Date();
    expires.setDate(expires.getDate() + this.COOKIE_EXPIRY_DAYS);
    document.cookie = `${this.COOKIE_NAME}=${value}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;

    // Dispara evento customizado para outros scripts saberem
    window.dispatchEvent(new CustomEvent('cookieConsentUpdated', { detail: data }));
    return data;
  },

  // Verifica se uma categoria foi aceita
  isAllowed(category) {
    if (this.categories[category]?.required) return true;
    const consent = this.getConsent();
    if (!consent) return false;
    return consent.preferences?.[category] === true;
  },

  // Utilitário: lê cookie por nome
  getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
  },

  // Inicializa o banner se ainda não houve consentimento
  init() {
    if (!this.getConsent()) {
      this.showBanner();
    }
    // Adiciona link "Gerenciar Cookies" no footer se existir
    this.injectFooterLink();
  },

  // Injeta link no footer
  injectFooterLink() {
    const footer = document.querySelector('footer');
    if (!footer) return;
    if (footer.querySelector('.pda-cookie-link')) return;

    const link = document.createElement('a');
    link.href = '/pedacinhodeamor/cookies/politica_cookies.php';
    link.className = 'pda-cookie-link';
    link.textContent = 'Política de Cookies';
    link.style.cssText = 'display:inline-block; margin-top:8px; color:inherit; text-decoration:underline; font-size:0.85rem; opacity:0.8;';

    const manageBtn = document.createElement('button');
    manageBtn.textContent = 'Gerenciar Cookies';
    manageBtn.className = 'pda-cookie-link';
    manageBtn.style.cssText = 'display:inline-block; margin-left:12px; background:none; border:none; cursor:pointer; color:inherit; text-decoration:underline; font-size:0.85rem; opacity:0.8; padding:0;';
    manageBtn.addEventListener('click', () => this.showBanner(true));

    const wrap = document.createElement('div');
    wrap.style.cssText = 'margin-top:8px;';
    wrap.appendChild(link);
    wrap.appendChild(manageBtn);
    footer.appendChild(wrap);
  },

  // Cria e exibe o banner
  showBanner(forceShow = false) {
    if (document.getElementById('pda-cookie-banner')) return;

    const consent = this.getConsent();
    const prefs = consent?.preferences || {};

    const banner = document.createElement('div');
    banner.id = 'pda-cookie-banner';
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-modal', 'true');
    banner.setAttribute('aria-label', 'Aviso de cookies');

    banner.innerHTML = `
      <div id="pda-cookie-overlay"></div>
      <div id="pda-cookie-box">
        <div id="pda-cookie-header">
          <span id="pda-cookie-icon">🍪</span>
          <div>
            <h2 id="pda-cookie-title">Usamos cookies</h2>
            <p id="pda-cookie-subtitle">Pedacinho de Amor — sua privacidade importa</p>
          </div>
        </div>

        <p id="pda-cookie-desc">
          Utilizamos cookies para garantir o funcionamento do site, lembrar suas preferências 
          e entender como você navega. Em conformidade com a <strong>LGPD (Lei 13.709/2018)</strong>, 
          você pode escolher quais aceita.
        </p>

        <div id="pda-cookie-categories">
          ${Object.entries(this.categories).map(([key, cat]) => `
            <label class="pda-cookie-cat ${cat.required ? 'pda-cookie-cat--required' : ''}">
              <div class="pda-cookie-cat-info">
                <strong>${cat.label}</strong>
                <span>${cat.description}</span>
              </div>
              <div class="pda-toggle-wrap">
                <input 
                  type="checkbox" 
                  id="pda-cat-${key}" 
                  name="${key}"
                  ${cat.required ? 'checked disabled' : (prefs[key] ? 'checked' : '')}
                  class="pda-toggle-input"
                />
                <span class="pda-toggle-track" aria-hidden="true">
                  <span class="pda-toggle-thumb"></span>
                </span>
                ${cat.required ? '<span class="pda-required-tag">Obrigatório</span>' : ''}
              </div>
            </label>
          `).join('')}
        </div>

        <div id="pda-cookie-actions">
          <button id="pda-btn-reject" class="pda-btn pda-btn--outline">Recusar opcionais</button>
          <button id="pda-btn-save" class="pda-btn pda-btn--secondary">Salvar seleção</button>
          <button id="pda-btn-accept" class="pda-btn pda-btn--primary">Aceitar todos</button>
        </div>

        <p id="pda-cookie-footer-note">
          Saiba mais em nossa <a href="/pedacinhodeamor/cookies/politica_cookies.php">Política de Cookies</a>.
        </p>
      </div>
    `;

    document.body.appendChild(banner);
    this.injectStyles();

    // Anima entrada
    requestAnimationFrame(() => {
      banner.classList.add('pda-cookie-visible');
    });

    // Eventos dos botões
    document.getElementById('pda-btn-accept').addEventListener('click', () => {
      const all = {};
      Object.keys(this.categories).forEach(k => all[k] = true);
      this.saveConsent(all);
      this.hideBanner();
    });

    document.getElementById('pda-btn-reject').addEventListener('click', () => {
      const minimal = {};
      Object.keys(this.categories).forEach(k => minimal[k] = this.categories[k].required);
      this.saveConsent(minimal);
      this.hideBanner();
    });

    document.getElementById('pda-btn-save').addEventListener('click', () => {
      const prefs = {};
      Object.keys(this.categories).forEach(k => {
        prefs[k] = this.categories[k].required || 
                   document.getElementById(`pda-cat-${k}`)?.checked || false;
      });
      this.saveConsent(prefs);
      this.hideBanner();
    });

    // Fechar pelo overlay (só se já tinha consentimento antes)
    if (forceShow && consent) {
      document.getElementById('pda-cookie-overlay').addEventListener('click', () => {
        this.hideBanner();
      });
    }
  },

  hideBanner() {
    const banner = document.getElementById('pda-cookie-banner');
    if (!banner) return;
    banner.classList.add('pda-cookie-hiding');
    setTimeout(() => banner.remove(), 400);
  },

  // Injeta CSS do banner
  injectStyles() {
    if (document.getElementById('pda-cookie-styles')) return;
    const style = document.createElement('style');
    style.id = 'pda-cookie-styles';
    style.textContent = `
      /* ===== OVERLAY ===== */
      #pda-cookie-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.45);
        backdrop-filter: blur(3px);
        z-index: 9998;
        opacity: 0;
        transition: opacity 0.35s ease;
      }
      #pda-cookie-banner.pda-cookie-visible #pda-cookie-overlay { opacity: 1; }
      #pda-cookie-banner.pda-cookie-hiding #pda-cookie-overlay { opacity: 0; }

      /* ===== BOX ===== */
      #pda-cookie-box {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        z-index: 9999;
        background: #fff;
        border-top: 3px solid #c8793a;
        border-radius: 18px 18px 0 0;
        padding: 28px 32px 24px;
        max-width: 720px;
        margin: 0 auto;
        box-shadow: 0 -8px 40px rgba(0,0,0,0.18);
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(.22,1,.36,1);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
      #pda-cookie-banner.pda-cookie-visible #pda-cookie-box { transform: translateY(0); }
      #pda-cookie-banner.pda-cookie-hiding #pda-cookie-box { transform: translateY(100%); }

      /* ===== HEADER ===== */
      #pda-cookie-header {
        display: flex; align-items: center; gap: 14px; margin-bottom: 14px;
      }
      #pda-cookie-icon { font-size: 2.2rem; line-height: 1; }
      #pda-cookie-title {
        margin: 0; font-size: 1.2rem; color: #3b1f0e; font-weight: 700;
      }
      #pda-cookie-subtitle {
        margin: 2px 0 0; font-size: 0.8rem; color: #888;
      }

      /* ===== DESC ===== */
      #pda-cookie-desc {
        font-size: 0.9rem; color: #555; margin: 0 0 18px; line-height: 1.6;
      }

      /* ===== CATEGORIAS ===== */
      #pda-cookie-categories { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }

      .pda-cookie-cat {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px;
        background: #fdf7f2;
        border: 1px solid #f0dece;
        border-radius: 10px;
        padding: 12px 14px;
        cursor: pointer;
        transition: background 0.2s;
      }
      .pda-cookie-cat:hover { background: #f9ede0; }
      .pda-cookie-cat--required { cursor: default; opacity: 0.85; }

      .pda-cookie-cat-info { flex: 1; }
      .pda-cookie-cat-info strong { display: block; font-size: 0.9rem; color: #3b1f0e; margin-bottom: 2px; }
      .pda-cookie-cat-info span { font-size: 0.78rem; color: #777; line-height: 1.4; }

      /* ===== TOGGLE ===== */
      .pda-toggle-wrap { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
      .pda-toggle-input { position: absolute; opacity: 0; width: 0; height: 0; }
      .pda-toggle-track {
        display: inline-block; width: 44px; height: 24px;
        background: #ddd; border-radius: 12px;
        position: relative; transition: background 0.25s;
        cursor: pointer;
      }
      .pda-toggle-input:checked + .pda-toggle-track { background: #c8793a; }
      .pda-toggle-input:disabled + .pda-toggle-track { background: #c8793a; opacity: 0.6; cursor: not-allowed; }
      .pda-toggle-thumb {
        position: absolute; top: 3px; left: 3px;
        width: 18px; height: 18px; background: #fff;
        border-radius: 50%; transition: left 0.25s;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
      }
      .pda-toggle-input:checked + .pda-toggle-track .pda-toggle-thumb { left: 23px; }
      .pda-required-tag {
        font-size: 0.7rem; color: #c8793a; font-weight: 600;
        background: #fde8d4; padding: 2px 7px; border-radius: 99px;
      }

      /* ===== BOTÕES ===== */
      #pda-cookie-actions {
        display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;
        margin-bottom: 12px;
      }
      .pda-btn {
        padding: 10px 20px; border-radius: 8px; font-size: 0.88rem;
        font-weight: 600; cursor: pointer; border: 2px solid transparent;
        transition: all 0.2s; white-space: nowrap;
      }
      .pda-btn--primary {
        background: #c8793a; color: #fff; border-color: #c8793a;
      }
      .pda-btn--primary:hover { background: #a85e28; border-color: #a85e28; }
      .pda-btn--secondary {
        background: #3b1f0e; color: #fff; border-color: #3b1f0e;
      }
      .pda-btn--secondary:hover { background: #5a3018; border-color: #5a3018; }
      .pda-btn--outline {
        background: transparent; color: #3b1f0e; border-color: #c8c0b8;
      }
      .pda-btn--outline:hover { background: #f5ede5; }

      /* ===== RODAPÉ ===== */
      #pda-cookie-footer-note {
        font-size: 0.78rem; color: #aaa; text-align: center; margin: 0;
      }
      #pda-cookie-footer-note a { color: #c8793a; }

      /* ===== RESPONSIVO ===== */
      @media (max-width: 600px) {
        #pda-cookie-box { padding: 20px 16px 18px; border-radius: 14px 14px 0 0; }
        #pda-cookie-actions { justify-content: stretch; }
        .pda-btn { flex: 1; text-align: center; }
      }
    `;
    document.head.appendChild(style);
  }
};

// Auto-inicia quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => CookieConsent.init());