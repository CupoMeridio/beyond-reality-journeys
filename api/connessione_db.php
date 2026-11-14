<?php
require_once __DIR__ . '/configurazione.php';

/** @var mysqli|false $db */
try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, defined('DB_PORT') ? DB_PORT : 3306);
} catch (mysqli_sql_exception $e) {
    $db = false;
}

if (!$db) {
    // Il dettaglio va nel log, mai all'utente (potrebbe contenere host e nome utente del DB)
    error_log('Connessione al database fallita: ' . mysqli_connect_error());
    http_response_code(503);
    exit('Servizio momentaneamente non disponibile. Riprova più tardi.');
}

mysqli_set_charset($db, 'utf8mb4');
