<?php
/**
 * Salva una recensione dell'utente autenticato per il mondo indicato dalla pagina.
 *
 * Il testo viene salvato così com'è (solo validato): l'escape si fa in fase di visualizzazione.
 * Prima veniva applicato htmlspecialchars anche qui, e con l'escape del frontend gli
 * apostrofi comparivano come &#039;.
 */
require_once __DIR__ . '/avvio.php';
require_once __DIR__ . '/mondi.php';

const COMMENTO_MAX_CARATTERI = 1000;
const COMMENTO_INTERVALLO_SECONDI = 30; // anti-spam: una recensione ogni 30 secondi per sessione

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rispondiJson(['error' => 'Metodo non consentito.'], 405);
}
if (!isset($_SESSION['email'])) {
    rispondiJson(['error' => 'Devi accedere per lasciare una recensione.'], 401);
}
if (!csrfValido()) {
    rispondiJson(['error' => MESSAGGIO_CSRF], 403);
}

$mondo = $_POST['mondo'] ?? null;
$testo = trim((string) ($_POST['experience'] ?? ''));
$stelle = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);

if (!mondoValido($mondo)) {
    rispondiJson(['error' => 'Mondo non valido: ricarica la pagina.'], 422);
}
if ($testo === '' || mb_strlen($testo) > COMMENTO_MAX_CARATTERI) {
    rispondiJson(['error' => 'La recensione deve contenere da 1 a ' . COMMENTO_MAX_CARATTERI . ' caratteri.'], 422);
}
if ($stelle === false) {
    rispondiJson(['error' => 'La valutazione deve essere da 1 a 5 stelle.'], 422);
}
$attesa = ($_SESSION['ultimo_commento'] ?? 0) + COMMENTO_INTERVALLO_SECONDI - time();
if ($attesa > 0) {
    rispondiJson(['error' => "Hai appena pubblicato una recensione: riprova tra $attesa secondi."], 429);
}

include __DIR__ . '/connessione_db.php';
/** @var mysqli $db */

// Username dal database: in sessione c'è solo dopo il passaggio dalla home
$stmt = mysqli_prepare($db, "SELECT username FROM utente WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $_SESSION['email']);
mysqli_stmt_execute($stmt);
$utente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$utente) {
    rispondiJson(['error' => 'Account non trovato.'], 401);
}

$stmt = mysqli_prepare($db, "INSERT INTO commento (email, username, testo, mondo, stelle) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssssi", $_SESSION['email'], $utente['username'], $testo, $mondo, $stelle);

try {
    mysqli_stmt_execute($stmt);
} catch (mysqli_sql_exception $e) {
    error_log('Salvataggio commento fallito: ' . $e->getMessage());
    rispondiJson(['error' => 'Errore durante il salvataggio della recensione. Riprova più tardi.'], 500);
}
mysqli_close($db);

$_SESSION['ultimo_commento'] = time();
rispondiJson(['messaggio' => 'Recensione pubblicata, grazie!']);
