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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Cost Stats</strong></div>
		<div style="padding: 0 0 5px 0; text-align: right;">Last 60 days</div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 750px;">
            <thead>
            <tr>
                <th width="150">Date</th>
                <th width="100">Apple FB</th>
                <th width="100">Apple APPL</th>
                <th width="100">Android FB</th>
                <th width="100">Windows MS</th>
                <th width="100">Windows FB</th>
                <th width="100">Total</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$oneDay = 3600 * 24;
$start = $now;

$total_apple_fb = 0;
$total_apple_apple = 0;
$total_android_fb = 0;

for($i=0; $i<60; $i++) {
	$thisDay = strtotime(date("F j, Y", $start));
	$query = "SELECT * FROM pt_adcost WHERE addate = $thisDay";
	
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$total_apple_fb = $total_apple_fb + $row[apple_fb];
	$total_apple_apple = $total_apple_apple + $row[apple_apple];
	$total_android_fb = $total_android_fb + $row[android_fb];
	$total_windows_fb = $total_windows_fb  + $row[windows_fb];
	$total_windows_ms = $total_windows_ms + $row[windows];
	$total = $row[apple_fb] + $row[apple_apple] + $row[android_fb] + $row[windows_fb] + $row[windows];
	
	if(date("D",$start) == "Sun") {
		$rowbg = "#288d00";
	} else {
		$rowbg = "#000000";
	}
	echo '<tr><td><span style="color:'. $rowbg .'; font-weight:bold;">'. date("m/d/Y", $thisDay) .'</span></td><td>$'. number_format($row[apple_fb], 2, '.', '') .'</td><td>$'. number_format($row[apple_apple], 2, '.', '') .'</td><td>$'. number_format($row[android_fb], 2, '.', '') .'</td><td>$'. number_format($row[windows], 2, '.', '') .'</td><td>$'. number_format($row[windows_fb], 2, '.', '') .'</td><td>$'. number_format($total, 2, '.', '') .'</td></tr>';
	
	$start = $start - $oneDay;
}

echo '<tr><td><strong>Total:</strong></td><td><strong>$'. number_format($total_apple_fb, 2, '.', '') .'</strong></td><td><strong>$'. number_format($total_apple_apple, 2, '.', '') .'</strong></td><td><strong>$'. number_format($total_android_fb, 2, '.', '') .'</strong></td><td><strong>$'. number_format($total_windows_ms, 2, '.', '') .'</strong></td><td><strong>$'. number_format($total_windows_fb, 2, '.', '') .'</strong></td><td><strong>$'. number_format($total_apple_fb + $total_apple_apple + $total_android_fb + $total_windows_ms + $total_windows_fb, 2, '.', '') .'</strong></td></tr>';
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>