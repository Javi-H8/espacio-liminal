<?php
/**
 * ========================================================================
 * Bootstrap del proyecto (sí, el “arranque” de toda la app)
 * - Carga .env (Dotenv)
 * - Configura errores según APP_ENV (dev/prod)
 * - Abre sesión con cookies seguras (pero sin romper en HTTP local)
 * - Conecta a MySQLi (utf8mb4, SQL estricto)
 * - Helpers útiles (json_out, csrf, rate_limiter, db_exec, etc.)
 * - NUEVO: Cargo helpers de URLs (url(), asset(), redirect(), app_base())
 * ========================================================================
 */

declare(strict_types=1);

// 0) Zona horaria (yo aquí Europa/Madrid, si cambias de país… pues lo cambias)
date_default_timezone_set('Europe/Madrid');

// 1) .env con vlucas/phpdotenv (si está instalado vía Composer)
$root = __DIR__ . '/../';
if (is_file($root . 'vendor/autoload.php')) {
    require_once $root . 'vendor/autoload.php';
    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = \Dotenv\Dotenv::createImmutable($root);
        $dotenv->safeLoad();
    }
}

// Constantes útiles (si ya existen, ni caso)
if (!defined('BUILD'))   define('BUILD', $_ENV['BUILD'] ?? 'dev-' . date('YmdHis'));
if (!defined('APP_ENV')) define('APP_ENV', $_ENV['APP_ENV'] ?? 'dev');

//   1.1) Helpers de URLs (para no volver a hardcodear rutas en la vida)
// - Aquí nacen url(), asset(), redirect(), app_base(), etc.
// - Usa APP_URL del .env si la pones (recomendado en producción).
require_once __DIR__ . '/urls.php';

// Errores: en dev lo quiero TODO, en prod los callo
if (APP_ENV === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');   // en dev quiero ver hasta los pelos
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');   // en prod, silencio elegante
}

// Helper rapidito porque me conozco:
function is_dev(): bool { return APP_ENV === 'dev'; }

// ------------------------------------------------------------------------
// 2) Sesión segura (sin romper el desarrollo en HTTP)
// ------------------------------------------------------------------------
// OJO: aquí estaba el bug gordo: usabas "|" (OR a nivel de bits). Tiene que ser "||" (OR lógico)
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
);

//  Ruta-base de la cookie de sesión: importantísimo cuando sirvo en subcarpeta.
// - Si tengo app_base() (gracias a urls.php), la uso.
// - Si no, me quedo con '/' para local/raíz.
// - En tu caso del hosting rf.gd, app_base() será '/espacio-liminal' y así la cookie no se “escapa”.
$cookiePath = '/';
if (function_exists('app_base')) {
    $cookiePath = app_base() ?: '/';
}

// session.cookie_secure = true solo si voy con HTTPS; en local http se pone false o reviento
session_set_cookie_params([
    'lifetime' => 0,            // cookie de sesión (hasta cerrar navegador)
    'path'     => $cookiePath,  // ← clave si la web vive en subcarpeta
    'domain'   => '',           // por defecto (sirve para localhost y host normal)
    'secure'   => $isHttps,     // true en HTTPS; false en HTTP local (sí, intencionado)
    'httponly' => true,         // que JS no me la toquetee
    'samesite' => 'Lax',        // CSRF básico para navegaciones normales
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('ELIMINAL_SESSID');
    session_start();
}

// ------------------------------------------------------------------------
// 3) MySQLi (conexión decente) — credenciales del .env
// ------------------------------------------------------------------------
$DB_HOST = $_ENV['DB_HOST'] ?? '127.0.0.1';
$DB_PORT = (int)($_ENV['DB_PORT'] ?? 3306);
$DB_NAME = $_ENV['DB_NAME'] ?? 'espacio_liminal';
$DB_USER = $_ENV['DB_USER'] ?? 'root';
$DB_PASS = $_ENV['DB_PASS'] ?? '';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli->connect_errno) {
    // Si peta la conexión, en dev digo el porqué; en prod, mensaje blandito
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    echo is_dev()
        ? "DB error ({$mysqli->connect_errno}): {$mysqli->connect_error}"
        : "DB error";
    exit;
}

// Charset modernito (emojis y lo que tú quieras)
$mysqli->set_charset('utf8mb4');

// SQL estricto para pillar rarezas a tiempo
$mysqli->query("SET SESSION sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

// Helper para acceder a la conexión en cualquier parte (pero controladito)
function db(): mysqli {
    /** @var mysqli $mysqli */
    global $mysqli;
    return $mysqli;
}

// ------------------------------------------------------------------------
// 4) Helpers útiles del lado PHP
// ------------------------------------------------------------------------

/** Saco JSON y me piro (como siempre hago) */
function json_out(array $arr, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    echo json_encode($arr, $flags);
    exit;
}

/** Hash de contraseña decente (Argon2id) */
function hash_pass(string $plain): string {
    return password_hash($plain, PASSWORD_ARGON2ID);
}

/** Verificación clásica */
function check_pass(string $plain, string $hash): bool {
    return password_verify($plain, $hash);
}

/** CSRF token por sesión (reuso el mismo en todos los POST/JSON) */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 64 chars
    }
    return $_SESSION['csrf_token'];
}
function csrf_verify(?string $token): void {
    if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        json_out(['ok'=>false, 'error'=>'CSRF inválido'], 400);
    }
}

/** Mini rate-limiter (por si lo quieres en login/register) */
function too_many_attempts(string $bucket, int $max, int $seconds): bool {
    $key = "rl_{$bucket}";
    $now = time();
    $arr = $_SESSION[$key] ?? [];
    // limpio
    $arr = array_filter($arr, fn($t) => ($now - $t) < $seconds);
    if (count($arr) >= $max) { $_SESSION[$key] = $arr; return true; }
    $arr[] = $now; $_SESSION[$key] = $arr; return false;
}

/** db_exec: SELECTs con prepare/params como dios manda */
function db_exec(string $sql, string $types = '', array $params = []): array {
    $stmt = db()->prepare($sql);
    if (!$stmt) {
        if (is_dev()) error_log('db_exec prepare error: ' . db()->error);
        return [];
    }
    if ($types && $params) $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        if (is_dev()) error_log('db_exec execute error: ' . $stmt->error);
        $stmt->close(); return [];
    }
    $res  = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

/** Para INSERT/UPDATE/DELETE cuando quieres afectadas e id */
function db_exec_nonquery(string $sql, string $types = '', array $params = []): array {
    $stmt = db()->prepare($sql);
    if (!$stmt) { if (is_dev()) error_log('db_exec_nonquery prepare error: ' . db()->error); return ['ok'=>false,'affected'=>0,'id'=>0]; }
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $ok = $stmt->execute();
    $info = ['ok'=>$ok, 'affected'=>$stmt->affected_rows, 'id'=>$stmt->insert_id];
    if (!$ok && is_dev()) error_log('db_exec_nonquery execute error: ' . $stmt->error);
    $stmt->close();
    return $info;
}
