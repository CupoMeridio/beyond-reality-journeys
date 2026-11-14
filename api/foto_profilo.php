<?php
/**
 * Restituisce la foto profilo dell'utente autenticato.
 * Il parametro ?v=<hash> cambia quando cambia la foto, quindi il browser può tenerla in cache.
 */
require_once __DIR__ . '/avvio.php';
require_once __DIR__ . '/foto_profilo_utils.php';

$email = $_SESSION['email'] ?? null;
session_write_close(); // la sessione serve solo in lettura: si libera subito il lock

function fotoPredefinita(): void
{
    header('Location: ../' . FOTO_DEFAULT);
    exit;
}

if ($email === null) {
    fotoPredefinita();
}

require __DIR__ . '/connessione_db.php';
/** @var mysqli $db */

$stmt = mysqli_prepare($db, "SELECT img, type FROM utente WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_close($db);

if (!$row || $row['img'] === null || $row['img'] === '') {
    fotoPredefinita();
}

// Il tipo viene verificato sul contenuto: copre anche righe salvate con versioni precedenti
$info = @getimagesizefromstring($row['img']);
if ($info === false || !in_array($info['mime'], FOTO_TIPI_AMMESSI, true)) {
    fotoPredefinita();
}

header('Content-Type: ' . $info['mime']);
header('Content-Length: ' . strlen($row['img']));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=86400');
echo $row['img'];
