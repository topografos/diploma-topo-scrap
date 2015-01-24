<?php
$db = new SQLite3('DATABASENAME');

$records = $db->query(" SELECT * FROM diplomas ORDER BY date DESC limit 15");
   while($row = $records->fetchArray(SQLITE3_ASSOC) ){
      echo "<a href=" . $row[link] . ">" . $row['title'] . "</a><br>";
      echo "<b> ". $row['author'] ."</b>[". $row['idryma'] ."]<br>";
      $abs = substr($row['abstract'],0,800); 
      echo "<i>Περίληψη:</i>$abs<a href=" . $row[link] . "?show=full>...</a><p>";
   }

?>
