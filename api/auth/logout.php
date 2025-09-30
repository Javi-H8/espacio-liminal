<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/session.php';
header('Content-Type: application/json; charset=utf-8');

auth_logout();
echo json_encode(['ok'=>true]);
