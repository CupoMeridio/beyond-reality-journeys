<?php
/**
 * Funzioni comuni per la foto profilo (registrazione e modifica account).
 *
 * La foto è salvata come BLOB nella tabella utente. Non viene più copiata in sessione
 * come data-URI base64 (fino a ~1,4 MB per ogni richiesta): la sessione contiene solo
 * l'URL di api/foto_profilo.php, con un hash del contenuto per la cache del browser.
 */

const FOTO_MAX_BYTES = 1024 * 1024; // 1 MB, limite scelto per InfinityFree
const FOTO_TIPI_AMMESSI = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
const FOTO_DEFAULT = 'img/common/default.png';

/**
 * Valida il file caricato e ne restituisce contenuto e tipo MIME reale.
 *
 * @param array<string, mixed>|null $file elemento di $_FILES
 * @return array{dati: string, tipo: string}|null null se l'utente non ha scelto un file
 * @throws RuntimeException con un messaggio mostrabile all'utente
 */
function leggiFotoProfilo(?array $file): ?array
{
    $errore = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($errore === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (in_array($errore, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || ($file['size'] ?? 0) > FOTO_MAX_BYTES) {
        throw new RuntimeException("L'immagine è troppo grande (max 1 MB).");
    }
    if ($errore !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException("Caricamento dell'immagine non riuscito, riprova.");
    }

    // Il tipo dichiarato dal browser ($file['type']) non è affidabile: si legge quello reale
    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info['mime'], FOTO_TIPI_AMMESSI, true)) {
        throw new RuntimeException("Formato non supportato: usa un'immagine JPG, PNG, GIF o WEBP.");
    }

    return ['dati' => file_get_contents($file['tmp_name']), 'tipo' => $info['mime']];
}

/**
 * Invia un BLOB a un parametro "b" di uno statement preparato, a pezzi,
 * per non superare max_allowed_packet con un singolo pacchetto.
 */
function inviaBlob(mysqli_stmt $stmt, int $indiceParametro, string $dati): void
{
    foreach (str_split($dati, 256 * 1024) as $pezzo) {
        mysqli_stmt_send_long_data($stmt, $indiceParametro, $pezzo);
    }
}

/** URL da usare negli <img>: l'endpoint della foto, oppure l'immagine predefinita. */
function urlFotoProfilo(?string $hash): string
{
    return $hash ? 'api/foto_profilo.php?v=' . $hash : FOTO_DEFAULT;
}
