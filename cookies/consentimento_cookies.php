<?php
/**
 * includes/cookie_helper.php
 * Pedacinho de Amor — Helper para verificar consentimento de cookies (LGPD)
 * 
 * Uso: include_once 'includes/cookie_helper.php';
 *      if (cookie_allowed('analytics')) { ... }
 */

class CookieHelper {

  const COOKIE_NAME = 'pda_cookie_consent';

  /**
   * Retorna o consentimento salvo ou null se não existir
   */
  public static function getConsent(): ?array {
    if (!isset($_COOKIE[self::COOKIE_NAME])) return null;

    $decoded = urldecode($_COOKIE[self::COOKIE_NAME]);
    $data = json_decode($decoded, true);

    if (json_last_error() !== JSON_ERROR_NONE) return null;
    return $data;
  }

  /**
   * Verifica se uma categoria de cookie foi aceita
   * 
   * @param string $category  'essential' | 'preferences' | 'analytics'
   * @return bool
   */
  public static function isAllowed(string $category): bool {
    // Cookies essenciais são sempre permitidos
    if ($category === 'essential') return true;

    $consent = self::getConsent();
    if (!$consent) return false;

    return isset($consent['preferences'][$category]) 
        && $consent['preferences'][$category] === true;
  }

  /**
   * Retorna todas as preferências salvas
   */
  public static function getPreferences(): array {
    $consent = self::getConsent();
    return $consent['preferences'] ?? [
      'essential'   => true,
      'preferences' => false,
      'analytics'   => false
    ];
  }

  /**
   * Verifica se o usuário já deu alguma resposta (aceitar/recusar)
   */
  public static function hasResponded(): bool {
    return self::getConsent() !== null;
  }

  /**
   * Salva um cookie respeitando o consentimento
   * Só salva se a categoria foi aceita (ou se for essencial)
   * 
   * @param string $name      Nome do cookie
   * @param string $value     Valor do cookie
   * @param int    $days      Dias de validade (0 = sessão)
   * @param string $category  Categoria do cookie
   * @return bool  True se salvou, false se não tinha consentimento
   */
  public static function setCookie(
    string $name,
    string $value,
    int $days = 30,
    string $category = 'essential'
  ): bool {
    if (!self::isAllowed($category)) return false;

    $expires = $days > 0 ? time() + ($days * 86400) : 0;
    setcookie($name, $value, [
      'expires'  => $expires,
      'path'     => '/',
      'samesite' => 'Lax',
      'secure'   => isset($_SERVER['HTTPS']),
      'httponly' => false // JS precisa acessar preferências
    ]);
    return true;
  }

  /**
   * Registra uma visualização de página (só se analytics aceito)
   * Pode ser expandido para salvar em banco de dados
   */
  public static function trackPageView(string $page = ''): void {
    if (!self::isAllowed('analytics')) return;

    // Incrementa contador de páginas vistas na sessão
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    if (!isset($_SESSION['pda_page_views'])) {
      $_SESSION['pda_page_views'] = 0;
    }
    $_SESSION['pda_page_views']++;

    // Aqui você pode salvar em banco de dados se quiser
    // Ex: INSERT INTO analytics (page, visited_at, session_id) VALUES (...)
  }
}

// Atalho global
function cookie_allowed(string $category): bool {
  return CookieHelper::isAllowed($category);
}