<?php

require_once('auth.php');
$user=$_SESSION["user"];
$pos = strpos("admin", $user->permissions);
if ($pos === false){
   $host  = $_SERVER['HTTP_HOST'];
   $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
   header("Location: http://$host$uri/index.php");
}

$message="";
$theDate=strftime("%Y-%m-%d");


/**
 * fonction générique de suppression : supprime un élément dans une table
 * @param $table    nom d'une table dans la BDD
 * @param $data  un tableau associatif colonne => nomVar désignant des valeurs
 *               à placer dans des colonnes. La première colonne citée est
 *               considérée comme une clé unique (pas de duplication).
 *               nomVar est le nom d'une variable postée. N.B.: seul le
 *               premier élément du tableau associatif est considéré.
 **/
function delete_action($table, $data){
  global $message;
  $dbh = new PDO('sqlite:edt.db');
  $column="";
  $postName="";
  foreach($data as $k=>$v){
    $column=$k;
    $postName=$v;
    break;
  }
  $cle=$_POST[$postName];
  $sql="DELETE FROM ".$table." WHERE ".$column."=".$dbh->quote($cle);
  $count=$dbh->exec($sql);
  $message="Supprimé $count enregistrement(s) : $table.$column pour $cle";
}

/**
 * Fonction générique pour créer un enregistrement
 * @param $table nom d'une table de la base de données
 * @param $data  un tableau associatif colonne => nomVar désignant des valeurs
 *               à placer dans des colonnes. La première colonne citée est
 *               considérée comme une clé unique (pas de duplication).
 *               nomVar est le nom d'une variable postée.
 **/
function create_action($table, $data){
  global $message;
  $valeurs=[];
  $colonnes=[];
  foreach($data as $cle=>$nomVar){
    $colonnes[]=$cle;
    if (is_array($_POST[$nomVar])){
      $valeurs[]=implode(";",$_POST[$nomVar]);
    } else {
      $val = $_POST[$nomVar];
      if ($cle == "passwd"){
	$valeurs[]=md5($val);
      } else {
	$valeurs[]=$val;
      }      
    }
  }
  $dbh = new PDO('sqlite:edt.db');
  $sql = "SELECT * FROM ".$table." WHERE ".$colonnes[0]."=".$dbh->quote($valeurs[0]);
  $res=$dbh->query($sql);
  $found=$res->fetchObject();
  $res->closeCursor();
  if ($found){
    $message="Enregistrement non créé. La clé ".$valeurs[0]." existe déjà dans $table.".$colonnes[0];
    return;
  }
  $ok=true;
  foreach ($valeurs as $v) $ok = $ok && $v;
  if (! $ok){
    $message="Enregistrement non créé. Une des valeurs postées est vide";
    return;
  }
  $sql="INSERT INTO $table (";
  $cols=[];
  foreach ($colonnes as $c) $cols[]=$c;
  $sql.=implode(",",$cols).") VALUES ( ";
  $vals=[];
  foreach($valeurs as $v) $vals[]=$dbh->quote($v);
  $sql.=implode(",",$vals).")";
  $count=$dbh->exec($sql);
  $message="Créé $count enregistrement(s), pour la clé ".$valeurs[0]." dans $table.".$colonnes[0];
}

/*********************************************************************
 * Les éléments du tableau $actions ont la structure suivante :
 * 0- un type d'action : "create" ou "delete"
 * 1- le nom de la table de BDD sur laquelle on doit agir
 * 2- un tableau association de paire colonne => nom de variable
 *    la colonne est relative à la table, le nom de variable désigne
 *    ce qui est reçu dans une requête POSTée.
 *********************************************************************
 * Pour ajouter une nouvelle fonctionnalité, il faut du code HTML
 * pour faire un formulaire dans le corps de la page HTML, puis on
 * ajoute une ligne par formulaire dans le tableau $actions.
 *********************************************************************
 **/
$actions=array(
  "createUser"     => array("create","users",  array("login"=>"login",
						     "passwd"=>"passwd",
						     "fullname"=>"fullname",
						     "permissions"=>"permissions",
						     "lastlogindate"=>"lastlogindate",
						     )),
  "deleteUser"     => array("delete","users",  array("login"=>"login",)),
  "createNom"      => array("create","noms",   array("nom"=>"name")),
  "deleteNom"      => array("delete","noms",   array("nom"=>"name")),
  "createResource" => array("create","cols",   array("ressource"=>"name")),
  "deleteResource" => array("delete","cols",   array("ressource"=>"name")),
  "createClasse"   => array("create","classes",array("classe"=>"name")),
  "deleteClasse"   => array("delete","classes",array("classe"=>"name")),
);

foreach ($actions as $key => $profil){
  if (isset($_POST[$key])){
    if ($profil[0]=="create"){
      create_action($profil[1],$profil[2]);
    }
    if ($profil[0]=="delete"){
      delete_action($profil[1],$profil[2]);
    }
    break;
  }
}

//=================== objets à afficher dans la page ========================

//================= sélection d'un login d'utilisateur =========================
$sql="SELECT login FROM users ORDER BY login ASC";
$res=$dbh->query($sql);
$loginOptions="";
while ($rec = $res->fetch(PDO::FETCH_ASSOC)) {
    $loginOptions.='<option value="'.$rec["login"].'">'.$rec["login"].'</option>\n';
}

//================= sélection d'un nom de prof ou de colleur =================
$sql="SELECT nom FROM noms ORDER BY nom ASC";
$res=$dbh->query($sql);
$nameOptions="";
while ($rec = $res->fetch(PDO::FETCH_ASSOC)) {
    $nameOptions.='<option value="'.$rec["nom"].'">'.$rec["nom"].'</option>\n';
}

//================== sélection d'une salle ==================
$sql="SELECT ressource FROM cols ORDER BY ressource ASC";
$res=$dbh->query($sql);
$salleOptions="";
while ($rec = $res->fetch(PDO::FETCH_ASSOC)) {
    $salleOptions.='<option value="'.$rec["ressource"].'">'.$rec["ressource"].'</option>\n';
}

//================== sélection d'un nom de classe ==============
$sql="SELECT classe FROM classes ORDER BY classe ASC";
$res=$dbh->query($sql);
$classeOptions="";
while ($rec = $res->fetch(PDO::FETCH_ASSOC)) {
    $classeOptions.='<option value="'.$rec["classe"].'">'.$rec["classe"].'</option>\n';
}

?>
<html>
<head>
<title>..:: EDT Administration ::..</title>
<link rel="stylesheet" type="text/css" href="css/edt.css"/>
<script src="/javascript/jquery/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="/javascript/jquery-ui/themes/base/jquery-ui.css"/>
<script src="/javascript/jquery-ui/jquery-ui.js"></script>
<script type="text/javascript">
$(window).load(function(){
$('.repliable').hide();
 
$('.inverseur').each(function() {
	$(this).append(' <span class="inter">&nbsp;+&nbsp;</span>');
	$(this).wrapInner('<a href="#"></a>');
  });
 
$('.inverseur a').click(function() {
	$(this).parent().next('.repliable').toggle('slow');
	var sp=$(this).parent().find("span");
	if (sp.html().indexOf("+") !== -1){
	  sp.html("&nbsp;-&nbsp;")
	} else {
	  sp.html("&nbsp;+&nbsp;")
	}
	$(this).toggleClass('unfolded');
	return false;
  });
if ('<?= $message ?>'){
  var box=$("<div>");
  box.appendTo($("body"));
  box.html('<?= $message ?>');
  box.dialog({
    modal: true,
    title: "Résultat de l'action",
    buttons: {
      OK: function() {
	$( this ).dialog( "close" );
      }
    }
  });
}
});
</script>
</head>
<body>
  <h1 class="inverseur">Créer ...</h1>
  <div class="repliable conteneur">
    <div class="admin-action">
      <h2 class="inverseur">Créer un utilisateur</h2>
      <form class="action-parms repliable" method="post">
	<fieldset>
	  <legend>Données concernant l&apos;utilisateur</legend>
	  <table>
	    <tr><td>Login : </td><td><input type="text" name="login"/></td></tr>
	    <tr><td>Passe : </td><td><input type="password" name="passwd"/></td></tr>
	    <tr><td>Nom complet : </td><td><input type="text" name="fullname"/></td></tr>
	  </table>
	</fieldset>
	<fieldset>
	  <legend>Permissions</legend>
	  <input type="checkbox" name="permissions[]" value="read" checked="1"/> Lire<br/>
	  <input type="checkbox" name="permissions[]" value="write"/> Écrire<br/>
	  <input type="checkbox" name="permissions[]" value="admin"/> Administrer<br/>
	</fieldset>
	<input type="hidden" name="lastlogindate" value="<?= $theDate ?>">
	<input class="submit" type="submit" value="Valider" name="createUser">
	<div style="clear:both;"></div>
      </form>
    </div>
    
    <div class="admin-action">
      <h2 class="inverseur">Créer un professeur ou un colleur</h2>
      <form class="action-parms repliable" method="post">
	<fieldset>
	  <legend>Données concernant le professeur</legend>
	  <table>
	    <tr><td>Nom : </td><td><input type="text" name="name"/></td></tr>
	  </table>
	</fieldset>
	<input class="submit" type="submit" value="Valider" name="createNom">
	<div style="clear:both;"></div>
      </form>
    </div>

    <div class="admin-action">
      <h2 class="inverseur">Créer une salle ou une ressource</h2>
      <form class="action-parms repliable" method="post">
	<fieldset>
	  <legend>Données concernant la ressource</legend>
	  <table>
	    <tr><td>Nom : </td><td><input type="text" name="name"/></td></tr>
	  </table>
	</fieldset>
	<input class="submit" type="submit" value="Valider" name="createResource">
	<div style="clear:both;"></div>
      </form>
    </div>

    <div class="admin-action">
      <h2 class="inverseur">Créer une classe ou une activité</h2>
      <form class="action-parms repliable" method="post">
	<fieldset>
	  <legend>Données concernant la classe</legend>
	  <table>
	    <tr><td>Nom : </td><td><input type="text" name="name"/></td></tr>
	  </table>
	</fieldset>
	<input class="submit" type="submit" value="Valider" name="createClasse">
	<div style="clear:both;"></div>
      </form>
    </div>

  </div>
  
  <h1 class="inverseur">Supprimer ...</h1>
  <div class="repliable conteneur">
    <div class="admin-action">
      <h2 class="inverseur">Supprimer un utilisateur</h2>
      <form class="action-parms repliable" method="post">
	<fieldset>
	  <legend>Données concernant l&apos;utilisateur</legend>
	  <table>
	    <tr><td>Login : </td><td>
		<select name="login">
          <?= $loginOptions ?>
		</select>
	    </td></tr>
	  </table>
	</fieldset>
	<input class="submit" type="submit" value="Valider" name="deleteUser">
	<div style="clear:both;"></div>
      </form>
    </div>
    
    <div class="admin-action">
      <h2 class="inverseur">Supprimer un professeur ou un colleur</h2>
      <form class="action-parms repliable" method="post">
	<fieldset>
	  <legend>Données concernant le professeur</legend>
	  <table>
	    <tr><td>Nom : </td><td>
		<select name="name">
          <?= $nameOptions ?>
		</select>
	    </td></tr>
	  </table>
	</fieldset>
	<input class="submit" type="submit" value="Valider" name="deleteNom">
	<div style="clear:both;"></div>
      </form>
    </div>

    <div class="admin-action">
      <h2 class="inverseur">Supprimer une salle ou une ressource</h2>
      <form class="action-parms repliable" method="post">
	<fieldset>
	  <legend>Données concernant la ressource</legend>
	  <table>
	    <tr><td>Nom : </td><td>
		<select name="name">
          <?= $salleOptions ?>
		</select>
	    </td></tr>
	  </table>
	</fieldset>
	<input class="submit" type="submit" value="Valider" name="deleteResource">
	<div style="clear:both;"></div>
      </form>
    </div>

    <div class="admin-action">
      <h2 class="inverseur">Supprimer une classe ou une activité</h2>
      <form class="action-parms repliable" method="post">
	<fieldset>
	  <legend>Données concernant la classe</legend>
	  <table>
	    <tr><td>Nom : </td><td>
		<select name="name">
          <?= $classeOptions ?>
		</select>
	    </td></tr>
	  </table>
	</fieldset>
	<input class="submit" type="submit" value="Valider" name="deleteClasse">
	<div style="clear:both;"></div>
      </form>
    </div>

  </div>  
</body>
</html>
