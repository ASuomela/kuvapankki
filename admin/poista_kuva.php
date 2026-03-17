<?php
session_start();  //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once '../db.php'; //Tuodaan tietokantayhteys

if ($_SESSION['role'] !== 'admin') die("Ei oikeuksia.");  // Varmistetaan, että käyttäjällä on admin-oikeudet ennen kuin hän pääsee käsiksi tähän sivuun, muuten näytä virheilmoitus ja lopetetaan skripti

if (isset($_GET['delete'])) {  // Jos delete-parametri on asetettu, suoritetaan kuvan poisto
    $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");  // Valmistellaan SQL-kysely, joka poistaa kuvan id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
    $stmt->execute([$_GET['delete']]);  // Suoritetaan SQL-kysely, ja välitetään kuvan id taulukkomuodossa execute-metodille, joka poistaa kuvan tietokannasta
}

$images = $pdo->query("SELECT * FROM images")->fetchAll();  // Haetaan kaikki kuvat tietokannasta, jotta voimme näyttää ne admin-sivulla, ja poistaa niitä tarvittaessa
?>
<h1>Poista kuvia</h1>
<table>
<tr><th>ID</th><th>Filename</th><th></th></tr>
<?php foreach($images as $img): ?>  <!-- Käydään läpi kaikki kuvat, ja näytetään heidän id ja filename kenttänsä taulukkomuodossa, sekä linkki kuvan poistamiseen -->
<tr>
  <td><?= $img['id'] ?></td>  <!-- Näytä kuvan id -->
  <td><?= $img['filename'] ?></td>  <!-- Näytä kuvan filename -->
  <td><a href="?delete=<?= $img['id'] ?>">Poista</a></td> <!-- Linkki, joka lähettää delete-parametrin, ja poistaa kuvan tietokannasta -->
</tr>
<?php endforeach; ?>  <!-- Käydään läpi kaikki kuvat, ja näytetään heidän id ja filename kenttänsä taulukkomuodossa, sekä linkki kuvan poistamiseen -->
</table>
