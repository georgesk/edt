<?php
header("Content-type: text/javascript");

$dateQuery=""; // date demandée expressément, format : yyyy-mm-jj
if (isset($_GET["date"])) $dateQuery=$_GET["date"]; else $dateQuery="";

require_once("common.inc.php");


?>

/**
 * Diverses fonctions Javascript pour le logiciel EDT-LABO
 **/

/**
 * détermine la colonne du rack ou se trouve un élément jQuery
 * @param o un objet jQuery représentant l'objet dont on cherche la colonne
 * @param nbcol nombre courant de colonnes
 * @return le numéro (entier) de colonne ou -1 si c'est hors du rack
 **/
function obj2col(o, nbcol){
  var left = o.offset().left;
  var col=Math.floor((left-<?= $mainLeft+$toolWidth ?>)/<?= $colWidth ?>);
  if (col < nbcol && col >=0 ){
    return col;
  }
  return -1; // par défaut
}

/**
 * Fabrique une représentation des étiquettes présentes dans la page
 * @return un tableau d'objets structurés
 **/
function tickets(){
  var result=[];
  var etq = $(".etiquette");
  for (var i=0; i < etq.length; i++){
    result.push(objEtiquette(etq[i]))
  }
  return result;
}

/**
 * Fabrique une représentation objet d'une étiquette
 * @param e un pointeur vers une étiquette
 * @retun un objet contenant ses données importantes
 **/
function objEtiquette(e){
  var result=new Object();
  var jQobj = $(e);
  var data=jQobj.find("p");
  result.nom=$(data[0]).text();
  result.classe=$(data[1]).text();
  result.duree=$(data[2]).text();
  result.offset=jQobj.offset();
  result.color=toHexColor(jQobj.css('backgroundColor'));
  return result;
}

/**
 * Fonction pour enregistrer les données du rack courant
 * 
 * Les données du rack courant sont associées à la date courante
 * dans la base de données. Ces données sont encodées dans l'objet 'data'
 * qui est transmis à la page 'ajax.php'
 **/
function save() {
  var data = new Object();
  data.date = $("#save_calendar").datepicker("getDate");
  data.colPos=[];
  data.nbcol = $(".column-header").length;
  var columTitles = $('[id^=column_]');
  for (i = 0; i< columTitles.length; i++){
    var c = $(columTitles[i]);
    var o = new Object();
    o.col = c.text();
    o.place = obj2col(c, data.nbcol);
    data.colPos.push(o);
    data.tickets=JSON.stringify(tickets());
  }
  $.post("ajax.php", data, saveOK);
}

/**
 * Fonction de rappel invoquée quand la sauvegarde est réussie
 * ou plus exactement quand le serveur signale que la page 'ajax.php'
 * a pu être traitée.
 **/
function saveOK(data){
  // il vaudrait mieux récupérer la date en feedback depuis le XHR
  var box=$("<div>");
  var d = $("#save_calendar").val();
  var contents="\
    <p>\
    <span class=\"ui-icon ui-icon-circle-check\" style=\"float: left; margin: 0 7px 50px 0;\"></span>\
    Les données de la page ont bien été enregistrées.\
    </p>\
    <p>\
    Date valide pour l&apos;enregistrement : <span id=\"date-save\">"+d+"</span>.\
    </p>\
    <p>Nombre d&apos;enregistrements : "+data+"\
    </p>";
  box.html(contents);
  box.dialog({
    modal: true,
    title: "Données enregistrées",
    buttons: {
      OK: function() {
	$( this ).dialog( "close" );
      }
    }
  });
}

/**
 * Injecte une couleur dans le contrôle minicolors attaché à #roue
 * la couleur est prise d'un objet dont on a la référence
 * @param jqObj un objet jQuery dont le style a un attribut background
 */
function setMinicolorsFromButton(jqObj){
  var color = jqObj.attr("style");
  var bgPattern = /.*background: ([^;]*).*/;
  var match=color.match(bgPattern);
  var color1=match[1];
  $("#roue").val(color1);
  $("#roue").minicolors('settings', {defaultValue: color1});
}

/**
 * Fonction pour créer une nouvelle étiquette
 * 
 * Un dialogue s'ouvre et on y renseigne des valeurs
 * @param orig un objet avec des champs nom, classe, color
 **/
function create(orig=false) {
  var box=$("<div>");
  var contents='                            \
<form>\
  <fieldset>\
    <legend>Données de la fiche</legend>\
    <table id="etiquetteInput"><tr><td class="right">Choix de couleur de fond :</td><td>\
    <input type="button" style="width: 1em; background: #aaaaaa;" onclick="setMinicolorsFromButton($(this))">\
    <input type="button" style="width: 1em; background: #ffaaaa;" onclick="setMinicolorsFromButton($(this))">\
    <input type="button" style="width: 1em; background: #ffffaa;" onclick="setMinicolorsFromButton($(this))">\
    <input type="button" style="width: 1em; background: #aaffaa;" onclick="setMinicolorsFromButton($(this))">\
    <input type="button" style="width: 1em; background: #aaffff;" onclick="setMinicolorsFromButton($(this))">\
    <input type="button" style="width: 1em; background: #aaaaff;" onclick="setMinicolorsFromButton($(this))">\
    <input type="button" style="width: 1em; background: #ffaaff;" onclick="setMinicolorsFromButton($(this))">\
    <input type="button" style="width: 1em; background: #e8e8e8;" onclick="setMinicolorsFromButton($(this))">\
    <input type="text" id="roue" value="#ff99ee"></td></tr>\
    <tr><td class="right">\
    Durée :</td><td>\
    <select id="duree" style="margin: 20px 0px">\
      <option value="1">1/2 heure</option>\
      <option value="2">1 heure</option>\
      <option value="3">1 heure 1/2</option>\
      <option value="4">2 heures</option>\
      <option value="5">2 heures 1/2</option>\
      <option value="6">3 heures</option>\
      <option value="7">3 heures 1/2</option>\
      <option value="8">4 heures</option>\
    </select></td></tr>\
    <tr><td class="right">\
    Professeur :</td><td>\
    <select id="nom" style="margin: 20px 0px">\
<?= $nomOptions ?>    </select></td></tr>\
    <tr><td class="right">\
    Classe :</td><td>\
    <select id="classe" style="margin: 20px 0px">\
<?= $classeOptions ?>    </select></td></tr>\
    </table>\
  </fieldset> \
</form>\
';
  box.html(contents);
  box.appendTo("body");
  $(box).find("#roue").minicolors({
     control: 'wheel',
     theme: 'default'
  });

  var title = 'Création d\'étiquette';

  if (orig){
    title = 'Copie d\'une étiquette';
    $(box).find("#roue").val(orig.color);
    $(box).find("#roue").minicolors('settings', {defaultValue: orig.color});
    $(box).find("#nom option[value='"+orig.nom+"']").attr("selected", "selected");
    $(box).find("#classe option[value='"+orig.classe+"']").attr("selected", "selected");
    $(box).find("#duree option[value='"+orig.duree+"']").attr("selected", "selected");
  }

  box.dialog({
    modal: true,
    width: '640px',
    title: title,
    buttons: {
      'Échappement': function() {
         $( this ).dialog( 'close' );
      },
      'Créer l\'étiquette': function() {
         var color = $(box).find('#roue').val();
	 var nom = $(box).find('#nom').val();
	 var duree = $(box).find('#duree').val();
	 var classe = $(box).find('#classe').val();
	 creeEtiquette(color, duree, nom, classe);
	 $( this ).dialog( 'close' );
       },
    }
  });

}

var etiqNum=0;

/**
 * Fonction de rappel invoquée à la fin du dialogue de création de fiche
 * @param couleur une couleur de fond pour l'étiquette
 * param duree durée correspondant à l'étiquette
 * @param nom un nom à marquer
 * param classe classe correspondant à l'étiquette
 * @return un pointeur vers l'étiquette ; le champ props de l'étiquette
 *   contient une liste de propriétés utiles
 */
function creeEtiquette(couleur, duree, nom, classe){
  var id = "etiq"+etiqNum;
  var ticket=$("<div>",{class: "etiquette bottom", id: id,});
  ticket.html("<p class=\"nom'\">"+nom+"</p><p class=\"cours\">"+classe+"</p><p style='display: none;'>"+duree+"</p>");
  ticket.css({
        position: "absolute",
        top: "200px",
        left: "50px",
        width: (<?= $colWidth ?>)+"px",
        height: (<?= $linHeight ?>*parseInt(duree))+"px",
        border: "1 px solid navy",
        background: couleur
    });
  var title=nom+" : "+classe+" : "+duree/2+"h";
  ticket.attr("title", title);
  ticket.appendTo("body");
  ticket.draggable({ 
        addClasses: false,
        stack: ".etiquette",
        opacity: 0.8,
        stop: creeStopEtiquette(id),
        });
  etiqNum +=1;
  return ticket;
}

/**
 * fonction pour transformer un indice de début en une notation horaire
 * @param debut nombre de demi-heures depuis 8:00
 * @result une chaîne indiquant une heure
 **/
function heureDebut(debut){
  var h = Math.floor(debut/2+8)+"";
  if (h.length < 2) h="0"+h;
  var m = "00";
  if (debut % 2 ==1) m="30";
  return h+":"+m;
}

/**
 * fonction adaptant un titre à une étiquette, en tenant compte 
 * d'une salle et d'une heure de début
 * @param salle une salle
 * @param debut heure de début
 **/
function titleEtiquette(ticket, salle, debut){
  var paragraphs=$(ticket).find("p");
  var nom=paragraphs[0].innerHTML;
  var classe=paragraphs[1].innerHTML;
  var duree=parseInt(paragraphs[2].innerHTML);
  var infos=new Array();
  infos[0]=nom;
  infos[1]=classe;
  infos[2]=duree/2+"h";
  infos[3]=salle;
  infos[4]=debut;
  setTimeout(function(){
      $("body").tooltip("destroy");
      var newTitle=infos.join(" : ");
      ticket.attr("title",newTitle);
      $("body").tooltip();
    },500);
}

/**
 * Fonction de rappel invoquée au clic sur une image d'étiquette
 * renvoie ver la page maintenant.php avec la bonne heure
 * @param timeslot une chaîne dont le format est "hh:mm"
 **/
function maintenant(timeslot){
  var d = $("#save_calendar").val();
  d=d.split("/");
  var date="20"+d[2]+"-"+d[1]+"-"+d[0]+"+"+timeslot;
  var url="maintenant.php?date="+date;
  window.open(url);
}

/**
 * détermniation d'un nom de salle d'après un index
 * @param x in index de colonne
 * @return un nom de salle si possible
 **/
function xToSalle(x){
  var salleTitres=$(".column-title");
  for (var i=0; i < salleTitres.length; i++){
    var obj=$(salleTitres[i]);
    if (obj2col(obj,100)==x) {
      var salle= obj.text();
      return salle;
    }
  }
  return "Salle ??";
}

/**
 * Fonction fabriquant une fonction de rappel invoquée quand une étiquette est lâchée
 **/
function creeStopEtiquette(id){
  return function(event, ui){
    var ticket=$("#"+id);
    var nblig = <?= $nbLig ?>;
    var nbcol = $(".column-header").length;

    if (ticket.draggable( "option", "revert")){
      /* l'étiquette revient de quelques part, on laisse faire */
      ticket.draggable( "option", "revert", false );
    } else {
      /**** Rend le rack "magnétique" **********/
      var t=Math.floor((ui.position.top-<?= $rackTop ?>) / <?= $linHeight ?>);
      var l=Math.floor((ui.position.left-<?= $rackLeft ?>) / <?= $colWidth ?>);
      if (t<0) t=0;
      if (l<0) l=0;
      if (t>= nblig) t = nblig-1;
      if (l>= nbcol) l= nbcol-1;
      var top=<?= $rackTop ?>+t*<?= $linHeight ?>;
      var left=<?= $rackLeft ?>+l*<?= $colWidth ?>;
      titleEtiquette(ticket,xToSalle(l),heureDebut(t));
      ticket.css({top: ""+top+"px", left: ""+left+"px"});
    }
  }
}

/**
 * Fonction de rappel pour le contrôle de création d'étiquette en cas
 * d'étiquette lâchée
 **/
function dropEtiqOnCreate(event,ui){
  var color = ui.draggable.css('backgroundColor');
  color=toHexColor(color);
  var data=ui.draggable.find("p");
  var nom = $(data[0]).text();
  var classe=$(data[1]).text();
  var duree=$(data[2]).text();
  create({color: color, nom: nom, classe: classe, duree: duree}); 
  ui.draggable.draggable( "option", "revert", true );
}

/**
 * assure que la couleur est en format hexa
 * @param c une couleur
 * @return la couleur en code hexadécimal
 **/
function toHexColor(c){
  var parts = c.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
  if(parts){
    delete(parts[0]);
    for (var i = 1; i <= 3; ++i) {
      parts[i] = parseInt(parts[i]).toString(16);
      if (parts[i].length == 1) parts[i] = '0' + parts[i];
    }
    return '#' + parts.join('');
  } else return c;

}

/**
 * Placement d'une tête de colonne
 * @param title son titre
 * @param place sa place (qui peut être au-delà de la barre de titres)
 * param headers une collection de widgets pour l'accrochage
 * @param nbcol nombre de colonne dans la bandeau
 **/
function place1col(title, place, headers, nbcol){
  if (title.length==0) return;
  var div1=$('<div>', {id: 'column_'+place});
  div1.html('<p style="vertical-align: middle;">'+title+'</p>');
  div1.appendTo("body");
  div1.addClass('column-title ui-widget-content bottom');
  div1.draggable({snap: '.column-header', snapmode: 'inner', snapTolerance: '20', stack: '.column-title' });
  if (place < nbcol){ /* on est dans le bandeau de titres de colonnes */
    div1.position({of: headers[place]});
  } else {
    var top=<?= $mainTop+0.5*$toolHeight ?>+20*(place-nbcol);
    var left=<?= $mainLeft+$toolWidth ?>+<?= $colWidth ?>*nbcol+50+5*(place-nbcol);
    div1.offset({top: top, left: left});
  }
}

/**
 * fonction de rappel pour l'ajout d'une colonnes au rack
 **/
function plusColonne(){
  var nbcol = $(".column-header").length;
  nbcol +=1;
  <?php
  /* récupération des objets tirables du jour demandé     */
  /* définit les variables PHP $columnList et $etiquettes */
  getDraggables($dateQuery);
  ?>
  $("#plus").remove();
  $("#moins").remove();
  dessineRack(nbcol, <?= $columnList ?>, <?= $columnData ?>, <?= $etiquettes ?>);  
}

/**
 * fonction de rappel pour la suppression d'une colonnes du rack
 **/
function moinsColonne(){
  var nbcol = $(".column-header").length;
  if (nbcol > 2) nbcol -=1;
  <?php
  /* récupération des objets tirables du jour demandé     */
  /* définit les variables PHP $columnList et $etiquettes */
  getDraggables($dateQuery);
  ?>
  $("#plus").remove();
  $("#moins").remove();
  dessineRack(nbcol, <?= $columnList ?>, <?= $columnData ?>, <?= $etiquettes ?>);  
}

/**
 * Mise place des colonnes, à des positions enregistrées au préalable
 * dans la table draggables.ordering, sinon à des places par défaut
 * issues de la table table pos.resource
 * @param columnList une liste de noms de colonnes, ou un objet faux
 * @param columnDefault une liste par défaut de colonnes
 * @param nbcol nombre de colonne dans la bandeau
 **/
function placeColonnes(columnList, columnDefault, nbcol){
  var columnHeaders=$(".column-header");
  var i=0;  /* un compteur pour la colonne courante */
  var div1; /* un objet qui sert à faire les titres de colonnes */
  if(columnList){
    /* placement des colonnes dont la position est connue */
    $.each(columnList, function(index, val){
        place1col(val, i, columnHeaders, nbcol);
        /* on retire le même titre de la liste des colonnes par défaut */
        var found = columnDefault.indexOf(val);
        if (found > -1) {
          columnDefault.splice(found, 1);
        }
        i=i+1;
      });
    if (i<nbcol) i=nbcol;
  }
  /* placement des colonnes restant à placer, issues de la liste par défaut */
  $.each(columnDefault, function(index, val){
      place1col(val, i, columnHeaders, nbcol);
      i=i+1;
    });
  /* placement des boutons de contrôle du nombre de colonnes */
  var plus = $("<img>",{src: "img/plus.svg",
        id: "plus",
        alt: "plus",
        title: "ajouter une colonne",
        onclick: "plusColonne()"});
  plus.css({position: "absolute", 
        top: "<?= $mainTop+$toolHeight ?>px",
        left: ""+(<?= $mainLeft+$toolWidth ?>+<?= $colWidth ?>*nbcol+5)+"px",
        width: "20px"});
  $("body").append(plus);
  var moins = $("<img>",{src: "img/moins.svg",
        id: "moins",
        alt: "moins",
        title: "supprimer une colonne",
        onclick: "moinsColonne()"});
  moins.css({position: "absolute", 
        top: "<?= 25+$mainTop+$toolHeight ?>px",
        left: ""+(<?= $mainLeft+$toolWidth ?>+<?= $colWidth ?>*nbcol+5)+"px",
        width: "20px"});
  $("body").append(moins);
}

/**
 * Fonction de mise en place d'une liste d'étiquettes
 * @param e une liste d'objets contenant les données des étiquettes
 */
function placeEtiquettes(e){
  $.each(e, function(index, val){
      var t = creeEtiquette(val.color, val.duree, val.nom, val.classe);
      t.css({
            top: val.offset.top,
            left: val.offset.left,
        });
    });
}

/**
 * Fonction pour dessiner le rack et sa ligne de titres
 * @param nbcol le nombre colonnes courant
 * @param columnlist une liste de colonnes placées (ou false)
 * @param columndata liste de colonne existantes plaçables
 * @param etiquettes liste d'étiquettes à placer
 */
function dessineRack(nbcol, columnlist, columndata, etiquettes){
  var rack=$("#rack");
  rack.css("width",""+(nbcol*<?= $colWidth ?>)+"px")
  var horizTitles=$("#horiz-titles");
  horizTitles.css("width",""+(nbcol*<?= $colWidth ?>)+"px");
  $(".column-header").remove();
  for (var i=0; i < nbcol; i++){
    var d = $("<div>"); d.addClass("column-header");
    $(horizTitles).append($(d));
  }
  $('[id^=column_]').remove();
  placeColonnes(columnlist, columndata, nbcol);
  $(".etiquette").remove();
  placeEtiquettes(etiquettes);
}

/**
 * Fonction de rappel de jQuery qui est invoquée quand la page web est chargée
 **/
$(function() {
  $( "body" ).tooltip();
  <?php
  /* récupération des objets tirables du jour demandé     */
  /* définit les variables PHP $columnList et $etiquettes */
  getDraggables($dateQuery);
  ?>
  dessineRack(<?= $nbCol ?>, <?= $columnList ?>, <?= $columnData ?>, <?= $etiquettes ?>);
  /**
   * mise en place des sous-racks
   **/
  for (var i=0; i<11; i++){
    var newrack=$("<div>",{id: "rack"+i});
    $("#rack"). append(newrack);
  }

  /**
   * mise en place des widgets calendriers
   **/
  $("#load_calendar").datepicker({
        dateFormat: "dd MM yy",
        autoSize: true,
        showButtonPanel: true,
        } );
  
  $("#save_calendar").datepicker({
        dateFormat: "dd/mm/y",
        autoSize: true,
        showButtonPanel: true,
        } );

  /**
   * Réglage de la date par défaut
   **/
  $("#load_calendar").datepicker('setDate', new Date('<?= $dateQuery ?>'));
  $("#load_calendar").change(function(){
      var d = ('0' + $(this).datepicker('getDate').getDate()).slice(-2);
      var m = ('0' + ($(this).datepicker('getDate').getMonth() + 1)).slice(-2);
      var y = $(this).datepicker('getDate').getFullYear();
      document.location.href="index.php?date="+y+"-"+m+"-"+d;
    });

  $("#save_calendar").datepicker('setDate', new Date('<?= $dateQuery ?>'));
  $("#save_calendar").change(function(){
      alert("Pour le prochain enregistrement,\nla date du tableau sera le "+ $(this).val());
      var pageTitle="..:: EDT -> "+$(this).val()+" ::..";
      $(document).attr('title', pageTitle);
    });

  /**
   * titre pour la page
   **/
  var pageTitle = "..:: EDT -> "+$("#save_calendar").val()+" ::..";
  $(document).attr('title', pageTitle);

  /**
   * rend droppable certains widgets
   **/
  $("#delete").droppable({
        accept: ".etiquette", 
        tolerance: "pointer", 
        drop: function( event, ui ) {ui.draggable.remove();},
        });
  $("#create").droppable({
        accept: ".etiquette", 
        tolerance: "pointer", 
        drop: dropEtiqOnCreate,
        });
  }
);

// Local Variables:
// js-indent-level: 2
// indent-tabs-mode: nil
// End: