[![it](https://img.shields.io/badge/lang-en-red.svg)](https://github.com/CupoMeridio/beyond-reality-journeys/blob/main/README.md)

*Clicca sulla bandiera per passare alla versione inglese.*

# BeyondReality Journeys - Progetto di Tecnologie Web (UNISA 2024-2025)
<img width="1213" height="454" alt="Immagine 2026-04-03 173918" src="https://github.com/user-attachments/assets/182d6dfa-36cd-41cc-8090-22a0af62a9fc" />

## Descrizione del Progetto
Applicazione web per la prenotazione di viaggi in mondi di fantasia (JoJo's Bizarre Adventure, Doctor Who, Dragon Ball), realizzata come prova d'esame per il corso di **Tecnologie Web** dell'Università degli Studi di Salerno (UNISA) e poi ripresa per essere pubblicata online.

Il sito è **dimostrativo**: serve a mostrare i fondamenti delle tecnologie web (HTML5, CSS3, JavaScript, PHP, MySQL). **I viaggi non esistono e nessun pagamento è reale**: Stripe funziona esclusivamente in modalità test.

🌐 Demo online: **[https://beyondrealityjourneys.site.je](https://beyondrealityjourneys.site.je)**

> 💳 Per provare il pagamento usa la carta di prova `4242 4242 4242 4242`, una data di scadenza futura e un CVC qualsiasi. Le carte reali vengono rifiutate.

## 📸Galleria
<table align="center">
  <thead>
    <tr>
      <th>Login</th>
      <th>Registrazione</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><img alt="Immagine1" src="https://github.com/user-attachments/assets/8fc060ed-7cfc-4443-a599-008fa3855271"/></td>
      <td><img width="376" height="243" alt="Immagine2" src="https://github.com/user-attachments/assets/9b87250b-495d-4579-a1fe-71c52236761b"/></td>
    </tr>
</tbody>
</table>
<table align="center">
  <thead>
    <tr>
      <th>Home page</th>
      <th>Dragon Ball page</th>
      <th>Doctor Who page</th>
      <th>Jojo page</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><img width="267" height="454" alt="Immagine3" src="https://github.com/user-attachments/assets/46d918a0-c5f8-4e82-b6d1-51e366785402"/></td>
      <td><img width="183" height="471" alt="Immagine4" src="https://github.com/user-attachments/assets/c9785e13-088d-481a-b946-27662d76ba2c"/></td>
      <td><img width="190" height="471" alt="Immagine5" src="https://github.com/user-attachments/assets/08c39b9f-78f7-47b5-9012-c494e8d4d5c2"/></td>
      <td><img width="185" height="471" alt="Immagine6" src="https://github.com/user-attachments/assets/68073c3e-d3da-47c2-ae2f-2bdcc8aa85f3"/></td>
    </tr>
</tbody>
</table>

---

## Caratteristiche Tecniche

### 1. Utenti e autenticazione
- **Registrazione e login** ([elabora_autenticazione.php](api/elabora_autenticazione.php)): validazione lato client e lato server, password con `password_hash()`, query preparate, nuovo ID di sessione a ogni login.
- **Conferma dell'email** ([conferma_registrazione.php](conferma_registrazione.php)): un nuovo account si attiva solo con il link inviato via email (valido 24 ore); le registrazioni non confermate scadono da sole.
- **Nessuna enumerazione degli account**: il sito non rivela mai se un'email è registrata. La registrazione risponde sempre "ti abbiamo inviato un'email" (se l'account esiste già, viene avvisato il proprietario), il login ha un unico messaggio "email o password non corretti" con lo stesso tempo di risposta e il recupero password risponde allo stesso modo per ogni indirizzo.
- **Limite ai tentativi di login** ([limite_accessi.php](api/limite_accessi.php)): dopo 5 errori sulla stessa email (o 20 dallo stesso IP) in 15 minuti il login viene bloccato temporaneamente.
- **Protezione CSRF** ([avvio.php](api/avvio.php)): ogni form e richiesta AJAX che modifica dati invia un token legato alla sessione; anche il logout è una richiesta POST.
- **Sessioni** ([avvio.php](api/avvio.php)): avvio comune a tutte le pagine, cookie `HttpOnly` e `SameSite=Lax`, HTTPS forzato in produzione.
- **Recupero password** ([recupero_password.php](recupero_password.php)): codice a 6 cifre inviato via email, valido 5 minuti, con al massimo 5 tentativi.
- **Foto profilo** ([foto_profilo_utils.php](api/foto_profilo_utils.php)): salvata come `BLOB`, formato verificato sul contenuto (JPG, PNG, GIF, WEBP, max 1 MB) e servita da un endpoint dedicato con cache del browser.

### 2. Prenotazioni e pagamento (Stripe in modalità test)
- **Il server è l'unica fonte di verità** ([prenotazioni_utils.php](api/prenotazioni_utils.php)): destinazioni, date, numero di biglietti e prezzo vengono validati e calcolati lato server.
- **Flusso di pagamento**:
  1. [crea_pagamento.php](api/stripe/crea_pagamento.php) crea il `PaymentIntent`;
  2. il browser conferma il pagamento con Stripe.js ([integrazione_stripe.js](js/integrazione_stripe.js));
  3. [conferma_prenotazione.php](api/stripe/conferma_prenotazione.php) verifica con Stripe che il pagamento sia riuscito e salva la prenotazione una sola volta.
- **Solo modalità test**: con chiavi Stripe non di test ([configurazione_stripe.php](api/stripe/configurazione_stripe.php)) i pagamenti vengono disabilitati.
- **Ricevuta PDF** ([genera_ricevuta_pdf.php](api/stripe/genera_ricevuta_pdf.php)): generata con FPDF dai dati salvati, scaricabile anche dallo storico ordini.

### 3. Interazioni asincrone (AJAX con `fetch`)
- Recensioni per ogni mondo ([gestione_commenti.js](js/gestione_commenti.js)):
  - aggiornate periodicamente, solo quando la scheda è visibile;
  - testo mostrato in modo sicuro (`textContent`);
  - email degli autori mai esposte.
- Storico ordini e modifica dell'account dalla dashboard utente, senza ricaricare la pagina.

### 4. Email
- Invio tramite l'**API HTTP di Brevo** ([servizio_email.php](api/servizio_email.php)), compatibile con InfinityFree, dove l'SMTP in uscita non è disponibile.
- In locale si può attivare la modalità "solo log", che scrive le email nel log di PHP invece di inviarle.

---

## Tecnologie Utilizzate
- **Frontend**: HTML5, CSS3, JavaScript (vanilla).
- **Backend**: PHP ≥ 8.1 (estensioni `mysqli`, `curl`, `mbstring`, `json`, `gd`).
- **Database**: MySQL / MariaDB.
- **Librerie** (via Composer): [Stripe PHP SDK](https://github.com/stripe/stripe-php), [FPDF](http://www.fpdf.org/).
- **Servizi esterni**: Stripe (modalità test), Brevo (email transazionali).
- **Hosting**: InfinityFree.

---

## Esecuzione in locale (XAMPP)
1. Clona il repository in `C:\xampp\htdocs\Sito_di_viaggi`.
2. Installa le dipendenze:
   ```bash
   composer install
   ```
   Senza Composer installato, scarica `composer.phar` e lancia `php composer.phar install`.
3. Avvia **Apache** e **MySQL** dal pannello di XAMPP.
4. Crea il database `sito_viaggi_fantasia` e importa [sql/database.sql](sql/database.sql), per esempio da phpMyAdmin.
5. Crea la configurazione:
   - copia `api/config.example.php` in **`api/config.php`** e inserisci le chiavi Stripe **di test** e Brevo;
   - copia lo stesso file anche in **`api/config.local.php`**, lasciando solo le costanti del database locale (`root` senza password).

   Il file `config.local.php` viene letto solo su `localhost`, quindi puoi tenere nello stesso progetto i valori locali e quelli di produzione. La registrazione richiede la conferma dell'email, quindi senza una chiave Brevo aggiungi a `config.local.php` la riga `define('EMAIL_SOLO_LOG', true);`: le email (link di conferma e codici di recupero) vengono scritte nel log di PHP invece di essere inviate (con XAMPP: `apache/logs/error.log`).
6. Apri **http://localhost/Sito_di_viaggi/**. In locale HTTPS non viene forzato.

## Pubblicazione (InfinityFree)
1. Esegui `composer install --no-dev` in locale: InfinityFree non ha Composer, quindi la cartella `vendor/` va caricata.
2. Carica via FTP tutti i file del progetto, **incluso `vendor/`** e il tuo `api/config.php` di produzione, **escluso `api/config.local.php`**.
3. Importa [sql/database.sql](sql/database.sql) nel database (da phpMyAdmin: seleziona il database, poi la scheda *Importa*).

Il file [.htaccess](.htaccess) impedisce l'accesso via web a `vendor/`, `sql/`, ai file di configurazione e di Composer.

---

## Schema del Database
Definito in [sql/database.sql](sql/database.sql):
- **utente**: `id, nome, cognome, username (unico), email (unica), password (hash), img (BLOB), type, data_registrazione, verificato, token_conferma (hash SHA-256), scadenza_conferma`
- **prenotazione**: `id_prenotazione, email, nome, cognome, mondo, destinazione, data_partenza, data_ritorno, num_biglietti, nominativi (JSON), commento, importo_centesimi, payment_intent_id (unico), data_prenotazione`
- **commento**: `id_testo, email, username, testo, mondo, stelle (1-5), data_commento`
- **tentativo_accesso**: `id, email, ip, data_tentativo` (le righe più vecchie di un giorno vengono eliminate automaticamente)

---

## Struttura del Progetto
```text
.
├── api/                            # Backend: endpoint e logica condivisa
│   ├── avvio.php                   # Avvio comune: configurazione, HTTPS, sessione, risposte JSON
│   ├── configurazione.php          # Rileva l'ambiente e carica config.php / config.local.php
│   ├── config.example.php          # Modello di configurazione (config.php e config.local.php sono esclusi da git)
│   ├── connessione_db.php          # Connessione MySQL
│   ├── mondi.php                   # Mondi e destinazioni prenotabili
│   ├── prenotazioni_utils.php      # Validazione e prezzo delle prenotazioni
│   ├── foto_profilo_utils.php      # Validazione e salvataggio della foto profilo
│   ├── foto_profilo.php            # Restituisce la foto profilo dell'utente
│   ├── servizio_email.php          # Invio email con Brevo
│   ├── elabora_autenticazione.php  # Login e registrazione
│   ├── limite_accessi.php          # Limite ai tentativi di login falliti
│   ├── recupera_*.php / salva_*.php  # Endpoint AJAX
│   └── stripe/                     # Pagamento e ricevuta
│       ├── configurazione_stripe.php   # Inizializzazione Stripe (solo chiavi di test)
│       ├── crea_pagamento.php          # Crea il PaymentIntent
│       ├── conferma_prenotazione.php   # Verifica il pagamento e salva la prenotazione
│       └── genera_ricevuta_pdf.php     # Ricevuta PDF
├── components/                     # Navbar, dashboard, popup di pagamento, footer, recensioni
├── css/  js/  img/  video/         # Stili, script, immagini e video
├── sql/
│   └── database.sql                # Schema completo
├── index.php                       # Home page
├── autenticazione.php              # Login e registrazione
├── conferma_registrazione.php      # Conferma dell'email dopo la registrazione
├── recupero_password.php           # Recupero password
├── gestione_profilo.php            # Modifica account (in iframe dalla dashboard)
├── viaggio_jojos.php / viaggio_doctorwho.php / viaggio_dragonball.php   # Pagine dei mondi
├── .htaccess                       # Protezione dei file non pubblici
└── composer.json / composer.lock   # Dipendenze PHP
```

## Licenza
Distribuito con licenza [MIT](LICENSE).
