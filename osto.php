<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once 'db.php';  //Tuodaan tietokantayhteys

// Varmistetaan, että käyttäjä on kirjautunut sisään ennen kuin hän voi ostaa kuvaa tai muuten näytä virheilmoitus ja lopetetaan skripti
if (!isset($_SESSION['user_id'])) {
    die("Kirjaudu ensin.");
}

// Varmistetaan, että id-parametri on asetettu, muuten näytä virheilmoitusja lopetetaan skripti
if (!isset($_GET['id'])) {
    die("Kuvaa ei valittu.");
}

$user_id = $_SESSION['user_id'];    // Haetaan kuvan id GET-parametreista ja muutetaan kokonaisluvuksi turvallisuuden vuoksi
$image_id = (int)$_GET['id'];

// Tarkistetaan onko kuva ostettu jo aiemmin, jotta vältetään tuplamaksut
$stmt = $pdo->prepare("
    SELECT * FROM purchases 
    WHERE user_id = ? AND image_id = ?
");
$stmt->execute([$user_id, $image_id]);  // Suoritetaan SQL-kysely, ja välitetään käyttäjään id ja kuvan id taulukkomuodossa execute-metodille

// Jos kuvaa ei ole ostettu, lisätään uusi rivi purchases-tauluun, joka kertoo että käyttäjä on ostanut kuvan
if ($stmt->rowCount() == 0) {

    // Tässä kohtaa suoritettaisiin maksuprosessi, oletetaan että maksu onnistuu ja lisätään ostotiedot suoraan tietokantaan
    $insert = $pdo->prepare("  
        INSERT INTO purchases (user_id, image_id) 
        VALUES (?, ?)
    ");
    $insert->execute([$user_id, $image_id]);    // Suoritetaan SQL-kysely, ja välitetään käyttäjään id ja kuvan id taulukkomuodossa execute-metodille
}

header("Location: kuva.php?id=" . $image_id);   // Uudelleenohjataan käyttäjä kuva.php-sivulle, jolle välitetään kuvan id GET-parametrina, jotta hän näkee ostamansa kuvan ilman vesileimaa
exit;   // Lopetetaan skripti, jotta varmistetaan ettei ohjaus jälkeen suoriteta mitään muuta koodia
