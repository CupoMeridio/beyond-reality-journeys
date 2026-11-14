<?php
/**
 * Caricamento della configurazione (nessun effetto collaterale: niente sessione, niente redirect).
 *
 * - AMBIENTE_LOCALE è true quando il sito gira su localhost/127.0.0.1 o da riga di comando.
 * - In locale, se esiste api/config.local.php, i suoi valori hanno la precedenza:
 *   config.php definisce soltanto le costanti non ancora definite.
 * - config.local.php non va mai caricato sul server di produzione (è nel .gitignore).
 */

if (!defined('AMBIENTE_LOCALE')) {
    define('AMBIENTE_LOCALE', (function (): bool {
        if (PHP_SAPI === 'cli') {
            return true;
        }
        $host = strtolower((string) parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST));
        return in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
    })());
}

if (AMBIENTE_LOCALE && is_file(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(500);
    exit('Configurazione mancante: copia api/config.example.php in api/config.php e inserisci i tuoi valori.');
}
require_once __DIR__ . '/config.php';
