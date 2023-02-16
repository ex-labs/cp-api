<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time() + 3600;
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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Push Messages</strong></div>
	    <div style="width: 1200px; padding: 0 0 5px 0; text-align: right;">
        	<strong><a href="messaging-edit.php">Schedule New Message</a></strong>
        </div>

		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 1200px;">
            <thead>
            <tr>
                <th width="150">Scheduled</th>
                <th width="120">Status</th>
                <th width="750">Message</th>
                <th width="60">Apple</th>
                <th width="60">Droid</th>
                <th width="60">Win</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php

$query = "SELECT * FROM pt_scheduled WHERE 1 ORDER BY timestamp DESC";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	if($row[timestamp] > $now + 60) $status = "<a href='messaging-edit.php?sid=$row[sid]'>Scheduled</a>";
	else if($row[timestamp] > $now - 60) $status = "Processing";
	else $status = "Sent";
	$scheduled = date("m/d/Y h:iA", $row[timestamp]);
	
	$apple = "No";
	$droid = "No";
	$win = "No";
	if($row[target_apple] == 1) $apple = "Yes";
	if($row[target_android] == 1) $droid = "Yes";
	if($row[target_windows] == 1) $win = "Yes";
	
	echo '<tr><td>'. $scheduled .'</td><td>'. $status .'</td><td>'. $row[message] .'</td><td>'. $apple .'</td><td>'. $droid .'</td><td>'. $win .'</td></tr>';
}
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>