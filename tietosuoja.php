<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Tietosuojaseloste</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include 'partials/header.php'; ?> <!-- Tuodaan header, joka sisältää navigaation -->

<main class="container">    <!-- Tietosuojaseloste, joka sisältää tietoa siitä, miten käyttäjien tietoja käsitellään ja suojataan -->

    <h1>Rekisteri- ja tietosuojaseloste</h1>

    <h2>1. Rekisterinpitäjä</h2>
    <p>
        Kuvapankki-sivusto<br>
        Yhteystiedot: +385 xxx xxx xxx
    </p>

    <h2>2. Kerättävät tiedot</h2>
    <p>
        Sivusto kerää käyttäjiltä seuraavat tiedot:
    </p>
    <ul>
        <li>Sähköpostiosoite</li>
        <li>Salasanan</li>
        <li>Profiilikuvan</li>
        <li>Käyttäjän kuvaustekstin</li>
        <li>Ostohistoria</li>
    </ul>

    <h2>3. Tietojen käyttötarkoitus</h2>
    <p>
        Tietoja käytetään käyttäjätilien hallintaan, kuvien ostamiseen
        sekä palvelun toiminnan mahdollistamiseen.
    </p>

    <h2>4. Tietojen suojaus</h2>
    <p>
        Salasanat tallennetaan tietokantaan hashattuina.
        Tietoja ei luovuteta kolmansille osapuolille.
    </p>

    <h2>5. Käyttäjän oikeudet</h2>
    <p>
        Käyttäjällä on oikeus:
    </p>
    <ul>
        <li>Tarkastaa omat tietonsa</li>
        <li>Pyytää tietojen oikaisua</li>
        <li>Pyytää tilin poistamista</li>
    </ul>

    <h2>6. Tietojen säilytysaika</h2>
    <p>
        Tietoja säilytetään niin kauan kuin käyttäjätili on aktiivinen.
        Käyttäjän pyynnöstä tiedot voidaan poistaa.
    </p>

</main>

<?php include 'partials/footer.php'; ?> <!-- Tuodaan footer, joka sisältää tekijänoikeustiedot -->

</body>
</html>
