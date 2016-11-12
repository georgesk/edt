<?php


$dbh = new PDO('sqlite:edt.db');
$errMsg="";

/**
 * Fabrication de la table des entrées temporelles, têtes de lignes
 * pour le rack.
 * @return un tableau de chaînes
 **/
function arrayOfTimeSlots(){
  return Array("08:00", "08:30",
	       "09:00", "09:30",
	       "10:00", "10:30",
	       "11:00", "11:30",
	       "12:00", "12:30",
	       "13:00", "13:30",
	       "14:00", "14:30",
	       "15:00", "15:30",
	       "16:00", "16:30",
	       "17:00", "17:30",
	       "18:00", "18:30",
	       );
}

/**
 * Fabrication d'une tdraggabable pour les titres de colonnes
 * pour le rack.
 * @return la source d'un tableau pour Javascript
 **/
function arrayOfColumnData(){
  global $dbh;
  $result='[';
  $sql = "SELECT * FROM cols";
  $res=$dbh->query($sql);
  while ($rec = $res->fetch(PDO::FETCH_ASSOC)) {
    $result.='"'.$rec["ressource"].'",';
  }
  return $result.']';
}

/**
 * deux variables globales concernant les positions initiales des objets
 * tirables à la souris : tes de colonnes et étiquettes.
 **/
$columnList = "false";
$etiquettes = "false";

/**
 * Met à jour les objets à tirer à partir de la table "draggables"
 * et aussi le nombre de colonnes, et l'étiquetage des colonnes,
 * par effet de bord.
 * @param date la date recherchée; si elle est vide, elle est
 * initialisée à la date du jour
 **/
function getDraggables($date=false){
  global $dbh, $columnList, $etiquettes, $nbCol;
  if (! $date){
    date_default_timezone_set("Europe/Paris");
    $d = new DateTime();
    $date = $d->format("Y-m-d");
  }
  $sql = "SELECT * FROM draggables WHERE date=".$dbh->quote($date);
  $res=$dbh->query($sql);
  $draggables = $res->fetch(PDO::FETCH_ASSOC);
  if($draggables){
    try{
      $columnList = $draggables["ordering"];
      $ob = json_decode($columnList);
      if($ob === null) {
	throw new Exception('syntaxe de liste incorrecte');
      }
    } catch (Exception $e) {
      $errMsg=$e->getMessage();
      $columnList='["<span style=\"font-size:25%\">Erreur dans la base de données : mauvais tableau de colonnes !</span>","<span style=\"font-size:25%\">'.$errMsg.'</span>"]';
    }
    try {
      $etiquettes = $draggables["tickets"];
      $ob = json_decode($etiquettes);
      if($ob === null) {
	throw new Exception('syntaxe incorrecte dans les étiquettes');
      }
    } catch (Exception $e) {
      $errMsg=$e->getMessage();
      $etiquettes='[{nom:"'.$errMsg.'", classe:"Base de données à revoir", duree:"4", offset:{top:324, left:749}, color:"#ffaaaa"}]';
    } 

    if ($draggables["nbcol"]>0){$nbCol =$draggables["nbcol"]; }
  }
}

// sets the date convention for strftime
date_default_timezone_set('Europe/Paris');

$tslots     = arrayOfTimeSlots(); // Entrées temporelles pour les lignes du rack
$nbLig      = count($tslots);     //nombre de lignes du rack
$nbCol      = 10;                 // nombre de colonnes du rack
$colWidth   = 90;                 // largeur des colonnes en pixels
$linHeight  = 50;                 // hauteur des lignes en pixels
$mainTop    = 60;                 // haut de l'affichage principal
$mainLeft   = 5;                  // marge gauche de l'affichage principal
$toolWidth  = 200;                // largeur de la zone d'outils
$toolHeight = 64;                 // hauteur de la zone d'outils
$columnData = arrayOfColumnData();// récupération des noms de colonnes
$dateCal= strftime("%d %B %Y");   // date pour le widget de calendrier

$rackTop    = $mainTop+$toolHeight; // le haut du rack
$rackLeft   = 210;                  // la gauche du rack

/**
 * Fabrique des éléments de style dont les tailles dépendent des valeurs de
 * - $nbLig      //nombre de lignes du rack
 * - $nbCol      // nombre de colonnes du rack
 * - $colWidth   // largeur des colonnes en pixels
 * - $linHeight  // hauteur des lignes en pixels
 * - $mainTop    // haut de l'affichage principal
 * - $mainLeft   // marge gauche de l'affichage principal
 * - $toolWidth  // largeur de la zone d'outils
 * - $toolHeight // hauteur de la zone d'outils
 * - $rackTop    // haut du rack
 * - $rackLeft   // gauche du rack
 *
 * Toutes ces variables doivent être déjà définies au moment ou ce fichier est
 * inclus.
 **/

function racks(){
  global $mainTop, $toolHeight, $nbLig, $nbCol, $colWidth, $linHeight;
  $result= "#rack {
    position: absolute;
    top: ".($mainTop+$toolHeight)."px;
    left: 210px;
    height: ".$nbLig*$linHeight."px;
    width: ".$nbCol*$colWidth."px;
    #border: 1px grey solid;
    #background: url('img/rack0.png');
    #background-size: ".$colWidth."px ".$linHeight."px;
    #opacity: 0.6;
}

";
  for ($i=0; $i<11; $i++){
    $opacity="0.6";
    if($i%2) $opacity="0.3";
    $result .= "#rack$i {
    position: absolute;
    top: ".(2*$i*$linHeight)."px;
    left: 0px;
    height: ".(2*$linHeight)."px;
    width: ".((1+$nbCol)*$colWidth)."px;
    border: 1px grey solid;
    background: url('img/rack0.png');
    background-size: ".$colWidth."px ".$linHeight."px;
    opacity: $opacity;
}
";
  }
  return $result;
}


$styleCalcule = "<style type='text/css'>
". racks() ."
#horiz-titles {
  position: absolute;
  top: ".$mainTop."px;
  left: 210px;
  height: ".($toolHeight-10)."px;
  width: ".$nbCol*$colWidth."px;
  border: 1px grey solid;
  background-color: wheat;
}


#timeslots {
  position: absolute;
  top: ".($mainTop+$toolHeight)."px;
  left: 5px;
  height: ".$nbLig*$linHeight."px;
  width: 190px;
  border: 1px grey solid;
  background-color: wheat;
}

.timeslot{ 
  border: 1px solid navy;
  border-radius: 3px;
  font-family: Courier, fixed;
  font-size: 40px;
  text-align: center;
  font-weight: bold;
  background-color: rgb(250,250,250);
  height: ".($linHeight-5)."px;
  width: 160px;
}

.column-title { 
  position: absolute;
  border: 1px solid brown;
  font-size: ".($toolHeight-30)."px;
  text-align: center;
  background: rgb(250,250,250);
  width: ".($colWidth-7)."px;
  height: ".($toolHeight-25)."px;
}

.column-header { 
  float: left;
  margin: 3px;
  width:  ".($colWidth-6)."px;
  height: ".($toolHeight-23)."px;
  background: rgb(250,242,220); /* like wheat with more blue and more light */
}

</style>
";

/**
 * Fonction pour faire un lien d'administration si possible
 * @param $sessvars tableau des variables de session
 * @return un lien pour administrer
 **/
function adminLink($sessvars){
  if (strpos('admin',$sessvars["user"]->permissions) !== false) 
    return "&nbsp;<a id='adminlink' href='admin.php' title='Cliquer pour passer en mode administration'>Administrer</a>";
  else return "";
}

/**
 * Fonction pour faire un lien d'enregistrement si possible
 * @param $sessvars tableau des variables de session
 * @param $saveDate date pour la sauvegarde (format dd/mm/y)
 * @return un lien pour enregistrer les données
 **/
function saveLink($sessvars, $saveDate){
  if (strpos('admin',$sessvars["user"]->permissions) !== false ||
      strpos('write',$sessvars["user"]->permissions) !== false) {
    return '<div id="save">
    <img src="img/save.svg" style="height: 40px;" alt="Cliquez pour enregistrer le tableau" title="Cliquez pour enregistrer le tableau" onclick="save();" style="cursor: pointer;"/>
    <div id="savecalendar">
      <input type="text" name="save_calendar" id="save_calendar" Title="Date pour enregistrer les données"  value="'.$saveDate.'" class="hasDatePicker" />
    </div>
</div>
';
  } else {
    return "";
  }
}

/**
 * Fonction pour faire un lien de création si possible
 * @param $sessvars tableau des variables de session
 * @return un lien pour créer une fiche
 **/
function createLink($sessvars){
  if (strpos('admin',$sessvars["user"]->permissions) !== false ||
      strpos('write',$sessvars["user"]->permissions) !== false) {
    return '<div id="create">
    <img src="img/Kjots.svg" style="height: 40px;" alt="Auteur : Oxygen Project, David Vigoni; Licence CC-BY-SA-3.0 ; href : http://commons.wikimedia.org/wiki/File:Kjots.svg" title="Cliquer pour créer une fiche, ou déposer une fiche pour la dupliquer" onclick="create();" style="cursor: pointer;"/>
</div>
';
  } else {
    return "";
  }
}

/**
 * Fonction pour faire un lien de suppression si possible
 * @param $sessvars tableau des variables de session
 * @return un lien pour supprimer une fiche
 **/
function deleteLink($sessvars){
  if (strpos('admin',$sessvars["user"]->permissions) !== false ||
      strpos('write',$sessvars["user"]->permissions) !== false) {
    return '<div id="delete">
    <img src="img/Exquisite-trashcan_silver.png" style="height: 40px;" alt="Auteur : viciarg ᚨ, bearbeitet Sozi ; licence : GPL2+ ; href : http://commons.wikimedia.org/wiki/File:Exquisite-trashcan_silver.png" title="Déposer une fiche dans la poubelle pour la supprimer définitivement" onclick="delete();" style="cursor: pointer;"/>
</div>
';
  } else {
    return "";
  }
}

/**
 * Texte source pour obtenir le calendrier interactif
 **/
$loadCalendarWidget = '<div id="loadcalendar" style="display: inline;">
    <input type="text" name="load_calendar" id="load_calendar" Title="Date pour lire les données"  value="'.$dateCal.'" class="hasDatePicker" />
</div>
';

$saveCalendarWidget = '<div id="savecalendar">
    <input type="text" name="save_calendar" id="save_calendar" Title="Date pour enregistrer les données"  value="'.$dateCal.'" class="hasDatePicker" />
</div>
';


/**
 * Texte source pour mettre en place les têtes de lignes
 **/
$divTimeslots = '<div id="timeslots">';
$top=0;
foreach ($tslots as $ts){
  $divTimeslots .= "<div class=timeslot style='position: absolute; top: ".$top."px; left: 13px;'>$ts<img src='img/voir.svg' width='20px' alt='image pour un œil, source commons.wikimedia.org, File Eye_open_font_awesome.svg' title='voir « maintenant »' onclick='maintenant(\"$ts\")' class='link' /></div>";
  $top += $linHeight;
}
$divTimeslots .= '</div>';

/**
 * Texte source pour faire le choix de nom dans le dialogue de
 * création d'étiquettes
 **/
$nomOptions="";
$sql = "SELECT * FROM noms ORDER BY nom ASC";
$res=$dbh->query($sql);
while ($rec = $res->fetch(PDO::FETCH_ASSOC)) {
  $nom=$rec["nom"];
  $nomOptions .= "      <option value=\"$nom\">$nom</option>\\\n";
}

/**
 * Texte source pour faire le choix de classe dans le dialogue de
 * création d'étiquettes
 **/
$classeOptions="";
$sql = "SELECT * FROM classes ORDER BY classe ASC";
$res=$dbh->query($sql);
while ($rec = $res->fetch(PDO::FETCH_ASSOC)) {
  $classe=$rec["classe"];
  $classeOptions .= "      <option value=\"$classe\">$classe</option>\\\n";
}

?>
