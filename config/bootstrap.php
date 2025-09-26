<?php
/**
 * Bootstrap del proyecto
 * Carga .env, configura sesión (compatible con HTTP local), prepara MySQLi
 * y deja helpers de JSON, contraseñas, CSRF y limitador simple de peticiones.
 */

declare(strict_types=1);

// Zona horaria por defecto (ajustar si hace falta)
date_default_timezone_set('Europe/Madrid');

// =========================================================
// 1) Variables de entorno (.env) con vlucas/phpdotenv
// =========================================================
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad(); // no peta si falta el .env (útil para dev)

// Versión de build para cache-busting en assets (se puede poner en .env)
if (!defined('BUILD')) {
    // Usa BUILD=.env si existe; si no, cae a una fecha fija que cambiarás al desplegar
    define('BUILD', $_ENV['BUILD'] ?? '20250926');
}

// Modo debug (en dev muestra errores; en prod, silencioso)
$APP_ENV = $_ENV['APP_ENV'] ?? 'prod';
if ($APP_ENV === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

// =========================================================
// 2) Sesión segura (sin romper local sin HTTPS)
// =========================================================
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
);

session_set_cookie_params([
    'lifetime' => 0,                  // cookie de sesión
    'path'     => '/',
    'domain'   => '',                 // por defecto
    'secure'   => $isHttps,           // true en HTTPS, false en HTTP local
    'httponly' => true,               // inaccesible por JS
    'samesite' => 'Lax',              // evita CSRF básico en navegaciones
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// =========================================================
// 3) Conexión MySQLi robusta
// =========================================================
$DB_HOST = $_ENV['DB_HOST'] ?? '127.0.0.1';
$DB_USER = $_ENV['DB_USER'] ?? 'root';
$DB_PASS = $_ENV['DB_PASS'] ?? '';
$DB_NAME = $_ENV['DB_NAME'] ?? 'espacio_liminal';
$DB_PORT = (int)($_ENV['DB_PORT'] ?? 3306);

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($mysqli->connect_errno) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "DB error";
    exit;
}

// Charset UTF-8 completo (incluye emojis)
$mysqli->set_charset('utf8mb4');

// SQL “estricto” para pillar datos raros cuanto antes
$mysqli->query("
  SET SESSION sql_mode =
  'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'
");

// Pequeño helper para acceder a la conexión de forma global controlada
function db(): mysqli {
    /** @var mysqli $mysqli */
    global $mysqli;
    return $mysqli;
}

// =========================================================
// 4) Helpers útiles del lado PHP
// =========================================================

/**
 * Respuesta JSON y salida inmediata (corta ejecución).
 */
function json_out(array $arr, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Hash seguro de contraseña (Argon2id).
 */
function hash_pass(string $plain): string {
    return password_hash($plain, PASSWORD_ARGON2ID);
}

/**
 * Comprobación de contraseña.
 */
function check_pass(string $plain, string $hash): bool {
    return password_verify($plain, $hash);
}

/**
 * CSRF token por sesión. Se genera una vez y se reutiliza.
 * En los formularios POST, incluir un input hidden con este valor.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        // 32 bytes aleatorios → 64 chars hex
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificación de CSRF (levanta 419 si falla).
 */
function csrf_verify(?string $token): void {
    $ok = is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);

    if (!$ok) {
        http_response_code(419);
        json_out(['ok' => false, 'error' => 'CSRF token inválido'], 419);
    }
}

/**
 * Limitador muy simple por clave (ej.: endpoint/login) e IP.
 * Evita abusos básicos: X intentos por ventana de tiempo.
 */
function rate_limiter(string $key, int $limit = 20, int $windowSeconds = 60): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $bucket = "_rl_{$key}_{$ip}";
    $now = time();

    if (!isset($_SESSION[$bucket])) {
        $_SESSION[$bucket] = ['start' => $now, 'count' => 0];
    }

    $winStart = $_SESSION[$bucket]['start'];
    $count    = $_SESSION[$bucket]['count'];

    if (($now - $winStart) > $windowSeconds) {
        // nueva ventana
        $_SESSION[$bucket] = ['start' => $now, 'count' => 0];
        return;
    }

    if ($count >= $limit) {
        http_response_code(429);
        json_out(['ok' => false, 'error' => 'Demasiadas peticiones, inténtalo en un minuto'], 429);
    }

    $_SESSION[$bucket]['count']++;
}

// =========================================================
// 5) Sugerencia: helper de prepared statements (opcional)
// =========================================================
/**
 * Ejecuta un preparado rápido.
 * Ej.: $rows = db_exec('SELECT * FROM users WHERE email=?', 's', [$email]);
 */
function db_exec(string $sql, string $types = '', array $params = []): array {
    $stmt = db()->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}
