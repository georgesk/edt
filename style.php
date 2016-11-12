<?php
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
 *
 * Toutes ces variables doivent être déjà définies au moment ou ce fichier est
 * inclus.
 **/

function racks(){
  $result= "#rack {
    position: absolute;
    top: ".($mainTop+$toolHeight)."px;
    left: 210px;
    height: ".$nbLig*$linHeight."px;
    width: ".$nbCol*$colWidth."px;
    border: 1px grey solid;
    background: url('rack0.svg');
    background-size: ".$colWidth."px ".$linHeight."px;
    font-size: 140%;
}

";
  for ($i=0; $i<11; $i++){
    $result .= "#rack$i {
    position: absolute;
    top: ".($mainTop+$toolHeight+2*$i*$linHeight)."px;
    left: 210px;
    height: ".2*$linHeight."px;
    width: ".$nbCol*$colWidth."px;
    border: 1px grey solid;
    background: url('rack0.svg');
    background-size: ".$colWidth."px ".$linHeight."px;
    opacity: ".(0.6+0.4*($i%2))."
}
";
  }
  return $result;
}

$styleCalcule="<style type='text/css'>
". racks() . "
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

.column-title-draggable { 
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
?>
