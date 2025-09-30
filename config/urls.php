<?php
/**
 * ========================================================================
 * URLs helpers (para que las rutas no me den la brasa ni en local ni en prod)
 * - Lee APP_URL (o APP_BASE) del .env si existen (prioridad máxima).
 * - Si no, detecta esquema/host y subcarpeta automáticamente.
 * - Expone helpers: url(), asset(), redirect(), app_base(), current_url().
 * ========================================================================
 */

declare(strict_types=1);

// wrapper seguro para $_SERVER
function _server(string $key, string $default=''): string {
  return isset($_SERVER[$key]) ? (string)$_SERVER[$key] : $default;
}

// ¿voy por HTTPS? (soporta proxies tipo Cloudflare/Nginx)
function is_https(): bool {
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
  if (_server('SERVER_PORT') === '443') return true;
  if (_server('HTTP_X_FORWARDED_PROTO') === 'https') return true;
  if (_server('HTTP_X_FORWARDED_SSL') === 'on') return true;
  return false;
}

// http/https según toque
function scheme(): string {
  return is_https() ? 'https' : 'http';
}

// host + puerto si es “rarito”
function host_port(): string {
  $host = _server('HTTP_X_FORWARDED_HOST') ?: _server('HTTP_HOST') ?: _server('SERVER_NAME');
  if (!$host) $host = 'localhost';
  $port = (int)(_server('HTTP_X_FORWARDED_PORT') ?: _server('SERVER_PORT') ?: 0);
  $isHttps = is_https();
  $default = $isHttps ? 443 : 80;
  if ($port && $port !== $default && strpos($host, ':') === false) {
    $host .= ':' . $port;
  }
  return $host;
}

// subcarpeta donde vive la web (ej: "/espacio-liminal") o vacío si está en raíz
function app_base(): string {
  if (!empty($_ENV['APP_BASE'])) {
    $b = '/' . ltrim((string)$_ENV['APP_BASE'], '/');
    return rtrim($b, '/');
  }
  $doc  = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\','/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
  $root = str_replace('\\','/', realpath(dirname(__DIR__))); // carpeta del proyecto
  if ($doc && $root && str_starts_with($root, $doc)) {
    $rel = substr($root, strlen($doc)); // ej: "/espacio-liminal"
    return rtrim($rel ?: '', '/');
  }
  return '';
}

// URL base completa (esquema + host + base). Si APP_URL está en .env, uso esa TAL CUAL.
function base_url(): string {
  if (!empty($_ENV['APP_URL'])) {
    return rtrim((string)$_ENV['APP_URL'], '/');
  }
  return scheme() . '://' . host_port() . app_base();
}

// Construye URLs de páginas/rutas
function url(string $path=''): string {
  $b = base_url();
  if ($path === '' || $path === '/') return $b . '/';
  return $b . '/' . ltrim($path, '/');
}

// Assets con cache-busting ?v=BUILD
function asset(string $path): string {
  $u = url($path);
  $hasQuery = str_contains($u, '?');
  $v = defined('BUILD') ? BUILD : date('Ymd');
  return $u . ($hasQuery ? '&' : '?') . 'v=' . $v;
}

// Redirección segura a ruta interna o URL absoluta
function redirect(string $to, int $code=302): void {
  $location = preg_match('~^https?://~i', $to) ? $to : url($to);
  header('Location: ' . $location, true, $code);
  exit;
}

// URL actual útil para canónicas/breadcrumbs
function current_url(): string {
  $uri = _server('REQUEST_URI');
  return base_url() . parse_url($uri, PHP_URL_PATH);
}
