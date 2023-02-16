<?php
$host = "mysql51-063.wc1.ord1.stabletransit.com";
$db = "971786_db";
$user = "971786_db";
$passwd = "Cust0mP1ay#)";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);

$query = "SELECT * FROM movies WHERE popcornactive = 1 OR popcornseriesactive = 1 OR popcornbonus > 0";
$stmt = $mysql->query($query);
while($movie = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$dbfile = "../data-tv/". $movie[id] ."/". $movie[id] .".s3db";
	$dbname = 'sqlite:'. $dbfile;
	$dbh = new PDO($dbname);
	
	$title = $movie[title];
    if(substr($title, -5) == ", The") {
    	$title = "The ". substr($title, 0, -5);
	}
	echo '<div style="padding: 50px 0 10px 0; font-weight: bold;">'. $title .'</div>';
	
	$subquery = "select SuperFanName, SnapShotFrame, WriteUp from TblSuperFan where 1 ";
	$stm = $dbh->query($subquery);
	while($row = $stm->fetch(PDO::FETCH_ASSOC)) {
		$snap = '../data-tv/'. $movie[id] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg';
		if(file_exists($snap)) {
			echo '<div style="padding: 0 0 5px 0;">'. $row[SuperFanName] .'</div>';
			echo '<div style="padding: 0 0 5px 0; font-style: italic;">'. $row[WriteUp] .'</div>';
			echo '<div style="padding: 0 0 25px 0;"><img src="http://api.customplay.com/data-tv/'. $movie[id] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg"></div>';
		}
	}
}
?>