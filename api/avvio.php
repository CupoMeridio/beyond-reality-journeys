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

/*
 * Protezione CSRF (Cross-Site Request Forgery).
 * Ogni sessione ha un token casuale: i form lo inviano nel campo nascosto "csrf_token",
 * le richieste AJAX lo leggono da <body data-csrf> e lo inviano nello stesso campo.
 * Un sito esterno non può conoscerlo, quindi non può far compiere azioni a un utente loggato.
 * Si proteggono solo le richieste che modificano dati: le letture non ne hanno bisogno.
 */

/** Token CSRF della sessione (creato al primo utilizzo). */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Campo nascosto da inserire in ogni form che modifica dati. */
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . tokenCsrf() . '">';
}

/** true se la richiesta POST contiene il token CSRF corretto. */
function csrfValido(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

const MESSAGGIO_CSRF = 'Sessione scaduta o richiesta non valida: ricarica la pagina e riprova.';

/** Chiude la sessione: svuota i dati, elimina il file di sessione e il suo cookie. */
function chiudiSessione(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Indirizzo assoluto di una pagina del sito, per i link nelle email (es. urlSito('conferma_registrazione.php')).
 * Se in config.php è definita URL_SITO si usa quella, altrimenti si ricava dalla richiesta corrente:
 * va chiamata da una pagina nella cartella principale del sito.
 */
function urlSito(string $percorso = ''): string
{
    $base = defined('URL_SITO')
        ? rtrim(URL_SITO, '/')
        : (RICHIESTA_HTTPS ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
            . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return $base . '/' . ltrim($percorso, '/');
}

/** Risposta JSON con codice HTTP, poi fine dello script. */
function rispondiJson(array $dati, int $codice = 200): void
{
    http_response_code($codice);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dati, JSON_UNESCAPED_UNICODE);
    exit();
}
