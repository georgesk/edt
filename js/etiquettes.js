/**
 * Reprend un enregistrement d'étiquettes de la base EDT, structure ça
 * et facilite une mise en tableau dynamique
 **/

window.dureeDefaut=5; // durée, 5 heures affichées par défaut
window.setInterval(refresh, 30000); // rafraîchit toutes les 30 secondes

/**
 * Rafraîchit les étiquettes
 **/
function refresh(){
    $.get("maintenant.ajax.php?date="+window.date, function(data){
	$("#titre").html(data.date);
	dechiffre(data);
	var intervalle={
	    // l'intervalle de temps pour le tableau
	    // 0 signifie : indéfini
	    min: 0,
	    max: 0,
	}
	$("#tableau").html(table(data, intervalle));
    });
}

/**
 * fabrique un filtre qui retiendra dans les données celles
 * qui sont dans un intervalle d'heure donné
 * @param intervalle un intervalle de temps 
 * {min:0, max:0} par défaut
 **/
function filtreHeure(intervalle){
    var result=function(data){
	var min, max;
	if(! intervalle.min){
	    intervalle.min=window.heure-1;
	    if (intervalle.min < 8) {
		// rien avant 8 heures
		intervalle.min=8;
	    }
	}
	if(!intervalle.max){
	    intervalle.max=intervalle.min-1+window.dureeDefaut;
	    if (intervalle.max > 18){
		// rien après 18 heures
		intervalle.max=18;
		intervalle.min=18-window.dureeDefaut+1;
	    }
	}
	
	d={};
	d.columnlist=data.columnlist;
	d.etiquettes=[];
	for(var i=0; i < data.etiquettes.length; i++){
	    var e=data.etiquettes[i];
	    if (e.heure+e.temps >= intervalle.min && e.heure <= intervalle.max){
		d.etiquettes.push(e);
	    }
	}
	return d;
    };
    return result;
}

/**
 * Déchiffrement des données de la base
 * d'après l'offset de l'étiquette, on déduit son heure et sa salle
 * @param data les données issues de la base, qui seront enrichies
 * par effet de bord.
 **/
function dechiffre(data){
    /**
     * définition des constantes du rack
     **/
    var rack ={
	colwidth: 90,                  // largeur des colonnes en pixels
	linHeight: 50,                 // hauteur des lignes en pixels
	mainTop:   60,                 // haut de l'affichage principal
	mainLeft:  5,                  // marge gauche de l'affichage principal
	toolWidth: 200,                // largeur de la zone d'outils
	toolHeight:64,                 // hauteur de la zone d'outils
    };
    for(var i=0; i < data.etiquettes.length; i++){
	var e=data.etiquettes[i];
	// calcul de l'heure ==> un entier ou demi-entier vu qu'on
	// compte par demi-heure
	e.heure=8+Math.floor(
	    (e.offset.top-rack.mainTop-rack.toolHeight)/rack.linHeight
	)/2;
	// calcul de la colonne
	e.col=Math.floor(
	    (e.offset.left-rack.mainLeft-rack.toolWidth)/rack.colwidth
	);
	// calcul de la salle
	if (e.col < data.columnlist.length){
	    e.salle=data.columnlist[e.col];
	} else {
	    // pour les salles non nommées, si on a oublié
	    e.salle="col. "+e.col;
	}
	// calcul du temps que dure la cours
	e.temps=parseInt(e.duree)/2;
    }
}

/**
 * crée un objet jQuery de type <tr>
 * @param data un dictionnaire qui contient une liste de salles sous la clé
 * "columnlist"
 **/
function salleRow(data){
    var result=$("<tr>");
    var row=data.columnlist;
    for(var i=0; i < row.length; i++){
	// on ne doit ajouter un nom de colonne que s'il y a un enseignement
	// pour cette colonne dans les données
	var ok=false;
	for(var j=0; j < d.etiquettes.length; j++){
	    if (d.etiquettes[j].salle==row[i] &&
		d.etiquettes[j].nom!="exterieur"
	       ){
		// dès qu'on trouve une étiquette dans la colonne c'est bon
		ok=true; break;
	    }
	}
	if (ok){
	    var th=$("<th>");
	    th.html(row[i]);
	    result.append(th);
	}
    }
    return result;
}

/**
 * Crée un objet jQuery de type table qui contient des données de la
 * base de données filtrées par une fonction
 * @param data les données issues de la base
 * @param intervalle un objet {min: <int>, max: <int>}
 **/
function table(data, intervalle){
    // par défaut filter sera l'identité
    var filter=function(d){return d;};
    if (intervalle!==undefined){
	filter=filtreHeure(intervalle);
    }
    
    var d=filter(data);
    var t=$("<table>",{
	"class": "etiq",
    });
    //////////// La ligne des titres ///////////////////
    var ligne1=salleRow(d);
    var tds=ligne1.find("th");
    var titres=[];
    for(var i=0; i < tds.length; i++){
	titres[i]=$(tds[i]).html();
    }
    ligne1.prepend($("<td>"));  // une case vide au début
    t.append(ligne1);
    //////////// Les lignes de remplissage ////////////
    for (var lig=intervalle.min; lig <= intervalle.max; lig++){
	var ligne=[];
	var tr=$("<tr>");
	tr.append(
	    $("<th>").html(lig+":00")
	);
	for (var col=0; col < titres.length; col++){
	    for (var i=0; i < d.etiquettes.length; i++){
		var e=d.etiquettes[i];
		// l'étiquette peut être écrite en lig, col ?
		var ok=(e.salle == titres[col]) &&
		    (e.heure <= lig) &&
		    (e.heure+e.temps > lig) &&
		    (e.nom != "exterieur");
		if(ok){
		    ligne[col]=e;
		}
	    }
	}
	// à ce stade, le tableau ligne contient les bonnes
	// étiquettes.
	for (var col=0; col < titres.length; col++){
	    var td=$("<td>");
	    if(ligne[col]!==undefined){
		var e=ligne[col];
		td.html(e.nom+"<br/>"+e.classe);
		td.css({
		    "background": e.color,
		    "text-align": "center",
		    "font-weight": "bold",
		});
	    }
	    tr.append(td);
	}
	t.append(tr);
    }
    return t;
}

$( function(){
    // to be launched at download finished time
    refresh();
});
