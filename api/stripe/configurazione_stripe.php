<?php
/**
 * Inizializzazione di Stripe. Il sito è dimostrativo: sono ammesse SOLO chiavi di test,
 * con cui Stripe accetta esclusivamente le carte di prova e non addebita mai denaro reale.
 * Con una chiave live ogni endpoint di pagamento si rifiuta di funzionare.
 */
require_once __DIR__ . '/../configurazione.php';
require_once __DIR__ . '/../../vendor/autoload.php';

function chiaviStripeDiTest(): bool
{
    return str_starts_with(STRIPE_SECRET_KEY, 'sk_test_')
        && str_starts_with(STRIPE_PUBLISHABLE_KEY, 'pk_test_');
}

if (!chiaviStripeDiTest()) {
    error_log('Pagamenti bloccati: configurate chiavi Stripe non di test.');
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Pagamenti disabilitati: questo sito dimostrativo funziona solo in modalità test.']);
    exit();
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
