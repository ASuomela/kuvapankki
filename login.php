<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once 'db.php';  //Tuodaan tietokantayhteys

$error = '';    // Alustetaan virheviestimuuttuja tyhjäksi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    // Tarkistetaan onko lomake lähetetty POST-metodilla

    $email = $_POST['email'];   // Haetaan sähköposti POST-data, joka on syötetty lomakkeelle
    $password = $_POST['password'];  // Haetaan salasana POST-data, joka on syötetty lomakkeelle

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");   // Valmistellaan SQL-kysely, joka hakee käyttäjätiedot sähköpostin perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
    $stmt->execute([$email]);   // Suoritetaan SQL-kysely, ja välitetään sähköposti taulukkomuodossa execute-metodille
    $user = $stmt->fetch();   // Haetaan käyttäjätiedot tietokannasta. Jos käyttäjä löytyy, $user on assosiatiivinen taulukko, muuten se on false

    if ($user && password_verify($password, $user['password'])) {   // Tarkistetaan onko käyttäjä löytynyt ja onko syötetty salasana oikea vertaamalla sitä tietokannassa olevaan hashattuun salasanaan password_verify-funktion avulla

        $_SESSION['user_id'] = $user['id'];  // Tallennetaan käyttäjän id sessioon, jotta voimme tunnistaa käyttäjän muilla sivuilla
        $_SESSION['role'] = $user['role'];  // Tallennetaan käyttäjän rooli sessioon, jotta voimme tarkistaa onko käyttäjällä admin-oikeudet muilla sivuilla
        $_SESSION['email'] = $user['email']; // Tallennetaan käyttäjän sähköposti sessioon, jotta voimme näyttää sen profiilisivulla tai headerissä

        header("Location: index.php");  // Ohjataan käyttäjä index.php-sivulle onnistuneen kirjautumisen jälkeen
        exit;   // Lopetetaan skripti, jotta varmistetaan ettei ohjaus jälkeen suoriteta mitään muuta koodia
    } else {
        $error = "Virheellinen sähköposti tai salasana.";   // Jos käyttäjää ei löydy tai salasana on väärä, asetetaan virheviestiksi "Virheellinen sähköposti tai salasana."
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="/kuvapankki/assets/images/logo.png">    <!-- Asetetaan sivuston selainikoniksi logo kuva-->
<title>Kirjaudu</title>
<link rel="stylesheet" href="assets/style.css"> <!-- Tuodaan CSS tyylitiedosto -->
</head>
<body>

<?php include 'partials/header.php'; ?> <!-- Tuodaan header, joka sisältää navigaation -->

<main class="login-wrapper"> 

    <div class="login-box">   <!-- Kirjautumislomake, jossa on kentät sähköpostille ja salasanalle, sekä kirjautumispainike -->

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
                <p class="error"><?= $error ?></p>  <!-- Näytä virheilmoitus, joka on suojattu htmlspecialchars-funktion avulla -->
            <?php endif; ?>   <!-- Suljetaan if-lause -->

            <a href="register.php" class="register-link">   <!-- Linkki rekisteröitymissivulle, jossa käyttäjä voi luoda uuden tilin -->
                Rekisteröidy
            </a>

            <button type="submit" class="login-btn">    <!-- Kirjautumispainike, joka lähettää lomakkeen -->
                Kirjaudu sisään
            </button>

        </form>

    </div>

</main>

<?php include 'partials/footer.php'; ?> <!-- Tuodaan footer, joka sisältää tekijänoikeustiedot -->

</body>
</html>
