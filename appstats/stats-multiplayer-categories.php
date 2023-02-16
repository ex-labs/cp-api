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
    $("#datepicker1").datepicker();
	$("#datepicker2").datepicker();
});
$(document).ready(function() {
	$( "#timespan" ).change(function() {
		$("#datepicker1").val("");
		$("#datepicker2").val("");
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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Multi-Player by Category Stats</strong></div>
        
        <form action="stats-multiplayer-categories.php" method="post">
        <div style="width: 600px; padding: 0 0 5px 0; text-align: right;">
        	<span style="padding: 0 3px 0 0; font-weight: bold;">From:</span><input type="text" style="width: 80px;" name="dateFrom" value="<?php echo $_POST[dateFrom];?>" id="datepicker1" />
            <span style="padding: 0 3px 0 0; font-weight: bold;">To:</span><input type="text" style="width: 80px;" name="dateTo" value="<?php echo $_POST[dateTo];?>" id="datepicker2" />
            <span style="padding: 0 40px 0 40px; font-weight: bold;"> - OR - </span>
        	<select name="timespan" id="timespan">
            	<option value="0"<?php if($_POST[timespan] == 0) echo ' selected="selected"';?>>Today</option>
                <option value="1"<?php if($_POST[timespan] == 1) echo ' selected="selected"';?>>Yesterday</option>
                <option value="7"<?php if($_POST[timespan] == 7) echo ' selected="selected"';?>>Last 7 Days</option>
                <option value="30"<?php if($_POST[timespan] == 30) echo ' selected="selected"';?>>Last 30 Days</option>
                <option value="1000"<?php if($_POST[timespan] == 1000) echo ' selected="selected"';?>>Lifetime</option>
                <?php if($_POST[dateFrom]) echo '<option value="99999" selected="selected">Custom</option>';?>
            </select>
            <input type="submit" value="Update" class="rounded">
        </div>
        </form>

        <div style="padding: 0 0 5px 0; text-align: right;">Last 75 days</div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 400px;">
            <thead>
            <tr>
                <th width="250">Category</th>
                <th width="150">Games</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
if($_POST[dateFrom] && $_POST[dateTo]) {
	$_POST[timespan] = 99999;
	$thisDay = strtotime($_POST[dateTo]);
	$start = strtotime($_POST[dateFrom]);
} else {
	if(!$_POST[timespan]) $_POST[timespan] = 0;
	$_POST[dateFrom] = "";
	$_POST[dateTo] = "";
	$oneDay = 3600 * 24;
	$thisDay = strtotime(date("F j, Y", $now));
	$start = $thisDay - 3600 * 24 * $_POST[timespan];
}


if($_POST[timespan] == 99999) {
	$query = "SELECT pt_gamedata.category AS catid, challenge_categories.category AS category, count(*) as count FROM `pt_gamedata` LEFT JOIN challenge_categories ON pt_gamedata.category = challenge_categories.categoryid WHERE statusOne > 0 AND statusTwo > 0 AND timestamp > $start AND timestamp < $thisDay GROUP BY catid ORDER BY count DESC";
} else if($_POST[timespan] == 1) {
	$query = "SELECT pt_gamedata.category AS catid, challenge_categories.category AS category, count(*) as count FROM `pt_gamedata` LEFT JOIN challenge_categories ON pt_gamedata.category = challenge_categories.categoryid WHERE statusOne > 0 AND statusTwo > 0 AND timestamp > $start AND timestamp < $thisDay GROUP BY catid ORDER BY count DESC";
} else {
	$query = "SELECT pt_gamedata.category AS catid, challenge_categories.category AS category, count(*) as count FROM `pt_gamedata` LEFT JOIN challenge_categories ON pt_gamedata.category = challenge_categories.categoryid WHERE statusOne > 0 AND statusTwo > 0 AND timestamp > $start GROUP BY catid ORDER BY count DESC";
}

$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	echo '<tr><td>'. $row[category] .'</td><td>'. number_format($row['count']) .'</td></tr>';
}


?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>