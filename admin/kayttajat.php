<?php
session_start();  //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
require_once '../db.php'; //Tuodaan tietokantayhteys

if ($_SESSION['role'] !== 'admin') die("Ei oikeuksia."); // Varmistetaan, että käyttäjällä on admin-oikeudet ennen kuin hän pääsee käsiksi tähän sivuun, muuten näytä virheilmoitus ja lopetetaan skripti

// Jos delete_user-parametri on asetettu, suoritetaan käyttäjän poisto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");  // Valmistellaan SQL-kysely, joka päivittää käyttäjän roolin id:n perusteella. SQL-injektio on estetty käyttämällä prepared statementia.
    $stmt->execute([$_POST['role'], $_POST['user_id']]);  // Suoritetaan SQL-kysely, ja välitetään uusi rooli ja käyttäjään id taulukkomuodossa execute-metodille
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();  // Haetaan kaikki käyttäjät tietokannasta, jotta voimme näyttää ne admin-sivulla
?>
<h1>Käyttäjät</h1>

<table>
<tr><th>ID</th><th>Email</th><th>Role</th><th></th></tr>

<?php foreach($users as $u): ?> <!-- Käydään läpi kaikki käyttäjät, ja näytetään heidän id, email ja role kenttänsä taulukkomuodossa, sekä lomake roolin päivittämiseen -->
<tr>
  <td><?= $u['id'] ?></td>  <!-- Näytä käyttäjään id -->
  <td><?= $u['email'] ?></td> <!-- Näytä käyttäjään email -->
  <td>
    <form method="post">  <!-- Lomake, joka lähettää tiedot POST-metodilla samaan osoitteeseen, ja sisältää piilotetun kentän user_id, joka välittää käyttäjään id:n, sekä select-kentän, jossa admin voi valita uuden roolin -->
      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">  <!-- Piilotettu kenttä, joka välittää käyttäjään id:n, jotta tiedämme minkä käyttäjän roolia päivitetään -->
      <select name="role">  <!-- Select-kenttä, jossa admin voi valita uuden roolin -->
        <option value="user" <?= $u['role']=='user'?'selected':'' ?>>user</option>  <!-- Option, joka edustaa "user" roolia, ja on valittuna, jos käyttäjään rooli on tällä hetkellä "user" -->
        <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>admin</option> <!-- Option, joka edustaa "admin" roolia, ja on valittuna, jos käyttäjään rooli on tällä hetkellä "admin" -->
      </select>
      <button type="submit">Tallenna</button> <!-- Tallenna-painike, joka lähettää lomakkeen, ja päivittää käyttäjään roolin tietokantaan -->
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>
