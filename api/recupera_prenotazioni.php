<?php
/**
 * Storico prenotazioni dell'utente autenticato (JSON), dalla prenotazione successiva
 * a $_POST['ultima_prenotazione'] in poi.
 */
require_once __DIR__ . '/avvio.php';
require_once __DIR__ . '/prenotazioni_utils.php';

if (!isset($_SESSION['email'])) {
    rispondiJson(["error" => "Utente non autenticato"], 401);
}

$ultima = filter_var($_POST['ultima_prenotazione'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);

include __DIR__ . '/connessione_db.php';
/** @var mysqli $db */

$stmt = mysqli_prepare($db,
    "SELECT id_prenotazione, mondo, destinazione, data_partenza, data_ritorno, num_biglietti, importo_centesimi
     FROM prenotazione WHERE email = ? AND id_prenotazione > ? ORDER BY id_prenotazione");
mysqli_stmt_bind_param($stmt, "si", $_SESSION['email'], $ultima);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$formattaData = fn(string $ymd) => DateTimeImmutable::createFromFormat('!Y-m-d', $ymd)->format('d/m/Y');
$prenotazioni = [];
while ($row = mysqli_fetch_assoc($result)) {
    $prenotazioni[] = [
        "id_prenotazione" => (int) $row['id_prenotazione'],
        "destinazione" => nomeDestinazione($row['mondo'], $row['destinazione']),
        "data_p" => $formattaData($row['data_partenza']),
        "data_r" => $formattaData($row['data_ritorno']),
        "num_biglietti" => (int) $row['num_biglietti'],
        "importo" => formattaEuro((int) $row['importo_centesimi']),
    ];
}
mysqli_close($db);

rispondiJson($prenotazioni);
