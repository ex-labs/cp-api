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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Sales Stats</strong></div>
		<div style="padding: 0 0 5px 0; text-align: right;">Last 30 days</div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 950px;">
            <thead>
            <tr>
                <th width="150">Date</th>
                <th width="100">Apple #</th>
                <th width="100">Apple $</th>
                <th width="100">Android #</th>
                <th width="100">Android $</th>
                <th width="100">Windows #</th>
                <th width="100">Windows $</th>
                <th width="100">Total #</th>
                <th width="100">Total $</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$oneDay = 3600 * 24;
$start = $now;

$total_apple_count = 0;
$total_apple_amt = 0;
$total_android_count = 0;
$total_android_amt = 0;
$total_windows_count = 0;
$total_windows_amt = 0;

for($i=0; $i<30; $i++) {
	$thisDay = strtotime(date("F j, Y", $start));
	$query = "SELECT * FROM pt_adcost WHERE addate = $thisDay";
	
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$total_apple_count = $total_apple_count + $row[sales_apple_count];
	$total_apple_amt = $total_apple_amt + $row[sales_apple_amount];
	$total_android_count = $total_android_count + $row[sales_android_count];
	$total_android_amt = $total_android_amt + $row[sales_android_amount];
	$total_windows_count = $total_windows_count + $row[sales_windows_count];
	$total_windows_amt = $total_windows_amt + $row[sales_windows_amount];
	
	if(date("D",$start) == "Sun") {
		$rowbg = "#288d00";
	} else {
		$rowbg = "#000000";
	}
	echo '<tr><td><span style="color:'. $rowbg .'; font-weight:bold;">'. date("m/d/Y", $thisDay) .'</span></td><td>'. number_format($row[sales_apple_count]) .'</td><td>$'. number_format($row[sales_apple_amount], 2, '.', '') .'</td><td>'. number_format($row[sales_android_count]) .'</td><td>$'. number_format($row[sales_android_amount], 2, '.', '') .'</td><td>'. number_format($row[sales_windows_count]) .'</td><td>$'. number_format($row[sales_windows_amount], 2, '.', '') .'</td><td>'. number_format($row[sales_apple_count] + $row[sales_android_count] + $row[sales_windows_count]) .'</td><td>$'. number_format($row[sales_apple_amount] + $row[sales_android_amount] + $row[sales_windows_amount], 2, '.', '') .'</td></tr>';
	
	$start = $start - $oneDay;
}

echo '<tr><td><strong>Total:</strong></td><td><strong>'. number_format($total_apple_count) .'</strong></td><td><strong>$'. number_format($total_apple_amt, 2, '.', '') .'</strong></td><td><strong>'. number_format($total_android_count) .'</strong></td><td><strong>$'. number_format($total_android_amt, 2, '.', '') .'</strong></td><td><strong>'. number_format($total_windows_count) .'</strong></td><td><strong>$'. number_format($total_windows_amt, 2, '.', '') .'</strong></td><td><strong>'. number_format($total_apple_count + $total_android_count + $total_windows_count) .'</strong></td><td><strong>$'. number_format($total_apple_amt + $total_android_amt + $total_windows_amt, 2, '.', '') .'</strong></td></tr>';
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>