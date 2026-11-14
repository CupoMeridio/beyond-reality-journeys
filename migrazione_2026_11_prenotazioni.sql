-- Migrazione novembre 2026: nuova struttura della tabella prenotazione.
-- Da eseguire una volta (es. da phpMyAdmin su InfinityFree).
-- ATTENZIONE: cancella le prenotazioni esistenti (sito dimostrativo, dati non importanti).

DROP TABLE IF EXISTS prenotazione;

-- Tabella prenotazioni: una riga per prenotazione (non per biglietto)
CREATE TABLE IF NOT EXISTS prenotazione (
    id_prenotazione INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    mondo VARCHAR(50) NOT NULL,
    destinazione VARCHAR(100) NOT NULL,
    data_partenza DATE NOT NULL,
    data_ritorno DATE NOT NULL,
    num_biglietti TINYINT UNSIGNED NOT NULL,
    nominativi TEXT NOT NULL,                       -- array JSON dei nomi dei passeggeri
    commento TEXT,
    importo_centesimi INT UNSIGNED NOT NULL,        -- importo verificato su Stripe
    payment_intent_id VARCHAR(255) NOT NULL UNIQUE, -- una prenotazione per pagamento
    data_prenotazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES utente(email) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_prenotazione_email ON prenotazione(email);
