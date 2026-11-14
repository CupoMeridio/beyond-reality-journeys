<?php
/**
 * Regole delle prenotazioni condivise da crea_pagamento.php, conferma_prenotazione.php,
 * recupera_prenotazioni.php e genera_ricevuta_pdf.php.
 * Il server è l'unica fonte di verità: destinazioni, limiti e prezzo non arrivano dal client.
 */

/** Destinazioni prenotabili per ogni mondo: valore del form => nome mostrato. */
const DESTINAZIONI = [
    'jojo' => [
        'roma' => 'Roma',
        'venezia' => 'Venezia',
        'napoli' => 'Napoli',
    ],
    'doctor_who' => [
        'Gallifrey' => 'Gallifrey',
        'Skaro' => 'Skaro',
        'Prigione_dei_signori_del_tempo' => 'Prigione dei signori del tempo',
    ],
    'dragon_ball' => [
        'kame_house' => 'Kame House',
        'namecc' => 'Namecc',
        'king_kai' => 'Pianeta di King Kai',
    ],
];

const NOMI_MONDI = [
    'jojo' => "JoJo's Bizarre Adventure",
    'doctor_who' => 'Doctor Who',
    'dragon_ball' => 'Dragon Ball',
];

const BIGLIETTI_MAX = 10;
const DURATA_MAX_GIORNI = 365;
const ANTICIPO_MAX_GIORNI = 730;      // si può prenotare al massimo 2 anni prima
const PREZZO_GIORNO_BIGLIETTO = 5000; // centesimi: 50 € al giorno per biglietto
const COSTO_FISSO = 500;              // centesimi: 5 € di diritti di prenotazione
const LUNGHEZZA_MAX_NOME = 100;
const LUNGHEZZA_MAX_COMMENTO = 500;

/** Giorni di viaggio fatturati: differenza tra le date, minimo 1. */
function giorniViaggio(DateTimeImmutable $partenza, DateTimeImmutable $ritorno): int
{
    return max(1, (int) $partenza->diff($ritorno)->days);
}

function calcolaImportoCentesimi(int $giorni, int $biglietti): int
{
    return $giorni * $biglietti * PREZZO_GIORNO_BIGLIETTO + COSTO_FISSO;
}

function formattaEuro(int $centesimi): string
{
    return number_format($centesimi / 100, 2, ',', '.') . ' €';
}

function nomeDestinazione(string $mondo, string $destinazione): string
{
    return DESTINAZIONI[$mondo][$destinazione] ?? $destinazione;
}

function leggiData(string $valore): ?DateTimeImmutable
{
    $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valore);
    return ($data && $data->format('Y-m-d') === $valore) ? $data : null;
}

/**
 * Valida i dati del form di prenotazione.
 *
 * @param array<string, mixed> $input  di solito $_POST
 * @return array{mondo: string, destinazione: string, data_partenza: string, data_ritorno: string,
 *               num_biglietti: int, nominativi: list<string>, commento: string, giorni: int, importo: int}
 * @throws InvalidArgumentException con un messaggio mostrabile all'utente
 */
function validaPrenotazione(array $input, string $mondo): array
{
    if (!isset(DESTINAZIONI[$mondo])) {
        throw new InvalidArgumentException("Mondo non valido: riapri la pagina del viaggio.");
    }

    $destinazione = (string) ($input['location'] ?? '');
    if (!isset(DESTINAZIONI[$mondo][$destinazione])) {
        throw new InvalidArgumentException("Destinazione non valida.");
    }

    $biglietti = filter_var($input['num_biglietti'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => BIGLIETTI_MAX]]);
    if ($biglietti === false) {
        throw new InvalidArgumentException("Il numero di biglietti deve essere tra 1 e " . BIGLIETTI_MAX . ".");
    }

    $partenza = leggiData((string) ($input['data_p'] ?? ''));
    $ritorno = leggiData((string) ($input['data_r'] ?? ''));
    if (!$partenza || !$ritorno) {
        throw new InvalidArgumentException("Inserisci date di partenza e ritorno valide.");
    }
    $oggi = new DateTimeImmutable('today');
    if ($partenza < $oggi) {
        throw new InvalidArgumentException("La data di partenza non può essere nel passato.");
    }
    if ($partenza > $oggi->modify('+' . ANTICIPO_MAX_GIORNI . ' days')) {
        throw new InvalidArgumentException("Si può prenotare al massimo due anni prima della partenza.");
    }
    if ($ritorno < $partenza) {
        throw new InvalidArgumentException("La data di ritorno deve essere successiva alla partenza.");
    }
    $giorni = giorniViaggio($partenza, $ritorno);
    if ($giorni > DURATA_MAX_GIORNI) {
        throw new InvalidArgumentException("Il viaggio può durare al massimo " . DURATA_MAX_GIORNI . " giorni.");
    }

    $nominativi = json_decode((string) ($input['nominativi'] ?? ''), true);
    if (!is_array($nominativi) || count($nominativi) !== $biglietti) {
        throw new InvalidArgumentException("Inserisci un nominativo per ogni biglietto.");
    }
    $nominativi = array_map(fn($n) => trim((string) $n), array_values($nominativi));
    foreach ($nominativi as $nome) {
        if ($nome === '' || mb_strlen($nome) > LUNGHEZZA_MAX_NOME) {
            throw new InvalidArgumentException("Ogni nominativo deve avere da 1 a " . LUNGHEZZA_MAX_NOME . " caratteri.");
        }
    }

    $commento = trim((string) ($input['commento'] ?? ''));
    if (mb_strlen($commento) > LUNGHEZZA_MAX_COMMENTO) {
        throw new InvalidArgumentException("Il commento può avere al massimo " . LUNGHEZZA_MAX_COMMENTO . " caratteri.");
    }

    return [
        'mondo' => $mondo,
        'destinazione' => $destinazione,
        'data_partenza' => $partenza->format('Y-m-d'),
        'data_ritorno' => $ritorno->format('Y-m-d'),
        'num_biglietti' => $biglietti,
        'nominativi' => $nominativi,
        'commento' => $commento,
        'giorni' => $giorni,
        'importo' => calcolaImportoCentesimi($giorni, $biglietti),
    ];
}

/** Risposta JSON con codice HTTP, poi fine dello script. */
function rispondiJson(array $dati, int $codice = 200): void
{
    http_response_code($codice);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dati, JSON_UNESCAPED_UNICODE);
    exit();
}
