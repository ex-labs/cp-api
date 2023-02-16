<?php
error_reporting(0);
//ini_set('display_errors',1);
//error_reporting(E_ALL);


$host = "64.91.249.141";
$db = "custompl_971786_db";
$user = "custompl_poptriv";
$passwd = "Cust0mPl@y!";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();

$query = "SELECT id, parentid, title, itunesid, price, trending, tmdb_version, releasedate, mapdate, popcornseriesactive FROM movies WHERE popcornactive = 1 OR popcornseriesactive = 1 OR popcornbonus > 0";
	
	/*
	if($testuser) {
		$query = "SELECT id, parentid, title, itunesid, price, trending, tmdb_version, releasedate, mapdate, popcornseriesactive FROM movies WHERE id = 2105";
	}
	*/


	$stmt = $mysql->query($query);
	
	$movies = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		print_r($row);
	}
/*
$starttime = 1506834000;
for($i=0;$i<30;$i++) {
	$starttime = $starttime + 3600 * 24;
	$readable = date("m/d/y", $starttime);
	$query = "INSERT INTO pt_adcost (addate, addate_readable) VALUES ($starttime, '$readable')";
	$mysql->query($query);
}
*/

/*
$query = "SELECT uid, SUM(points) as totalpoints FROM pt_acts WHERE movieid = 1807 GROUP BY uid HAVING totalpoints = 300";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$query = "INSERT INTO pt_contestwinner (uid, contest) VALUES ($row[uid], 20)";
	$mysql->query($query);
}
*/

?>
