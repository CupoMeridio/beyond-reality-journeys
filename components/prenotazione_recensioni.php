<?php
/**
 * Prenotazione del viaggio e recensioni: uguali per i tre mondi (incluso da viaggio_*.php).
 *
 * Richiede $mondo (chiave di DESTINAZIONI in api/mondi.php) e, se l'utente ha fatto
 * l'accesso, $email. Le destinazioni vengono dalla stessa lista con cui il server valida
 * le prenotazioni, quindi pagina e server non possono andare fuori sincrono.
 */
$loggato = isset($email);
$tab = $loggato ? '0' : '-1'; // chi non ha fatto l'accesso non raggiunge i campi con il tasto Tab

$opzioniDestinazioni = '';
foreach (DESTINAZIONI[$mondo] as $valore => $nome) {
    $opzioniDestinazioni .= '<option value="' . htmlspecialchars($valore, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . "</option>\n";
}
?>
<!-- Prenotazione del viaggio -->
<section class="booking-section">
    <h2>Prenota il Tuo Viaggio Fantastico!</h2>
    <div id="form-container">
        <form id="booking-form">
            <label for="tickets-count">Numero di Biglietti:</label>
            <input type="number" id="tickets-count" name="tickets-count" min="1" max="10" value="1" required autocomplete="off" tabindex="<?= $tab ?>">

            <h3>Nominativi</h3>
            <div id="ticket-names-container">
                <label for="ticket-name-0">Nome Passeggero 1:</label>
                <input type="text" id="ticket-name-0" name="ticket-name-0" required placeholder="Nome Passeggero 1" autocomplete="name" tabindex="<?= $tab ?>">
            </div>

            <label for="location">Scegli la Destinazione:</label>
            <select id="location" name="location" required autocomplete="off" tabindex="<?= $tab ?>">
                <?= $opzioniDestinazioni ?>
            </select>

            <label for="departure-date">Data di Partenza:</label>
            <input type="date" id="departure-date" name="departure-date" required autocomplete="off" tabindex="<?= $tab ?>">

            <label for="return-date">Data di Ritorno:</label>
            <input type="date" id="return-date" name="return-date" required autocomplete="off" tabindex="<?= $tab ?>">

            <label for="comments">Commenti Speciali:</label>
            <textarea id="comments" name="comments" rows="4" placeholder="Inserisci richieste particolari o commenti" autocomplete="off" tabindex="<?= $tab ?>"></textarea>

            <?php if ($loggato) { ?>
                <input type="submit" id="submit-form-button" value="Prenota il tuo viaggio!">
            <?php } else { ?>
                <input type="button" id="submit-form-button" value="Registrati o accedi per prenotare il tuo viaggio!" tabindex="-1">
            <?php } ?>
        </form>

        <?php if (!$loggato) { ?>
            <div id="form-overlay" class="form-overlay">
                <div class="overlay-message">Registrati o accedi per prenotare il tuo viaggio!</div>
            </div>
        <?php } ?>
    </div>
</section>

<?php include __DIR__ . '/popup.html'; ?>

<!-- Recensioni: a sinistra quelle pubblicate, a destra il form per scriverne una -->
<section class="reviews-section">
    <?php include __DIR__ . '/recensioni.html'; ?>

    <div class="review-form">
        <h2>Lascia una Recensione</h2>
        <div id="form-container-review">
            <form id="reviewForm" name="commenti">
                <p>
                    <label for="location_selection">Seleziona la location:</label>
                    <select id="location_selection" name="location" autocomplete="off" tabindex="<?= $tab ?>">
                        <?= $opzioniDestinazioni ?>
                    </select>
                </p>

                <p>
                    <label for="rating">Valutazione (1-5 stelle):</label>
                    <select id="rating" name="rating" autocomplete="off" tabindex="<?= $tab ?>">
                        <option value="1">1 ⭐</option>
                        <option value="2">2 ⭐⭐</option>
                        <option value="3">3 ⭐⭐⭐</option>
                        <option value="4">4 ⭐⭐⭐⭐</option>
                        <option value="5">5 ⭐⭐⭐⭐⭐</option>
                    </select>
                </p>

                <p class="campo-testo">
                    <label for="experience">La tua esperienza:</label>
                    <textarea id="experience" name="experience" rows="4" cols="50" required placeholder="Scrivi la tua esperienza..." autocomplete="off" tabindex="<?= $tab ?>"></textarea>
                </p>

                <?php if ($loggato) { ?>
                    <input type="button" id="invia-recensione" class="bottone-recensione" value="Invia Recensione">
                <?php } else { ?>
                    <input type="button" value="Registrati o accedi per inviare una recensione" tabindex="-1">
                <?php } ?>
            </form>

            <?php if (!$loggato) { ?>
                <div id="form-overlay-review" class="form-overlay">
                    <div class="overlay-message">Registrati o accedi per scrivere una recensione!</div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
