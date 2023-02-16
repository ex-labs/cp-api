<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();
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
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('.usedbox').click(function(event) {
		var parent = $(this).parent().parent();
		var myid = event.target.id;
		var url = "plotinfo-used.php";
		var params = {
			needle: myid
		};
		$.ajax({
			type: 'POST',
			url: url,
			data: params,
			success: function(res) {
				parent.hide();
			},
			error: function() { alert('Ooops... Something went wrong!'); }			
		});
		
	});
});
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <div class="grad1 rounded module">
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / PlotInfo Writeups</strong></div>
        
<?php
$myfile = fopen("used-plotinfo.txt", "r");
$used = fgets($myfile);
fclose($myfile);

$query = "SELECT * FROM movies WHERE id != 1876 AND id != 9999 ORDER BY title";
$stmt = $mysql->query($query);
while($movie = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$dbfile = "/home/ptdownloads/www/data-tv/". $movie[id] ."/". $movie[id] .".s3db";
	$dbname = 'sqlite:'. $dbfile;
	$dbh = new PDO($dbname);
	
	$title = $movie[title];
    if(substr($title, -5) == ", The") {
    	$title = "The ". substr($title, 0, -5);
	}
	echo '<div style="clear:both;"></div>';
	echo '<div style="padding: 50px 0 10px 0; font-weight: bold;">'. $title .'</div>';
	
	$subquery = "select WhyName, SnapShotFrame, WriteUp from TblWhy where 1 ";
	$stm = $dbh->query($subquery);
	while($row = $stm->fetch(PDO::FETCH_ASSOC)) {
		$snap = '/home/ptdownloads/www/data-tv/'. $movie[id] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg';
		if(file_exists($snap)) {
			$needle = $movie[id] .":". $row[SnapShotFrame] ."|";
			if(strpos($used,$needle) === FALSE) {
				echo '<div style="float: left; padding: 0 20px 10px 0; width: 360px;">';
				echo '<div style="padding: 0 0 5px 0;"><input type="checkbox" class="usedbox" id="'. $needle .'" /> '. $row[WhyName] .'</div>';
				echo '<div style="padding: 0 0 25px 0;"><img src="http://www.popcorntriviadownloads.com/data-tv/'. $movie[id] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg" width="360" height="auto"></div>';
				echo '<div style="padding: 0 0 5px 0; font-style: italic;">'. $title .' - '. $row[WhyName] .'<br>'. $row[WriteUp] .'</div>';
				echo '</div>';
			}
				
		}
	}
}
?>

	</div>    
</div>
</body>
</html>