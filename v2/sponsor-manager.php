<?php
$host = "64.91.249.141";
$db = "custompl_971786_db";
$user = "custompl_poptriv";
$passwd = "Cust0mPl@y!";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();

$query = "UPDATE `pt_sponsor` SET timestamp_status = $now, status = 1 WHERE status = 0 ORDER BY supporters DESC LIMIT 1";        
$mysql->query($query);
?>