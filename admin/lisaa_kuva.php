<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once '../db.php';   //Tuodaan tietokantayhteys

// Varmistetaan, että käyttäjällä on admin-oikeudet ennen kuin hän pääsee käsiksi tähän sivuun, muuten näytä virheilmoitus ja lopetetaan skripti
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Ei oikeuksia.");
}
// Jos lomake on lähetetty, käsitellään kuvan lataus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Varmistetaan, että tiedosto on lähetetty onnistuneesti, muuten näytä virheilmoitus ja lopetetaan skripti
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        die("Tiedoston lataus epäonnistui.");
    }

    $price = $_POST['price'];   // Haetaan kuvan hinta POST-parametreista

    // Varmistetaan, että tiedostotyyppi on sallittu, muuten näytä virheilmoitus ja lopetetaan skripti
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES['image']['type'], $allowed)) {
        die("Vain JPG, PNG ja WEBP sallittu.");
    }

    // Turvallinen tiedostonimi, joka poistaa polut ja välilyönnit
    $filename = basename($_FILES['image']['name']);

    // Korvataan mahdolliset välilyönnit alaviivoilla, jotta tiedostonimi on yhteensopiva eri käyttöjärjestelmien kanssa
    $filename = str_replace(' ', '_', $filename);
    // Määritellään kuvan tallennuspolku, joka on kuvat/originals-kansio
    $uploadPath = "../kuvat/originals/" . $filename;

    // Siirretään ladattu tiedosto väliaikaisesta sijainnista määritettyyn tallennuspolkuun
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);

    // Tarkistetaan onko kuva jo tietokannassa, jos on päivitetään hinta, muuten lisätään uusi kuva tietokantaan
    $stmt = $pdo->prepare("SELECT id FROM images WHERE filename = ?");  // Valmistellaan SQL-kysely, joka hakee kuvan id:n tiedostonimen perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
    $stmt->execute([$filename]);    // Suoritetaan SQL-kysely, ja välitetään tiedoston nimi taulukkomuodossa execute-metodille
    // Jos rowCount() palauttaa enemmän kuin 0, kuva löytyy tietokannasta, päivitetään hinta, muuten lisätään uusi kuva tietokantaan
    if ($stmt->rowCount() > 0) {
        $update = $pdo->prepare("UPDATE images SET price = ? WHERE filename = ?");  // Valmistellaan SQL-kysely, joka päivittää kuvan hinnan tiedostonimen perusteella
        $update->execute([$price, $filename]);  // Suoritetaan SQL-kysely, ja välitetään uusi hinta ja tiedoston nimi taulukkomuodossa execute-metodille, joka päivittää kuvan hinnan tietokantaan
    } else {    // Kuvaa ei löydy tietokannasta, lisätään uusi kuva tietokantaan
        $insert = $pdo->prepare("INSERT INTO images (filename, price) VALUES (?, ?)");  // Valmistellaan SQL-kysely, joka lisää uuden kuvan tietokantaan. SQL-injektio on estetty käyttämällä prepared statementia.
        $insert->execute([$filename, $price]);  // Suoritetaan SQL-kysely, ja välitetään tiedoston nimi ja hinta taulukkomuodossa execute-metodille, joka lisää uuden kuvan tietokantaan
    }

    header("Location: index.php");  // Uudelleenohjataan admin-sivulle, jotta nähdään päivitetty kuvagalleria, jossa uusi kuva on nyt näkyvissä
    exit;   // Lopetetaan skripti, jotta varmistetaan ettei ohjaus jälkeen suoriteta mitään muuta koodia
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Lisää kuva</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include '../partials/header.php'; ?>

<main class="container">
    <h1>Lisää kuva</h1>

    <form method="post" enctype="multipart/form-data">
        <label>Kuva:</label><br>
        <input type="file" name="image" required><br><br>

        <label>Hinta (€):</label><br>
        <input type="number" name="price" step="0.01" required><br><br>

        <button type="submit">Lataa kuva</button>
    </form>
</main>

<?php include '../partials/footer.php'; ?>

</body>
</html>
