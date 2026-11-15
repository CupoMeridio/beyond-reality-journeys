<?php
/**
 * Registrazione e login nella stessa pagina: ?login mostra il login, altrimenti la registrazione.
 * L'invio dei form viene elaborato da api/elabora_autenticazione.php (stessa pagina, metodo POST).
 */
require_once __DIR__ . '/api/avvio.php';

if (isset($_POST["inviato"])) {
    include 'api/elabora_autenticazione.php';
    exit();
}

$mostraRegistrazione = !isset($_GET['login']);

// Messaggi lasciati in sessione dall'ultima richiesta (mostrati una sola volta)
$messaggioErrore = $_SESSION['errore'] ?? '';
$messaggioAuth = $_SESSION['auth'] ?? '';
unset($_SESSION['errore'], $_SESSION['auth']);

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
    <title>Accedi o registrati</title>
    <link rel="stylesheet" href="css/stile_autenticazione.css">
    <?php include 'components/imposta_icona.html'; ?>
    <script src="js/utils.js" defer></script>
    <script src="js/logica_autenticazione.js" defer></script>
</head>

<body>
    <nav>
        <a href="index.php"><img src="img/common/logo.png" alt="BeyondReality Journeys - Home"></a>
        <a class="navButton" id="homeButton" href="index.php">Home</a>
        <a class="navButton" id="aboutButton" href="index.php#about-section">About</a>
        <a class="navButton" id="contactButton" href="index.php#contact-section">Contact</a>
    </nav>

    <video id="background-video" autoplay muted loop playsinline></video>

    <!-- Registrazione -->
    <div id="main-container" class="regcontainer" style="display: <?= $mostraRegistrazione ? 'block' : 'none' ?>;">
        <div id="registrazione_page" class="page">
            <form id="form-registrazione" action="autenticazione.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="reg">
                <?= campoCsrf() ?>
                <div id="registrazione">
                    <div class="form-group">
                        <div class="form-fields">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" autocomplete="given-name" required>
                            <div id="nameError" class="error"></div>
                        </div>

                        <div class="form-fields">
                            <label for="cognome">Cognome</label>
                            <input type="text" id="cognome" name="cognome" autocomplete="family-name" required>
                            <div id="cognomeError" class="error"></div>
                        </div>

                        <div class="form-fields">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" autocomplete="username" maxlength="100" required>
                        </div>

                        <div class="form-fields">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" autocomplete="email" required>
                            <div id="emailError" class="error"></div>
                        </div>

                        <div class="form-fields">
                            <label for="password">Password <span id="pwReg" class="vedoPassword" data-mostra-password="password"></span></label>
                            <input type="password" id="password" name="password" autocomplete="new-password" required>
                            <div class="password-hint" id="passwordHint">
                                La password deve contenere almeno 8 caratteri e includere almeno una
                                lettera maiuscola, una lettera minuscola, un numero e un carattere speciale.
                            </div>
                            <span id="passwordError" class="error"></span>
                            <div class="password-security" id="passwordSecurity"></div>
                        </div>

                        <div class="form-fields">
                            <label for="confirmPassword">Conferma password <span id="confirmReg" class="vedoPassword" data-mostra-password="confirmPassword"></span></label>
                            <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="new-password" required>
                            <div id="passwordOK" class="confirm"></div>
                            <div id="confirmPasswordError" class="error"></div>
                        </div>
                    </div>

                    <div class="form-photo">
                        <div class="form-group">
                            <label for="fotoProfilo">Foto profilo</label>
                            <div id="dropArea" class="drop-area">
                                Trascina qui la tua immagine o clicca per selezionarla.
                                <input type="file" id="fotoProfilo" name="fotoProfilo" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                            </div>
                            <div id="anteprimaFoto" class="anteprima-foto">
                                <img id="immagineAnteprima" src="" alt="Anteprima foto profilo" style="display: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button-submit-container">
                    <input type="submit" value="Registrati" name="inviato" id="submitButton">
                </div>
                <div class="messaggio-login">Sei già registrato? <a href="?login">Login</a></div>
                <p id="message"></p>
            </form>

            <?php if ($messaggioErrore !== '') { ?>
                <p id="messageErrorReg" class="messageError"><?= e($messaggioErrore) ?></p>
            <?php } ?>
        </div>
    </div>

    <!-- Login -->
    <div id="login_page" class="logincontainer" style="display: <?= $mostraRegistrazione ? 'none' : 'block' ?>;">
        <?php if ($messaggioAuth !== '') { ?>
            <p id="messageAuth" class="messageAuth"><?= e($messaggioAuth) ?></p>
        <?php } ?>

        <form id="form-login" action="autenticazione.php" method="post">
            <input type="hidden" name="action" value="login">
            <?= campoCsrf() ?>
            <div id="login">
                <div class="form-group">
                    <div class="form-fields">
                        <label for="email-login">Email</label>
                        <input type="email" id="email-login" name="email" value="<?= e((string) ($_GET['email'] ?? '')) ?>" autocomplete="email" required>
                        <div id="emailErrorLogin" class="error"></div>
                    </div>

                    <div class="form-fields">
                        <label for="password-login">Password <span id="pwLogin" class="vedoPassword" data-mostra-password="password-login"></span></label>
                        <input type="password" id="password-login" name="password" autocomplete="current-password" required>
                        <div id="passwordErrorLogin" class="error"></div>
                    </div>
                </div>

                <div class="button-submit-container">
                    <input type="submit" value="Login" name="inviato">
                </div>
                <div class="messaggio-registrazione">Non sei registrato? <a href="?register">Registrati</a></div>
                <div class="messaggio-registrazione">Password dimenticata? <a href="recupero_password.php">Resetta</a></div>

                <?php if ($messaggioErrore !== '') { ?>
                    <p id="messageErrorLog" class="messageError"><?= e($messaggioErrore) ?></p>
                <?php } ?>
            </div>
        </form>
    </div>
</body>

</html>
