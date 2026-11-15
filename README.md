[![it](https://img.shields.io/badge/lang-it-blue.svg)](https://github.com/CupoMeridio/beyond-reality-journeys/blob/main/README.it.md)

*Click on the flag to switch to the Italian version.*

# BeyondReality Journeys - Web Technologies Project (UNISA 2024-2025)
<img width="1213" height="454" alt="Image 2026-04-03 173918" src="https://github.com/user-attachments/assets/182d6dfa-36cd-41cc-8090-22a0af62a9fc" />

## Project Description
A web application for booking trips to fantasy worlds (JoJo's Bizarre Adventure, Doctor Who, Dragon Ball). It was built as an exam project for the **Web Technologies** course at the University of Salerno (UNISA) and later reworked to be published online.

The site is a **demo**: it shows the fundamentals of web technologies (HTML5, CSS3, JavaScript, PHP, MySQL). **The trips do not exist and no payment is real**: Stripe runs in test mode only.

🌐 Live demo: **https://beyondreality-viaggi.rf.gd/**

> 💳 To try the payment, use the test card `4242 4242 4242 4242`, any future expiry date and any CVC. Real cards are declined.

## 📸Gallery
<table align="center">
  <thead>
    <tr>
      <th>Login</th>
      <th>Registration</th>
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

## Technical Features

### 1. Users and authentication
- **Registration and login** ([elabora_autenticazione.php](api/elabora_autenticazione.php)): client-side and server-side validation, `password_hash()`, prepared statements, a new session ID on every login.
- **Sessions** ([avvio.php](api/avvio.php)): a shared bootstrap for every page, `HttpOnly` and `SameSite=Lax` cookies, HTTPS enforced in production.
- **Password recovery** ([recupero_password.php](recupero_password.php)): a 6-digit code sent by email, valid for 5 minutes, with at most 5 attempts.
- **Profile picture** ([foto_profilo_utils.php](api/foto_profilo_utils.php)): stored as a `BLOB`, format checked from the file content (JPG, PNG, GIF, WEBP, max 1 MB), served by a dedicated endpoint with browser caching.

### 2. Bookings and payment (Stripe test mode)
- **The server is the single source of truth** ([prenotazioni_utils.php](api/prenotazioni_utils.php)): destinations, dates, ticket count and price are validated and computed server-side.
- **Payment flow**:
  1. [crea_pagamento.php](api/stripe/crea_pagamento.php) creates the `PaymentIntent`;
  2. the browser confirms the payment with Stripe.js ([integrazione_stripe.js](js/integrazione_stripe.js));
  3. [conferma_prenotazione.php](api/stripe/conferma_prenotazione.php) checks with Stripe that the payment succeeded and saves the booking exactly once.
- **Test mode only**: with non-test Stripe keys ([configurazione_stripe.php](api/stripe/configurazione_stripe.php)) payments are disabled.
- **PDF receipt** ([genera_ricevuta_pdf.php](api/stripe/genera_ricevuta_pdf.php)): generated with FPDF from the stored booking, and downloadable again from the order history.

### 3. Asynchronous interactions (AJAX with `fetch`)
- Real-time email availability check during registration ([verifica_email_ajax.php](api/verifica_email_ajax.php)).
- Reviews for each world ([gestione_commenti.js](js/gestione_commenti.js)):
  - refreshed periodically, only while the tab is visible;
  - text displayed safely (`textContent`);
  - author emails never exposed.
- Order history and account editing from the user dashboard, without reloading the page.

### 4. Email
- Sent through the **Brevo HTTP API** ([servizio_email.php](api/servizio_email.php)), which works on InfinityFree, where outgoing SMTP is not available.
- A "log only" mode can be enabled locally: emails are written to the PHP log instead of being sent.

---

## Technologies Used
- **Frontend**: HTML5, CSS3, JavaScript (vanilla).
- **Backend**: PHP ≥ 8.1 (extensions `mysqli`, `curl`, `mbstring`, `json`, `gd`).
- **Database**: MySQL / MariaDB.
- **Libraries** (via Composer): [Stripe PHP SDK](https://github.com/stripe/stripe-php), [FPDF](http://www.fpdf.org/).
- **External services**: Stripe (test mode), Brevo (transactional email).
- **Hosting**: InfinityFree.

---

## Running locally (XAMPP)
1. Clone the repository into `C:\xampp\htdocs\Sito_di_viaggi`.
2. Install the dependencies:
   ```bash
   composer install
   ```
   Without Composer installed, download `composer.phar` and run `php composer.phar install`.
3. Start **Apache** and **MySQL** from the XAMPP control panel.
4. Create the `sito_viaggi_fantasia` database and import [sql/database.sql](sql/database.sql), for example from phpMyAdmin.
5. Create the configuration:
   - copy `api/config.example.php` to **`api/config.php`** and fill in the **test** Stripe keys and the Brevo key;
   - copy the same file to **`api/config.local.php`** too, keeping only the local database constants (`root` with no password).

   `config.local.php` is read only on `localhost`, so the local and production values can live in the same project. To try password recovery without Brevo, add the `EMAIL_SOLO_LOG` constant to `config.local.php`: emails go to the PHP log.
6. Open **http://localhost/Sito_di_viaggi/**. HTTPS is not enforced locally.

## Deployment (InfinityFree)
1. Run `composer install --no-dev` locally: InfinityFree has no Composer, so the `vendor/` folder must be uploaded.
2. Upload all project files via FTP, **including `vendor/`** and your production `api/config.php`, **but not `api/config.local.php`**.
3. On the first deployment import [sql/database.sql](sql/database.sql). If the database already exists, run the scripts in [sql/migrazioni/](sql/migrazioni/), each **only once**.

The [.htaccess](.htaccess) file blocks web access to `vendor/`, `sql/`, and the configuration and Composer files.

---

## Database Schema
Defined in [sql/database.sql](sql/database.sql):
- **utente** (user): `id, nome, cognome, username (unique), email (unique), password (hash), img (BLOB), type, data_registrazione`
- **prenotazione** (booking): `id_prenotazione, email, nome, cognome, mondo, destinazione, data_partenza, data_ritorno, num_biglietti, nominativi (JSON), commento, importo_centesimi, payment_intent_id (unique), data_prenotazione`
- **commento** (review): `id_testo, email, username, testo, mondo, stelle (1-5), data_commento`

---

## Project Structure
```text
.
├── api/                            # Backend: endpoints and shared logic
│   ├── avvio.php                   # Shared bootstrap: configuration, HTTPS, session, JSON responses
│   ├── configurazione.php          # Detects the environment, loads config.php / config.local.php
│   ├── config.example.php          # Configuration template (config.php and config.local.php are git-ignored)
│   ├── connessione_db.php          # MySQL connection
│   ├── mondi.php                   # Worlds and bookable destinations
│   ├── prenotazioni_utils.php      # Booking validation and pricing
│   ├── foto_profilo_utils.php      # Profile picture validation and storage
│   ├── foto_profilo.php            # Serves the user's profile picture
│   ├── servizio_email.php          # Email sending through Brevo
│   ├── elabora_autenticazione.php  # Login and registration
│   ├── recupera_*.php / salva_*.php / verifica_email_ajax.php  # AJAX endpoints
│   └── stripe/                     # Payment and receipt
│       ├── configurazione_stripe.php   # Stripe setup (test keys only)
│       ├── crea_pagamento.php          # Creates the PaymentIntent
│       ├── conferma_prenotazione.php   # Verifies the payment and saves the booking
│       └── genera_ricevuta_pdf.php     # PDF receipt
├── components/                     # Navbar, dashboard, payment popup, footer, reviews
├── css/  js/  img/  video/         # Styles, scripts, images and videos
├── sql/
│   ├── database.sql                # Full schema
│   └── migrazioni/                 # Updates for existing databases
├── index.php                       # Home page
├── autenticazione.php              # Login and registration
├── recupero_password.php           # Password recovery
├── gestione_profilo.php            # Account editing (iframe opened from the dashboard)
├── viaggio_jojos.php / viaggio_doctorwho.php / viaggio_dragonball.php   # World pages
├── .htaccess                       # Blocks access to non-public files
└── composer.json / composer.lock   # PHP dependencies
```

## License
Released under the [MIT](LICENSE) license.
