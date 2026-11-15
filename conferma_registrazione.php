<?php
/**
 * Conferma dell'email dopo la registrazione (link inviato da api/elabora_autenticazione.php).
 *
 * Aprire il link (GET) mostra solo un pulsante: la conferma avviene con un POST.
 * Alcuni filtri antispam aprono da soli i link delle email per controllarli; se bastasse
 * il GET, attiverebbero l'account al posto del proprietario dell'indirizzo.
 */
require_once __DIR__ . '/api/avvio.php';

$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$tokenValido = preg_match('/^[0-9a-f]{64}$/', $token) === 1;
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($tokenValido) {
        include __DIR__ . '/api/connessione_db.php';
        /** @var mysqli $db */
        $hashToken = hash('sha256', $token);

        $stmt = mysqli_prepare($db, "SELECT email FROM utente WHERE token_conferma = ? AND verificato = 0 AND scadenza_conferma > NOW()");
        mysqli_stmt_bind_param($stmt, "s", $hashToken);
        mysqli_stmt_execute($stmt);
        $riga = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($riga) {
            $stmt = mysqli_prepare($db, "UPDATE utente SET verificato = 1, token_conferma = NULL, scadenza_conferma = NULL WHERE email = ? AND token_conferma = ?");
            mysqli_stmt_bind_param($stmt, "ss", $riga['email'], $hashToken);
            mysqli_stmt_execute($stmt);
            $_SESSION['auth'] = "Email confermata: ora puoi accedere.";
            header("Location: autenticazione.php?login&email=" . urlencode($riga['email']));
            exit();
        }
    }
    $errore = "Il link non è valido o è scaduto. Registrati di nuovo per ricevere un nuovo link.";
} elseif (!$tokenValido) {
    $errore = "Il link non è valido. Controlla di averlo copiato per intero.";
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma registrazione</title>
    <link rel="stylesheet" href="css/stile_autenticazione.css">
    <?php include 'components/imposta_icona.html'; ?>
    <!-- Stesso video di sfondo della pagina di recupero password -->
    <script src="js/logica_recupero_password.js" defer></script>
    <style>
        .conferma-testo { margin: 3px 3px 12px; font-size: 15px; }
        .conferma-errore { color: crimson; margin: 3px 3px 12px; font-size: 15px; }
    </style>
</head>

<body>
    <video id="background-video" autoplay muted loop playsinline></video>
    <div id="main-container" class="regcontainer" style="display: block;">
        <?php if ($errore === '') { ?>
            <p class="conferma-testo">Ultimo passaggio: conferma il tuo indirizzo email per attivare l'account.</p>
            <form action="conferma_registrazione.php" method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="submit" value="Conferma email" style="width: 100%;">
            </form>
        <?php } else { ?>
            <p class="conferma-errore"><?= htmlspecialchars($errore, ENT_QUOTES, 'UTF-8') ?></p>
            <p><a href="autenticazione.php">Vai alla registrazione</a></p>
        <?php } ?>
    </div>
</body>

</html>
