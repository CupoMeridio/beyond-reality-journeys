<?php
require_once __DIR__ . '/api/avvio.php';

// Dati dell'utente autenticato per la navbar ($email, $username, $img)
include 'api/recupera_dati_utente.php';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeyondReality Journeys</title>
    <?php include 'components/imposta_icona.html'; ?>

    <!-- Prima gli stili dei componenti, per ultimo quello della home (che li personalizza) -->
    <link rel="stylesheet" href="css/stile_navbar.css">
    <link rel="stylesheet" href="css/stile_dashboard.css">
    <link rel="stylesheet" href="css/stile_storico_ordini.css">
    <link rel="stylesheet" href="css/stile_footer.css">
    <link rel="stylesheet" href="css/stile_home.css">

    <script src="js/utils.js" defer></script>
    <script src="js/logica_home.js" defer></script>
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <?php include 'components/dashboard.html'; ?>

    <div class="slideshow-container">
        <div class="mySlides fade">
            <div class="numberText">1 / 3</div>
            <a href="viaggio_jojos.php"><img src="img/index/vesuvio.webp" alt="Il Vesuvio visto da Napoli"></a>
            <div class="captionText">Scopri: Napoli!</div>
        </div>

        <div class="mySlides fade">
            <div class="numberText">2 / 3</div>
            <a href="viaggio_doctorwho.php"><img src="img/index/gallifrey.webp" alt="Gallifrey"></a>
            <div class="captionText">Scopri: Gallifrey!</div>
        </div>

        <div class="mySlides fade">
            <div class="numberText">3 / 3</div>
            <a href="viaggio_dragonball.php"><img src="img/dragonball/namecc.webp" alt="Namecc"></a>
            <div class="captionText">Scopri: Namecc!</div>
        </div>

        <!-- Frecce e pallini: gestiti da js/logica_home.js -->
        <button type="button" class="prev" data-cambia-slide="-1" aria-label="Immagine precedente">&#10094;</button>
        <button type="button" class="next" data-cambia-slide="1" aria-label="Immagine successiva">&#10095;</button>

        <div class="pallini">
            <span class="dot" data-vai-slide="1"></span>
            <span class="dot" data-vai-slide="2"></span>
            <span class="dot" data-vai-slide="3"></span>
        </div>
    </div>

    <section class="destinations">
        <h1>Our destinations</h1>
        <p>Clicca su una delle mete qui sotto per esplorare destinazioni uniche e scoprire cosa hanno da offrirti!</p>
        <div class="destinations_selector" id="destinations_selector">
            <div class="destination" id="destination1">
                <a href="viaggio_jojos.php"><img src="img/index/jojos.webp" alt="JoJo's Bizarre Adventure" loading="lazy"></a>
                <div class="destination-name">Le bizzarre avventure di Jojo</div>
            </div>
            <div class="destination" id="destination2">
                <a href="viaggio_doctorwho.php"><img src="img/index/drwho.webp" alt="Doctor Who" loading="lazy"></a>
                <div class="destination-name">Doctor Who</div>
            </div>
            <div class="destination" id="destination3">
                <a href="viaggio_dragonball.php"><img src="img/index/dragonballtitleindex.webp" alt="Dragon Ball" loading="lazy"></a>
                <div class="destination-name">Dragonball</div>
            </div>
        </div>
    </section>

    <section class="about-section" id="about-section">
        <div id="presentazione">
            <h2>About Us – BeyondReality Journeys</h2>
            <p>
                Benvenuti in <strong>BeyondReality Journeys</strong>, l’agenzia di viaggi definitiva per chi sogna di esplorare mondi oltre l’immaginazione!
                Da sempre appassionati di avventure straordinarie, abbiamo trasformato l’impossibile in realtà: ora puoi prenotare viaggi nei tuoi universi preferiti,
                dalle terre incantate dei cartoni agli scenari epici di anime, film e serie TV.
            </p>

            <h3>Come abbiamo reso possibile tutto questo?</h3>
            <p>
                Grazie a un rivoluzionario mix di tecnologia avanzata e pura magia narrativa, i nostri esperti hanno sviluppato il <strong>Reality Gateway</strong>,
                un sistema in grado di aprire portali verso qualsiasi universo tu possa immaginare. Ogni viaggio è progettato per garantirti un’esperienza immersiva e autentica,
                permettendoti di interagire con i tuoi personaggi preferiti e vivere storie uniche in prima persona.
            </p>

            <h3>La nostra missione</h3>
            <p>
                Il nostro obiettivo è abbattere i confini tra fantasia e realtà, offrendo ai viaggiatori la possibilità di scoprire mondi straordinari come mai prima d’ora.
                Che tu voglia volare su una scopa a Hogwarts, esplorare la Contea con gli hobbit o combattere al fianco dei tuoi eroi anime preferiti, noi possiamo portarti lì.
            </p>

            <p>Sei pronto a partire? <br> <strong>BeyondReality Journeys</strong> ti aspetta per il viaggio della tua vita!</p>

            <h3>Il nostro team</h3>
            <p>Clicca su un membro del team per scoprire di più su di lui e sui suoi contatti!</p>
            <div class="team" id="contact-section">
                <div class="team-member">
                    <a href="https://linktr.ee/ciiprosk"><div class="profile-pic"><img src="img/common/ciiprosk_profile.webp" alt="Foto di Rosa Passaro" loading="lazy"></div></a>
                    <h4>Passaro Rosa</h4>
                    <p>r.passaro5@studenti.unisa.it</p>
                </div>
                <div class="team-member">
                    <a href="https://linktr.ee/CupoMeridio"><div class="profile-pic"><img src="img/index/20200806_125456.webp" alt="Foto di Vittorio Postiglione" loading="lazy"></div></a>
                    <h4>Postiglione Vittorio</h4>
                    <p>v.postiglione7@studenti.unisa.it</p>
                </div>
                <div class="team-member">
                    <a href="https://linktr.ee/MattiaSanzari"><div class="profile-pic"><img src="img/common/mattia_profile.webp" alt="Foto di Mattia Sanzari" loading="lazy"></div></a>
                    <h4>Sanzari Mattia</h4>
                    <p>m.sanzari@studenti.unisa.it</p>
                </div>
                <div class="team-member">
                    <a href="https://linktr.ee/a.ntibiotico"><div class="profile-pic"><img src="img/index/av.webp" alt="Foto di Antonio Vitale" loading="lazy"></div></a>
                    <h4>Vitale Antonio</h4>
                    <p>a.vitale132@studenti.unisa.it</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'components/footer.html'; ?>
</body>

</html>
