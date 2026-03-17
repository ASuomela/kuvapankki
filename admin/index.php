<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once '../db.php';   //Tuodaan tietokantayhteys

// Varmistetaan, että käyttäjällä on admin-oikeudet ennen kuin hän pääsee käsiksi tähän sivuun, muuten näytä virheilmoitus ja lopetetaan skripti
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Ei oikeuksia.");
}
if (isset($_GET['delete_user'])) {  // Jos delete_user-parametri on asetettu, suoritetaan käyttäjän poisto
    $id = (int)$_GET['delete_user'];    // Varmistetaan, että admin ei vahingossa poista omaa tiliään, joka aiheuttaisi sen, että hän ei enää pääse admin-sivulle

    // Poistetaan käyttäjä vain, jos id ei ole sama kuin kirjautuneen käyttäjän id
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");    // Valmistellaan SQL-kysely, joka poistaa käyttäjän id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
        $stmt->execute([$id]);  // Suoritetaan SQL-kysely, ja välitetään käyttäjään id taulukkomuodossa execute-metodille
    }

    header("Location: index.php");  // Uudelleenohjataan admin-sivulle, jotta nähdään päivitetty käyttäjälista
    exit;   // Lopetetaan skripti, jotta varmistetaan ettei ohjaus jälkeen suoriteta mitään muuta koodia
}
// Jos delete_image-parametri on asetettu, suoritetaan kuvan poisto
if (isset($_GET['delete_image'])) {
    $id = (int)$_GET['delete_image'];   // Haetaan kuvan tiedot tietokannasta, jotta saadaan selville kuvan tiedostonimi, joka tarvitaan kuvatiedoston poistamiseen levyltä

    $stmt = $pdo->prepare("SELECT filename FROM images WHERE id = ?");  // Valmistellaan SQL-kysely, joka hakee kuvan tiedot id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
    $stmt->execute([$id]);  // Suoritetaan SQL-kysely, ja välitetään kuvan id taulukkomuodossa execute-metodille
    $image = $stmt->fetch();    // Haetaan kuvan tiedot tietokannasta, jos kuva löytyy, poista se sekä tietokannasta että levyltä

    if ($image) {
        $filePath = "../kuvat/originals/" . $image['filename'];  // Määritellään kuvan tiedostopolku, joka on tallennettu kuvat/originals-kansioon
        if (file_exists($filePath)) {   // Varmistetaan, että tiedosto todella löytyy, ennen kuin yritetään poistaa sitä
            unlink($filePath);  // Poistetaan tiedosto levyltä
        }

        $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");   // Valmistellaan SQL-kysely, joka poistaa kuvan id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
        $stmt->execute([$id]);  // Suoritetaan SQL-kysely, ja välitetään kuvan id taulukkomuodossa execute-metodille, joka poistaa kuvan tietokannasta
    }

    header("Location: index.php");  // Uudelleenohjataan admin-sivulle, jotta nähdään päivitetty kuvagalleria
    exit;   // Lopetetaan skripti, jotta varmistetaan ettei ohjaus jälkeen suoriteta mitään muuta koodia
}

$users = $pdo->query("SELECT id, email FROM users ORDER BY id ASC")->fetchAll();    // Haetaan kaikki käyttäjät tietokannasta, jotta voimme näyttää ne admin-sivulla. Valitaan vain id ja email kentät, koska muita tietoja ei tarvita admin-sivulla
$images = $pdo->query("SELECT * FROM images ORDER BY id ASC")->fetchAll();  // Haetaan kaikki kuvat tietokannasta, jotta voimme näyttää ne admin-sivulla
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="/kuvapankki/assets/images/logo.png">    <!-- Asetetaan sivuston selainikoniksi logo kuva-->
<title>Admin</title>
<link rel="stylesheet" href="../assets/style.css">  <!-- Tuodaan CSS tyylitiedosto, joka on assets-kansiossa, joten polku on ../assets/style.css -->
</head>
<body>

<?php include '../partials/header.php'; ?>  <!-- Tuodaan header, joka sisältää navigaation. Header on partials-kansiossa, joten polku on ../partials/header.php -->

<main class="container">

<h1>Admin</h1>

<div class="admin-box"> <!-- Admin-sivun päälaatikko, joka sisältää sekä käyttäjät että kuvat -->

    <h2>Käyttäjät</h2>

    <div class="admin-grid">
        <?php foreach ($users as $user): ?> <!-- Käydään läpi kaikki käyttäjät, ja näytetään heidän sähköpostinsa sekä poistolinkki, jos kyseessä ei ole kirjautunut käyttäjä itse -->
            <div class="admin-user">
                <strong><?= htmlspecialchars($user['email']) ?></strong><br>
                <?php if ($user['id'] != $_SESSION['user_id']): ?>  <!-- Varmistetaan, että admin ei vahingossa poista omaa tiliään, joka aiheuttaisi sen, että hän ei enää pääse admin-sivulle -->
                    <a href="?delete_user=<?= $user['id'] ?>"
                       onclick="return confirm('Poistetaanko käyttäjä?')">
                       Poista
                    </a>    <!-- Poistolinkki, joka lähettää delete_user-parametrin GET-pyyntöön, ja varmistaa vielä JavaScriptin confirm-funktiolla, että käyttäjä todella haluaa poistaa tilin -->
                <?php else: ?>  <!-- Jos kyseessä on kirjautunut käyttäjä itse, näytä vain teksti "Sinä", jotta admin tietää että kyseessä on hänen oma tilinsä, eikä vahingossa poista sitä -->
                    (Sinä)
                <?php endif; ?>
            </div>
        <?php endforeach; ?> 
    </div>

</div>

<div class="admin-box"> <!-- Admin-sivun päälaatikko, joka sisältää sekä käyttäjät että kuvat -->

    <h2>Kuvat</h2>

    <div class="admin-grid"> 

        <?php foreach ($images as $img): ?> <!-- Käydään läpi kaikki kuvat, ja näytetään niiden thumbnailit sekä muokkaus- ja poistolinkit -->
            <div class="admin-image"> 

                <img src="../kuvat/originals/<?= htmlspecialchars($img['filename']) ?>">    <!-- Näytä kuvan thumbnail, joka on tallennettu kuvat/originals-kansioon -->

                <div class="admin-image-actions">   <!-- Kuvan muokkaus- ja poistolinkit, jotka on sijoitettu kuvan alle -->
                    <a href="muokkaa_kuva.php?id=<?= $img['id'] ?>">Muokkaa</a> |   <!-- Muokkauslinkki, joka ohjaa muokkaa_kuva.php-sivulle, jolle välitetään kuvan id GET-parametrina -->
                    <a href="?delete_image=<?= $img['id'] ?>"
                       onclick="return confirm('Poistetaanko kuva?')">
                       Poista
                    </a>    <!-- Poistolinkki, joka lähettää delete_image-parametrin GET-pyyntöön, ja varmistaa vielä JavaScriptin confirm-funktiolla, että käyttäjä todella haluaa poistaa kuvan -->
                </div>

            </div>
        <?php endforeach; ?>

    </div>

    <div style="margin-top:20px;">  <!-- Lisää kuva -painike, joka on sijoitettu kuvagallerian alle, jotta admin näkee ensin kaikki kuvat ennen kuin lisää uuden -->
        <a href="lisaa_kuva.php" class="admin-add-btn">Lisää kuva</a>
    </div>

</div>

</main>

<?php include '../partials/footer.php'; ?>  <!-- Tuodaan footer -->

</body>
</html>
