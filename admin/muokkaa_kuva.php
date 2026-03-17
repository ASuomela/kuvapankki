<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once '../db.php';   //Tuodaan tietokantayhteys
// Varmistetaan, että käyttäjällä on admin-oikeudet ennen kuin hän pääsee käsiksi tähän sivuun, muuten näytä virheilmoitus ja lopetetaan skripti
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Ei oikeuksia.");
}

$id = (int)$_GET['id']; // Haetaan kuvan tiedot tietokannasta, jotta näytetään ne lomakkeessa, ja jotta tiedämme minkä kuvan tietoja päivitetään

$stmt = $pdo->prepare("SELECT * FROM images WHERE id = ?"); // Valmistellaan SQL-kysely, joka hakee kuvan tiedot id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
$stmt->execute([$id]);  // Suoritetaan SQL-kysely, ja välitetään kuvan id taulukkomuodossa execute-metodille
$image = $stmt->fetch(); // Haetaan kuvan tiedot tietokannasta. Jos kuva löytyy, $image on assosiatiivinen taulukko, muuten se on false
// Jos kuvaa ei löydy, näytä virheilmoitus ja lopetetaan skripti
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $description = $_POST['description'];   // Haetaan kuvan kuvaus POST-parametreista, joka on textarea-kenttä, joten se voi sisältää useita rivejä tekstiä, ja se on tallennettu tietokantaan ilman muotoilua, joten näytettäessä sitä HTML:ssä, käytetään nl2br-funktiota, joka muuttaa rivinvaihdot <br> tageiksi, jotta ne näkyvät oikein HTML:ssä
    $price = $_POST['price'];   // Haetaan kuvan hinta POST-parametreista, joka on number-kenttä, joten se on tallennettu tietokantaan desimaalilukuna, ja näytettäessä sitä HTML:ssä, käytetään number_format-funktiota, joka muuttaa desimaaliluvun muotoon 0.00, jotta se näkyy oikein HTML:ssä

    $update = $pdo->prepare("UPDATE images SET description = ?, price = ? WHERE id = ?");   // Valmistellaan SQL-kysely, joka päivittää kuvan kuvaus ja hinta id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
    $update->execute([$description, $price, $id]);  // Suoritetaan SQL-kysely, ja välitetään uusi kuvaus, hinta ja kuvan id taulukkomuodossa execute-metodille, joka päivittää kuvan tiedot tietokantaan

    header("Location: index.php");  // Uudelleenohjataan admin-sivulle, jotta nähdään päivitetty kuvagalleria, jossa kuvan uusi kuvaus ja hinta ovat nyt näkyvissä
    exit;   // Lopetetaan skripti, jotta varmistetaan ettei ohjaus jälkeen suoriteta mitään muuta koodia
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../assets/style.css">  <!-- Tuodaan CSS tyylitiedosto, joka on assets-kansiossa, joten polku on ../assets/style.css -->
<title>Muokkaa kuvaa</title>
</head>
<body>

<?php include '../partials/header.php'; ?>  <!-- Tuodaan header, joka sisältää navigaation. Header on partials-kansiossa, joten polku on ../partials/header.php -->

<main class="container">    <!-- Pääsisältöalue, jossa on lomake kuvan kuvausta ja hintaa varten -->
<h1>Muokkaa kuvaa</h1>

<form method="post"> 
    <label>Kuvaus:</label><br>
    <textarea name="description" rows="6" style="width:100%;"><?= htmlspecialchars($image['description']) ?></textarea><br><br>

    <label>Hinta (€):</label><br>
    <input type="number" name="price" step="0.01" value="<?= $image['price'] ?>"><br><br>

    <button type="submit">Tallenna</button>
</form>

</main>

<?php include '../partials/footer.php'; ?>  <!-- Tuodaan footer, joka sisältää tekijänoikeustiedot. Footer on partials-kansiossa, joten polku on ../partials/footer.php -->

</body>
</html>
