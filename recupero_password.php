<?php
require_once __DIR__ . '/api/avvio.php';
/**
 * Recupero password in due fasi:
 *  1. l'utente inserisce l'email -> se esiste un account, riceve un codice a 6 cifre;
 *  2. l'utente inserisce codice + nuova password -> la password viene aggiornata.
 *
 * Lo stato del reset vive in $_SESSION['reset'] e NON tocca mai $_SESSION['email'],
 * che indica l'utente autenticato: richiedere un reset non deve mai equivalere a un login.
 * Ogni POST termina con un redirect (pattern Post/Redirect/Get) e i messaggi passano
 * in $_SESSION['reset_msg'] / $_SESSION['reset_err'], mostrati una sola volta.
 */

include('api/validazione_generale.php');
require_once __DIR__ . '/api/servizio_email.php';

const RESET_DURATA_CODICE = 300; // secondi di validità del codice
const RESET_MAX_TENTATIVI = 5;   // tentativi di inserimento del codice prima di invalidarlo

function fineRichiesta(?string $errore = null, ?string $messaggio = null): void
{
    if ($errore !== null) {
        $_SESSION['reset_err'] = $errore;
    }
    if ($messaggio !== null) {
        $_SESSION['reset_msg'] = $messaggio;
    }
    header('Location: recupero_password.php');
    exit();
}

// Permette di ricominciare (es. email sbagliata o codice mai arrivato)
if (isset($_GET['annulla'])) {
    unset($_SESSION['reset']);
    fineRichiesta();
}

// Un codice scaduto viene eliminato prima di qualsiasi altra elaborazione
if (isset($_SESSION['reset']) && time() > $_SESSION['reset']['scadenza']) {
    unset($_SESSION['reset']);
    $_SESSION['reset_err'] = "Il codice è scaduto. Richiedine uno nuovo.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('api/connessione_db.php');
    /** @var mysqli $db */

    // --- FASE 1: richiesta del codice ---
    if (isset($_POST['email'])) {
        $email = strtolower(trim($_POST['email']));

        if (!controlloPatternEmail($email)) {
            fineRichiesta("Errore nella forma della email.");
        }

        $stmt = mysqli_prepare($db, "SELECT nome, cognome FROM utente WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$row) {
            fineRichiesta("Non esiste un account con questa email. <a href='autenticazione.php'>Registrati</a>");
        }

        $codice = (string) random_int(100000, 999999);
        $nomeCompleto = trim($row['nome'] . ' ' . $row['cognome']);
        $html = modelloEmail('Recupero password',
            '<p>Ciao ' . htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>ecco il codice per reimpostare la password:</p>'
            . '<p style="font-size:28px;font-weight:bold;letter-spacing:6px;">' . $codice . '</p>'
            . '<p>Il codice è valido per ' . (RESET_DURATA_CODICE / 60) . ' minuti.</p>');

        if (!inviaEmail($email, $nomeCompleto, 'Codice per il recupero della password', $html)) {
            fineRichiesta("Non è stato possibile inviare l'email in questo momento. Riprova più tardi.");
        }

        // Il reset diventa attivo solo se l'email è partita davvero
        $_SESSION['reset'] = [
            'email' => $email,
            'codice' => $codice,
            'scadenza' => time() + RESET_DURATA_CODICE,
            'tentativi' => 0,
        ];
        fineRichiesta(null, "Ti abbiamo inviato un'email all'indirizzo " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8'));
    }

    // --- FASE 2: verifica del codice e cambio password ---
    if (isset($_POST['Cambia_password'], $_POST['codice_cambia_password'])) {
        if (!isset($_SESSION['reset'])) {
            fineRichiesta("Nessuna richiesta di reset attiva. Inserisci la tua email.");
        }

        $password = $_POST['Cambia_password'];
        $codiceInserito = trim($_POST['codice_cambia_password']);

        if (!controlloPatternPassword($password)) {
            fineRichiesta("La password non rispetta i requisiti richiesti.");
        }

        if (!hash_equals($_SESSION['reset']['codice'], $codiceInserito)) {
            $_SESSION['reset']['tentativi']++;
            if ($_SESSION['reset']['tentativi'] >= RESET_MAX_TENTATIVI) {
                unset($_SESSION['reset']);
                fineRichiesta("Troppi tentativi errati. Richiedi un nuovo codice.");
            }
            $rimasti = RESET_MAX_TENTATIVI - $_SESSION['reset']['tentativi'];
            fineRichiesta("Il codice non è corretto. Tentativi rimasti: $rimasti.");
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $emailReset = $_SESSION['reset']['email'];
        $stmt = mysqli_prepare($db, "UPDATE utente SET password = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $hash, $emailReset);

        if (!mysqli_stmt_execute($stmt)) {
            fineRichiesta("Problema con la procedura: password non cambiata. Riprova più tardi.");
        }

        unset($_SESSION['reset']);
        $_SESSION['auth'] = "Password cambiata con successo. Ora puoi accedere.";
        header('Location: autenticazione.php?login&email=' . urlencode($emailReset));
        exit();
    }

    fineRichiesta();
}

// --- Messaggi flash (mostrati una sola volta) ---
$messaggio = $_SESSION['reset_msg'] ?? '';
$errore = $_SESSION['reset_err'] ?? '';
unset($_SESSION['reset_msg'], $_SESSION['reset_err']);
$faseCodice = isset($_SESSION['reset']);
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <title>Recupero password</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/stile_autenticazione.css">
    <?php include("components/imposta_icona.html"); ?>
    <script src="js/utils.js" type="text/javascript" defer></script>
    <script src="js/logica_recupero_password.js" type="text/javascript" defer></script>

    <style>
        #error, .reset-errore {
            color: crimson;
            margin: auto;
            font-size: 15px;
        }

        #hint {
            color: grey;
            margin: 2px 2px 10px;
            font-size: 13px;
        }

        .reset-messaggio {
            margin: 3px;
            font-size: 15px;
            color: deeppink;
        }

        .container {
            margin: auto;
        }
    </style>
</head>

<body>
    <video id="background-video" autoplay muted loop playsinline></video>
    <div id="main-container" class="regcontainer" style="display: block;">
        <?php if ($messaggio !== '') { ?>
            <div class="reset-messaggio"><?php echo $messaggio; ?></div>
        <?php } ?>

        <?php if (!$faseCodice) { ?>
            <form action="recupero_password.php" method="post">
                <label for="email">Inserisci la tua e-mail:</label>
                <input type="email" id="email" name="email" autocomplete="email" required>
                <input type="submit" value="Invio" style="width: 100%; margin-top:10px;">
            </form>
        <?php } else { ?>
            <form action="recupero_password.php" method="post">
                <div class="container">
                    <label for="Cambia_password">Scrivi la nuova password:</label>
                    <input type="password" id="Cambia_password" name="Cambia_password" autocomplete="new-password" required>
                    <div id="hint">La lunghezza della password deve essere di almeno 8 caratteri. <br>La password deve contenere almeno una lettera maiuscola, una minuscola, un numero e un carattere speciale.</div>
                    <div id="error"></div>
                </div>
                <p class="container">
                    <label for="codice_cambia_password">Scrivi il codice:</label>
                    <input name="codice_cambia_password" id="codice_cambia_password" type="text" inputmode="numeric" pattern="\d{6}" autocomplete="one-time-code" required>
                </p>
                <input type="submit" value="Invio" style="width: 100%; margin-top:10px;">
            </form>
            <p><a href="recupero_password.php?annulla">Non hai ricevuto il codice? Ricomincia</a></p>
        <?php } ?>

        <?php if ($errore !== '') { ?>
            <div class="reset-errore"><?php echo $errore; ?></div>
        <?php } ?>
    </div>
</body>

</html>
