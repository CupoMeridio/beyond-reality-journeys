<?php
/**
 * Carica in sessione i dati dell'utente autenticato (nome, cognome, username, URL della foto)
 * e imposta $email, $nome, $cognome, $username, $img per la navbar.
 * Incluso da index.php e autenticazione.php.
 */
require_once __DIR__ . '/avvio.php';
require_once __DIR__ . '/foto_profilo_utils.php';

if (isset($_SESSION['email'])) {
    include __DIR__ . '/connessione_db.php';
    /** @var mysqli $db */

    // MD5(img) evita di trasferire il BLOB: serve solo a versionare l'URL della foto
    $stmt = mysqli_prepare($db, "SELECT nome, cognome, username, MD5(img) AS img_hash FROM utente WHERE email = LOWER(?)");
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['email']);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_close($db);

    if (!$row) {
        // L'account non esiste più: si chiude la sessione
        header("Location: api/logout_utente.php");
        exit();
    }

    $_SESSION['nome'] = $row['nome'];
    $_SESSION['cognome'] = $row['cognome'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['img'] = urlFotoProfilo($row['img_hash']);
    unset($_SESSION['type']); // residuo della vecchia gestione (data-URI in sessione)

    // Variabili usate da components/navbar.php nella pagina che include questo file
    $email = $_SESSION['email'];
    $nome = $_SESSION['nome'];
    $cognome = $_SESSION['cognome'];
    $username = $_SESSION['username'];
    $img = $_SESSION['img'];
}
