<?php
/**
 * =========================================================================
 * SESSION (capa de autenticación y remember-me)
 * -------------------------------------------------------------------------
 * OJO: Aquí NO definimos csrf_token() ni csrf_verify() para no duplicar.
 *      Eso vive en bootstrap.php (centralizado). Si duplicas → Fatal error.
 *
 * Qué hace este fichero:
 *  - Helpers de auth: auth_user_id(), auth_login(), auth_logout()
 *  - Cookie "remember" persistente (selector + validator rotatorios)
 *  - Auto-login por cookie si no hay sesión y la cookie es válida
 *  - TODO comentadito “a mi rollo”, que luego yo me entiendo
 * =========================================================================
 */

declare(strict_types=1);

// Requisito: siempre cargar bootstrap antes, porque allí se arranca la sesión,
// se conectan DB y viven los helpers base (json_out, db_exec, etc.).
// Si alguien llama a este fichero “a pelo”, lo intento cubrir:
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Sesión debería estar ya abierta por bootstrap; si no, la abrimos
    session_start();
}

// ---------------------------------------------------------
// Pequeños helpers internos (sin chocar con bootstrap)
// ---------------------------------------------------------

// Detección HTTPS (idéntica lógica que en bootstrap, pero sin pisar nombres)
if (!function_exists('_el_https')) {
    function _el_https(): bool {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );
    }
}

// Seteo común de la cookie remember (mismo dominio/path que la de sesión)
if (!function_exists('_el_setcookie')) {
    function _el_setcookie(string $name, string $value, int $expires): void {
        // Reutilizo el secure de acuerdo al entorno; httponly true siempre
        setcookie($name, $value, [
            'expires'  => $expires,
            'path'     => '/',
            'domain'   => '',
            'secure'   => _el_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

// ---------------------------------------------------------
// API de autenticación (funciones públicas que usamos por todo el site)
// ---------------------------------------------------------

// ¿Quién soy? dame el id del user o null
if (!function_exists('auth_user_id')) {
    function auth_user_id(): ?int {
        return isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null;
    }
}

// Login “oficial” (con opción remember)
if (!function_exists('auth_login')) {
    function auth_login(int $uid, bool $remember = false): void {
        // 1) Refuerzo de sesión (regenero id para evitar fijación)
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        // 2) Guardo mi user id en la sesión (clave coherente en todo el proyecto)
        $_SESSION['uid'] = $uid;

        // 3) ¿Remember activado? Pues creo token persistente
        if ($remember) {
            // Genero par selector/validator al estilo “como manda la RFC”:
            //  - selector: identificador público (lo guardo TAL CUAL en DB)
            //  - validator: secreto que viaja en cookie, pero guardo su HASH en DB
            $selector  = bin2hex(random_bytes(9));   // 18 chars hex → cortito pero único
            $validator = bin2hex(random_bytes(32));  // 64 chars hex → gordito
            $hash      = hash('sha256', $validator); // lo que guardo en DB (si roban DB, no pueden validar)

            $expiresAt = (new DateTime('+30 days'))->format('Y-m-d H:i:s');

            // Limpio tokens antiguos del mismo user (opcional, me gusta “1 dispositivo = 1 token”)
            db_exec_nonquery("DELETE FROM auth_tokens WHERE user_id=?", 'i', [$uid]);

            // Inserto el nuevo token
            db_exec_nonquery(
                "INSERT INTO auth_tokens (user_id, selector, validator_hash, expires_at, created_at)
                 VALUES (?,?,?,?,NOW())",
                'issss',
                [$uid, $selector, $hash, $expiresAt, $expiresAt]
            );

            // Y dejo cookie: selector:validator
            $cookieVal = $selector . ':' . $validator;
            _el_setcookie('ELIMINAL_REMEMBER', $cookieVal, time() + 60*60*24*30);
        } else {
            // Si dijeron que NO, me aseguro de borrar cookie si existía
            if (!empty($_COOKIE['ELIMINAL_REMEMBER'])) {
                _el_setcookie('ELIMINAL_REMEMBER', '', time() - 3600);
            }
        }
    }
}

// Logout “de verdad”: sesión fuera + remember fuera (DB + cookie)
if (!function_exists('auth_logout')) {
    function auth_logout(): void {
        // 1) Si hay cookie remember, bórrala y borra su token en DB
        if (!empty($_COOKIE['ELIMINAL_REMEMBER'])) {
            $raw = (string)$_COOKIE['ELIMINAL_REMEMBER'];
            // Cookie con formato selector:validator → me quedo con selector para limpiar
            $parts = explode(':', $raw, 2);
            $selector = $parts[0] ?? '';
            if ($selector !== '') {
                db_exec_nonquery("DELETE FROM auth_tokens WHERE selector=?", 's', [$selector]);
            }
            _el_setcookie('ELIMINAL_REMEMBER', '', time() - 3600);
        }

        // 2) Limpio sesión (variables + cookie + regeneración de id)
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Borro variables de $_SESSION
            $_SESSION = [];

            // Borro cookie de sesión si existe (usamos la firma NUEVA con array options)
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();

                // aquí nada de pasar el 3er parámetro como int; SOLO el array de opciones
                setcookie(session_name(), '', [
                    // expiro en el pasado para que el navegador la quite
                    'expires'  => time() - 42000,
                    // respetamos path/domine/flags originales
                    'path'     => $params['path']     ?? '/',
                    'domain'   => $params['domain']   ?? '',
                    'secure'   => $params['secure']   ?? false,
                    'httponly' => $params['httponly'] ?? true,
                    // samesite moderno; si tu PHP no soporta samesite en session_get_cookie_params, le ponemos 'Lax'
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]);
            }

            // Destruyo la sesión y regenero id por higiene
            session_destroy();
        }


        // 3) Por si acaso (nuevo ciclo)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            session_regenerate_id(true);
        }
    }
}

// ---------------------------------------------------------
// Auto-login por cookie (si no hay sesión pero sí cookie remember válida)
// Esto se ejecuta cada vez que incluyes session.php (que es lo que queremos).
// ---------------------------------------------------------

(function () {
    // Si ya estoy dentro → no hago nada
    if (auth_user_id()) return;

    // Si no hay cookie remember → nada
    $cookie = $_COOKIE['ELIMINAL_REMEMBER'] ?? '';
    if ($cookie === '') return;

    // Formato esperado: selector:validator
    $parts = explode(':', $cookie, 2);
    if (count($parts) !== 2) {
        // Formato raro → limpio cookie y me voy
        _el_setcookie('ELIMINAL_REMEMBER', '', time() - 3600);
        return;
    }
    [$selector, $validator] = $parts;

    // Busco token por selector
    $rows = db_exec("SELECT user_id, validator_hash, expires_at FROM auth_tokens WHERE selector=?", 's', [$selector]);
    $tok  = $rows[0] ?? null;
    if (!$tok) {
        // Selector desconocido → limpio cookie (alguien manipuló o expiró y ya no está)
        _el_setcookie('ELIMINAL_REMEMBER', '', time() - 3600);
        return;
    }

    // ¿Caducado?
    if (strtotime($tok['expires_at']) < time()) {
        // Token caducado: lo elimino y limpio cookie
        db_exec_nonquery("DELETE FROM auth_tokens WHERE selector=?", 's', [$selector]);
        _el_setcookie('ELIMINAL_REMEMBER', '', time() - 3600);
        return;
    }

    // Comparo el hash del validator (lo que guardo) con el hash del validator que viene en la cookie
    $calc = hash('sha256', $validator);
    if (!hash_equals($tok['validator_hash'], $calc)) {
        // No coincide → posible intento de robo → elimino token y cookie (medida defensiva)
        db_exec_nonquery("DELETE FROM auth_tokens WHERE selector=?", 's', [$selector]);
        _el_setcookie('ELIMINAL_REMEMBER', '', time() - 3600);
        return;
    }

    // Todo OK → hago login silencioso
    $uid = (int)$tok['user_id'];
    // Rotación de validator (para que un token robado deje de valer tras primer uso)
    $newValidator = bin2hex(random_bytes(32));
    $newHash      = hash('sha256', $newValidator);
    $newCookie    = $selector . ':' . $newValidator;

    db_exec_nonquery("UPDATE auth_tokens SET validator_hash=?, expires_at=DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE selector=?", 'ss', [$newHash, $selector]);
    _el_setcookie('ELIMINAL_REMEMBER', $newCookie, time() + 60*60*24*30);

    // Y ya dejo sesión armada
    $_SESSION['uid'] = $uid;
})();
