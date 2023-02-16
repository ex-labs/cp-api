<?php
ini_set('memory_limit', '256M');

$dbh = mysql_connect ("mysql51-063.wc1.ord1.stabletransit.com", "971786_db", "Cust0mP1ay#)") or die ('Cannot connect db: '. mysql_error());
mysql_select_db ("971786_db") or die('Cannot select db: ' . mysql_error());


$query = "SELECT id, title, tmdb_id, tmdb_boxart FROM movies WHERE 1 ORDER BY id LIMIT 60, 30";
$res = mysql_query($query);
while($row = mysql_fetch_array($res)) {
	$url = "https://api.themoviedb.org/3/movie/". $row[tmdb_id] ."/images?api_key=2842da55488d33ac0b3b9f9f88759e18";
	$contents = file_get_contents($url);
	$json = json_decode($contents, true);
	$posters = $json[posters];
	
	foreach($posters as $poster) {
		$file_path = "/". $row[tmdb_boxart];
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
			
			$dst_img = imagecreatetruecolor(1000, 1500);
			imagecopyresampled($dst_img,$src_img,0,0,0,0,1000,1500,imagesx($src_img),imagesy($src_img));
			
			$outputimg = '/mnt/stor10-wc1-ord1/818295/971786/api.customplay.com/web/content/v1/working/boxarts/'. $row[tmdb_boxart];
			imagejpeg($dst_img, $outputimg, 45);
			imagedestroy($dst_img);
				
			
			$dst_img_small = imagecreatetruecolor(433, 650);
			imagecopyresampled($dst_img_small,$src_img,0,0,0,0,433,650,imagesx($src_img),imagesy($src_img));
			$outputimg = '/mnt/stor10-wc1-ord1/818295/971786/api.customplay.com/web/content/v1/working/boxarts-small/'. $row[tmdb_boxart];
			imagejpeg($dst_img_small, $outputimg, 45);
			
			imagedestroy($src_img);
			imagedestroy($dst_img_small);
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