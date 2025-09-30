<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/session.php';

// A mi rollo: limpiar todo (sesión + token persistente) y a login
auth_logout();
header('Location: /espacio-liminal/login.php');
