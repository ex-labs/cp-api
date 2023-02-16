<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();
$start = $now - 3600 * 24;

$query = "SELECT id FROM movies WHERE popcornactive = 1 OR popcornseriesactive = 1 OR popcornbonus > 0";        
$stmt = $mysql->query($query);

while($movie = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$movieid = $movie[id];
	$query = "SELECT COUNT(*) as count FROM tvactivity WHERE event = 2 AND movieid = $movieid AND timestamp > $start AND timestamp < $now";
	$stmt2 = $mysql->query($query);
	$count = $stmt2->fetch(PDO::FETCH_ASSOC);
	$upd_query = "UPDATE movies SET trending = $count[count] WHERE id = $movieid";
	$mysql->query($upd_query);
}
?>