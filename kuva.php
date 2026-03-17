<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once 'db.php';  //Tuodaan tietokantayhteys

if (!isset($_GET['id'])) {  //Jos id-parametriä ei ole, näytä virheilmoitus ja lopetetaan skripti
    die("Kuvaa ei löytynyt.");
}

$image_id = (int)$_GET['id'];   //Haetaan kuvan id GET-parametreista ja muutetaan kokonaisluvuksi turvallisuuden vuoksi

$stmt = $pdo->prepare("SELECT * FROM images WHERE id = ?"); //Valmistellaan SQL-kysely kuvan hakemiseksi id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
$stmt->execute([$image_id]);    //Suoritetaan SQL-kysely, ja välitetään kuvan id taulukkomuodossa execute-metodille
$image = $stmt->fetch();    //Haetaan kuvan tiedot tietokannasta. Jos kuva löytyy, $image on assosiatiivinen taulukko, muuten se on false

if (!$image) {  //Jos kuvaa ei löydy, näytä virheilmoitus ja lopetetaan skripti
    die("Kuvaa ei löytynyt.");
}

$isPurchased = false;   // Oletuksena kuvaa ei ole ostettu

if (isset($_SESSION['user_id'])) {  // Jos käyttäjä on kirjautunut, tarkistetaan onko kuva ostettu

    $stmt = $pdo->prepare("
        SELECT * FROM purchases 
        WHERE user_id = ? AND image_id = ?
    "); // Valmistellaan SQL-kysely, joka hakee ostotiedot käyttäjälle ja kuvalle
    $stmt->execute([$_SESSION['user_id'], $image_id]);  // Suoritetaan SQL-kysely, ja välitetään käyttäjään id ja kuvan id taulukkomuodossa execute-metodille

    $isPurchased = $stmt->rowCount() > 0;   // Jos rowCount() palauttaa enemmän kuin 0, kuva on ostettu, muuten ei
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($image['filename']) ?></title>  <!-- Asetetaan sivun otsikoksi kuvan filename, joka on turvallisesti suojattu htmlspecialchars-funktiolla -->
<link rel="stylesheet" href="assets/style.css"> <!-- Tuodaan CSS tyylitiedosto -->
<link rel="icon" type="image/png" href="/kuvapankki/assets/images/logo.png"> <!-- Asetetaan sivuston selainikoniksi logo kuva-->
</head>
<body>

<?php include 'partials/header.php'; ?> <!-- Tuodaan header, joka sisältää navigaation -->

<main class="container">

    <div class="image-view">    <!-- Kuvan näyttöalue -->
        <img src="kuvat/watermarked/<?= htmlspecialchars($image['filename']) ?>" alt=""> <!-- Näytetään vesileimallinen kuva, joka on tallennettu kuvat/watermarked-kansioon -->
    </div>

    <div class="image-bottom">  <!-- Alue, jossa näytetään kuvan kuvaus ja ostopainike -->

        <div class="image-description"> <!-- Kuvan kuvaus -->
            <?= nl2br(htmlspecialchars($image['description'] ?? 'Ei kuvausta vielä.')) ?>
        </div>

        <div class="image-buy"> <!-- Ostoalue, jossa näytetään ostopainike tai latauspainike riippuen siitä onko kuva ostettu vai ei -->

            <?php if (!isset($_SESSION['user_id'])): ?> <!-- Jos käyttäjä ei ole kirjautunut, näytä kehotus kirjautua sisään ostaakseen kuva -->

                <a href="login.php" class="buy-btn">    <!-- Linkki login.php-sivulle, jossa käyttäjä voi kirjautua sisään -->
                    Kirjaudu ostaaksesi
                </a>

            <?php elseif ($isPurchased): ?> <!-- Jos kuva on ostettu, näytä latauspainike -->

                <a href="lataa.php?id=<?= $image_id ?>" class="buy-btn"> <!-- Linkki lataa.php-sivulle, jolle välitetään kuvan id GET-parametrina -->
                    LATAA
                </a>

            <?php else: ?>

                <a href="osto.php?id=<?= $image_id ?>" class="buy-btn"> <!-- Linkki osto.php-sivulle, jolle välitetään kuvan id GET-parametrina -->
                    OSTA
                </a>

            <?php endif; ?> <!-- Suljetaan if-lauseet -->

        </div>

    </div>

</main>

<?php include 'partials/footer.php'; ?> <!-- Tuodaan footer, joka sisältää tekijänoikeustiedot -->

</body>
</html>
