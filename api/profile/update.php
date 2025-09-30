<?php
/**
 * api/profile/update.php
 * ---------------------------------------------------------------
 * Endpoint único para editar perfil:
 *   action: name|phone|gender|locale|category|password
 * Toca la tabla users con UPDATE ... WHERE id=? (o sea, base de datos real).
 * Devuelve SIEMPRE JSON (nada de HTML que rompa el front).
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
header('Content-Type: application/json; charset=utf-8');

$uid = auth_user_id();
if (!$uid) json_out(['ok'=>false, 'error'=>'No autorizado'], 401);

$in = json_decode(file_get_contents('php://input'), true) ?? [];
csrf_verify($in['csrf'] ?? null);

$action = (string)($in['action'] ?? '');
$now    = date('Y-m-d H:i:s');

try {
  switch ($action) {
    case 'name': {
      $name = trim((string)($in['name'] ?? ''));
      if (mb_strlen($name) < 2 || mb_strlen($name) > 120) throw new RuntimeException('El nombre debe tener 2–120 caracteres', 400);
      $ok = db_exec_nonquery("UPDATE users SET name=?, updated_at=? WHERE id=?", 'ssi', [$name, $now, $uid])['ok'];
      if (!$ok) throw new RuntimeException('No se pudo guardar', 500);
      json_out(['ok'=>true]);
    }

    case 'phone': {
      $phone = trim((string)($in['phone'] ?? ''));
      if (mb_strlen($phone) > 30) throw new RuntimeException('Teléfono demasiado largo (máx 30)', 400);
      $ok = db_exec_nonquery("UPDATE users SET phone=?, updated_at=? WHERE id=?", 'ssi', [$phone, $now, $uid])['ok'];
      if (!$ok) throw new RuntimeException('No se pudo guardar', 500);
      json_out(['ok'=>true]);
    }

    case 'gender': {
      // ENUM real en tu DB: male|female|unspecified|other
      $allowed = ['male','female','unspecified','other'];
      $g = (string)($in['gender'] ?? 'unspecified');
      if (!in_array($g, $allowed, true)) $g = 'unspecified';
      $ok = db_exec_nonquery("UPDATE users SET gender=?, updated_at=? WHERE id=?", 'ssi', [$g, $now, $uid])['ok'];
      if (!$ok) throw new RuntimeException('No se pudo guardar', 500);
      json_out(['ok'=>true]);
    }

    case 'locale': {
      $allowed = ['es','en','fr','it'];
      $loc = (string)($in['locale'] ?? 'es');
      if (!in_array($loc, $allowed, true)) $loc = 'es';
      $ok = db_exec_nonquery("UPDATE users SET locale=?, updated_at=? WHERE id=?", 'ssi', [$loc, $now, $uid])['ok'];
      if (!$ok) throw new RuntimeException('No se pudo guardar', 500);
      json_out(['ok'=>true]);
    }

    case 'category': {
      // ENUM real en tu DB: naturaleza|ocio|aventura|cultural|todos|custom
      $allowed = ['naturaleza','ocio','aventura','cultural','todos','custom'];
      $cat = (string)($in['preferred_category'] ?? 'todos');
      if (!in_array($cat, $allowed, true)) $cat = 'todos';
      $ok = db_exec_nonquery("UPDATE users SET preferred_category=?, updated_at=? WHERE id=?", 'ssi', [$cat, $now, $uid])['ok'];
      if (!$ok) throw new RuntimeException('No se pudo guardar', 500);
      json_out(['ok'=>true]);
    }

    case 'password': {
      $cur = (string)($in['current'] ?? '');
      $nw  = (string)($in['new'] ?? '');
      $rp  = (string)($in['new2'] ?? '');
      if ($nw !== $rp) throw new RuntimeException('Las nuevas no coinciden', 400);
      if (mb_strlen($nw) < 8) throw new RuntimeException('Mínimo 8 caracteres', 400);

      $row = db_exec("SELECT password_hash FROM users WHERE id=?", 'i', [$uid])[0] ?? null;
      if (!$row || !password_verify($cur, $row['password_hash'])) throw new RuntimeException('La contraseña actual no es correcta', 401);

      $hash = hash_pass($nw);
      $ok = db_exec_nonquery("UPDATE users SET password_hash=?, updated_at=? WHERE id=?", 'ssi', [$hash, $now, $uid])['ok'];
      if (!$ok) throw new RuntimeException('No se pudo actualizar', 500);

      // Higiene: revoco remember tokens y roto el id de sesión
      db_exec_nonquery("DELETE FROM auth_tokens WHERE user_id=?", 'i', [$uid]);
      if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);

      json_out(['ok'=>true]);
    }

    default:
      throw new RuntimeException('Acción no soportada', 400);
  }
} catch (RuntimeException $e) {
  json_out(['ok'=>false, 'error'=>$e->getMessage()], $e->getCode() ?: 400);
} catch (Throwable $e) {
  if (is_dev()) error_log('profile/update error: '.$e->getMessage());
  json_out(['ok'=>false, 'error'=>'Error interno'], 500);
}
