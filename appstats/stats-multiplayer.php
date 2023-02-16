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
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="css/pe.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/tables.css" type="text/css" media="all" />
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(function() {
    $("#datepicker").datepicker();
});
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <div class="grad1 rounded module">
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Multi-Player Stats</strong></div>

        <div style="padding: 0 0 5px 0; text-align: right;">Last 120 days</div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 600px;">
            <thead>
            <tr>
                <th width="150">Date</th>
                
                <th width="150">Players</th>
                <th width="150">Games</th>
                <th width="150">Games/Player</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$oneDay = 3600 * 24;
$start = $now;

for($i=0; $i<120; $i++) {
	$thisDay = strtotime(date("F j, Y", $start));
	
	$query = "SELECT DISTINCT playerOne FROM `pt_gamedata` WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND scoreOne > 0 UNION SELECT DISTINCT playerTwo FROM `pt_gamedata` WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND scoreTwo > 0";
	$stmt = $mysql->query($query);
	$count_players = $stmt->rowCount();
	
	$query = "SELECT gid FROM pt_gamedata WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND scoreOne > 0 AND scoreTwo > 0";
	$stmt = $mysql->query($query);
	$count_games = $stmt->rowCount();
	
	$games_per_user = $count_games / $count_players;
	if(date("D",$start) == "Sun") {
		$rowbg = "#288d00";
	} else {
		$rowbg = "#000000";
	}
	echo '<tr><td><span style="color:'. $rowbg .'; font-weight:bold;">'. date("m/d/Y", $thisDay) .'</span></td><td>'. number_format($count_players) .'</td><td>'. number_format($count_games) .'</td><td>'. number_format($games_per_user, 2, '.', '') .'</td></tr>';
	
	$start = $start - $oneDay;
}


$query = "SELECT DISTINCT playerOne FROM `pt_gamedata` WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay * 30 AND scoreOne > 0 UNION SELECT DISTINCT playerTwo FROM `pt_gamedata` WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay * 75 AND scoreTwo > 0";
$stmt = $mysql->query($query);
$count_players = $stmt->rowCount();
	
$query = "SELECT gid FROM pt_gamedata WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay * 75 AND scoreOne > 0 AND scoreTwo > 0";
$stmt = $mysql->query($query);
$count_games = $stmt->rowCount();
	
$games_per_user = $count_games / $count_players;


echo '<tr><td><strong>Total:</strong></td><td><strong>'. number_format($count_players) .'</strong></td><td><strong>'. number_format($count_games) .'</strong></td><td><strong>'. number_format($games_per_user, 2, '.', '') .'</strong></td></tr>';
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>
