<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once 'db.php';  //Tuodaan tietokantayhteys

$error = '';    // Alustetaan virheviestimuuttuja tyhjäksi, jotta voimme näyttää mahdolliset virheilmoitukset lomakkeella

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    // Tarkistetaan onko lomake lähetetty POST-metodilla

    $email = $_POST['email'];      // Haetaan sähköposti POST-data, joka on syötetty lomakkeelle
    $password = $_POST['password']; // Haetaan salasana POST-data, joka on syötetty lomakkeelle

    if (empty($email) || empty($password)) {    // Tarkistetaan että molemmat kentät on täytetty, muuten asetetaan virheviestiksi "Täytä kaikki kentät."
        $error = "Täytä kaikki kentät."; 
    } else {    // Jos kentät on täytetty, tarkistetaan onko sähköposti jo rekisteröity tietokantaan

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");  // Valmistellaan SQL-kysely, joka hakee käyttäjään id sähköpostin perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
        $stmt->execute([$email]);   // Suoritetaan SQL-kysely, ja välitetään sähköposti taulukkomuodossa execute-metodille

        if ($stmt->rowCount() > 0) {    // Jos rowCount() palauttaa enemmän kuin 0, sähköposti on jo rekisteröity, asetetaan virheviesti
            $error = "Sähköposti on jo rekisteröity.";
        } else {    // Jos sähköposti ei ole rekisteröity, luodaan uusi käyttäjätiedot tietokantaan

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);   // Hashataan salasana turvallisesti ennen kuin tallennetaan se tietokantaan, jotta edes tietokantavuodon sattuessa salasanat eivät paljastu

            $insert = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'user')"); // Valmistellaan SQL-kysely, joka lisää uuden käyttäjätiedot tietokantaan. SQL-injektio on estetty käyttämällä prepared statementia. Oletetaan että kaikille uusille käyttäjille annetaan "user" rooli.
            $insert->execute([$email, $hashedPassword]);    // Suoritetaan SQL-kysely, ja välitetään sähköposti ja hashattu salasana taulukkomuodossa execute-metodille

            header("Location: login.php");  // Uudelleenohjataan käyttäjä login.php-sivulle, jotta hän voi kirjautua sisään uudella tilillään
            exit;   // Lopetetaan skripti, jotta varmistetaan ettei ohjaus jälkeen suoriteta mitään muuta koodia
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="/kuvapankki/assets/images/logo.png">    <!-- Asetetaan sivuston selainikoniksi logo kuva-->
<title>Rekisteröidy</title>
<link rel="stylesheet" href="assets/style.css"> <!-- Tuodaan CSS tyylitiedosto -->
</head>
<body>

<?php include 'partials/header.php'; ?> <!-- Tuodaan header, joka sisältää navigaation -->

<main class="login-wrapper">    <!-- Rekisteröitymislomake, jossa on kentät sähköpostille ja salasanalle, sekä rekisteröitymispainike -->

    <div class="login-box">

        <form method="post">    <!-- Lomake, joka lähettää tiedot POST-metodilla samaan osoitteeseen -->

            <input type="email"
                   name="email"
                   placeholder="Sähköposti..."
                   required>

            <input type="password"
                   name="password"
                   placeholder="Salasana..."
                   required>

            <?php if($error): ?>    <!-- Jos virheviestimuuttuja ei ole tyhjä, näytä virheilmoitus -->
                <p class="error"><?= $error ?></p>
            <?php endif; ?>

            <a href="login.php" class="register-link">  <!-- Linkki login.php-sivulle, jossa käyttäjä voi kirjautua sisään -->
                Kirjaudu sisään
            </a>

            <button type="submit" class="login-btn">    <!-- Rekisteröitymispainike, joka lähettää lomakkeen -->
                Rekisteröidy
            </button>
                <!-- Tietosuojaselostelinkki, joka on sijoitettu rekisteröitymispainikkeen alle, jotta käyttäjät näkevät sen ennen rekisteröitymistä -->
            <p style="font-size:14px; text-align:center;">
                Rekisteröitymällä hyväksyt
                <a href="tietosuoja.php">tietosuojaselosteen</a>.   <!-- Linkki tietosuojaselosteeseen, joka on tärkeä näyttää rekisteröitymislomakkeella, jotta käyttäjät tietävät miten heidän tietojaan käsitellään -->
            </p>
        </form>

    </div>

</main>

<?php include 'partials/footer.php'; ?> <!-- Tuodaan footer, joka sisältää tekijänoikeustiedot -->

</body>
</html>
