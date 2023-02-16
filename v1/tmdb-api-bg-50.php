<?php
ini_set('memory_limit', '256M');

$dbh = mysql_connect ("mysql51-063.wc1.ord1.stabletransit.com", "971786_db", "Cust0mP1ay#)") or die ('Cannot connect db: '. mysql_error());
mysql_select_db ("971786_db") or die('Cannot select db: ' . mysql_error());


$query = "SELECT id, title, tmdb_id, tmdb_background, tmdb_background_offset FROM movies WHERE 1 ORDER BY id LIMIT 50, 10";
$res = mysql_query($query);
while($row = mysql_fetch_array($res)) {
	$url = "https://api.themoviedb.org/3/movie/". $row[tmdb_id] ."/images?api_key=2842da55488d33ac0b3b9f9f88759e18";
	$contents = file_get_contents($url);
	$json = json_decode($contents, true);
	$posters = $json[backdrops];
	
	foreach($posters as $poster) {
		$file_path = "/". $row[tmdb_background];
		if($poster[file_path] == $file_path) {
			$posterUrl = $file_path;
		}
	}
	if($posterUrl) {
		$imgUrl = "http://image.tmdb.org/t/p/original". $posterUrl;
		$imageString = file_get_contents($imgUrl);
		
		$tmpimage = "/mnt/stor10-wc1-ord1/818295/971786/api.customplay.com/web/content/v1/tmp/image.jpg";
		$save = file_put_contents($tmpimage,$imageString);
		
		//PROCESS IMAGE
		if($save) {
			$src_img = imagecreatefromjpeg($tmpimage);
		
			$dst_img = imagecreatetruecolor(1920, 1080);
			imagecopyresampled($dst_img,$src_img,0,0,0,0,1920,1080,imagesx($src_img),imagesy($src_img));
			
			$overlay_img = imagecreatefrompng("/mnt/stor10-wc1-ord1/818295/971786/api.customplay.com/web/content/v1/tmp/overlay.png");
			imagecopyresampled($dst_img,$overlay_img,0,0,0,0,1920,1080,1920,1080);
	
			$outputimg = '/mnt/stor10-wc1-ord1/818295/971786/api.customplay.com/web/content/v1/working/backgrounds/'. $row[tmdb_background];
			imagejpeg($dst_img, $outputimg, 50);
			
			imagedestroy($src_img);
			imagedestroy($overlay_img);
			
			
			$portrt_img = imagecreatetruecolor(960, 1347);
			$offset = (-1) * floor($row[tmdb_background_offset] * 1.247222222 - 480);
			imagecopyresampled($portrt_img,$dst_img,$offset,0,0,0,2395,1347,1920,1080);
			$outputimg = '/mnt/stor10-wc1-ord1/818295/971786/api.customplay.com/web/content/v1/working/backgrounds-portrait/'. $row[tmdb_background];
			imagejpeg($portrt_img, $outputimg, 50);
			
			imagedestroy($dst_img);
			imagedestroy($portrt_img);
		} else {
			$now = time();
			$query = "INSERT INTO errorlog (mid, type, timestamp	, description) VALUES ($row[id], 0, $now, 'Image Download Error')";
			mysql_query($query);
		}
	} else {
		$now = time();
		$query = "INSERT INTO errorlog (mid, type, timestamp	, description) VALUES ($row[id], 0, $now, 'Image Not Found on TMDB')";
		mysql_query($query);
	}
}

mysql_close($dbh);
?>