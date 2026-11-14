<?php
// Chiude la sessione dell'utente: svuota i dati, elimina il file di sessione sul server
// e il relativo cookie. Gli altri cookie (es. quello anti-bot di InfinityFree) restano intatti.
require_once __DIR__ . '/avvio.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

header("Location: ../index.php");
exit;
