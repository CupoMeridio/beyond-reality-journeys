<?php
/**
 * Limite ai tentativi di login falliti (protezione dai tentativi a forza bruta).
 *
 * I tentativi falliti vengono salvati nella tabella tentativo_accesso (non in sessione:
 * chi attacca potrebbe cancellare i cookie e ripartire da zero). Il blocco è a finestra mobile:
 *  - al massimo ACCESSI_MAX_PER_EMAIL errori per la stessa email negli ultimi ACCESSI_FINESTRA_SECONDI;
 *  - al massimo ACCESSI_MAX_PER_IP errori dallo stesso indirizzo IP (chi prova molte email diverse).
 * Un accesso riuscito azzera il contatore dell'email. Tutti i tempi sono calcolati dal database,
 * così non dipendono dal fuso orario di PHP.
 */

const ACCESSI_FINESTRA_SECONDI = 900; // 15 minuti
const ACCESSI_MAX_PER_EMAIL = 5;
const ACCESSI_MAX_PER_IP = 20;
const ACCESSI_CONSERVAZIONE_SECONDI = 86400; // le righe più vecchie di un giorno vengono eliminate

function ipRichiesta(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/**
 * Secondi che mancano allo sblocco, oppure 0 se il login è consentito.
 */
function secondiBloccoAccesso(mysqli $db, string $email, string $ip): int
{
    $attesa = 0;
    foreach ([['email', $email, ACCESSI_MAX_PER_EMAIL], ['ip', $ip, ACCESSI_MAX_PER_IP]] as [$colonna, $valore, $massimo]) {
        // $colonna è una delle due costanti qui sopra, mai un dato dell'utente
        $stmt = mysqli_prepare($db,
            "SELECT COUNT(*) AS n,
                    TIMESTAMPDIFF(SECOND, NOW(), MIN(data_tentativo) + INTERVAL " . ACCESSI_FINESTRA_SECONDI . " SECOND) AS attesa
             FROM tentativo_accesso
             WHERE $colonna = ? AND data_tentativo > NOW() - INTERVAL " . ACCESSI_FINESTRA_SECONDI . " SECOND");
        mysqli_stmt_bind_param($stmt, "s", $valore);
        mysqli_stmt_execute($stmt);
        $riga = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if ((int) $riga['n'] >= $massimo) {
            $attesa = max($attesa, (int) $riga['attesa'], 1);
        }
    }
    return $attesa;
}

function registraTentativoFallito(mysqli $db, string $email, string $ip): void
{
    $stmt = mysqli_prepare($db, "INSERT INTO tentativo_accesso (email, ip) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $email, $ip);
    mysqli_stmt_execute($stmt);

    // Pulizia: nessuna manutenzione manuale, la tabella resta piccola
    mysqli_query($db, "DELETE FROM tentativo_accesso WHERE data_tentativo < NOW() - INTERVAL " . ACCESSI_CONSERVAZIONE_SECONDI . " SECOND");
}

function azzeraTentativi(mysqli $db, string $email): void
{
    $stmt = mysqli_prepare($db, "DELETE FROM tentativo_accesso WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
}
