<?php
/**
 * I mondi del sito e le loro destinazioni prenotabili.
 * Usato da prenotazioni (prenotazioni_utils.php) e recensioni (commenti).
 *
 * Ogni pagina viaggio dichiara il proprio mondo in <body data-mondo="..."> e il JS lo invia
 * esplicitamente: prima stava in $_SESSION['mondo'], che con più schede aperte puntava
 * all'ultima pagina visitata.
 */

const NOMI_MONDI = [
    'jojo' => "JoJo's Bizarre Adventure",
    'doctor_who' => 'Doctor Who',
    'dragon_ball' => 'Dragon Ball',
];

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

function mondoValido(mixed $mondo): bool
{
    return is_string($mondo) && isset(NOMI_MONDI[$mondo]);
}
