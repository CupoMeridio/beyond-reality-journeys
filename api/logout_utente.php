<?php
// Chiude la sessione dell'utente. Solo via POST con token CSRF: con un semplice link
// qualsiasi sito esterno poteva disconnettere l'utente. Gli altri cookie
// (es. quello anti-bot di InfinityFree) restano intatti.
require_once __DIR__ . '/avvio.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfValido()) {
    chiudiSessione();
}

header("Location: ../index.php");
exit;
