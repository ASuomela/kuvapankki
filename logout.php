<?php
session_start();    //Aloitetaan sessio, jotta voimme käyttää $_SESSION-muuttujaa
session_unset();    // Tyhjennetään kaikki sessiomuuttujat, mikä käytännössä kirjaa käyttäjän ulos
session_destroy();  // Tuhoetaan sessio kokonaan
header("Location: index.php");  // Uudelleenohjataan käyttäjä etusivulle, joka on index.php
exit;   // Lopetetaan skripti, jotta varmistetaan ettei ohjaus jälkeen suoriteta mitään muuta koodia
