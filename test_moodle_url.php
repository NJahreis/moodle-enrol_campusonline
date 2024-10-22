<?php

require_once('../../config.php');

// Erstellen der URL zum Anzeigen eines Kurses
$courseviewurl = new moodle_url('/course/view.php');

// Ausgabe der URL direkt im Browser
echo "<h1>Test der moodle_url Funktion</h1>";
echo "<p>Die Kurs-URL lautet: <strong>" . $courseviewurl->out() . "</strong></p>";
?>
