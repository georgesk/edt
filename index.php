<?php

/**
 * S'assure que l'utilisateur soit authentifié
 **/
require_once('auth.php');

/**
 * Définit des ressources communes, dont :
 * - $dbh : la poignée pour l'accès à la base de données
 * - $tslots     // Entrées temporelles pour les lignes du rack
 * - $nbLig      //nombre de lignes du rack
 * - $nbCol      // nombre de colonnes du rack
 * - $colWidth   // largeur des colonnes en pixels
 * - $linHeight  // hauteur des lignes en pixels
 * - $mainTop    // haut de l'affichage principal
 * - $mainLeft   // marge gauche de l'affichage principal
 * - $toolWidth  // largeur de la zone d'outils
 * - $toolHeight // hauteur de la zone d'outils
 * - $columnData // récupération des noms de colonnes par défaut
 * - $dateCal    // date pour le widget de calendrier
 **/
require_once('common.inc.php'); // 

$dateQuery=""; // date demandée expressément, format : yyyy-mm-jj
if (isset($_GET["date"])) $dateQuery=$_GET["date"];
if (isset($_POST["date"])) $dateQuery=$_POST["date"];
$includeJS='<script src="functions.js.php"></script>';
if ($dateQuery != ""){
    $includeJS='<script src="functions.js.php?date='.$dateQuery.'"></script>';
}
?>

<html>
<head>
<title>..:: EDT ::..</title>
<link rel="stylesheet" type="text/css" href="css/edt.css"/>
<!--
  -- éléments de style dépendant de dimensions définies dans 'common.inc.php'
  -->
<?= $styleCalcule ?>

<link rel="stylesheet" type="text/css" href="/javascript/jquery-ui/themes/base/jquery-ui.css"/>
<script src="/javascript/jquery/jquery.js"></script>
<script src="/javascript/jquery-ui/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="/javascript/jquery-timepicker/jquery-ui-timepicker-addon.css"/>
<script src="/javascript/jquery-timepicker/jquery-ui-timepicker-addon.js"></script>
<script src="js/timepicker-fr.inc.js"></script>

<script src="js/jquery.minicolors.js"></script>
<link rel="stylesheet" type="text/css" href="js/jquery.minicolors.css"/>
<?= $includeJS ?>
</head>
<body title="">
<div id="header">
  <div class="logout">
    <a href="login.php"><img src="img/logout.svg" title="Cliquer pour se déconnecter" width="32px" alt="logout"/></a>  
  </div>
  <div class="ident">
    Bonjour, <?= $user->fullname." " ?>
  </div>
  <?php 
   echo "Date des données lues : ". $loadCalendarWidget .adminLink($_SESSION);
?>
</div>
<!-- Widget combiné pour le calendrier
<?= $saveCalendarWidget ?> -->
<!-- Lien et icône pour la sauvegarde si on a la permission -->
 <?= saveLink($_SESSION, $dateCal) ?>
<!-- Lien et icône pour la création de fiches si on a la permission -->
<?= createLink($_SESSION) ?>
<!-- Lien et icône pour la suppression de fiches si on a la permission -->
<?= deleteLink($_SESSION) ?>
<!-- Mise en place des emplacements pour les titres de colonnes -->
<div id='horiz-titles'></div>
<div id="rack">
</div>
<!-- Mise en place des emplacements pour les têtes de lignes -->
<?= $divTimeslots ?>
</body>
</html>
