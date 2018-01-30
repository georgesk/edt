<?php

$colpos = $_REQUEST["colPos"];
$tickets = $_REQUEST["tickets"];
$colpos1=Array();
$colpos4db=Array();
$max=-1;
foreach ($colpos as $c){
  $index=intval($c["place"]);
  if ($index >= 0){
    $colpos1[$index]=$c["col"];
    $out=print_r($colpos4db, true);
    if ($index > $max) $max=$index;
  }
}
for($i=0; $i<=$max; $i++){
  if(!isset($colpos1[$i])) $colpos4db[]="";
  else $colpos4db[]=$colpos1[$i];
}
$colpos4db='["'.implode('","',$colpos4db).'"]'; // syntaxe d'un tableau de chaines
date_default_timezone_set("Europe/Paris");
$d = new DateTime($_REQUEST["date"]);
$nbCol = $_REQUEST["nbcol"];
$d4db = $d->format("Y-m-d");
$dbh = new PDO('sqlite:edt.db');
$sql = "SELECT * FROM draggables WHERE date='".$d4db."'";
$res = $dbh->query($sql);
$o=$res->fetchObject();
$dbh->beginTransaction();
if (! $o){ // Rien pour cette date, on crée l'enregistrement
  $sql = "INSERT INTO draggables (tickets,ordering,date,nbcol) VALUES(".$dbh->quote($tickets).",".$dbh->quote($colpos4db).",".$dbh->quote($d4db).",".$dbh->quote($nbCol).")";
  $count=$dbh->exec($sql);
} else { // on met à jour à la bonne date
  $sql = "UPDATE draggables SET tickets=".$dbh->quote($tickets).", ordering=".$dbh->quote($colpos4db).", nbcol=".$dbh->quote($nbCol)." WHERE date='".$d4db."'";
  $count=$dbh->exec($sql);
}
$dbh->commit();

header('Content-Type: application/json');
echo json_encode($count);
?>
