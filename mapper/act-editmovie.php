<?php
ini_set('memory_limit','256M');
include('includes/config.php');

//ini_set('display_errors',1);
//error_reporting(E_ALL);


if($_POST[action] == "record") {
	if($_GET[movieid]) {
		$popcornactive = 0;
		$popcornseriesactive = 0;
		if($_POST[active] == 0) {
			$popcornactive = 0;
			$popcornseriesactive = 0;
		} else {
			if($_POST[parentid] > 0) {
				$popcornseriesactive = 1;
			} else {
				$popcornactive = 1;
			}
		}
		$query = "UPDATE movies SET parentid = ?, title = ?, subtitle = ?, year = ?, price = ?, releasedate = ?, mapdate = ?, streetdate = ?, tmdb_id = ?, tmdb_boxart = ?, tmdb_background = ?, tmdb_background_offset = ?, itunesid = ?, popcornactive = ?, popcornseriesactive = ? WHERE id = $_GET[movieid]";
		$stmt = $mysql->prepare($query);
		$array = array("$_POST[parentid]", "$_POST[title]", "$_POST[subtitle]", "$_POST[year]", "$_POST[price]", "$_POST[releasedate]", "$_POST[mapdate]", "$_POST[streetdate]", "$_POST[tmdb_id]", "$_POST[tmdb_boxart]", "$_POST[tmdb_background]", "$_POST[tmdb_background_offset]", "$_POST[itunesid]", "$popcornactive", "$popcornseriesactive");
		$stmt->execute($array);
	} else {
		$query = "SELECT id FROM movies WHERE 1 ORDER BY id DESC LIMIT 1";
		$stmt = $mysql->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$_GET[movieid] = $row[id] + 1;
		
		$query = "INSERT INTO movies (id, parentid, title, subtitle, year, price, releasedate, mapdate, streetdate, tmdb_id, tmdb_boxart, tmdb_background, tmdb_background_offset, itunesid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$stmt = $mysql->prepare($query);
		$array = array("$_GET[movieid]", "$_POST[parentid]", "$_POST[title]", "$_POST[subtitle]", "$_POST[year]", "$_POST[price]", "$_POST[releasedate]", "$_POST[mapdate]", "$_POST[streetdate]", "$_POST[tmdb_id]", "$_POST[tmdb_boxart]", "$_POST[tmdb_background]", "$_POST[tmdb_background_offset]", "$_POST[itunesid]");
		$stmt->execute($array);


		$mapdir = '../data-tv/'. $_GET[movieid];
		if(!file_exists($mapdir)) {
			mkdir($mapdir);
		}
		$imagedir = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets';
		if(!file_exists($imagedir)) {
			mkdir($imagedir);
		}
		$dbfile = '../data-tv/'. $_GET[movieid] .'/'. $_GET[movieid] .'.s3db';
		if(!file_exists($dbfile)) {
			$blankdb = 'includes/blank_db.s3db';
			copy($blankdb, $dbfile);
		}
	}
	
}
if($_POST[action] == "upload") {
	$tmpzipfile = '../data-tv/tmp/map.zip';
	if(file_exists($tmpzipfile)) {
		unlink($tmpzipfile);
	}
	
	$path = '../data-tv/'. $_GET[movieid];
	$todelete = $path .'/avisynth.avs';
	if(file_exists($todelete)) {
		unlink($todelete);
	}
	$todelete = $path .'/Thumbs.db';
	if(file_exists($todelete)) {
		unlink($todelete);
	}
	$todelete = $path .'/AppleTVAssets/Thumbs.db';
	if(file_exists($todelete)) {
		unlink($todelete);
	}
	$todelete = $path .'/'. $_GET[movieid] .'.zip';
	if(file_exists($todelete)) {
		unlink($todelete);
	}
		
	HZip::zipDir($path, $tmpzipfile);
	$zipfile = $path .'/'. $_GET[movieid] .'.zip';
	rename($tmpzipfile, $zipfile);
}


$movie = array();

if($_GET[movieid]) {
	$query = "SELECT * FROM movies WHERE id = ". $_GET[movieid];
	$stmt = $mysql->query($query);
	$movie = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$titlestring = "Edit Movie";
	$displaytitle = $movie[title];
} else {
	$titlestring = "Add New Movie";
	$displaytitle = "Add New Movie";
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow">
<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<title>Data Management Thingy</title>
<link rel="stylesheet" href="css/pe.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/tables.css" type="text/css" media="all" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function() { 
	$("#setparent").change(function() {
		if($("#setparent").val() < 1) {
			$("#ser_episode").hide();
		} else {
			if($("#setparent").val() == <?php echo $movie[id];?>) {
				$("#ser_episode").show();
			} else {
				$("#ser_episode").show();
			}
		}
	});
});
function verifyServerUpdate() {
	var result = confirm("Are you absolutely sure you want to update data on LIVE server? It will affect what is available on apps...");
	if(result) {
		return true;
	}
	return false;
}
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    <a href="dashboard.php">Dashboard</a> / <?php echo $displaytitle;?>
    <div class="grad1 rounded module">

    
    <div style="float: left; width: 800px;">
    <h3 style="padding: 0 0 10px 150px;"><?php echo $titlestring;?></h3>
    
    <form action="act-editmovie.php?movieid=<?php echo $_GET[movieid]?>" method="post">
    <input type="hidden" name="action" value="record">
    
    <div class="record"><label>Movie Title:</label><input type="text" id="title" name="title" value="<?php echo $movie[title];?>" style="width:400px;"></div>
    <div class="record"><label>Year:</label><input type="text" name="year" value="<?php echo $movie[year];?>" style="width:100px;"></div>
    <div class="record"><label>Release Date:</label><input type="text" name="releasedate" value="<?php echo $movie[releasedate];?>" style="width:200px;"></div>
    <div class="record"><label>Street Date:</label><input type="text" name="streetdate" value="<?php echo $movie[streetdate];?>" style="width:200px;"></div>
    <div class="record"><label>Map Date:</label><input type="text" name="mapdate" value="<?php echo $movie[mapdate];?>" style="width:200px;"></div>
    <div class="record"><label>Popcorn Price:</label><input type="text" name="price" value="<?php echo $movie[price];?>" style="width:100px;"></div>
    <div class="record"><label>TMDB ID:</label><input type="text" name="tmdb_id" value="<?php echo $movie[tmdb_id];?>" style="width:200px;"></div>
	<div class="record"><label>Boxart Image:</label><input type="text" name="tmdb_boxart" value="<?php echo $movie[tmdb_boxart];?>" style="width:400px;"></div>
    <div class="record"><label>BG Image:</label><input type="text" name="tmdb_background" value="<?php echo $movie[tmdb_background];?>" style="width:400px;"></div>
    <div class="record"><label>BG Offset:</label><input type="text" name="tmdb_background_offset" value="<?php echo $movie[tmdb_background_offset];?>" style="width:100px;"></div>
    <div class="record"><label>Itunes ID:</label><input type="text" name="itunesid" value="<?php echo $movie[itunesid];?>" style="width:200px;"></div>
    <div class="record"><label>Video Set:</label><select id="setparent" name="parentid">
    	<option value="0"<?php if($movie[parentid] == 0) echo ' selected="true"';?>>Not a part of any Video Set</option>
    <?php
	$query = "SELECT id, title FROM movies WHERE 1 ORDER BY title";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		echo '<option value="'. $row[id] .'"';
		if($movie[parentid] == $row[id]) echo ' selected="true"';
		echo '>'. $row[title] .'</option>';
	}
	?></select></div>
    <div class="record" id="ser_episode"<?php if($movie[parentid] < 1) echo ' style="display: none;"';?>><label>Episode Title:</label><input type="text" name="subtitle" value="<?php echo $movie[subtitle];?>" style="width:400px;"></div>
    
    <?php if($_GET[movieid]) {?>
    <?php
	$active = 0;
	if(($movie[popcornactive] == 1) || ($movie[popcornseriesactive])) {
		$active = 1;
	}
	?>
    <div class="record"><label>Map Active:</label><select name="active">
    	<option value="0"<?php if($active == 0) echo ' selected="true";';?>>Not Active</option>
    	<option value="1"<?php if($active == 1) echo ' selected="true";';?>>Active Popcorntrivia Map</option>
	</select></div>
   	<?php }?>
    
	<div class="record"><label></label><input type="submit" value="Update Record" class="rounded"></div>
    </form>
	</div>
    
	<?php if($_GET[movieid]) {?>
    
    <div style="float:left;">
    <h3>Image Assets</h3>
    
<?php
if($movie[tmdb_id] > 0) {
	if($movie[parentid] == 0) {
		$url = "https://api.themoviedb.org/3/movie/". $movie[tmdb_id] ."/images?api_key=2842da55488d33ac0b3b9f9f88759e18";
		$contents = file_get_contents($url);
		$json = json_decode($contents, true);

		$posters = $json[posters];
		foreach($posters as $poster) {
			$file_path = "/". $movie[tmdb_boxart];
			if($poster[file_path] == $file_path) {
				$posterUrl = $file_path;
			}
		}

		$backdrops = $json[backdrops];
		foreach($backdrops as $backdrop) {
			$file_path = "/". $movie[tmdb_background];
			if($backdrop[file_path] == $file_path) {
				$backdropUrl = $file_path;
			}
		}
	} else {
		$url = "https://api.themoviedb.org/3/tv/". $movie[tmdb_id] ."/images?api_key=2842da55488d33ac0b3b9f9f88759e18";
		$contents = file_get_contents($url);
		$json = json_decode($contents, true);

		$backdrops = $json[backdrops];
		foreach($backdrops as $backdrop) {
			$file_path = "/". $movie[tmdb_background];
			if($backdrop[file_path] == $file_path) {
				$backdropUrl = $file_path;
			}
		}

		$parts = explode(" ", $displaytitle);
		$fullseason = end($parts);
		if(substr($fullseason,0,1) == "S") {
			$season = intval(substr(end($parts), 1, 3));
		} else {
			$season = $fullseason;
		}
		$url = "https://api.themoviedb.org/3/tv/". $movie[tmdb_id] ."/season/". $season ."/images?api_key=2842da55488d33ac0b3b9f9f88759e18";
		$contents = file_get_contents($url);
		$json = json_decode($contents, true);

		$posters = $json[posters];
		foreach($posters as $poster) {
			$file_path = "/". $movie[tmdb_boxart];
			if($poster[file_path] == $file_path) {
				$posterUrl = $file_path;
			}
		}
	}
}


echo '<div style="float: left; padding: 0 120px 0 0;">';
echo '<h3>Boxart</h3>';
if(!$posterUrl) echo '<span style="color: #ff0000;">Not Present in TMDB API!</span>';
else {
	$imgUrl = "http://image.tmdb.org/t/p/original". $posterUrl;
	$imageString = file_get_contents($imgUrl);
	$tmpimage = "tmp/image.jpg";
	$save = file_put_contents($tmpimage,$imageString);
	
	//PROCESS IMAGE
	if($save) {
		$src_img = imagecreatefromjpeg($tmpimage);
		$dst_img_small = imagecreatetruecolor(433, 650);
		imagecopyresampled($dst_img_small,$src_img,0,0,0,0,433,650,imagesx($src_img),imagesy($src_img));
		$outputimg = '../api.customplay.com/data-tv/'. $_GET[movieid] .'/Boxart.jpg';
		imagejpeg($dst_img_small, $outputimg, 45);
		
		imagedestroy($src_img);
		imagedestroy($dst_img_small);
		
		echo '<div><a href="https://api.customplay.com/data-tv/'. $_GET[movieid] .'/Boxart.jpg" target="_blank"><img src="https://api.customplay.com/data-tv/'. $_GET[movieid] .'/Boxart.jpg" height="120" width="auto" border="0" /></a></div>';
	} else {
		echo '<span style="color: #ff0000;">Error downloading the image from TMDB</span>';
	}
}

echo '</div>';
echo '<div style="float: left;">';

echo '<h3>Background - Landscape Image</h3>';
if(!$backdropUrl) echo '<span style="color: #ff0000;">Not Present in TMDB API!</span>';
else {
	$imgUrl = "http://image.tmdb.org/t/p/original". $backdropUrl;
	$imageString = file_get_contents($imgUrl);
	$tmpimage = "tmp/image.jpg";
	$save = file_put_contents($tmpimage,$imageString);
	if($save) {
		$src_img = imagecreatefromjpeg($tmpimage);
	
		$dst_img = imagecreatetruecolor(1920, 1080);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,1920,1080,imagesx($src_img),imagesy($src_img));
		
		//$overlay_img = imagecreatefrompng("tmp/overlay.png");
		//imagecopyresampled($dst_img,$overlay_img,0,0,0,0,1920,1080,1920,1080);
		
		$outputimg = '../data-tv/'. $_GET[movieid] .'/BG.jpg';
		imagejpeg($dst_img, $outputimg, 50);
		
		imagedestroy($src_img);
		//imagedestroy($overlay_img);
		
		
		$portrt_img = imagecreatetruecolor(960, 1347);
		$offset = (-1) * floor($movie[tmdb_background_offset] * 1.247222222 - 480);
		imagecopyresampled($portrt_img,$dst_img,$offset,0,0,0,2395,1347,1920,1080);
		
		$outputimg = '../data-tv/'. $_GET[movieid] .'/BG_Portrait.jpg';
		imagejpeg($portrt_img, $outputimg, 50);
		
		imagedestroy($dst_img);
		imagedestroy($portrt_img);
		
		echo '<div><a href="https://api.customplay.com/data-tv/'. $_GET[movieid] .'/BG.jpg" target="_blank"><img src="https://api.customplay.com/data-tv/'. $_GET[movieid] .'/BG.jpg" height="120" width="auto" border="0" /></a></div>';
		echo '<h3>Background - Portrait Image</h3>';
		echo '<div><a href="https://api.customplay.com/data-tv/'. $_GET[movieid] .'/BG_Portrait.jpg" target="_blank"><img src="https://api.customplay.com/data-tv/'. $_GET[movieid] .'/BG_Portrait.jpg" height="120" width="auto" border="0" /></a></div>';
	} else {
		echo '<span style="color: #ff0000;">Error downloading the image from TMDB</span>';
	}
}
echo '</div>';
?>

	<div class="clearfix"></div>
	<div style="margin: 30px; border-bottom: 1px solid #e6e6e6;"></div>
    <h3>Update Map Files</h3>
		<div style="font-style: italic; padding: 0 0 15px 0;">* Please make sure the map is created with all the required questions and resources...</div>
<?php
$mapready = false;
if($_GET[movieid]) {
	$dbfile = '../data-tv/'. $_GET[movieid] .'/'. $_GET[movieid] .'.s3db';
	$dbname = 'sqlite:'. $dbfile;
	$dbh = new PDO($dbname);
	
	$query = "SELECT MovieApiID FROM TblMovie";
	$stmt = $dbh->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if($row[MovieApiID] == 1) {
		$mapready = true;
	}
}

if($mapready) {
?>
   	<form action="act-editmovie.php?movieid=<?php echo $_GET[movieid]?>" method="post">
    <input type="hidden" name="action" value="upload">
    <input type="submit" value="Update PopcornTrivia Map" class="rounded">
    </form>
<?php }?>
    
    </div>
    <?php }?>
    
	<div class="clearfix"></div>
	</div>    
</div>
</body>
</html>
<?php

class HZip { 
  /** 
   * Add files and sub-directories in a folder to zip file. 
   * @param string $folder 
   * @param ZipArchive $zipFile 
   * @param int $exclusiveLength Number of text to be exclusived from the file path. 
   */ 
  private static function folderToZip($folder, &$zipFile, $exclusiveLength) { 
    $handle = opendir($folder); 
    while (false !== $f = readdir($handle)) { 
      if ($f != '.' && $f != '..') { 
        $filePath = "$folder/$f"; 
        // Remove prefix from file path before add to zip. 
        $localPath = substr($filePath, $exclusiveLength); 
        if (is_file($filePath)) { 
		  if (strpos($filePath, 'Thumbs.db') === false) {
			 $zipFile->addFile($filePath, $localPath); 
		  }
        } elseif (is_dir($filePath)) { 
          // Add sub-directory. 
          $zipFile->addEmptyDir($localPath); 
          self::folderToZip($filePath, $zipFile, $exclusiveLength); 
        } 
      } 
    } 
    closedir($handle); 
  } 

  /** 
   * Zip a folder (include itself). 
   * Usage: 
   *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip'); 
   * 
   * @param string $sourcePath Path of directory to be zip. 
   * @param string $outZipPath Path of output zip file. 
   */ 
  public static function zipDir($sourcePath, $outZipPath) { 
    $pathInfo = pathInfo($sourcePath); 
    $parentPath = $pathInfo['dirname']; 
    $dirName = $pathInfo['basename']; 

    $z = new ZipArchive(); 
    $z->open($outZipPath, ZIPARCHIVE::CREATE); 
    $z->addEmptyDir($dirName); 
    self::folderToZip($sourcePath, $z, strlen("$parentPath/")); 
    $z->close(); 
  } 
}
?>