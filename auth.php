<?php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);

require_once("common-headers.php");

session_start();
if (! array_key_exists('uid', $_SESSION)){
  $uid='';
 } else {
  $uid=$_SESSION['uid'];
 }

$dbh = new PDO('sqlite:edt.db');
//$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// sets the date convention for strftime
date_default_timezone_set('Europe/Paris');

$sql = "SELECT * FROM users WHERE login='$uid'";
$res=$dbh->query($sql);
$user=$res->fetchObject();
if ($user){
  $_SESSION['user']=$user;
  $currentdate=strftime("%Y-%m-%d");
  if ($user->lastlogindate < $currentdate){
    $newdate = $dbh->quote($currentdate);
    $quid = $dbh->quote($uid);
    $sql="UPDATE users SET lastlogindate={$newdate} WHERE login={$quid};";
    $count=$dbh->exec($sql);
  }
 } // $user is defined, all data were found in the database
 else { // no valid user, so ask for authentication
   $host  = $_SERVER['HTTP_HOST'];
   $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
   header("Location: http://$host$uri/login.php");
 }
$res->closeCursor();

?>
