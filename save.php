<?php
// sauvegarde de la base de données
require_once('auth.php');

$dbh = new PDO('sqlite:edt.db');

// sqlite DB specific; emalates SHOW TABLES and SHOW TABLE CREATE
$q = "SELECT * from sqlite_master";
$res = $dbh->query($q);
$data = "
-- PDO/SQLITE3 SQL Dump --
 
 
--
-- Database: `edt.db`
--
 
-- --------------------------------------------------------
";

while ($tableStruc = $res->fetch(PDO::FETCH_ASSOC)) {
  $tableName = $tableStruc["tbl_name"];
  //$createQuery = str_replace("TABLE", "TABLE IF NOT EXIST", $tableStruc["sql"]);
  $createQuery = $tableStruc["sql"];
  $data .= "\n\n--
-- Table structure of `$tableName`
--\n\n";
  $data .= $createQuery . ";\n";
 
  $data .= "\n\n--
-- Data of the table `$tableName`
--\n\n";
  $q = "SELECT * FROM $tableName";
  $res1 = $dbh->query($q);
  while ($rec = $res1->fetch(PDO::FETCH_ASSOC)) {
 
    // Insert query per record
    $data .= "INSERT INTO $tableName VALUES (";
    $setAccumul = "";
    foreach( $rec as $field => $val ) {
      $setAccumul .= "'$val',";
    }
    $data .= substr( $setAccumul, 0, -1 );
    $data .= ");\n";
  }
}
$savefile="edt_db-".strftime("%Y-%m-%d").".sql";
$handle = fopen($savefile, 'w');
fwrite($handle, $data); 
fclose($handle);

echo("<h1>Données sauvegardées dans $savefile</h1>");
?>