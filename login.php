<?php

$oklogin=false;
session_start();
$_SESSION["uid"]="";

if (isset($_POST["uid"]) && isset($_POST["pwd"])){
  $_POST["uid"]=strtolower($_POST["uid"]);
  $dbh = new PDO('sqlite:edt.db');
  $sql = "SELECT * FROM users WHERE login='".$_POST["uid"]."'";
  $res=$dbh->query($sql);
  $user=$res->fetchObject();
  if ($user){
    if ($_POST["pwd"] == $user->passwd || md5($_POST["pwd"]) == $user->passwd){
      $oklogin=true;
    }
  }
  $res->closeCursor();
  if ($oklogin){
   $host  = $_SERVER['HTTP_HOST'];
   $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
   $_SESSION['uid']=$_POST["uid"];
   header("Location: http://$host$uri/index.php");
  }
} 
?>
<html>
<head>
<title>Login</title>
</head>
<body>
<form action="#" method="post">
<fieldset>
<legend>Votre identifiant</legend>
Login : <input type="text" name="uid"></input>
</fieldset>
<fieldset>
<legend>Mot de passe</legend>
Passe : <input type="password" name="pwd"></input>
</fieldset>
<input type='submit' value='Valider'/>
</form>
</body>
</html>
