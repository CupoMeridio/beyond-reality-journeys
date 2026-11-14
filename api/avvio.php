<?php
/**
 * Avvio comune di ogni pagina ed endpoint: va incluso come prima istruzione.
 *
 * 1. carica la configurazione (vedi configurazione.php);
 * 2. mostra gli errori solo in locale (in produzione finiscono nel log);
 * 3. in produzione forza HTTPS (necessario anche per Stripe);
 * 4. avvia la sessione una sola volta, con cookie HttpOnly e SameSite=Lax.
 */
require_once __DIR__ . '/configurazione.php';

date_default_timezone_set('Europe/Rome');
error_reporting(E_ALL);
ini_set('display_errors', AMBIENTE_LOCALE ? '1' : '0');

if (!defined('RICHIESTA_HTTPS')) {
    define('RICHIESTA_HTTPS',
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    );
}

if (!AMBIENTE_LOCALE && !RICHIESTA_HTTPS && PHP_SAPI !== 'cli') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => RICHIESTA_HTTPS,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
