-- Script di creazione database per il sito di viaggi fantasia
-- MySQL (compatibile con XAMPP e InfinityFree)

-- Tabella utenti
CREATE TABLE IF NOT EXISTS utente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    img LONGBLOB,
    type VARCHAR(50),
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verificato TINYINT(1) NOT NULL DEFAULT 0,      -- 1 dopo il clic sul link di conferma dell'email
    token_conferma CHAR(64) NULL UNIQUE,           -- hash SHA-256 del token del link di conferma
    scadenza_conferma DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- Tabella commenti
CREATE TABLE IF NOT EXISTS commento (
    id_testo INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    testo TEXT NOT NULL,
    mondo VARCHAR(50) NOT NULL,
    stelle INT DEFAULT 0,
    data_commento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES utente(email) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tentativi di login falliti (limite anti forza bruta, vedi api/limite_accessi.php)
CREATE TABLE IF NOT EXISTS tentativo_accesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    data_tentativo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentativo_email (email, data_tentativo),
    INDEX idx_tentativo_ip (ip, data_tentativo),
    INDEX idx_tentativo_data (data_tentativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indici per migliorare le performance
CREATE INDEX idx_prenotazione_email ON prenotazione(email);
CREATE INDEX idx_commento_mondo ON commento(mondo);
CREATE INDEX idx_commento_email ON commento(email);