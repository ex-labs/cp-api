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

        <div style="padding: 0 0 5px 0; text-align: right;">Last 50 days</div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 1100px;">
            <thead>
            <tr>
                <th width="150">Date</th>
                <th width="120">Players</th>
                <th width="140">Games/Player</th>
                <th width="140">Total Games</th>
                <th width="140">Challenge Games</th>
                <th width="140">% of Challenge</th>
                <th width="140">Finished Games</th>
                <th width="140">% Finished</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$oneDay = 3600 * 24;
$start = $now;

$total_players = 0;
$total_games = 0;
$total_challenge = 0;
$total_finished = 0;

for($i=0; $i<50; $i++) {
	$thisDay = strtotime(date("F j, Y", $start));

	$query = "SELECT DISTINCT playerOne FROM `pt_gamedata` WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND challenge = 0 UNION SELECT DISTINCT playerTwo FROM `pt_gamedata` WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND challenge = 0";
	$stmt = $mysql->query($query);
	$count_players = $stmt->rowCount();
	
	$query = "SELECT gid FROM pt_gamedata WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND challenge = 0";
	$stmt = $mysql->query($query);
	$count_games = $stmt->rowCount();
	
	$query = "SELECT gid FROM pt_gamedata WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND challenge_game > 0 AND challenge = 0";
	$stmt = $mysql->query($query);
	$count_challenge = $stmt->rowCount();
	
	$query = "SELECT gid FROM pt_gamedata WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND statusOne > 0 AND statusTwo > 0 AND challenge = 0";
	$stmt = $mysql->query($query);
	$count_finished = $stmt->rowCount();
	
	$ratio = 100 * $count_challenge / $count_games;
	$ratio2 = 100 * $count_finished / $count_games;
	$games_per_user = $count_games / $count_players;
	if(date("D",$start) == "Sun") {
		$rowbg = "#288d00";
	} else {
		$rowbg = "#000000";
	}
	echo '<tr><td><span style="color:'. $rowbg .'; font-weight:bold;">'. date("m/d/Y", $thisDay) .'</span></td><td>'. $count_players .'</td><td>'. number_format($games_per_user, 2, '.', '') .'</td><td>'. $count_games .'</td><td>'. $count_challenge .'</td><td>'. number_format($ratio) .'%</td><td>'. $count_finished .'</td><td>'. number_format($ratio2) .'%</td></tr>';
	
	$total_games = $total_games + $count_games;
	$total_challenge = $total_challenge + $count_challenge;
	$total_finished = $total_finished + $count_finished;
	
	$start = $start - $oneDay;
}

$query = "SELECT DISTINCT playerOne FROM `pt_gamedata` WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay * 30 AND challenge = 0 UNION SELECT DISTINCT playerTwo FROM `pt_gamedata` WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay * 30 AND challenge = 0";
$stmt = $mysql->query($query);
$total_players = $stmt->rowCount();

$ratio = 100 * $total_challenge / $total_games;
$ratio2 = 100 * $total_finished / $total_games;
$games_per_user = $total_games / $total_players;
echo '<tr><td><strong>Total:</strong></td><td><strong>'. $total_players .'</strong></td><td><strong>'. number_format($games_per_user, 2, '.', '') .'</strong></td><td><strong>'. $total_games .'</strong></td><td><strong>'. $total_challenge .'</strong></td><td><strong>'. number_format($ratio) .'%</strong></td><td><strong>'. $total_finished .'</strong></td><td><strong>'. number_format($ratio2) .'%</strong></td></tr>';
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>