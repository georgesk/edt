<?php
header("Content-Type: text/html; charset=UTF-8");
?>
<html>
<head>
<title>..:: EDT maintenant ::..</title>
<link rel="stylesheet" type="text/css" href="css/maintenant.css"/>
</head>
<body>

<?php
/**
 * affiche un tableau avec les professeurs et les classes présents
 * durant trois heures, centrés sur l'heure demandée
 * paramètres : date au format aaaa-mm-jj+hh:mm
 * si ce paramètre est absent, prend la date courante
 **/

require_once("common.inc.php");

/**
 * formatte une étiquette pour affichage dans une case de tableau
 * @param $e l'objet étiquette
 * @return du code HTML
 **/
function formateEtiquette($e){
  $result="";
  $result.="<p class='nom'>".$e->nom."</p>";
  $result.="<p class='cours'>".$e->classe."</p>";
  return $result;
}

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
$categories=array("tout" => ""); // variétés de sous-tableaux

$dt=DateTime::createFromFormat("Y-m-d" , $datetime[0]);
$jour=$dt->format("d/m/Y");
echo "<b>Nous sommes le ".$jour."</b>";;
echo "<h1>Affichage des salles pour ".$datetime[1]."</h1>\n";

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
   if ($draggables["nbcol"]>0){$nbCol =$draggables["nbcol"]; }
} else {
  die("Pas de données pour le ".$datetime[0]);
}

$salleRemplies=Array();

foreach ($etiquettes as $e){
  $heure=($e->offset->top-$mainTop-$toolHeight)/$linHeight/2+8;
  $duree=$e->duree/2;
  $col=round(($e->offset->left-$mainLeft-$toolWidth)/$colWidth);
  $salle=$columnList[$col];
  $okMoinsUn = ($h-1 >= $heure) && ($h-1 < ($heure+$duree));
  $ok = ($h >= $heure) && ($h < ($heure+$duree));
  $okPlusUn = ($h+1 >= $heure) && ($h+1 < ($heure+$duree));
  if ($okMoinsUn || $okPlusUn || $ok) $sallesRemplies[$salle]=Array();
  if($ok){
    $sallesRemplies[$salle]["zero"]=$e;
  }
  if($okMoinsUn){
    $sallesRemplies[$salle]["-1"]=$e;
  }
  if($okPlusUn){
    $sallesRemplies[$salle]["+1"]=$e;
  }
}

ksort($sallesRemplies);
// echo "<pre>"; print_r($sallesRemplies); echo "</pre>\n";

if (array_key_exists("tout",$categories)){
    $categories["tout"]=$sallesRemplies;
}

$parEtages=array(
    "autres" => [],
    "deuxième étage" => [],
    "troisième étage" => [],
);

foreach($sallesRemplies as $k => $v){
    if(substr($k, 1, 1) == "3"){
        $parEtages["troisième étage"][$k]=$v;
    } else {
        if (substr($k,1,1)=="2"){
            $parEtages["deuxième étage"][$k]=$v;
        } else {
            $parEtages["autres"][$k]=$v;
        }
    }
}

$maxCol=1+max(count($parEtages["autres"]), count($parEtages["deuxième étage"]), count($parEtages["troisième étage"]));

// !!!! GRRRR !!!! ici il y a un choix à faire !
$categories=$parEtages;

echo "<table class='edt'>\n";
// ligne de titres
foreach ($categories as $c => $sallesR){
    echo "  <tr><th colspan='$maxCol'>$c</th></tr>\n";
    echo "  <tr><th>Heure</th>";
    foreach ($sallesR as $k => $v){
        echo "<th>$k</th>";
    }
    echo "</tr>\n";
    // ligne h-1
    echo "  <tr><th text-align='right'>".($h0-1).":00</th>";
    foreach ($sallesR as $k => $v){
        $texte="&nbsp;";
        if(isset($v["-1"])){
            $texte=formateEtiquette($v["-1"]);
        }
        echo "<td>$texte</td>";
    }
    echo "</tr>\n";

    // ligne h
    echo "  <tr><th text-align='right'>".($h0).":00</th>";
    foreach ($sallesR as $k => $v){
        $texte="&nbsp;";
        if(isset($v["zero"])){
            $texte=formateEtiquette($v["zero"]);
        }
        echo "<td>$texte</td>";
    }
    echo "</tr>\n";

    // ligne h+1
    echo "  <tr><th text-align='right'>".($h0+1).":00</th>";
    foreach ($sallesR as $k => $v){
        $texte="&nbsp;";
        if(isset($v["+1"])){
            $texte=formateEtiquette($v["+1"]);
        }
        echo "<td>$texte</td>";
    }
}
echo "</tr>\n";

echo "</table>\n";
?>

</body>
</html>
