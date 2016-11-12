<?php
/**
 * renvoie un tableau des étiquettes valides à une date donnée
 **/

header('Cache-Control: no-cache, must-revalidate');

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

header('Content-type: application/json');

setlocale (LC_ALL, "fr_FR.UTF-8");
date_default_timezone_set("Europe/Paris");
if (isset($_GET["date"])){
  $date=$_GET["date"];
} else {
  $date=strftime("%Y-%m-%d %H:%M");
}

$datetime=explode(" ", $date);

$dbh = new PDO('sqlite:edt.db');

$sql = "SELECT * FROM draggables WHERE date=".$dbh->quote($datetime[0]);
$res=$dbh->query($sql);
$draggables = $res->fetch(PDO::FETCH_ASSOC);
if($draggables){
  $columnList = json_decode($draggables["ordering"]);
  $tickets=$draggables["tickets"];
  /* traite les octets passés en byte-encoded */
  $tickets=utf8_encode(preg_replace("#(\\\x[0-9A-F]{2})#e", "chr(hexdec('\\1'))", $tickets));
  /* ajoute des guillemets autour des clés */
  $ticketObj=preg_replace('/(\w+):/', '"${1}":', $tickets);

  $etiquettes = json_decode($ticketObj);
  print json_encode(array(
      "columnlist" => $columnList,
      "etiquettes" => $etiquettes,
      "date"       => strftime("%A %e %B %Y, il est %H:%M"),
  ));
} else {
    print json_encode(array(
        "columnlist" => array(),
        "etiquettes" => array(),
        "date"       => "ERREUR ".strftime("%A %e %B %Y, il est %H:%M"),
    ));
}

?>
