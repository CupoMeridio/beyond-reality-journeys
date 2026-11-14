<?php
/**
 * Recensioni di un mondo (JSON), dalla successiva a $_POST['max'] in poi.
 * Al primo caricamento (max = 0) restituisce solo le ultime RECENSIONI_INIZIALI.
 * L'email degli autori non viene mai inviata al browser: è un dato personale.
 */
require_once __DIR__ . '/avvio.php';
require_once __DIR__ . '/mondi.php';
session_write_close(); // la sessione non serve: si libera subito il lock (le richieste sono periodiche)

const RECENSIONI_INIZIALI = 50;

$mondo = $_POST['mondo'] ?? null;
if (!mondoValido($mondo)) {
    rispondiJson(['error' => 'Mondo non valido.'], 422);
}
$ultimo = filter_var($_POST['max'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);

include __DIR__ . '/connessione_db.php';
/** @var mysqli $db */

if ($ultimo === 0) {
    // Ultime N in ordine cronologico
    $stmt = mysqli_prepare($db,
        "SELECT * FROM (SELECT id_testo, username, testo, stelle FROM commento WHERE mondo = ?
                        ORDER BY id_testo DESC LIMIT " . RECENSIONI_INIZIALI . ") ultime ORDER BY id_testo");
    mysqli_stmt_bind_param($stmt, "s", $mondo);
} else {
    $stmt = mysqli_prepare($db, "SELECT id_testo, username, testo, stelle FROM commento WHERE mondo = ? AND id_testo > ? ORDER BY id_testo");
    mysqli_stmt_bind_param($stmt, "si", $mondo, $ultimo);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$commenti = [];
while ($row = mysqli_fetch_assoc($result)) {
    $commenti[] = [
        'id_testo' => (int) $row['id_testo'],
        'username' => $row['username'],
        'testo' => $row['testo'],
        'stelle' => max(1, min(5, (int) $row['stelle'])),
    ];
}
mysqli_close($db);

rispondiJson($commenti);
