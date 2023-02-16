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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Ingame Click Activity</strong></div>
		<div style="padding: 0 0 5px 0; text-align: right;">Last 30 days</div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 550px;">
            <thead>
            <tr>
                <th width="150">Date</th>
                <th width="100">Shopping</th>
                <th width="100">IDs</th>
                <th width="100">Recipe</th>
                <th width="100">Dilemma</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$oneDay = 3600 * 24;
$start = $now;

$total_shopping = 0;
$total_ids = 0;
$total_recipe = 0;
$total_dilemma = 0;

for($i=0; $i<30; $i++) {
	$thisDay = strtotime(date("F j, Y", $start));
	
	$query = "SELECT uid FROM tvlogs WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND eventid = 102";
	$stmt = $mysql->query($query);
	$count_shopping = $stmt->rowCount();
	
	$query = "SELECT uid FROM tvlogs WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND eventid = 103";
	$stmt = $mysql->query($query);
	$count_ids = $stmt->rowCount();
	
	$query = "SELECT uid FROM tvlogs WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND eventid = 114";
	$stmt = $mysql->query($query);
	$count_recipe = $stmt->rowCount();
	
	$query = "SELECT uid FROM tvlogs WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND eventid = 106";
	$stmt = $mysql->query($query);
	$count_dilemma = $stmt->rowCount();
	
	$total_shopping = $total_shopping + $count_shopping;
	$total_ids = $total_ids + $count_ids;
	$total_recipe = $total_recipe + $count_recipe;
	$total_dilemma = $total_dilemma + $count_dilemma;
	
	if(date("D",$start) == "Sun") {
		$rowbg = "#288d00";
	} else {
		$rowbg = "#000000";
	}
	echo '<tr><td><span style="color:'. $rowbg .'; font-weight:bold;">'. date("m/d/Y", $thisDay) .'</span></td><td>'. number_format($count_shopping) .'</td><td>'. number_format($count_ids) .'</td><td>'. number_format($count_recipe) .'</td><td>'. number_format($count_dilemma) .'</td></tr>';
	
	$start = $start - $oneDay;
}

echo '<tr><td><strong>Total:</strong></td><td><strong>'. number_format($total_shopping) .'</strong></td><td><strong>'. number_format($total_ids) .'</strong></td><td><strong>'. number_format($total_recipe) .'</strong></td><td><strong>'. number_format($total_dilemma) .'</strong></td></tr>';
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>