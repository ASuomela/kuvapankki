<header>
  <div>
    <a href="/kuvapankki/index.php">Etusivu</a> <!-- Linkki etusivulle, joka on index.php -->
  </div>

  <nav> <!-- Navigaatiolinkit, jotka näytetään riippuen siitä onko käyttäjä kirjautunut sisään vai ei -->

    <?php if(isset($_SESSION['user_id'])): ?>   <!-- Jos käyttäjä on kirjautunut sisään, näytä linkit profiiliin ja uloskirjautumiseen -->

        <?php if($_SESSION['role'] === 'admin'): ?> <!-- Jos käyttäjällä on admin-rooli, näytä linkki admin-sivulle -->
            <a href="/kuvapankki/admin/index.php">Admin</a>
        <?php else: ?>
            <a href="/kuvapankki/profiili.php">Profiili</a> <!-- Linkki profiilisivulle, jossa käyttäjä näkee omat tietonsa ja ostohistoriansa -->
        <?php endif; ?>

        <a href="/kuvapankki/logout.php">Kirjaudu ulos</a>  <!-- Linkki logout.php-sivulle, joka tuhoaa session ja kirjaa käyttäjän ulos -->

    <?php else: ?>
        <a href="/kuvapankki/login.php">Kirjaudu</a>  <!-- Linkki login.php-sivulle, jossa käyttäjä voi kirjautua sisään -->
    <?php endif; ?>

  </nav>
</header>
