<?php
/**
 * affiche un tableau avec les professeurs et les classes présents
 * durant cinq heures, centrés sur l'heure demandée
 * paramètres : date au format aaaa-mm-jj+hh:mm
 * si ce paramètre est absent, prend la date courante
 **/

setlocale (LC_ALL, "fr_FR.UTF-8");
date_default_timezone_set("Europe/Paris");
if (isset($_GET["date"])){
  $date=$_GET["date"];
} else {
  $date=strftime("%Y-%m-%d %H:%M");
}

$datetime=explode(" ", $date);
$date=$datetime[0];
$hm=explode(":",$datetime[1]);
$h=intval($hm[0])+intval($hm[1])/60;
$h0=intval($hm[0]);

// $date est le jour au format aaaa-mm-jj
// $h est l'heure flottante ; 08:30 donne 8.5
// $h0 est l'heure entière ;  08:30 donne 8

header("Content-Type: text/html; charset=UTF-8");
print('<html>
  <head>
    <title>..:: EDT maintenant ::..</title>
    <link rel="stylesheet" type="text/css" href="css/maintenant.css"/>
    <script type="text/javascript">
      window.titre="'.strftime("%A %e %B %Y, il est %H:%M").'"
      window.date="'.strftime("%Y-%m-%d+%H:%M").'";
      window.heure="'.$h0.'"
    </script>
    <script type="text/javascript" src="/javascript/jquery/jquery.js">
    </script>
    <script type="text/javascript" src="js/etiquettes.js">
    </script>
  </head>
  <body>
    <h1 id="titre">Titre</h1>
    <div id="tableau"> </div>
  </body>
</html>
');

?>