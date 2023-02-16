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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Overview</strong></div>

        <div style="padding: 0 0 5px 0; text-align: right;">Last 30 days</div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th colspan="5"><div style="text-align:center;">Users</div></th>
                <th colspan="5"><div style="text-align:center;">Single Player</div></th>
                <th colspan="5"><div style="text-align:center;">Multi-Player</div></th>
            </tr>
            <tr>
                <th width="70">Date</th>
                <th width="70">New Users</th>
                <th width="90">Active</th>
                <th width="70">%</th>
                <th width="70">Cost</th>
                <th width="70">Cost/A</th>
                <th width="90">All Users</th>
                <th width="90">Sum Users</th>
                <th width="70">%</th>
                <th width="70">Cost</th>
                <th width="70">Cost/A</th>
                <th width="70">Users</th>
                <th width="90">Active</th>
                <th width="70">%</th>
                <th width="70">Cost</th>
                <th width="70">Cost/A</th>
                <th width="70">Users</th>
                <th width="90">Active</th>
                <th width="70">%</th>
                <th width="70">Cost</th>
                <th width="70">Cost/A</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$oneDay = 3600 * 24;
$start = $now;

$total_android = 0;
$total_apple = 0;
$total_windows = 0;
$total_android_active = 0;
$total_apple_active = 0;
$total_windows_active = 0;
$total_android_cost = 0;
$total_apple_cost = 0;
$total_windows_cost = 0;

for($i=0; $i<30; $i++) {
	$thisDay = strtotime(date("F j, Y", $start));

	$query = "SELECT * FROM pt_adcost WHERE addate = $thisDay";
	$stmt = $mysql->query($query);
	$costs = $stmt->fetch(PDO::FETCH_ASSOC);

	$query = "SELECT uid FROM pt_users WHERE legacy_uid = 0 AND timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND (length(activedevice) < 17 AND length(activedevice) > 12)";
	$stmt = $mysql->query($query);
	$count_android_all = $stmt->rowCount();
	
	$query = "SELECT uid FROM pt_users WHERE legacy_uid = 0 AND timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND (sp_points > 0 OR mp_points > 0) AND (length(activedevice) < 17 AND length(activedevice) > 12)";
	$stmt = $mysql->query($query);
	$count_android_active = $stmt->rowCount();
	
	
	$query = "SELECT uid FROM pt_users WHERE legacy_uid = 0 AND timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND length(activedevice) = 36";
	$stmt = $mysql->query($query);
	$count_apple_all = $stmt->rowCount();
	
	$query = "SELECT uid FROM pt_users WHERE legacy_uid = 0 AND timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND (sp_points > 0 OR mp_points > 0) AND length(activedevice) = 36";
	$stmt = $mysql->query($query);
	$count_apple_active = $stmt->rowCount();
	
	
	$query = "SELECT uid FROM pt_users WHERE legacy_uid = 0 AND timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND length(activedevice) = 32";
	$stmt = $mysql->query($query);
	$count_windows_all = $stmt->rowCount();
	
	$query = "SELECT uid FROM pt_users WHERE legacy_uid = 0 AND timestamp > $thisDay AND timestamp < $thisDay + $oneDay AND (sp_points > 0 OR mp_points > 0) AND length(activedevice) = 32";
	$stmt = $mysql->query($query);
	$count_windows_active = $stmt->rowCount();
	
	$ratio = 100 * $count_android_active / $count_android_all;
	
	echo '<tr><td style="border-right: 1px solid #8c8c8c;">'. date("m/d/Y", $thisDay) .'</td><td>'. $count_android_all .'</td><td>'. $count_android_active .'</td><td>'. number_format($ratio) .'%</td><td>';
	if($costs[android_fb] > 0) {
		$number = $costs[android_fb] / $count_android_active;
		echo '$'. $costs[android_fb] .'</td><td style="border-right: 1px solid #8c8c8c;">$'. number_format($number, 2, '.', '') .'</td>';
	} else {
		echo '$0.00</td><td style="border-right: 1px solid #8c8c8c;">N/A</td>';
	}
	
	$ratio = 100 * $count_apple_active / $count_apple_all;
	echo '<td>'. $count_apple_all .'</td><td>'. $count_apple_active .'</td><td>'. number_format($ratio) .'%</td><td>';
	if(($costs[apple_fb] + $costs[apple_apple]) > 0) {
		$number = ($costs[apple_fb] + $costs[apple_apple]) / $count_apple_active;
		echo '$'. ($costs[apple_fb] + $costs[apple_apple]) .'</td><td style="border-right: 1px solid #8c8c8c;">$'. number_format($number, 2, '.', '') .'</td>';
	} else {
		echo '$0.00</td><td style="border-right: 1px solid #8c8c8c;">N/A</td>';
	}
	
	$ratio = 100 * $count_windows_active / $count_windows_all;
	echo '<td>'. $count_windows_all .'</td><td>'. $count_windows_active .'</td><td>'. number_format($ratio) .'%</td><td>';
	if($costs[windows] > 0) {
		$number = $costs[windows] / $count_windows_active;
		echo '$'. $costs[windows] .'</td><td style="border-right: 1px solid #8c8c8c;">$'. number_format($number, 2, '.', '') .'</td>';
	} else {
		echo '$0.00</td><td style="border-right: 1px solid #8c8c8c;">N/A</td>';
	}
	
	$count_all = $count_android_all + $count_apple_all + $count_windows_all;
	$count_active = $count_android_active + $count_apple_active + $count_windows_active;
	$ratio = 100 * $count_active / $count_all;
	echo '<td>'. $count_all .'</td><td>'. $count_active .'</td><td>'. number_format($ratio) .'%</td><td>';
	if(($costs[android_fb] + $costs[apple_fb] + $costs[apple_apple] + $costs[windows]) > 0) {
		$number = ($costs[android_fb] + $costs[apple_fb] + $costs[apple_apple] + $costs[windows]) / $count_active;
		echo '$'. ($costs[android_fb] + $costs[apple_fb] + $costs[apple_apple] + $costs[windows]) .'</td><td>$'. number_format($number, 2, '.', '') .'</td>';
	} else {
		echo '$0.00</td><td>N/A</td>';
	}
	
	echo '</tr>';
	
	
	$total_android_all = $total_android_all + $count_android_all;
	$total_apple_all = $total_apple_all + $count_apple_all;
	$total_windows_all = $total_windows_all + $count_windows_all;
	$total_android_active = $total_android_active + $count_android_active;
	$total_apple_active = $total_apple_active + $count_apple_active;
	$total_windows_active = $total_windows_active + $count_windows_active;
	$total_android_cost = $total_android_cost + $costs[android_fb];
	$total_apple_cost = $total_apple_cost + $costs[apple_fb] + $costs[apple_apple]; 
	$total_windows_cost = $total_windows_cost + $costs[windows];
	
	$start = $start - $oneDay;
}
$ratio = 100 * $total_android_active / $total_android_all;
echo '<tr><td style="border-right: 1px solid #8c8c8c;"><strong>Total:</strong></td><td><strong>'. $total_android_all .'</strong></td><td><strong>'. $total_android_active .'</td><td><strong>'. number_format($ratio) .'%</strong></td><td><strong>';
$number = $total_android_cost / $total_android_active;
echo '$'. number_format($total_android_cost, 2, '.', ',') .'</strong></td><td style="border-right: 1px solid #8c8c8c;"><strong>$'. number_format($number, 2, '.', '') .'</strong></td>';

$ratio = 100 * $total_apple_active / $total_apple_all;
echo '<td><strong>'. $total_apple_all .'</strong></td><td><strong>'. $total_apple_active .'</td><td><strong>'. number_format($ratio) .'%</strong></td><td><strong>';
$number = $total_apple_cost / $total_apple_active;
echo '$'. number_format($total_apple_cost, 2, '.', ',') .'</strong></td><td style="border-right: 1px solid #8c8c8c;"><strong>$'. number_format($number, 2, '.', '') .'</strong></td>';

$ratio = 100 * $total_windows_active / $total_windows_all;
echo '<td><strong>'. $total_windows_all .'</strong></td><td><strong>'. $total_windows_active .'</strong></td><td><strong>'. number_format($ratio) .'%</strong></td><td><strong>';
$number = $total_windows_cost / $total_windows_active;
echo '$'. number_format($total_windows_cost, 2, '.', ',') .'</strong></td><td style="border-right: 1px solid #8c8c8c;"><strong>$'. number_format($number, 2, '.', '') .'</strong></td>';

$total_all = $total_android_all + $total_apple_all + $total_windows_all;
$total_active = $total_android_active + $total_apple_active + $total_windows_active;
$total_cost = $total_android_cost + $total_apple_cost + $total_windows_cost;

$ratio = 100 * $total_active / $total_all;
echo '<td><strong>'. $total_all .'</strong></td><td><strong>'. $total_active .'</strong></td><td><strong>'. number_format($ratio) .'%</strong></td><td><strong>';
$number = $total_cost / $total_active;
echo '$'. number_format($total_cost, 2, '.', ',') .'</strong></td><td><strong>$'. number_format($number, 2, '.', '') .'</strong></td>';
	
echo '</tr>';
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>