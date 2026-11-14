<?php
/**
 * Fase 1 del pagamento: valida la prenotazione, calcola il prezzo e crea il PaymentIntent.
 * I dati validati restano in sessione (bozza) finché conferma_prenotazione.php non verifica
 * con Stripe che il pagamento sia riuscito.
 */
require_once __DIR__ . '/../avvio.php';
require_once __DIR__ . '/../prenotazioni_utils.php';
require_once __DIR__ . '/configurazione_stripe.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rispondiJson(['error' => 'Metodo non consentito.'], 405);
}
if (!isset($_SESSION['email'])) {
    rispondiJson(['error' => 'Devi accedere per prenotare.'], 401);
}

try {
    $prenotazione = validaPrenotazione($_POST, (string) ($_POST['mondo'] ?? ''));
} catch (InvalidArgumentException $e) {
    rispondiJson(['error' => $e->getMessage()], 422);
}

$nomeDestinazione = nomeDestinazione($prenotazione['mondo'], $prenotazione['destinazione']);

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $prenotazione['importo'],
        'currency' => 'eur',
        'payment_method_types' => ['card'],
        'description' => "BeyondReality: $nomeDestinazione, {$prenotazione['num_biglietti']} biglietti",
        'metadata' => [
            'email' => $_SESSION['email'],
            'mondo' => $prenotazione['mondo'],
            'destinazione' => $prenotazione['destinazione'],
            'partenza' => $prenotazione['data_partenza'],
            'ritorno' => $prenotazione['data_ritorno'],
            'biglietti' => (string) $prenotazione['num_biglietti'],
        ],
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log('Stripe, creazione PaymentIntent fallita: ' . $e->getMessage());
    rispondiJson(['error' => 'Impossibile avviare il pagamento, riprova più tardi.'], 502);
}

$_SESSION['bozza_pagamento'] = $prenotazione + ['payment_intent' => $paymentIntent->id];

rispondiJson([
    'clientSecret' => $paymentIntent->client_secret,
    'publishableKey' => STRIPE_PUBLISHABLE_KEY,
    'importo' => $prenotazione['importo'],
    'importoFormattato' => formattaEuro($prenotazione['importo']),
    'riepilogo' => sprintf(
        '%s · %d bigliett%s · %d giorn%s',
        $nomeDestinazione,
        $prenotazione['num_biglietti'],
        $prenotazione['num_biglietti'] === 1 ? 'o' : 'i',
        $prenotazione['giorni'],
        $prenotazione['giorni'] === 1 ? 'o' : 'i'
    ),
]);
