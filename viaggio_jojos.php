<?php
require_once __DIR__ . '/api/avvio.php';
require_once __DIR__ . '/api/mondi.php';

// Dati dell'utente per la navbar (components/navbar.php) e per i form (components/prenotazione_recensioni.php)
$email = $_SESSION['email'] ?? null;
$username = $_SESSION['username'] ?? null;
$img = $_SESSION['img'] ?? null;

// Mondo di questa pagina: arriva al JS tramite <body data-mondo> (recensioni e prenotazioni)
$mondo = 'jojo';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JoJo's Bizarre Adventure</title>
    <?php include 'components/imposta_icona.html'; ?>

    <!-- Prima gli stili comuni, per ultimo quello del mondo (solo colori e personalizzazioni) -->
    <link rel="stylesheet" href="css/stile_mondi.css">
    <link rel="stylesheet" href="css/stile_navbar.css">
    <link rel="stylesheet" href="css/stile_header.css">
    <link rel="stylesheet" href="css/stile_dashboard.css">
    <link rel="stylesheet" href="css/stile_storico_ordini.css">
    <link rel="stylesheet" href="css/stile_overlay.css">
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet" href="css/stile_footer.css">
    <link rel="stylesheet" href="css/jojos.css">

    <script src="js/utils.js" defer></script>
    <script src="js/logica_mondi.js" defer></script>
    <script src="js/gestione_commenti.js" defer></script>
    <script src="js/integrazione_stripe.js" defer></script>
</head>

<body data-mondo="<?= $mondo ?>" data-csrf="<?= tokenCsrf() ?>">
   <?php include 'components/navbar.php'; ?>

   <?php include 'components/dashboard.html'; ?>

  <header>
    <video class="headerVideo" id="background-video" autoplay muted loop playsinline>
      <source src="video/jojo/jojovideo.webm" type="video/webm">
      <source src="video/jojo/jojovideo.mp4" type="video/mp4">
      Il tuo browser non supporta il tag video.
    </video>
    <img class="headerImg" id="worldTitle" src="img/jojos/jojostitle.webp" alt="JoJo&#039;s Bizarre Adventure: Vento Aureo">
  </header>

  <div class="container" id="container">
    <h2>Esplora le più belle location del mondo di JoJo's bizarre adventure: Vento Aureo</h2>
    <p>Clicca su una delle location qui sopra per scoprire i dettagli delle ambientazioni di JojoC'!</p>
    <div class="locations_selector" id="locations_selector">
      <div class="location" id="location1">
        <img src="img/jojos/rome.webp" alt="Roma" loading="lazy">
         <div class="location-name">Roma</div>
      </div>
      <div class="location" id="location2">
        <img src="img/jojos/venice.webp" alt="Venezia" loading="lazy">
         <div class="location-name">Venezia</div>
      </div>
      <div class="location" id="location3">
        <img src="img/jojos/naples.webp" alt="Napoli" loading="lazy">
         <div class="location-name">Napoli</div>
      </div>
    </div>
    <div class="info_location">
        <div id="info_location1">
            <h2>
                🐺 Scopri la magica Roma di JoJo’s Bizarre Adventure 🌟
            </h2>
            <p>
                🏛️Benvenuti a Roma, il cuore pulsante d’Italia e uno dei luoghi più iconici di JoJo’s Bizarre Adventure: Vento Aureo!<br>
                Qui, tra antiche rovine e vicoli pieni di fascino, si intrecciano le avventure di Giorno Giovanna e i suoi amici, in una battaglia per il destino dell'Itala intera.
            </p>
            <h3>Cosa ti aspetta nella Roma di Vento Aureo?</h3>
            <section class="blocco">
                <img src="img/jojos/tritone.webp" alt="La fontana del Tritone a Roma" loading="lazy">
                <div class="blocco-testo">
                    <h4>🫧 L’Incontro del Destino: Joseph Joestar e Caesar Antonio Zeppeli</h4>
                    <p>
                        Nel cuore di Roma, prima delle avventure di Vento Aureo, tra gli antichi palazzi, Joseph Joestar incontra il suo futuro alleato Caesar Zeppeli.
                        È proprio qui, davanti la fontana del Tritone, che il loro pungente primo incontro avviene.
                    </p>
                    <h4>🏛️Una città tutta da scoprire</h4>
                    <p>
                        Se ami l’avventura, il dramma e lo stile unico della serie,
                        preparati a immergerti in una capitale ricca di emozioni!
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/jojos/colosseo_manga.webp" alt="Il Colosseo disegnato in stile manga" loading="lazy">
                <div class="blocco-testo">
                    <h4> Il Colosseo: Arena di Duelli Epici 🏹</h4>
                    <p>
                        Il simbolo di Roma diventa teatro di una delle battaglie più leggendarie di JoJo! <br>
                        Qui, Jean Pierre Polnareff svela i segreti di Requiem, mentre Diavolo scatena il caos con King Crimson!
                    </p>
                    <h4> Incontra Jean Pierre Polnareff 🐢🗡</h4>
                    <p>
                        Preparati a incontrare Jean Pierre Polnareff, il cavaliere francese dallo spirito indomito.<br>
                        Ascolta le storie delle sue esperienze, i suoi combattimenti epici e, ovviamente, qualche battuta sopra le righe!
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/jojos/battaglia_roma.webp" alt="Lo scontro di Roma con Cioccolata e Secco" loading="lazy">
                <div class="blocco-testo">
                    <h4>⚔ Il luogo della Epica Battaglia</h4>
                    <p>
                        Visita il luogo dello scontro con Cioccolata e Secco, i due assassini spietati al servizio di Diavolo!<br>
                        Con l’infernale Stand Green Day, Cioccolata trasforma Roma in un inferno in discesa, mentre Secco si muove con agilità sotterranea grazie a Oasis.
                    </p>
                    <h4>📖 Racconti di Eroi e Villain</h4>
                    <p>
                        Roma è una città di leggende, e JoJo’s Bizarre Adventure l’ha resa ancora più epica! Rivivi le storie di Caesar, Joseph, Giorno e Bruno
                        mentre cammini tra i monumenti, e immagina di far parte anche tu di questa incredibile avventura!
                    </p>

                </div>
            </section>
        </div>
        <div id="info_location2">
            <h2>
                🛶 Esplora la misteriosa Venezia di JoJo’s Bizarre Adventure 🌊
            </h2>
            <p>
                Benvenuti a Venezia, la città sull’acqua, intrisa di mistero e teatro di alcuni degli scontri più emozionanti di JoJo’s Bizarre Adventure: Vento Aureo!<br>
                Attraversando i suoi canali e i vicoli nascosti, potrai rivivere le gesta di Giorno Giovanna e dei suoi compagni, immerso in un’atmosfera unica e carica di tensione.
            </p>

            <h3>
                ✨ Cosa ti aspetta a Venezia?
            </h3>

            <section class="blocco">
                <img src="img/jojos/tumblr_pbxnpr7WID1wxbjq5o1_1280.webp" alt="Lisa Lisa a Venezia" loading="lazy">
                <div class="blocco-testo">
                    <h4> Lisa Lisa e l’addestramento segreto 🩸</h4>
                    <p>
                        Prima di Vento Aureo, un'altra eroina di JoJo ha calcato le calli veneziane!
                        Lisa Lisa, la maestra di Onde Concentriche, ha vissuto qui in segreto mentre preparava Joseph Joestar e Caesar Zeppeli alla battaglia contro i temibili Uomini del Pilastro.
                    </p>
                    <h4> Un luogo pieno di pericoli nascosti 🌊</h4>
                    <p>
                        Venezia non è solo una città romantica: è un labirinto perfetto per imboscate e battaglie all’ultimo respiro!
                        Proprio qui, nel cuore della città, la Squadra Esecuzioni della Passione ha dato la caccia a Bucciarati e ai suoi uomini.
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/jojos/gondola.webp" alt="Una gondola sui canali di Venezia" loading="lazy">
                <div class="blocco-testo">
                    <h4>❄ Ghiaccio: Il terrore gelido tra i canali </h4>
                    <p>
                        Uno degli scontri più brutali della serie avviene proprio qui!<br>
                        Ghiaccio, con il suo terrificante Stand White Album, trasforma Venezia in un deserto glaciale, mentre Mista e Giorno lottano disperatamente per sopravvivere.
                    </p>
                    <h4>🚣‍♂️ In gondola verso la chiesa di San Giorgio </h4>
                    <p>
                        Raggiungere la chiesa di San Giorgio in gondola, scivolando silenziosamente tra i canali veneziani, aumenta la tensione dell’incontro con Diavolo.<br>
                        Le acque scure riflettono le luci tremolanti della città, mentre il suono ritmico dei remi si mescola con il battito accelerato del cuore.
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/jojos/sangiorgio.webp" alt="La chiesa di San Giorgio a Venezia" loading="lazy">
                <div class="blocco-testo">
                    <h4> Il primo incontro con Diavolo 🎭</h4>
                    <p>
                        Nel cuore di Venezia, tra le antiche mura della chiesa di San Giorgio, si consuma un momento cruciale di Vento Aureo: il primo scontro con il misterioso Boss di Passione!
                        Bruno Bucciarati si trova faccia a faccia con il pericoloso criminale, scoprendo troppo tardi l’orrore di King Crimson e le abilità del suo stand.
                    </p>
                </div>
            </section>
        </div>
        <div id="info_location3">
            <h2>
                Perditi nella bellissima Napoli
            </h2>
            <p>
                Benvenuti a Napoli, una città intrisa di storia e cultura, che diventa il primo palcoscenico di JoJo's Bizarre Adventure: Vento Aureo.
            </p>
            <h3>
                Cosa ti aspetta a Napoli?
            </h3>
            <section class="blocco">
                <img src="img/jojos/metro.webp" alt="La metropolitana di Napoli" loading="lazy">
                <div class="blocco-testo">
                    <h4>Koichi e l’aeroporto di Napoli 🛫</h4>
                    <p>
                        Uno degli incontri più intriganti della saga si svolge proprio qui, dove atterrerete: all’aeroporto di Napoli!<br>
                        È proprio qui che Koichi Hirose, uno dei protagonisti delle avventure precedenti, incontra per la prima volta il nuovo protagonista Giorno Giovanna!
                    </p>
                    <h4>Un incontro fatale in metro🚇</h4>
                    <p>
                        Scopri gli efficienti mezzi di trasporto che collegano tutta la città di Napoli.<br>
                        È proprio la metro il luogo di incontro tra il protagonista Gionro Giovanna e quello che sarà uno dei suoi migliori amici, Bruno Bucciarati.
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/jojos/capri.webp" alt="L&#039;isola di Capri" loading="lazy">
                <div class="blocco-testo">
                    <h4>⛵ Salpa verso l'isola di Capri</h4>
                    <p>
                        Da Napoli potrai salpare verso l'isola di Capri, tre le acque cristalline e le rocce mozzafiato!<br>
                        È proprio qui che i nostri protagonisti trovano il tesoro di Polpo, uno dei capi di Passione, che finanzia il loro viaggio verso le altre città d'Italia.
                    </p>
                    <h4>🏹 Ottieni il tuo stand visitando Polpo</h4>
                    <p>
                        Come Giorno Giovanna, fai visita a Polpo e ottieni anche tu il tuo potere stand<br>
                        O magari no. Pensa ai pericoli che dovrai affrontare prima di arrivare ad un capo di una gang...😨
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/jojos/vedinapoli.webp" alt="Il golfo di Napoli" loading="lazy">
                <div class="blocco-testo">
                    <h4>Esplora la città 🌇</h4>
                    <p>
                        Vedi Napoli e poi muori! Ma non letteralmente, spero...<br>
                        Esplora la città, mangia una pizza e goditi il panorama mozzafiato del golfo di Napoli!<br>
                        Chissà che tu non possa incontrare qualche portatore di stand tra la folla!
                    </p>
                </div>
            </section>
        </div>
    </div>
</div>

<?php include 'components/prenotazione_recensioni.php'; ?>

<?php include 'components/footer.html'; ?>
</body>
</html>
