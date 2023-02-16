<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);

$query = "DELETE FROM challenge_categories_mov WHERE mid = $_POST[movieid]"; //echo $query ."\n";
$result = $mysql->exec($query);

for($i=0; $i<count($_POST[categories]); $i++) {
	if($_POST[categories][$i] == "on") {
		$query = "INSERT INTO challenge_categories_mov (mid, categoryid) VALUES ($_POST[movieid], $i)"; //echo $query ."\n";
		$result = $mysql->exec($query);
	}
}

echo "\nTaDaaaaaaaaa!";
?>