<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once 'db.php';  //Tuodaan tietokantayhteys

if (!isset($_SESSION['user_id'])) { // Jos käyttäjä ei ole kirjautunut, näytä virheilmoitus ja lopetetaan skripti
    die("Kirjaudu ensin.");
}

//Jos id-parametriä ei ole, näytä virheilmoitus ja lopetetaan skripti
if (!isset($_GET['id'])) {
    die("Kuvaa ei valittu.");
}

$user_id = $_SESSION['user_id'];    // Haetaan kuvan id GET-parametreista ja muutetaan kokonaisluvuksi turvallisuuden vuoksi
$image_id = (int)$_GET['id'];

// Tarkistetaan onko kuva ostettu
$stmt = $pdo->prepare("
    SELECT * FROM purchases 
    WHERE user_id = ? AND image_id = ?
");
$stmt->execute([$user_id, $image_id]);  // Suoritetaan SQL-kysely, ja välitetään käyttäjään id ja kuvan id taulukkomuodossa execute-metodille

// Jos rowCount() palauttaa 0, kuvaa ei ole ostettu, näytä virheilmoitus ja lopetetaan skripti
if ($stmt->rowCount() == 0) { 
    die("Et ole ostanut tätä kuvaa.");
}

$stmt = $pdo->prepare("SELECT filename FROM images WHERE id = ?");  //Valmistellaan SQL-kysely kuvan hakemiseksi id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
$stmt->execute([$image_id]);    //Suoritetaan SQL-kysely, ja välitetään kuvan id taulukkomuodossa execute-metodille
$image = $stmt->fetch();    //Haetaan kuvan tiedot tietokannasta. Jos kuva löytyy, $image on assosiatiivinen taulukko, muuten se on false

// Jos kuvaa ei löydy, näytä virheilmoitus ja lopetetaan skripti
if (!$image) {
    die("Kuvaa ei löytynyt.");
}

$filePath = "kuvat/originals/" . $image['filename'];    // Määritellään kuvan tiedostopolku, joka on tallennettu kuvat/originals-kansioon

// Tarkistetaan, että tiedosto todella löytyy, ennen kuin yritetään ladata sitä
if (!file_exists($filePath)) {
    die("Tiedostoa ei löytynyt.");
}

header('Content-Type: application/octet-stream');   // Asetetaan HTTP-otsikko, joka kertoo selaimelle, että kyseessä on ladattava tiedosto
header('Content-Disposition: attachment; filename="' . $image['filename'] . '"');   // Asetetaan HTTP-otsikko, joka kertoo selaimelle, että tiedosto tulisi ladata ja ehdotetaan tiedoston nimeä
readfile($filePath);    // Luetaan tiedosto ja lähetetään se selaimelle, mikä saa selaimen aloittamaan latauksen
exit;   // Lopetetaan skripti latauksen jälkeen
