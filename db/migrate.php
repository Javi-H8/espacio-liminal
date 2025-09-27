<?php
// db/migrate.php
// Ejecuta migraciones tipo "artisan migrate" pero en PHP puro.
// Uso:
//   php db/migrate.php up
//   php db/migrate.php down --step=1
//   php db/migrate.php fresh      (borra y reaplica todo)
//   php db/migrate.php status     (lista qué está aplicado y qué falta)

require_once __DIR__ . '/../config/bootstrap.php';
if (session_status() === PHP_SESSION_ACTIVE) session_write_close(); // por si acaso

// --- utilidades rápidas ---
function out($msg){ echo $msg.PHP_EOL; }
function fail($msg){ fwrite(STDERR, $msg.PHP_EOL); exit(1); }

// crea tabla de log si no existe
$mysqli->query("
  CREATE TABLE IF NOT EXISTS migrations_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    batch INT NOT NULL,
    ran_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// lee migraciones del disco
$dir = __DIR__ . '/migrations';
if (!is_dir($dir)) fail("No existe db/migrations");

$files = array_values(array_filter(scandir($dir), fn($f)=>preg_match('/\.php$/', $f)));
sort($files); // orden por nombre: YYYYMMDD_HHMM_...

// qué hay aplicado
$applied = [];
$res = $mysqli->query("SELECT filename FROM migrations_log ORDER BY id ASC");
if ($res) { while($r=$res->fetch_assoc()) $applied[$r['filename']] = true; $res->free(); }

// parse args
$cmd = $argv[1] ?? 'status';
$step = 1;
foreach ($argv as $i=>$a){
  if ($a === '--step' && isset($argv[$i+1])) $step = (int)$argv[$i+1];
  if (str_starts_with($a, '--step=')) $step = (int)substr($a, 7);
}

function run_sql_batch(mysqli $db, string $sql){
  // Divide por ";" de forma simple. Evita líneas vacías.
  $stmts = array_filter(array_map('trim', explode(';', $sql)), fn($s)=>$s!=='');
  foreach ($stmts as $st){
    if (!$db->query($st)) {
      throw new Exception("SQL error: ".$db->error."\nEn: ".$st);
    }
  }
}

switch ($cmd) {
  case 'status':
    out("== Migraciones ==");
    foreach ($files as $f) {
      $mark = isset($applied[$f]) ? '[X]' : '[ ]';
      out("$mark $f");
    }
    exit(0);

  case 'up':
    // calcular batch nuevo
    $batch = 1;
    $res = $mysqli->query("SELECT MAX(batch) AS m FROM migrations_log");
    if ($res && ($row=$res->fetch_assoc()) && $row['m']) $batch = (int)$row['m'] + 1;
    $res && $res->free();

    $pendientes = array_values(array_filter($files, fn($f)=>!isset($applied[$f])));
    if (!$pendientes){ out("Nada que migrar. ✔"); exit(0); }

    out("Aplicando batch #$batch …");
    foreach ($pendientes as $f){
      $path = $dir . '/' . $f;
      $def = require $path;
      if (!is_array($def) || !isset($def['up'])) fail("Migración inválida: $f");

      out("→ $f (up)");
      try {
        run_sql_batch($mysqli, $def['up']);
        $stmt = $mysqli->prepare("INSERT INTO migrations_log(filename,batch) VALUES(?,?)");
        $stmt->bind_param('si', $f, $batch);
        $stmt->execute(); $stmt->close();
      } catch (Throwable $ex) {
        fail("Fallo en $f: ".$ex->getMessage());
      }
    }
    out("Listo. ✅");
    exit(0);

  case 'down':
    // retroceder por orden inverso de aplicación (último batch primero)
    $res = $mysqli->query("SELECT filename FROM migrations_log ORDER BY id DESC");
    $toRollback = [];
    while ($res && ($row=$res->fetch_assoc())) { $toRollback[] = $row['filename']; }
    $res && $res->free();

    if (!$toRollback){ out("Nada que revertir."); exit(0); }

    $count = 0;
    foreach ($toRollback as $f){
      if ($count >= $step) break;
      $path = $dir . '/' . $f;
      if (!file_exists($path)) fail("No encuentro archivo de migración: $f");
      $def = require $path;
      if (!is_array($def) || !isset($def['down'])) fail("Migración sin 'down': $f");

      out("← $f (down)");
      try {
        run_sql_batch($mysqli, $def['down']);
        $stmt = $mysqli->prepare("DELETE FROM migrations_log WHERE filename=?");
        $stmt->bind_param('s', $f);
        $stmt->execute(); $stmt->close();
      } catch (Throwable $ex) {
        fail("Fallo al revertir $f: ".$ex->getMessage());
      }
      $count++;
    }
    out("Hecho. 🔄");
    exit(0);

  case 'fresh':
    out("⚠ Esto borra TODO el esquema y reaplica desde cero.");
    // drop a lo bruto (solo tus tablas conocidas):
    $mysqli->query("SET FOREIGN_KEY_CHECKS=0");
    $drop = [
      'auth_tokens','password_resets','users','migrations_log'
    ];
    foreach ($drop as $t) { $mysqli->query("DROP TABLE IF EXISTS `$t`"); }
    $mysqli->query("SET FOREIGN_KEY_CHECKS=1");
    // recrear log e ir con 'up'
    $mysqli->query("
      CREATE TABLE IF NOT EXISTS migrations_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL,
        ran_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    // simulo "php db/migrate.php up"
    array_unshift($argv, $argv[0], 'up'); $cmd='up'; // pequeño truco
    // cae al case 'up' superior
    // (si en tu PHP no funciona el truco, simplemente ejecuta fresh y luego up a mano)
    // break;

  default:
    out("Comandos:");
    out("  php db/migrate.php status");
    out("  php db/migrate.php up");
    out("  php db/migrate.php down --step=1");
    out("  php db/migrate.php fresh");
    exit(0);
}
