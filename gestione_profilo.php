<?php
/**
 * Modifica dei dati dell'account. La pagina viene mostrata in un iframe (components/dashboard.html).
 *
 * Flusso: il form viene inviato normalmente dentro l'iframe. In caso di successo la risposta
 * è una piccola pagina che avvisa la pagina principale (postMessage 'operationComplete'),
 * che chiude l'iframe e si ricarica. In caso di errore si torna al form (Post/Redirect/Get).
 */
require_once __DIR__ . '/api/avvio.php';
require_once __DIR__ . '/api/validazione_generale.php';
require_once __DIR__ . '/api/foto_profilo_utils.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    exit('Devi accedere per modificare il tuo account.');
}

function tornaAlForm(string $errore): void
{
    $_SESSION['errore_profilo'] = $errore;
    header("Location: gestione_profilo.php");
    exit();
}

if (isset($_POST['update'])) {
    if (!csrfValido()) {
        tornaAlForm(MESSAGGIO_CSRF);
    }
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $username = trim($_POST['username'] ?? '');

    // Stesse regole della registrazione
    if (!controlloPatternNome($nome) || !controlloPatternCognome($cognome) || $username === '' || mb_strlen($username) > 100) {
        tornaAlForm("Dati non validi: controlla nome, cognome e username.");
    }

    try {
        $foto = leggiFotoProfilo($_FILES['fotoProfilo'] ?? null);
    } catch (RuntimeException $e) {
        tornaAlForm($e->getMessage());
    }

    include __DIR__ . '/api/connessione_db.php';
    /** @var mysqli $db */

    if ($foto !== null) {
        $stmt = mysqli_prepare($db, "UPDATE utente SET nome = ?, cognome = ?, username = ?, img = ?, type = ? WHERE email = ?");
        $img = null;
        mysqli_stmt_bind_param($stmt, "sssbss", $nome, $cognome, $username, $img, $foto['tipo'], $_SESSION['email']);
        inviaBlob($stmt, 3, $foto['dati']);
    } else {
        $stmt = mysqli_prepare($db, "UPDATE utente SET nome = ?, cognome = ?, username = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "ssss", $nome, $cognome, $username, $_SESSION['email']);
    }

    try {
        mysqli_stmt_execute($stmt);
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            tornaAlForm("Questo username è già in uso.");
        }
        error_log("Errore aggiornamento profilo: " . $e->getMessage());
        tornaAlForm("Errore durante l'aggiornamento dei dati.");
    }
    mysqli_close($db);

    // La sessione viene aggiornata subito: anche le pagine che non rileggono il DB (viaggio_*.php)
    // mostrano i dati nuovi
    $_SESSION['nome'] = $nome;
    $_SESSION['cognome'] = $cognome;
    $_SESSION['username'] = $username;
    if ($foto !== null) {
        $_SESSION['img'] = urlFotoProfilo(md5($foto['dati']));
    }
    ?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Dati aggiornati</title></head>
<body>
    <script>window.parent.postMessage('operationComplete', window.location.origin);</script>
    <p>Dati aggiornati.</p>
</body>
</html>
    <?php
    exit();
}

$nome = $_SESSION['nome'] ?? '';
$cognome = $_SESSION['cognome'] ?? '';
$username = $_SESSION['username'] ?? '';
$img = $_SESSION['img'] ?? FOTO_DEFAULT;
$errore = $_SESSION['errore_profilo'] ?? '';
unset($_SESSION['errore_profilo']);

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica dati</title>
    <link rel="stylesheet" href="css/stile_navbar.css">
    <link rel="stylesheet" href="css/stile_footer.css">
    <link rel="stylesheet" href="css/stile_modifica_profilo.css">
    <?php include("components/imposta_icona.html"); ?>
    <script src="js/utils.js" type="text/javascript" defer></script>
</head>

<body>
    <div id="modifica">
        <form action="gestione_profilo.php" method="post" enctype="multipart/form-data">
            <?= campoCsrf() ?>
            <h2>Modifica Dati</h2>
            <h3>Attenzione, modificare i dati ricaricherà la pagina.</h3>
            <div class="wrapper">
                <img src="<?php echo e($img); ?>" alt="Foto Profilo" width="100" id="current">

                <div id="anteprimaFoto" class="anteprima-foto">
                    <img id="immagineAnteprima" src="" alt="Anteprima foto profilo">
                </div>
            </div>

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo e($nome); ?>" autocomplete="given-name" required>

            <label for="cognome">Cognome:</label>
            <input type="text" id="cognome" name="cognome" value="<?php echo e($cognome); ?>" autocomplete="family-name" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo e($username); ?>" autocomplete="username" maxlength="100" required>

            <div class="file-input-container">
                <label for="fotoProfilo" class="custom-file-label">Scegli un file</label>
                <input type="file" id="fotoProfilo" name="fotoProfilo" accept="image/jpeg,image/png,image/gif,image/webp" class="custom-file-input">
            </div>

            <div class="wrapper">
                <input type="submit" name="update" value="Aggiorna Dati">
            </div>
            <div class="wrapper">
                <input type="button" name="close" value="Chiudi" onclick="window.parent.postMessage('closeIframe', window.location.origin)">
            </div>
        </form>
        <?php if ($errore !== '') { ?>
            <p role="alert"><?php echo e($errore); ?></p>
        <?php } ?>
    </div>
    <script>
        const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1 MB, come il controllo lato server

        const fotoProfiloInput = document.getElementById('fotoProfilo');
        const immagineAnteprima = document.getElementById('immagineAnteprima');
        const anteprimaDiv = document.getElementById('anteprimaFoto');
        const imgCorrente = document.getElementById('current');

        function mostraFotoCorrente() {
            anteprimaDiv.style.display = "none";
            imgCorrente.style.display = "block";
        }

        fotoProfiloInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                mostraFotoCorrente();
                return;
            }
            if (file.size > MAX_FILE_SIZE) {
                alert('Il file supera la dimensione massima consentita di 1 MB.');
                this.value = "";
                mostraFotoCorrente();
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                immagineAnteprima.src = e.target.result;
                anteprimaDiv.style.display = "block";
                imgCorrente.style.display = "none";
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>

</html>
