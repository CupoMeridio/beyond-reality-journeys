<?php
require_once __DIR__ . '/avvio.php';

require __DIR__ . '/connessione_db.php';
/** @var mysqli $db */

require __DIR__ . '/validazione_generale.php';
require_once __DIR__ . '/foto_profilo_utils.php';
if (!isset($_POST["action"])) {
    exit;
}

$form = $_POST["action"];
$_SESSION['errore'] = "";
if ($form == "reg") {
    if (isset($_POST["nome"], $_POST["cognome"], $_POST["username"], $_POST["email"], $_POST["password"])) {
        $nome = trim($_POST["nome"]);
        $cognome = trim($_POST["cognome"]);
        $email = strtolower(trim($_POST["email"]));
        $username = trim($_POST["username"]);
        $password_pre_hash = $_POST["password"];

        if (!(controlloPatternEmail($email) && controlloPatternPassword($password_pre_hash) && controlloPatternNome($nome) && controlloPatternCognome($cognome) && $username !== '')) {
            $_SESSION['errore'] = "Alcuni dati non sono validi: controlla i campi e riprova.";
            header("Location: autenticazione.php");
            exit();
        }

        try {
            $foto = leggiFotoProfilo($_FILES["fotoProfilo"] ?? null);
        } catch (RuntimeException $e) {
            $_SESSION['errore'] = $e->getMessage();
            header("Location: autenticazione.php");
            exit();
        }

        $hash = password_hash($password_pre_hash, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($db, "INSERT INTO utente (nome, cognome, username, email, password, img, type) VALUES (?, ?, ?, ?, ?, ?, ?)");

        // img è un parametro "b": il contenuto arriva con send_long_data; senza foto resta NULL
        $img = null;
        $tipo = $foto['tipo'] ?? null;
        mysqli_stmt_bind_param($stmt, "sssssbs", $nome, $cognome, $username, $email, $hash, $img, $tipo);
        if ($foto !== null) {
            inviaBlob($stmt, 5, $foto['dati']);
        }

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            $_SESSION['errore'] = $e->getCode() === 1062
                ? "Email o username già in uso."
                : "Impossibile contattare il database, riprova più tardi.";
            if ($e->getCode() !== 1062) {
                error_log("Errore registrazione: " . $e->getMessage());
            }
            header("Location: autenticazione.php");
            exit();
        }

        $_SESSION['auth'] = "Registrazione completata: ora puoi accedere.";
        header("Location: autenticazione.php?login&email=" . urlencode($email));
        exit();
    }
} else if ($form == "login") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    if (controlloEmail($email, $db)) {
        if (controlloPassword($email, $password, $db)) {
            // Nuovo ID di sessione al login: impedisce la session fixation
            session_regenerate_id(true);
            $_SESSION['email'] = strtolower($email);
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['errore'] = $_SESSION['errore'] . " Password errata. ";
            header("Location: autenticazione.php?login");
            exit();
        }

    } else {
        $_SESSION['errore'] = $_SESSION['errore'] . " L'email inserita non è associata ad alcun account. ";
        header("Location: autenticazione.php?login");
        exit();
    }

}
mysqli_close($db);

?>