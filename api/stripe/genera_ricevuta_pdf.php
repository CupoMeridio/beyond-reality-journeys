<?php
/**
 * Ricevuta PDF di una prenotazione: ?id=<id_prenotazione>.
 * I dati vengono letti dal database (non dalla sessione), quindi la ricevuta si può
 * riscaricare in qualsiasi momento dallo storico ordini. Ogni utente vede solo le proprie.
 */
require_once __DIR__ . '/../avvio.php';
require_once __DIR__ . '/../prenotazioni_utils.php';
require_once __DIR__ . '/../../vendor/autoload.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    exit('Devi accedere per scaricare la ricevuta.');
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('Ricevuta non valida.');
}

require __DIR__ . '/../connessione_db.php';
/** @var mysqli $db */

$stmt = mysqli_prepare($db, "SELECT * FROM prenotazione WHERE id_prenotazione = ? AND email = ?");
mysqli_stmt_bind_param($stmt, "is", $id, $_SESSION['email']);
mysqli_stmt_execute($stmt);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_close($db);

if (!$p) {
    http_response_code(404);
    exit('Ricevuta non trovata.');
}

/** I font standard di FPDF usano Windows-1252: senza conversione "è" e "€" diventano caratteri a caso. */
function pdfTesto(string $s): string
{
    return function_exists('iconv')
        ? iconv('UTF-8', 'windows-1252//TRANSLIT', $s)
        : mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
}

$data = fn(string $ymd) => DateTimeImmutable::createFromFormat('!Y-m-d', $ymd)->format('d/m/Y');
$nominativi = json_decode($p['nominativi'], true) ?: [];
$giorni = giorniViaggio(new DateTimeImmutable($p['data_partenza']), new DateTimeImmutable($p['data_ritorno']));

$pdf = new FPDF();
// I metadati accettano UTF-8 se dichiarato (senza, FPDF 1.82 usa utf8_encode, deprecata in PHP 8.2)
$pdf->SetTitle("Ricevuta prenotazione n. {$p['id_prenotazione']}", true);
$pdf->AddPage();

// Intestazione con logo piccolo (FPDF non gestisce la trasparenza: una filigrana a colori
// coprirebbe il testo)
$pdf->Image(__DIR__ . '/../../img/common/logo.png', 10, 8, 24, 24);
$pdf->SetXY(38, 12);
$pdf->SetFont('Arial', 'B', 22);
$pdf->Cell(0, 16, pdfTesto('Ricevuta di acquisto viaggio'), 0, 1, 'L');
$pdf->Line(10, 36, 200, 36);
$pdf->SetY(40);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, pdfTesto("Ricevuta n. {$p['id_prenotazione']} del " . date('d/m/Y H:i', strtotime($p['data_prenotazione']))), 0, 1, 'R');
$pdf->Ln(6);

$pdf->SetFont('Arial', 'I', 15);
$pdf->MultiCell(0, 9, pdfTesto("Grazie {$p['nome']} {$p['cognome']} per aver viaggiato con noi verso "
    . nomeDestinazione($p['mondo'], $p['destinazione']) . "!\nIl team di BeyondReality Journeys"), 1, 'C');
$pdf->Ln(8);

$riga = function (string $etichetta, string $valore) use ($pdf): void {
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(55, 9, pdfTesto($etichetta), 0, 0);
    $pdf->SetFont('Arial', '', 13);
    $pdf->MultiCell(0, 9, pdfTesto($valore), 0, 'L');
};

$riga('Mondo:', NOMI_MONDI[$p['mondo']] ?? $p['mondo']);
$riga('Destinazione:', nomeDestinazione($p['mondo'], $p['destinazione']));
$riga('Partenza:', $data($p['data_partenza']));
$riga('Ritorno:', $data($p['data_ritorno']));
$riga('Durata:', $giorni . ($giorni === 1 ? ' giorno' : ' giorni'));
$riga('Biglietti:', (string) $p['num_biglietti']);
$riga('Passeggeri:', implode("\n", $nominativi));
if ($p['commento'] !== null && $p['commento'] !== '') {
    $riga('Richieste speciali:', $p['commento']);
}
$pdf->Ln(4);
$riga('Totale pagato:', formattaEuro((int) $p['importo_centesimi']));
$riga('Rif. pagamento:', $p['payment_intent_id']);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->MultiCell(0, 6, pdfTesto("Sito dimostrativo: pagamento simulato con Stripe in modalità test. Nessun addebito reale è stato effettuato e il viaggio non esiste."), 0, 'C');

$pdf->Output('D', "Ricevuta_BeyondReality_{$p['id_prenotazione']}.pdf");
