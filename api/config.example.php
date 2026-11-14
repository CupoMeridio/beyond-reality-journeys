<?php
/**
 * BeyondReality Journeys - Modello di configurazione.
 *
 * Produzione: copia questo file in api/config.php e inserisci i valori reali.
 * Locale (XAMPP): copia questo file anche in api/config.local.php e lascia solo le
 * costanti da sovrascrivere (di solito quelle del database). config.local.php viene
 * letto solo quando il sito gira su localhost.
 *
 * Né config.php né config.local.php vanno committati (sono nel .gitignore).
 * Usa il formato `defined('X') || define('X', ...)` così i valori locali hanno la precedenza.
 */

// --- DATABASE (MySQL/MariaDB) ---
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_PORT') || define('DB_PORT', 3306);
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
defined('DB_NAME') || define('DB_NAME', 'sito_viaggi_fantasia');

// --- STRIPE (solo chiavi di TEST: il sito è dimostrativo) ---
defined('STRIPE_SECRET_KEY') || define('STRIPE_SECRET_KEY', 'sk_test_...');
defined('STRIPE_PUBLISHABLE_KEY') || define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');

// --- BREVO (invio email via API HTTP) ---
defined('BREVO_API_KEY') || define('BREVO_API_KEY', 'xkeysib-...');
defined('BREVO_SENDER_EMAIL') || define('BREVO_SENDER_EMAIL', 'mittente@example.com');
defined('BREVO_SENDER_NAME') || define('BREVO_SENDER_NAME', 'BeyondReality Journeys');
