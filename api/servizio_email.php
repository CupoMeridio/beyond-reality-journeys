<?php
/**
 * BeyondReality Journeys - Invio email tramite l'API HTTP di Brevo.
 * (Sostituisce PHPMailer/SMTP: su InfinityFree le porte SMTP in uscita non sono utilizzabili.)
 *
 * Uso: require_once 'api/servizio_email.php'; $ok = inviaEmail($email, $nome, $oggetto, $html);
 *
 * In locale si può definire EMAIL_SOLO_LOG = true in api/config.local.php: le email non vengono
 * inviate ma scritte nel log degli errori di PHP (utile per provare il recupero password).
 */
require_once __DIR__ . '/configurazione.php';

/**
 * Invia un'email HTML (con alternativa in testo semplice).
 *
 * @return bool true se Brevo ha accettato il messaggio
 */
function inviaEmail(string $destinatario, string $nomeDestinatario, string $oggetto, string $html): bool
{
    // Alternativa in testo semplice: i link diventano "testo (indirizzo)" (altrimenti strip_tags li
    // cancellerebbe), a capo al posto di <br>/</p>, niente rientri né righe vuote multiple
    $testo = preg_replace('~<a\s[^>]*href="([^"]*)"[^>]*>(.*?)</a>~is', '$2 ($1)', $html);
    $testo = html_entity_decode(strip_tags(preg_replace('~<(br|/p|/h\d)\s*/?>~i', "\n", $testo)), ENT_QUOTES, 'UTF-8');
    $testo = trim(preg_replace(["~^[ \t]+~m", "~\n{3,}~"], ['', "\n\n"], $testo));

    if (AMBIENTE_LOCALE && defined('EMAIL_SOLO_LOG') && EMAIL_SOLO_LOG) {
        error_log("[EMAIL non inviata, modalità solo log] A: $destinatario | Oggetto: $oggetto\n$testo");
        return true;
    }

    $dati = [
        'sender' => ['name' => BREVO_SENDER_NAME, 'email' => BREVO_SENDER_EMAIL],
        'to' => [['email' => $destinatario, 'name' => trim($nomeDestinatario) !== '' ? trim($nomeDestinatario) : $destinatario]],
        'subject' => $oggetto,
        'htmlContent' => $html,
        'textContent' => $testo,
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($dati, JSON_UNESCAPED_UNICODE),
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'api-key: ' . BREVO_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $risposta = curl_exec($ch);
    $codice = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erroreCurl = curl_errno($ch) ? curl_error($ch) : '';
    // Niente curl_close(): da PHP 8.0 non ha effetto (l'handle si chiude da solo) ed è deprecata in PHP 8.5

    if ($erroreCurl !== '') {
        error_log("Invio email fallito (cURL): $erroreCurl");
        return false;
    }
    if ($codice < 200 || $codice >= 300) {
        error_log("Invio email fallito (Brevo HTTP $codice): $risposta");
        return false;
    }
    return true;
}

/** Corpo HTML comune delle email del sito. $contenuto è HTML già sicuro (escapato dal chiamante). */
function modelloEmail(string $titolo, string $contenuto): string
{
    $titolo = htmlspecialchars($titolo, ENT_QUOTES, 'UTF-8');
    return <<<HTML
<!DOCTYPE html>
<html lang="it">
<body style="margin:0;padding:24px;background:#f2f4f8;font-family:Arial,sans-serif;color:#222;">
  <div style="max-width:480px;margin:auto;background:#fff;border-radius:10px;padding:24px;">
    <h2 style="margin-top:0;color:#142c71;">$titolo</h2>
    $contenuto
    <p style="margin-top:24px;font-size:12px;color:#777;">BeyondReality Journeys &middot; sito dimostrativo.<br>
    Se non hai richiesto tu questa email, puoi ignorarla.</p>
  </div>
</body>
</html>
HTML;
}
