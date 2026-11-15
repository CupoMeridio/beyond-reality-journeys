<?php
require_once __DIR__ . '/api/avvio.php';
require_once __DIR__ . '/api/mondi.php';

// Dati dell'utente per la navbar (components/navbar.php) e per i form (components/prenotazione_recensioni.php)
$email = $_SESSION['email'] ?? null;
$username = $_SESSION['username'] ?? null;
$img = $_SESSION['img'] ?? null;

// Mondo di questa pagina: arriva al JS tramite <body data-mondo> (recensioni e prenotazioni)
$mondo = 'dragon_ball';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dragon Ball</title>
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
    <link rel="stylesheet" href="css/DragonBallStyle.css">

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
      <source src="video/dragonball/dragonballvideo.webm" type="video/webm">
      <source src="video/dragonball/dragonballvideo.mp4" type="video/mp4">
      Il tuo browser non supporta il tag video.
    </video>
    <img class="headerImg" id="worldTitle" src="img/dragonball/dragonballtitleheader.webp" alt="Dragon Ball">
  </header>

  <div class="container" id="container">
    <h2>Esplora le più belle location del mondo di Dragon Ball!</h2>
    <p>Clicca su una delle location qui sopra per esplorare i dettagli dei mondi di Dragon Ball!</p>
    <div class="locations_selector" id="locations_selector">
      <div class="location" id="location1">
        <img src="img/dragonball/kamehouse_.webp" alt="Kame House" loading="lazy">
         <div class="location-name">Kame House</div>
      </div>
      <div class="location" id="location2">
        <img src="img/dragonball/Namek.webp" alt="Namecc" loading="lazy">
         <div class="location-name">Namecc</div>
      </div>
      <div class="location" id="location3">
        <img src="img/dragonball/KingKaisPlanetNV.webp" alt="Pianeta di King Kai" loading="lazy">
         <div class="location-name">Pianeta di King Kai</div>
      </div>
    </div>
    <div class="info_location">
        <div id="info_location1">
            <h2>
                🏝️ Scopri la Kame House: Paradiso Tropicale del Maestro Muten! 🐢🌊
            </h2>
            <p>
                🌴 Benvenuti alla Kame House, la leggendaria isoletta tropicale dove il tempo sembra fermarsi e l’avventura è sempre dietro l’angolo!<br>
                Situata in mezzo a un oceano cristallino, questa destinazione è perfetta per chi desidera un mix di relax, allenamento e un pizzico di magia marziale!
            </p>
            <h3>🐢 Cosa ti aspetta alla Kame House?</h3>
            <section class="blocco">
                <img src="img/dragonball/Dragon-Ball-Son-Goku-Bulma-Young-Bulma-Dragon-Ball-Z-group-of-people-2232722.webp" alt="Goku, Bulma e gli amici alla Kame House" loading="lazy">
                <div class="blocco-testo">
                    <h4>🌅 Un’Isola da Sogno</h4>
                    <p>
                        Circondata da acque turchesi e sabbie bianchissime, la Kame House è il luogo ideale per chi cerca tranquillità e un panorama mozzafiato.<br>
                        Rilassati all’ombra delle palme e lasciati cullare dal suono delle onde.
                    </p>
                    <h4>👓 Incontra il Maestro Muten</h4>
                    <p>
                        Preparati a conoscere il leggendario Maestro delle Tartarughe, Muten Roshi!<br>
                        Partecipa alle sue esclusive sessioni di allenamento (e qualche storia strampalata!) per scoprire i segreti delle arti marziali più potenti dell’universo.
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/dragonball/Krillin-Goku-training-Master-Roshi.webp" alt="Goku e Crilin si allenano con il Maestro Muten" loading="lazy">
                <div class="blocco-testo">
                    <h4>💪 Allenamenti sulla Spiaggia</h4>
                    <p>
                        Per i più temerari, la Kame House offre programmi di allenamento personalizzati.<br>
                        Prova l’esperienza di allenarti con pesi giganteschi, corse sulla spiaggia e, se sei fortunato, qualche lezione speciale di Kamehameha direttamente dal Maestro!
                    </p>
                    <h4>🌊 Attività Acquatiche Avventurose</h4>
                    <p>
                        Esplora le acque circostanti con sessioni di snorkeling e immersioni, alla scoperta di fauna marina incredibile e, chissà, magari anche qualche tesoro nascosto!
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/dragonball/kamehousefood.webp" alt="Il cibo della Kame House" loading="lazy">
                <div class="blocco-testo">
                    <h4>🥥 Cucina Esotica</h4>
                    <p>
                        Dopo una giornata di avventure, rilassati gustando specialità locali a base di cocco, pesce fresco e deliziosi piatti ispirati alle ricette segrete di Muten Roshi.
                    </p>
                    <h4>📜 Storie e Leggende Epiche</h4>
                    <p>
                        Rilassati al tramonto ascoltando le incredibili avventure del Maestro Muten, dalle storie di Goku e Crilin fino alle tecniche segrete dei guerrieri Z.<br>
                        Ogni serata è un tuffo nella storia dell'arte marziale più potente dell'universo!
                    </p>

                </div>
            </section>
        </div>
        <div id="info_location2">
            <h2>
                🌌 Esplora Namecc: Un Viaggio tra Natura Mistica e Potere Leggendario! 🛸
            </h2>
            <p>
                🌿 Benvenuti su Namecc, il pianeta verde smeraldo dove la natura incontaminata e il misticismo si fondono in un’esperienza di viaggio unica!<br>
                Situato in una galassia lontana, questo paradiso extraterrestre offre paesaggi mozzafiato, villaggi pacifici e un’energia spirituale che scorre tra le sue terre sacre.
            </p>

            <h3>
                ✨ Cosa ti aspetta su Namecc?
            </h3>

            <section class="blocco">
                <img src="img/dragonball/desktop-wallpaper-planet-namek.webp" alt="Paesaggio del pianeta Namecc" loading="lazy">
                <div class="blocco-testo">
                    <h4>🟢 Paesaggi Alieni e Incantevoli</h4>
                    <p>
                        Immergiti in un mondo dalle sfumature verde-blu, dove gli alberi alti e slanciati, i cieli cangianti e gli immensi specchi d’acqua creano un’atmosfera di pura meraviglia.<br>
                        Goditi tramonti surreali mentre esplori questo angolo di universo lontano dalla frenesia terrestre.
                    </p>
                    <h4>🛕 Visita i Villaggi Namecciani</h4>
                    <p>
                        Scopri le antiche comunità dei Namecciani, una razza pacifica e saggia.<br>
                        Interagisci con gli abitanti locali e lasciati affascinare dalla loro cultura basata sull’equilibrio, la meditazione e l’armonia con la natura.
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/dragonball/adding-a-new-character-every-day-until-sparking-zero-is-v0-z7u56vhfjzyc1.webp" alt="Le sfere del drago di Namecc" loading="lazy">
                <div class="blocco-testo">
                    <h4>🌟 Alla Ricerca delle Sfere del Drago</h4>
                    <p>
                        Namecc è il luogo d’origine delle leggendarie Sfere del Drago!<br>
                        Unisciti a una spedizione esclusiva per scoprire i templi sacri e gli altari nascosti dove queste reliquie mistiche sono state custodite per secoli.<br>
                        Chi lo sa? Magari potresti persino avvistare Porunga, il drago eterno!
                    </p>
                    <h4>⚔ Le Rovine della Battaglia Epica</h4>
                    <p>
                        Se sei un fan dell’azione, visita i luoghi dove si è combattuta una delle battaglie più leggendarie dell’universo!<br>
                        Atraversa le valli devastate dallo scontro tra Goku e Freezer e ascolta i racconti che hanno reso questo pianeta un'icona della storia intergalattica.
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/dragonball/Namecciani.webp" alt="I Namecciani" loading="lazy">
                <div class="blocco-testo">
                    <h4>🌱 Rigenerazione e Benessere</h4>
                    <p>
                        Dopo tanta avventura, rilassati nelle acque rigenerative naturali del pianeta e goditi un percorso di benessere sotto la guida di un anziano namecciano.<br>
                        Qui potrai ricaricare corpo e spirito, avvolto dalla quiete e dalla magia di Namecc.
                    </p>
                </div>
            </section>
        </div>

        <div id="info_location3">
            <h2>
                🌌 Viaggio sul Pianeta di King Kai: Allenati con il Maestro dell’Oltretomba! 🏃‍♂️⚡
            </h2>
            <p>
                ✨ Benvenuti sul mitico Pianeta di King Kai! ✨<br>
                Situato alla fine del Serpentone dell’aldilà, questo piccolo ma straordinario mondo è il luogo perfetto per chi desidera affinare la propria forza interiore, migliorare i riflessi e, perché no, farsi qualche risata con gli indovinelli del suo eccentrico abitante!
            </p>
            <h3>
                🔥 Cosa ti aspetta sul Pianeta di King Kai?
            </h3>
            <section class="blocco">
                <img src="img/dragonball/Dead_Z-Fighters_on_King_Kai's_planet.webp" alt="I guerrieri Z sul pianeta di King Kai" loading="lazy">
                <div class="blocco-testo">
                    <h4>🪐 Un Pianeta Unico nel Suo Genere</h4>
                    <p>
                        Qui la gravità è 10 volte superiore a quella terrestre! Ogni passo è una sfida, ogni movimento un allenamento. Sei pronto a testare i tuoi limiti?
                    </p>
                    <h4>🐵 Incontra King Kai e la Sua Simpatica Compagnia</h4>
                    <p>
                        Interagisci con King Kai e la sua fedele compagnia, composta da Bubbles e Gregory.<br>
                        Lasciati affascinare dalla loro saggezza, ma preparati: King Kai adora le barzellette, quindi assicurati di averne qualcuna pronta!
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/dragonball/Goku-Struggles-With-The-Gravity-On-King-Kais-Planet.webp" alt="Goku alle prese con la gravità del pianeta di King Kai" loading="lazy">
                <div class="blocco-testo">
                    <h4>🐒 Sfida Bubbles e Gregory!</h4>
                    <p>
                        Per diventare un vero allievo di King Kai, dovrai superare due prove iconiche:
                    </p>
                    <ul>
                        <li>Insegui e cattura Bubbles, la scimmietta più veloce del pianeta! Non lasciarti ingannare dal suo aspetto carino, con la gravità aumentata ogni salto sarà un’impresa!</li>
                        <li>Colpisci Gregory, la cavalletta volante con un riflesso fulmineo! Se ci riesci, King Kai potrebbe svelarti alcuni segreti proibiti…</li>
                    </ul>
                    <h4>🌠 Panorama Celestiale e Relax Spirituale</h4>
                    <p>
                        Dopo tanto allenamento, rilassati sotto un cielo stellato incredibilmente limpido, circondato da un’atmosfera di pura pace cosmica.<br>
                        Medita con King Kai e scopri i segreti dell’equilibrio tra mente e corpo.
                    </p>

                </div>
            </section>
            <section class="blocco">
                <img src="img/dragonball/dragon-ball-goku-riesce-superare-velocitA-luce-scopriamolo-v3-532194-1280x720.webp" alt="Goku in volo a grande velocità" loading="lazy">
                <div class="blocco-testo">
                    <h4>📜 Tecniche Leggendarie</h4>
                    <p>
                        Se dimostrerai il tuo valore, King Kai potrebbe insegnarti tecniche leggendarie come il Kaio-ken o la Sfera Genkidama!<br>
                        Queste arti marziali divine ti permetteranno di superare i tuoi limiti e proteggere chi ami.
                    </p>
                    <h4>🏎️ Un Giro in Macchina</h4>
                    <p>
                        Sì, hai letto bene! King Kai possiede una macchina fiammante e ama fare qualche giro sul suo piccolo pianeta.<br>
                        Unisciti a lui per un momento di svago tra una sessione di allenamento e l'altra!
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
