<?php
$host = "64.91.249.141";
$db = "custompl_971786_db";
$user = "custompl_db";
$passwd = "Cust0mP1ay#)";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();
$start = $now - 3600 * 24;

$query = "SELECT id FROM movies WHERE 1";        
$stmt = $mysql->query($query);

while($movie = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$movieid = $movie[id];
	$query = "SELECT * FROM itunesids WHERE movieid = $movieid";
	$stmt2 = $mysql->query($query);
	$item = $stmt2->fetch(PDO::FETCH_ASSOC);
	$upd_query = "UPDATE movies SET itunesid = $item[itunesid] WHERE id = $movieid";
	$mysql->query($upd_query);
}
?>