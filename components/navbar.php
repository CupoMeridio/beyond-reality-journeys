<?php
/**
 * @var string|null $username
 * @var string|null $img
 * @var string|null $email
 */

$username = $username ?? '';
$img = $img ?? 'img/common/default.png';
?>

<nav id="navbar">
    <a href="index.php">
        <img src="img/common/logo.png" alt="Logo">
    </a>

    <a class="navButton" id="homeButton" href="index.php">Home</a>
    <a class="navButton" id="aboutButton" href="index.php#about-section">About</a>
    <a class="navButton" id="contactButton" href="index.php#contact-section">Contact</a>

    <?php if (empty($email)): ?>
        <div id="navAuth">
            <a class="navButton" id="registrazioneButton" href="autenticazione.php">
                Registrati
            </a>

            <a class="navButton" id="accessoButton" href="autenticazione.php?login">
                Accedi
            </a>
        </div>
    <?php else: ?>

        <div id="userProfile">
            <span id="welcomeMessage">
                Ciao, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <img
                id="profilePic"
                src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                alt="Immagine profilo">
        </div>

    <?php endif; ?>
</nav>