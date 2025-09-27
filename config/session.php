<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

/**
 * Durante desarrollo, si no hay login, fuerza un user_id.
 * Ponlo a false en producción.
 */
const DEV_AUTH = true;
const DEV_USER_ID = 1;

if (!isset($_SESSION['user_id']) && DEV_AUTH) {
  $_SESSION['user_id'] = DEV_USER_ID;
}

/** Helper */
function auth_user_id(): ?int {
  return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}
