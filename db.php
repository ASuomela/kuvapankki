<?php
$host = 'localhost'; //Tietokantapalvelimen osoite
$db   = 'kuvapankki'; //Tietokannan nimi
$user = 'root'; //Tietokantakäyttäjätunnus XAMPP oletuksena root
$pass = ''; //Tietokantasalasana XAMPP oletuksena tyhjä
$charset = 'utf8mb4'; //Merkistö

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";  //Data Source Name, joka kertoo PDO:lle miten yhdistää tietokantaan

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,    // Aseta virhetilaksi poikkeukset
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Aseta oletushakutavaksi assosiatiivinen taulukko
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);   // Luo PDO-yhteys tietokantaan
} catch (PDOException $e) {
    die("Tietokantavirhe: " . $e->getMessage());    // Jos yhteyden muodostaminen epäonnistuu, näytä virheilmoitus
}
