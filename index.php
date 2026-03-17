<?php
session_start();  //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once 'db.php';  //Tuodaan tietokantayhteys

$search = $_GET['search'] ?? '';  //Haetaan hakusana URL-parametreista, jos ei ole, käytetään tyhjää merkkijonoa

if ($search) {  //Jos hakusana on annettu, haetaan kuvat, joiden filename sisältää hakusanan
    $stmt = $pdo->prepare("SELECT * FROM images WHERE filename LIKE ? ORDER BY id ASC");  //Valmistellaan SQL-kysely sql-injektion estämiseksi
    $stmt->execute(["%$search%"]);  //Suoritetaan SQL-lause
} else {
    $stmt = $pdo->query("SELECT * FROM images ORDER BY id ASC");  //Jos hakusanaa ei ole, haetaan kaikki kuvat järjestyksessä
}

$images = $stmt->fetchAll();  //Haetaan kaikki kuvat taulukkomuodossa
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Kuvapankki</title>
<link rel="stylesheet" href="assets/style.css"> <!-- Tuodaan CSS tyylitiedosto -->
<link rel="icon" type="image/png" href="/kuvapankki/assets/images/logo.png">  <!-- Asetetaan sivuston selainikoniksi logo kuva-->
</head>

<body>

<?php include 'partials/header.php'; ?> <!-- Tuodaan header, joka sisältää navigaation -->

<main>

  <div class="container">

    <div class="search">  <!-- Hakupalkki GET-parametrina -->
      <form method="get"> 
        <input type="text"
               name="search"
               placeholder="🔍 Hae kuvia"
               value="<?= htmlspecialchars($search) ?>">  <!-- Hakukenttä, joka säilyttää hakusanan syötettynä -->
      </form>
    </div>

    <div class="gallery"> <!-- Kuvagalleria, jossa näytetään kuvat -->

      <?php foreach($images as $img): ?>  <!-- Käydään läpi kaikki kuvat ja näytetään ne -->
        <a href="kuva.php?id=<?= $img['id'] ?>"
           class="image-box"
           style="background-image: url('kuvat/originals/<?= htmlspecialchars($img['filename']) ?>');">
        </a>  <!-- Jokainen kuva on linkki kuva.php-sivulle, jolle välitetään kuvan id GET-parametrina -->
      <?php endforeach; ?>  <!-- Suljetaan foreach-silmukka -->

    </div>

  </div>

</main>

<?php include 'partials/footer.php'; ?> <!-- Tuodaan footer -->

</body>
</html>
