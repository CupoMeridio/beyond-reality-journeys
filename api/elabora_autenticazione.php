<?php
/**
 * Registrazione e login (incluso da autenticazione.php quando il form viene inviato).
 *
 * Il sito non rivela mai se un'email è registrata:
 *  - registrazione: la risposta è sempre "ti abbiamo inviato un'email". Se l'email è nuova,
 *    l'email contiene il link di conferma (l'account si attiva solo dopo il clic); se esiste già,
 *    avvisa il proprietario che ha già un account;
 *  - login: un unico messaggio "Email o password non corretti", con tempi di risposta uguali
 *    anche quando l'email non esiste.
 */
require_once __DIR__ . '/avvio.php';

require __DIR__ . '/connessione_db.php';
/** @var mysqli $db */

require __DIR__ . '/validazione_generale.php';
require_once __DIR__ . '/foto_profilo_utils.php';
require_once __DIR__ . '/limite_accessi.php';
require_once __DIR__ . '/servizio_email.php';

const CONFERMA_DURATA_ORE = 24; // validità del link di conferma della registrazione

if (!isset($_POST["action"])) {
    exit;
}

$form = $_POST["action"];
$_SESSION['errore'] = "";

function tornaAllaPagina(string $errore, string $query = ""): void
{
    $_SESSION['errore'] = $errore;
    header("Location: autenticazione.php" . $query);
    exit();
}

if (!csrfValido()) {
    tornaAllaPagina(MESSAGGIO_CSRF, $form === "login" ? "?login" : "");
}

if ($form == "reg") {
    if (!isset($_POST["nome"], $_POST["cognome"], $_POST["username"], $_POST["email"], $_POST["password"])) {
        tornaAllaPagina("Compila tutti i campi.");
    }
    $nome = trim($_POST["nome"]);
    $cognome = trim($_POST["cognome"]);
    $email = strtolower(trim($_POST["email"]));
    $username = trim($_POST["username"]);
    $password_pre_hash = $_POST["password"];

    if (!(controlloPatternEmail($email) && controlloPatternPassword($password_pre_hash) && controlloPatternNome($nome) && controlloPatternCognome($cognome) && $username !== '' && mb_strlen($username) <= 100)) {
        tornaAllaPagina("Alcuni dati non sono validi: controlla i campi e riprova.");
    }

    try {
        $foto = leggiFotoProfilo($_FILES["fotoProfilo"] ?? null);
    } catch (RuntimeException $e) {
        tornaAllaPagina($e->getMessage());
    }

    // Le registrazioni mai confermate scadono e liberano email e username
    mysqli_query($db, "DELETE FROM utente WHERE verificato = 0 AND scadenza_conferma < NOW()");

    // L'hash si calcola sempre, anche se poi non serve: i tempi di risposta non devono dipendere dall'email
    $hash = password_hash($password_pre_hash, PASSWORD_DEFAULT);

    // Username occupato da un altro account (non conta una registrazione in sospeso con la stessa email).
    // Gli username sono pubblici (compaiono nelle recensioni), quindi dirlo non rivela nulla di riservato.
    $stmt = mysqli_prepare($db, "SELECT 1 FROM utente WHERE username = ? AND NOT (email = ? AND verificato = 0)");
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    if (mysqli_fetch_row(mysqli_stmt_get_result($stmt))) {
        tornaAllaPagina("Questo username è già in uso: scegline un altro.");
    }

    $stmt = mysqli_prepare($db, "SELECT nome, verificato FROM utente WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $esistente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($esistente && (int) $esistente['verificato'] === 1) {
        // Email già registrata: nessuna modifica all'account, si avvisa solo il proprietario
        $html = modelloEmail('Hai già un account',
            '<p>Ciao ' . htmlspecialchars($esistente['nome'], ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>qualcuno, forse tu, ha provato a registrarsi su BeyondReality Journeys con questo indirizzo email, '
            . 'ma esiste già un account associato.</p>'
            . '<p>Puoi <a href="' . htmlspecialchars(urlSito('autenticazione.php?login'), ENT_QUOTES, 'UTF-8') . '">accedere</a> '
            . 'oppure, se hai dimenticato la password, <a href="' . htmlspecialchars(urlSito('recupero_password.php'), ENT_QUOTES, 'UTF-8') . '">reimpostarla</a>.</p>'
            . '<p>Se non sei stato tu, ignora questa email: il tuo account non è stato modificato.</p>');
        $inviata = inviaEmail($email, $esistente['nome'], 'Hai già un account su BeyondReality Journeys', $html);
    } else {
        // Email nuova (o registrazione in sospeso, che viene sostituita): account da confermare
        $token = bin2hex(random_bytes(32));
        $hashToken = hash('sha256', $token); // nel database solo l'hash: chi legge il DB non può confermare
        $img = null; // parametro "b": il contenuto arriva con send_long_data; senza foto resta NULL
        $tipo = $foto['tipo'] ?? null;

        if ($esistente) {
            $stmt = mysqli_prepare($db, "UPDATE utente SET nome = ?, cognome = ?, username = ?, password = ?, img = ?, type = ?,
                token_conferma = ?, scadenza_conferma = NOW() + INTERVAL " . CONFERMA_DURATA_ORE . " HOUR
                WHERE email = ? AND verificato = 0");
            mysqli_stmt_bind_param($stmt, "ssssbsss", $nome, $cognome, $username, $hash, $img, $tipo, $hashToken, $email);
        } else {
            $stmt = mysqli_prepare($db, "INSERT INTO utente (nome, cognome, username, email, password, img, type, verificato, token_conferma, scadenza_conferma)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NOW() + INTERVAL " . CONFERMA_DURATA_ORE . " HOUR)");
            mysqli_stmt_bind_param($stmt, "sssssbss", $nome, $cognome, $username, $email, $hash, $img, $tipo, $hashToken);
        }
        if ($foto !== null) {
            inviaBlob($stmt, $esistente ? 4 : 5, $foto['dati']);
        }

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                // Username preso nel frattempo da un'altra registrazione
                tornaAllaPagina("Questo username è già in uso: scegline un altro.");
            }
            error_log("Errore registrazione: " . $e->getMessage());
            tornaAllaPagina("Impossibile contattare il database, riprova più tardi.");
        }

        $link = urlSito('conferma_registrazione.php?token=' . $token);
        $html = modelloEmail('Conferma la registrazione',
            '<p>Ciao ' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>per completare la registrazione a BeyondReality Journeys conferma il tuo indirizzo email:</p>'
            . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:10px 18px;background:#142c71;color:#fff;border-radius:6px;text-decoration:none;">Conferma email</a></p>'
            . '<p>Il link è valido ' . CONFERMA_DURATA_ORE . ' ore. Se il pulsante non funziona, copia questo indirizzo nel browser:<br>'
            . '<span style="font-size:12px;word-break:break-all;">' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</span></p>');
        $inviata = inviaEmail($email, trim("$nome $cognome"), 'Conferma la tua registrazione a BeyondReality Journeys', $html);
    }

    if (!$inviata) {
        tornaAllaPagina("Non è stato possibile inviare l'email di conferma in questo momento. Riprova più tardi.");
    }

    // Stessa risposta in entrambi i casi
    $_SESSION['auth'] = "Ti abbiamo inviato un'email all'indirizzo $email: apri il link entro " . CONFERMA_DURATA_ORE
        . " ore per completare la registrazione (controlla anche lo spam).";
    header("Location: autenticazione.php?login&email=" . urlencode($email));
    exit();

} else if ($form == "login") {
    $email = strtolower(trim((string) ($_POST["email"] ?? "")));
    $password = (string) ($_POST["password"] ?? "");
    $ip = ipRichiesta();
    $tornaAlLogin = "?login&email=" . urlencode($email);

    // Troppi errori recenti: il login viene rifiutato senza nemmeno controllare la password
    $attesa = secondiBloccoAccesso($db, $email, $ip);
    if ($attesa > 0) {
        $minuti = (int) ceil($attesa / 60);
        tornaAllaPagina("Troppi tentativi di accesso falliti. Riprova tra $minuti minut" . ($minuti === 1 ? "o" : "i") . ".", $tornaAlLogin);
    }

    $stmt = mysqli_prepare($db, "SELECT password, verificato FROM utente WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $utente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($utente) {
        $passwordCorretta = password_verify($password, $utente['password']);
    } else {
        // Email inesistente: un calcolo bcrypt equivalente, così la risposta impiega lo stesso tempo
        password_hash($password, PASSWORD_DEFAULT);
        $passwordCorretta = false;
    }

    if (!$passwordCorretta) {
        registraTentativoFallito($db, $email, $ip);
        tornaAllaPagina("Email o password non corretti.", $tornaAlLogin);
    }

    // Password giusta ma email non ancora confermata (lo può sapere solo chi conosce la password)
    if ((int) $utente['verificato'] !== 1) {
        tornaAllaPagina("Devi prima confermare l'email: apri il link che ti abbiamo inviato (controlla anche lo spam). "
            . "Se è scaduto, registrati di nuovo per riceverne uno nuovo.", $tornaAlLogin);
    }

    azzeraTentativi($db, $email);
    // Nuovo ID di sessione al login: impedisce la session fixation.
    // Anche il token CSRF viene rigenerato (alla prossima pagina).
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['email'] = $email;
    header("Location: index.php");
    exit();
}
mysqli_close($db);
