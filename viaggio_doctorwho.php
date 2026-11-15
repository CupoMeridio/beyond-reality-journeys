<?php
require_once __DIR__ . '/api/avvio.php';
require_once __DIR__ . '/api/mondi.php';

// Dati dell'utente per la navbar (components/navbar.php) e per i form (components/prenotazione_recensioni.php)
$email = $_SESSION['email'] ?? null;
$username = $_SESSION['username'] ?? null;
$img = $_SESSION['img'] ?? null;

// Mondo di questa pagina: arriva al JS tramite <body data-mondo> (recensioni e prenotazioni)
$mondo = 'doctor_who';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Who</title>
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
    <link rel="stylesheet" href="css/doctorwho.css">

    <script src="js/utils.js" defer></script>
    <script src="js/animazioni_doctorwho.js" defer></script>
    <script src="js/logica_mondi.js" defer></script>
    <script src="js/gestione_commenti.js" defer></script>
    <script src="js/integrazione_stripe.js" defer></script>
</head>

<body data-mondo="<?= $mondo ?>" data-csrf="<?= tokenCsrf() ?>">

<?php include 'components/navbar.php'; ?>

<?php include 'components/dashboard.html'; ?>

<header>
  <video class="headerVideo" id="background-video" autoplay muted loop playsinline>
    <source src="video/doctorwho/doctorwhovideo.webm" type="video/webm">
    <source src="video/doctorwho/doctorwhovideo.mp4" type="video/mp4">
    Il tuo browser non supporta il tag video.
  </video>
    <img class="headerImg" id="worldTitle" src="img/doctorwho/Doctor-Who-Logo.webp" alt="Doctor Who">
</header>

<p>Naviga tra le destinazioni usando le frecce e clicca sull'immagine per saperne di più!</p>
<div class="slideshow-container">
  <div class="intermezzo fade">
      <img id="intermezzo-img" src="img/doctorwho/tardis_.gif" alt="Il TARDIS in viaggio tra le destinazioni">
  </div>
  <div class="mySlides fade">
    <img id="location1" src="img/doctorwho/gallifrey2.webp" alt="Gallifrey" loading="lazy">
    <div class="captionText">Gallifrey</div>
  </div>

  <div class="mySlides fade">
    <img id="location2" src="img/doctorwho/Skyro.webp" alt="Skaro" loading="lazy">
    <div class="captionText">Skaro</div>
  </div>

  <div class="mySlides fade">
    <img id="location3" src="img/doctorwho/castello.webp" alt="La prigione dei Signori del Tempo" loading="lazy">
    <div class="captionText">Prigione del tempo</div>
  </div>

  <!--pulsanti <- e -> -->
  <button type="button" class="prev" data-cambia-slide="-1" aria-label="Destinazione precedente">&#10094;</button>
  <button type="button" class="next" data-cambia-slide="1" aria-label="Destinazione successiva">&#10095;</button>

  <!--pallini di scorrimento-->
  <div class="pallini">
    <span class="dot" data-vai-slide="1"></span>
    <span class="dot" data-vai-slide="2"></span>
    <span class="dot" data-vai-slide="3"></span>
  </div>

</div>
<div class="info_location">
    <div id="info_location1">
        <h2>
            Scopri il pianeta di Gallyfeid
        </h2>
        <section class="blocco">
            <img src="img/doctorwho/citadel.webp" alt="La Cittadella di Gallifrey" loading="lazy">
            <div class="blocco-testo">
                <p>
                    Gallifrey è un pianeta affascinante con una lunga storia e una cultura avanzata. <br>
                    Situato nella costellazione di Kasterborous, è il decimo pianeta dal suo sole. <br>
                    Questo mondo straordinario è famoso per la sua tecnologia avanzata e le sue enormi città coperte da cupole,
                    la più celebre delle quali è la capitale, chiamata anche Gallifrey o più comunemente Citadel.
                </p>
            </div>
        </section>
        <h2> Per chi è adatto questo viaggio ?</h2>
        <ul>
            <li>Tutti gli esseri viventi che amano tecnologie aliene avanzate:<br>
            Se sei affascinato dalle innovazioni tecnologiche e dalle culture aliene, Gallifrey è il posto perfetto per te.</li>
            <li>Coloro che non hanno paura del nuovo e del diverso:<br>
            Gallifrey è un mondo che sfida le convenzioni e offre esperienze uniche, adatte a chi è aperto a scoprire l'ignoto.</li>
        </ul>
    </div>
    <div id="info_location2">
        <h2>
            Esplora Skaro: La terra dei Daleck! 🛸
        </h2>
        <p>
            Skaro è il pianeta natale della razza Dalek, uno degli antagonisti più noti di "Doctor Who".
            <br> È un mondo devastato dalla guerra e dalla distruzione, riflettendo la natura bellicosa dei suoi abitanti.

        </p>

        <h3>
            ✨Caratteristiche di Skaro:
        </h3>
        <section class="blocco">
            <img src="img/doctorwho/dalek-inside.webp" alt="L&#039;interno della città dei Dalek" loading="lazy">
            <div class="blocco-testo">
                <h4>🟢 Aspetto:</h4>
                <p>
                    La superficie di Skaro è caratterizzata da vasti deserti, pianure aride, e rovine di antiche città.
                    Le zone di guerra radioattive e i crateri di esplosioni nucleari sono una presenza comune.
                </p>
                <h4>🛕 Città:</h4>
                <p>
                    La città più famosa di Skaro è la "Cittadella dei Dalek", un'area altamente fortificata e tecnologicamente avanzata dove risiedono i Dalek.<br>
                    È piena di torri minacciose e strutture metalliche.
                </p>

            </div>
        </section>
        <section class="blocco">
            <img src="img/doctorwho/dalek_wiki.webp" alt="Un Dalek" loading="lazy">
            <div class="blocco-testo">
                <h4> Storia:</h4>
                <p>
                    Skaro ha una storia di conflitti continui, principalmente tra i Kaleds e i Thals, due razze originarie del pianeta.<br>
                    I Dalek sono il risultato delle mutazioni dei Kaleds causate dalla guerra nucleare.
                </p>
                <h4>Ambiente:</h4>
                <p>
                    L'ambiente di Skaro è inospitale per la maggior parte delle forme di vita.<br>
                    La contaminazione radioattiva e le condizioni climatiche estreme rendono difficile la sopravvivenza.
                </p>

            </div>
        </section>
        <section class="blocco">
            <img src="img/doctorwho/dalek-skaro-asylum-2.webp" alt="Il manicomio dei Dalek su Skaro" loading="lazy">
            <div class="blocco-testo">
                <h4> Guerriglia per sopravvivere</h4>
                <p>
                    Adatta a chi ama combattere per sopravvivere. La nosta socetà non si prenderà cura di voi. <br>
                    Anche se i dalek sembrano degli aspirapolvere, <br> sono la cosa più cattiva che ci sia.
                </p>
            </div>
        </section>
    </div>
    <div id="info_location3">
        <h2>
            Combatti per la tua sopravvivenza!
        </h2>
        <p>Il castello(simile a quello di Guardia Sanframondi) è un luogo di tortura progettato per interrogare il Dottore e scoprire la verità su un misterioso "ibrido".
            Le sue pareti sono fatte di un minerale chiamato Azbantium, più duro del diamante, e il castello cambia continuamente forma,
            rendendo la fuga quasi impossibile.
        </p>

        <section class="blocco">
            <img src="img/doctorwho/castello_doctor_who.webp" alt="Il castello della prigione" loading="lazy">
            <div class="blocco-testo">
                <h3>
                    🔥 Cosa ti aspetta in questa escaperoom?
                </h3>
                <p>Questo castello è un luogo enigmatico e pericoloso, <br>
                    con stanze che si riconfigurano continuamente e una creatura velata chiamata "Il Velo" che ti insegurà implacabilmente.</p>
                    <h4> Scappa, ragiona e impara a conoscerti</h4>
                    <p>
                    Non potrai uscire sfruttando una sola vita ma non preoccuparti verrai rigenerato ogni volta.<br>
                    Lascia messaggi e indizzi al te del futuro prima di venir rigenerato e perdere la memoria.<br>
                    Solo chi sa conoscersi puo sfuggire.
                </p>

            </div>
        </section>
        <section class="blocco">
            <img src="img/doctorwho/ilvelo.webp" alt="Il Velo" loading="lazy">
            <div class="blocco-testo">
                <h4>Per chi è adatto?</h4>
                <p>
                    Consigliamo l'esperienza a tutti, ma sono preferiti:
                </p>
                <ul>
                    <li>Tutte le persone e alieni che vogliono mettersi in gioco</li>
                    <li>Persone che vogliono sbarazzarsi di una partner scomodo</li>
                </ul>
            </div>
        </section>
    </div>
</div>

<?php include 'components/prenotazione_recensioni.php'; ?>

<?php include 'components/footer.html'; ?>
</body>

</html>
