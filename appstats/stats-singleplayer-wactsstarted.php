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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Single Player Stats</strong></div>

        <div style="padding: 0 0 5px 0; text-align: right;">Last 30 days</div>
        <form action="stats-singleplayer.php" method="post">
        <div style="width: 960px; padding: 0 0 5px 0; text-align: right;">
        	<input type="checkbox" name="newusers" <?php if($_POST[newusers] == "on") echo 'checked';?>>  <span style="font-weight: bold; padding: 0 50px 0 0;">Only New Users</span>
        	<select name="platform">
            	<option value="0"<?php if($_POST[platform] == 0) echo ' selected="selected"';?>>All Platforms</option>
                <option value="1"<?php if($_POST[platform] == 1) echo ' selected="selected"';?>>Apple</option>
                <option value="2"<?php if($_POST[platform] == 2) echo ' selected="selected"';?>>Android</option>
                <option value="3"<?php if($_POST[platform] == 3) echo ' selected="selected"';?>>Windows</option>
            </select>
            <input type="submit" value="Update" class="rounded">
        </div>
        </form>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 960px;">
            <thead>
            <tr>
                <th width="120">Date</th>
                <th width="140">All Players</th>
                <th width="140">Fin Players</th>
                <th width="140">% Finished</th>
                <th width="140">Acts Started</th>
                <th width="140">Acts Finished</th>
                <th width="140">% Finished</th>
                <th width="140">Acts/Player</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$oneDay = 3600 * 24;
$start = $now;

$total_started = 0;
$total_finished = 0;
$total_players_started = 0;
$total_players_finished = 0;

$platformfilter = "";
if($_POST[platform] > 0) $platformfilter = " AND platform = $_POST[platform]";

for($i=0; $i<30; $i++) {
	$thisDay = strtotime(date("F j, Y", $start));
	
	if($_POST[newusers] == "on") {
		$query = "SELECT DISTINCT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE tvactivity.timestamp > $thisDay AND tvactivity.timestamp < $thisDay + $oneDay AND event = 1 AND pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay $platformfilter";
		$stmt = $mysql->query($query);
		$count_players_start = $stmt->rowCount();
		
		$query = "SELECT DISTINCT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE tvactivity.timestamp > $thisDay AND tvactivity.timestamp < $thisDay + $oneDay AND event = 2 AND pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay $platformfilter";
		$stmt = $mysql->query($query);
		$count_players_finish = $stmt->rowCount();
		
		$query = "SELECT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE tvactivity.timestamp > $thisDay AND tvactivity.timestamp < $thisDay + $oneDay AND event = 1 AND pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay $platformfilter";
		$stmt = $mysql->query($query);
		$count_started = $stmt->rowCount();
		
		$query = "SELECT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE tvactivity.timestamp > $thisDay AND tvactivity.timestamp < $thisDay + $oneDay AND event = 2 AND pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay $platformfilter";
		$stmt = $mysql->query($query);
		$count_finished = $stmt->rowCount();
	} else {
		$query = "SELECT DISTINCT uuid FROM tvactivity WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND event = 1 $platformfilter";
		$stmt = $mysql->query($query);
		$count_players_start = $stmt->rowCount();
		
		$query = "SELECT DISTINCT uuid FROM tvactivity WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND event = 2 $platformfilter";
		$stmt = $mysql->query($query);
		$count_players_finish = $stmt->rowCount();
		
		$query = "SELECT uuid FROM tvactivity WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND event = 1 $platformfilter";
		$stmt = $mysql->query($query);
		$count_started = $stmt->rowCount();
		
		$query = "SELECT uuid FROM tvactivity WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND event = 2 $platformfilter";
		$stmt = $mysql->query($query);
		$count_finished = $stmt->rowCount();
	}
	
	$ratio = 100 * $count_finished / $count_started;
	$ratio2 = 100 * $count_players_finish / $count_players_start;
	$acts_player = $count_finished / $count_players_finish;
	
	if(date("D",$start) == "Sun") {
		$rowbg = "#288d00";
	} else {
		$rowbg = "#000000";
	}
	echo '<tr><td><span style="color:'. $rowbg .'; font-weight:bold;">'. date("m/d/Y", $thisDay) .'</span></td><td>'. number_format($count_players_start) .'</td><td>'. number_format($count_players_finish) .'</td><td>'. number_format($ratio2) .'%</td><td>'. $count_started .'</td><td>'. $count_finished .'</td><td>'. number_format($ratio) .'%</td><td>'. number_format($acts_player, 2, '.', '') .'</td></tr>';
	
	$total_started = $total_started + $count_started;
	$total_finished = $total_finished + $count_finished;
	$total_players_started = $total_players_started + $count_players_start;
	$total_players_finished = $total_players_finished + $count_players_finish;
	
	$start = $start - $oneDay;
}


$ratio = 100 * $total_finished / $total_started;
$ratio2 = 100 * $total_players_finished / $total_players_started;
$acts_player = $total_finished / $total_players_finished;
echo '<tr><td><strong>Month Total:</strong></td><td><strong>'. number_format($total_players_started) .'</strong></td><td><strong>'. number_format($total_players_finished) .'</strong></td><td><strong>'. number_format($ratio2) .'%</strong></td><td><strong>'. $total_started .'</strong></td><td><strong>'. $total_finished .'</strong></td><td><strong>'. number_format($ratio) .'%</strong></td><td><strong>'. number_format($acts_player, 2, '.', '') .'</strong></td></tr>';
?>


		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>