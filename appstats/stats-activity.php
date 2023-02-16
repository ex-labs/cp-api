<?php
$host = "mysql51-063.wc1.ord1.stabletransit.com";
$db = "971786_db";
$user = "971786_db";
$passwd = "Cust0mP1ay#)";

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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / User Activity</strong></div>

		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 960px;">
            <thead>
            <tr>
                <th width="120">Date</th>
                <th width="140">New Players</th>
                <th width="140">Week 1</th>
                <th width="140">Week 2</th>
                <th width="140">Week 3</th>
                <th width="140">Week 4</th>
                <th width="140">Week 5</th>
                <th width="140">Week 6</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$oneDay = 3600 * 24;
$oneWeek = $oneDay * 7;
$start = $now;


for($i=0; $i<42; $i++) {
	$thisDay = strtotime(date("F j, Y", $start));

	$query = "SELECT uid FROM pt_users WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay";
	$stmt = $mysql->query($query);
	$count_players = $stmt->rowCount();
	
	$query = "SELECT DISTINCT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay AND tvactivity.timestamp > $thisDay AND tvactivity.timestamp < $thisDay + $oneDay * 7 AND event = 2";
	$stmt = $mysql->query($query);
	$count_players_1 = $stmt->rowCount();
	
	$query = "SELECT DISTINCT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay AND tvactivity.timestamp > $thisDay + $oneDay * 7 AND tvactivity.timestamp < $thisDay + $oneDay * 14 AND event = 2";
	$stmt = $mysql->query($query);
	$count_players_2 = $stmt->rowCount();
	
	$query = "SELECT DISTINCT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay AND tvactivity.timestamp > $thisDay + $oneDay * 14 AND tvactivity.timestamp < $thisDay + $oneDay * 21 AND event = 2";
	$stmt = $mysql->query($query);
	$count_players_3 = $stmt->rowCount();
	
	$query = "SELECT DISTINCT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay AND tvactivity.timestamp > $thisDay + $oneDay * 21 AND tvactivity.timestamp < $thisDay + $oneDay * 28 AND event = 2";
	$stmt = $mysql->query($query);
	$count_players_4 = $stmt->rowCount();
	
	$query = "SELECT DISTINCT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay AND tvactivity.timestamp > $thisDay + $oneDay * 28 AND tvactivity.timestamp < $thisDay + $oneDay * 35 AND event = 2";
	$stmt = $mysql->query($query);
	$count_players_5 = $stmt->rowCount();
	
	$query = "SELECT DISTINCT uuid FROM tvactivity LEFT JOIN pt_users ON tvactivity.uuid = pt_users.activedevice WHERE pt_users.timestamp > $thisDay AND pt_users.timestamp < $thisDay + $oneDay AND tvactivity.timestamp > $thisDay + $oneDay * 35 AND tvactivity.timestamp < $thisDay + $oneDay * 42 AND event = 2";
	$stmt = $mysql->query($query);
	$count_players_6 = $stmt->rowCount();
	
	
	
	
	$query = "SELECT uuid FROM tvactivity WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND event = 2";
	
	
	
	echo '<tr><td>'. date("m/d/Y", $thisDay) .'</td><td>'. number_format($count_players) .'</td><td>'. number_format($count_players_1) .'</td><td>'. number_format($count_players_2) .'</td><td>'. number_format($count_players_3) .'</td><td>'. number_format($count_players_4) .'</td><td>'. number_format($count_players_5) .'</td><td>'. number_format($count_players_6) .'</td></tr>';
	
	$start = $start - $oneDay;
}
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>