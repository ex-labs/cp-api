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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Movie Stats</strong></div>
		<form action="stats-movies-started.php" method="post">
        <div style="width: 600px; padding: 0 0 5px 0; text-align: right;">
        	<select name="timespan">
            	<option value="0"<?php if($_POST[timespan] == 0) echo ' selected="selected"';?>>Today</option>
                <option value="1"<?php if($_POST[timespan] == 1) echo ' selected="selected"';?>>Yesterday</option>
                <option value="7"<?php if($_POST[timespan] == 7) echo ' selected="selected"';?>>Last 7 Days</option>
                <option value="30"<?php if($_POST[timespan] == 30) echo ' selected="selected"';?>>Last 30 Days</option>
                <option value="1000"<?php if($_POST[timespan] == 1000) echo ' selected="selected"';?>>Lifetime</option>
            </select>
            <input type="submit" value="Update" class="rounded">
        </div>
        </form>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 600px;">
            <thead>
            <tr>
            	<th width="30">#</th>
            	<th width="70">Acts</th>
                <th width="500">Movie</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
if(!$_POST[timespan]) $_POST[timespan] = 0;
$oneDay = 3600 * 24;
$thisDay = strtotime(date("F j, Y", $now));
$start = $thisDay - 3600 * 24 * $_POST[timespan];

$movies = array();
$query = "SELECT id, title FROM movies WHERE 1";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$index = $row[id];
	$movies[$index] = $row[title];
}

$total_acts = 0;
if($_POST[timespan] == 1) {
	$query = "SELECT movieid, count(*) as count FROM `tvactivity` WHERE event = 1 AND timestamp > $start AND timestamp < $thisDay GROUP BY movieid ORDER BY count DESC";
} else {
	$query = "SELECT movieid, count(*) as count FROM `tvactivity` WHERE event = 1 AND timestamp > $start GROUP BY movieid ORDER BY count DESC";
}

$stmt = $mysql->query($query);
$order = 1;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$index = $row[movieid];
	echo '<tr><td>'. $order .'</td><td>'. number_format($row['count']) .'</td><td>'. $movies[$index] .'</td></tr>';
	$total_acts = $total_acts + $row['count'];
	$order++;
}

echo '<tr><td>&nbsp;</td><td><strong>'. number_format($total_acts) .'</strong></td><td><strong>Total acts from all movies</strong></td></tr>';
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>