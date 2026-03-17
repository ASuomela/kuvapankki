<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once 'db.php';  //Tuodaan tietokantayhteys

// Jos käyttäjä ei ole kirjautunut, näytä virheilmoitus ja lopetetaan skripti
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Haetaan käyttäjän id sessiosta, jotta voimme hakea ja päivittää hänen tietojaan

// Käsitellään profiilin päivitys, jos lomake on lähetetty
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bio = $_POST['bio'];   // Haetaan bio POST-data, joka on syötetty lomakkeelle

    // Käsitellään profiilikuvan päivitys, jos uusi kuva on ladattu
    if (!empty($_FILES['profile_image']['name'])) {

        $filename = basename($_FILES['profile_image']['name']); // Haetaan ladatun tiedoston nimi ja varmistetaan, että se on turvallinen käyttämällä basename-funktiota
        $filename = str_replace(' ', '_', $filename);   // Korvataan mahdolliset välilyönnit alaviivoilla, jotta tiedostonimi on yhteensopiva eri käyttöjärjestelmien kanssa

        $uploadPath = "kuvat/profiles/" . $filename;    // Määritellään kuvan tallennuspolku, joka on kuvat/profiles-kansio

        move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath);  // Siirretään ladattu tiedosto väliaikaisesta sijainnista määritettyyn tallennuspolkuun

        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");   // Valmistellaan SQL-kysely, joka päivittää käyttäjän profiilikuvan tietokantaan. SQL-injektio on estetty käyttämällä prepared statementia.
        $stmt->execute([$filename, $user_id]);  // Suoritetaan SQL-kysely, ja välitetään tiedoston nimi ja käyttäjään id taulukkomuodossa execute-metodille
    }

    $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");  // Valmistellaan SQL-kysely, joka päivittää käyttäjän biografian tietokantaan. SQL-injektio on estetty käyttämällä prepared statementia.
    $stmt->execute([$bio, $user_id]);   // Suoritetaan SQL-kysely, ja välitetään biografia ja käyttäjään id taulukkomuodossa execute-metodille
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");  // Valmistellaan SQL-kysely, joka hakee käyttäjätiedot id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
$stmt->execute([$user_id]);  // Suoritetaan SQL-kysely, ja välitetään käyttäjään id taulukkomuodossa execute-metodille
$user = $stmt->fetch(); // Haetaan käyttäjätiedot tietokannasta. Jos käyttäjä löytyy, $user on assosiatiivinen taulukko, muuten se on false

// Haetaan kaikki ostetut kuvat, jotta voimme näyttää ne profiilisivulla
$stmt = $pdo->prepare("
    SELECT images.* 
    FROM purchases
    JOIN images ON purchases.image_id = images.id
    WHERE purchases.user_id = ?
");
$stmt->execute([$user_id]); // Suoritetaan SQL-kysely, ja välitetään käyttäjään id taulukkomuodossa execute-metodille
$purchasedImages = $stmt->fetchAll();   // Haetaan kaikki ostetut kuvat tietokannasta. $purchasedImages on taulukko, joka sisältää assosiatiivisia taulukkoja, joissa on ostettujen kuvien tiedot
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Profiili</title>
<link rel="stylesheet" href="assets/style.css"> <!-- Tuodaan CSS tyylitiedosto -->
<link rel="icon" type="image/png" href="/kuvapankki/assets/images/logo.png">    <!-- Asetetaan sivuston selainikoniksi logo kuva-->
</head>
<body>

<?php include 'partials/header.php'; ?> <!-- Tuodaan header, joka sisältää navigaation -->

<main class="container">

<div class="profile-top">

    <div class="profile-image"> <!-- Profiilikuva, joka näytetään kuvat/profiles-kansiosta. Jos kuvaa ei ole, näytetään placeholder -->
        <?php if($user['profile_image']): ?>    <!-- Jos käyttäjällä on profiilikuva, näytä se -->
            <img src="kuvat/profiles/<?= htmlspecialchars($user['profile_image']) ?>">  <!-- Näytä käyttäjän profiilikuva, joka on tallennettu kuvat/profiles-kansioon -->
        <?php else: ?>
            <div class="profile-placeholder"></div> <!-- Näytä placeholder, jos käyttäjällä ei ole profiilikuvaa -->
        <?php endif; ?>
    </div>

    <div class="profile-info">  <!-- Käyttäjään liittyvät tiedot, kuten sähköposti ja biografia -->
        <form method="post" enctype="multipart/form-data">  <!-- Lomake, joka lähettää tiedot POST-metodilla samaan osoitteeseen, ja mahdollistaa tiedostojen lähettämisen enctype="multipart/form-data" avulla -->

            <textarea name="bio" rows="6" placeholder="Kerro itsestäsi..."><?= htmlspecialchars($user['bio']) ?></textarea> <!-- Biografiakenttä, joka säilyttää syötetyn tekstin lomakkeella ja on suojattu htmlspecialchars-funktion avulla -->

            <input type="file" name="profile_image">    <!-- Tiedostolomake, joka mahdollistaa uuden profiilikuvan lataamisen -->

            <button type="submit">Tallenna</button> <!-- Tallenna-painike, joka lähettää lomakkeen -->

        </form>
    </div>

</div>

<h2>Ostetut kuvat</h2>

<div class="profile-gallery">   <!-- Ostettujen kuvien galleria, jossa näytetään kaikki käyttäjän ostamat kuvat -->
    <?php foreach($purchasedImages as $img): ?>   <!-- Käydään läpi kaikki ostetut kuvat ja näytetään ne -->
        <img src="kuvat/originals/<?= htmlspecialchars($img['filename']) ?>">   <!-- Näytä ostettu kuva, joka on tallennettu kuvat/originals-kansioon -->
    <?php endforeach; ?>    <!-- Suljetaan foreach-silmukka -->
</div>

</main>

<?php include 'partials/footer.php'; ?> <!-- Tuodaan footer -->

</body>
</html>
