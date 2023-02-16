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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Notepad</strong></div>
<?php
$query = "SELECT uuid, movieid, act FROM tvactivity WHERE timestamp > 1484460000 + 3600*18 AND timestamp < 1484460000 + 3600*19 AND event = 1";
$stmt = $mysql->query($query);
$count = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$subquery = "SELECT uuid FROM tvactivity WHERE timestamp > 1484460000 AND uuid = '$row[uuid]' AND movieid = $row[movieid] AND act = $row[act] AND event = 2";
	$substmt = $mysql->query($subquery);
	$count_finished = $substmt->rowCount();
	if($count_finished > 0) $count++;
}

echo "completed count = ". $count;


?>
        
	</div>    
</div>
</body>
</html>