<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();

if($_POST[action] == 1) {
	$query = "INSERT INTO pt_sponsor_reserved (itunesid, title, year) VALUES (?, ?, ?)";
	$params = array($_POST[itunesid], $_POST[title], $_POST[year]);
	$stmt = $mysql->prepare($query);
	$stmt->execute($params);
}
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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Movies Excluded from Voting</strong></div>
		<div style="padding: 30px 0 10px 0;">
        	<div style="padding: 0 0 5px 0; font-weight: bold;">Add New Movie to the List</div>
            <form action="vote-excluded.php" method="post">
            <input type="hidden" name="action" value="1" />
            <div class="record">
            	<input type="text" name="title" style="width: 480px;" /><span style="padding:10px;"></span>
                <input type="text" name="year" style="width: 90px;" /><span style="padding:10px;"></span>
                <input type="text" name="itunesid" style="width: 90px;" />
            </div>
            <div class="record" style="width: 740px; text-align: right;">
            	<input type="submit" value="Add!" class="rounded">
            </div>
            </form>
        </div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 750px;">
            <thead>
            <tr>
                <th width="550">Title</th>
                <th width="100">Year</th>
                <th width="100">iTunes ID</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$query = "SELECT * FROM pt_sponsor_reserved WHERE 1 ORDER BY title";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	echo '<tr><td>'. $row[title] .'</td><td>'. $row[year] .'</td><td>'. $row[itunesid] .'</td></tr>';
}
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>