<?php
/**
 * Fase 2 del pagamento: chiede a Stripe lo stato reale del PaymentIntent e, solo se il
 * pagamento è riuscito con l'importo atteso, salva la prenotazione.
 * Sostituisce salva_prenotazione.php, che si fidava del client e salvava senza verificare.
 * Idempotente: la stessa conferma ripetuta restituisce la prenotazione già salvata.
 */
require_once __DIR__ . '/../avvio.php';
require_once __DIR__ . '/../prenotazioni_utils.php';
require_once __DIR__ . '/configurazione_stripe.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rispondiJson(['error' => 'Metodo non consentito.'], 405);
}
if (!isset($_SESSION['email'])) {
    rispondiJson(['error' => 'Devi accedere per prenotare.'], 401);
}

$idPagamento = (string) ($_POST['payment_intent'] ?? '');
$bozza = $_SESSION['bozza_pagamento'] ?? null;

require __DIR__ . '/../connessione_db.php';
/** @var mysqli $db */

function prenotazioneEsistente(mysqli $db, string $idPagamento, string $email): ?int
{
    $stmt = mysqli_prepare($db, "SELECT id_prenotazione FROM prenotazione WHERE payment_intent_id = ? AND email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $idPagamento, $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ? (int) $row['id_prenotazione'] : null;
}

// Conferma ripetuta (es. doppio clic o rete instabile): si restituisce quella già salvata
if ($idPagamento !== '' && ($id = prenotazioneEsistente($db, $idPagamento, $_SESSION['email'])) !== null) {
    rispondiJson(['id_prenotazione' => $id]);
}

if (!$bozza || $idPagamento === '' || !hash_equals($bozza['payment_intent'], $idPagamento)) {
    rispondiJson(['error' => 'Nessun pagamento in corso corrispondente: ricomincia la prenotazione.'], 409);
}

try {
    $paymentIntent = \Stripe\PaymentIntent::retrieve($idPagamento);
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log('Stripe, lettura PaymentIntent fallita: ' . $e->getMessage());
    rispondiJson(['error' => 'Impossibile verificare il pagamento, riprova tra poco.'], 502);
}

if ($paymentIntent->status !== 'succeeded') {
    rispondiJson(['error' => 'Il pagamento non risulta completato.'], 402);
}
if ($paymentIntent->amount !== $bozza['importo'] || $paymentIntent->currency !== 'eur'
    || ($paymentIntent->metadata['email'] ?? null) !== $_SESSION['email']) {
    error_log("PaymentIntent $idPagamento non coerente con la bozza in sessione.");
    rispondiJson(['error' => 'Il pagamento non corrisponde alla prenotazione.'], 409);
}

$stmt = mysqli_prepare($db,
    "INSERT INTO prenotazione (email, nome, cognome, mondo, destinazione, data_partenza, data_ritorno,
                               num_biglietti, nominativi, commento, importo_centesimi, payment_intent_id)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$nominativi = json_encode($bozza['nominativi'], JSON_UNESCAPED_UNICODE);

// Nome e cognome dal database: in sessione ci sono solo dopo il passaggio dalla home
$stmtUtente = mysqli_prepare($db, "SELECT nome, cognome FROM utente WHERE email = ?");
mysqli_stmt_bind_param($stmtUtente, "s", $_SESSION['email']);
mysqli_stmt_execute($stmtUtente);
$utente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUtente)) ?: ['nome' => '', 'cognome' => ''];
$nome = $utente['nome'];
$cognome = $utente['cognome'];
mysqli_stmt_bind_param($stmt, "sssssssissis",
    $_SESSION['email'], $nome, $cognome, $bozza['mondo'], $bozza['destinazione'],
    $bozza['data_partenza'], $bozza['data_ritorno'], $bozza['num_biglietti'],
    $nominativi, $bozza['commento'], $bozza['importo'], $idPagamento);

try {
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($db);
} catch (mysqli_sql_exception $e) {
    // Due conferme in parallelo: la seconda trova il vincolo UNIQUE e restituisce la prima
    if ($e->getCode() === 1062 && ($id = prenotazioneEsistente($db, $idPagamento, $_SESSION['email'])) !== null) {
        rispondiJson(['id_prenotazione' => $id]);
    }
    error_log('Salvataggio prenotazione fallito: ' . $e->getMessage());
    rispondiJson(['error' => 'Pagamento riuscito, ma il salvataggio della prenotazione è fallito. Contattaci.'], 500);
}

unset($_SESSION['bozza_pagamento']);
rispondiJson(['id_prenotazione' => $id]);
