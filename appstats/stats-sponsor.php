<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();

if(!$_POST[status]) $_POST[status] = 0;
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
	$("table").tablesorter();
});
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <div class="grad1 rounded module">
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Sponsor/Vote Stats</strong></div>
    	<form action="stats-sponsor.php" method="post">
        <div style="width: 1300px; padding: 0 0 5px 0; text-align: right;">
        	<select name="status">
            	<option value="0"<?php if($_POST[status] == 0) echo ' selected="selected"';?>>Voting</option>
                <option value="1"<?php if($_POST[status] == 1) echo ' selected="selected"';?>>In App</option>
            </select>
            <input type="submit" value="Update" class="rounded">
        </div>
        </form>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 1300px;">
            <thead>
            <tr>
            	<th width="60">&nbsp;</th>
                <th width="350">Title</th>
                <th width="100">Added</th>
                <th width="100">Status</th>
                <th width="180">Sponsor</th>
                <th width="110" class="{sorter: 'digit'}">Pop Votes</th>
                <th width="110" class="{sorter: 'digit'}">Share Votes</th>
                <th width="110" class="{sorter: 'digit'}">Ad Votes</th>
                <th width="110" class="{sorter: 'digit'}">Total Votes</th>
                <th width="110" class="{sorter: 'digit'}">Votes by Me</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$index = 1;
			
if($_POST[status] > 0) {
	$query = "SELECT title, name, pt_sponsor.timestamp, status, vote_pop, vote_social, vote_ad, supporters, mysupport FROM pt_sponsor LEFT JOIN pt_users ON pt_sponsor.uid = pt_users.uid WHERE status > 0 ORDER BY status DESC, supporters DESC";
} else {
	$query = "SELECT title, name, pt_sponsor.timestamp, status, vote_pop, vote_social, vote_ad, supporters, mysupport FROM pt_sponsor LEFT JOIN pt_users ON pt_sponsor.uid = pt_users.uid WHERE status = 0 ORDER BY status DESC, supporters DESC";
}
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	if($row[status] == 0) $status = "Voting";
	else if($row[status] == 1) $status = "In Prod";
	else $status = "In App";
	
	echo '<tr><td>'. $index .'.</td><td>'. $row[title] .'</td><td>'. date("m/d/Y", $row[timestamp]) .'</td><td>'. $status .'</td><td>'. $row[name] .'</td><td>'. $row[vote_pop] .'</td><td>'. $row[vote_social] .'</td><td>'. $row[vote_ad] .'</td><td>'. $row[supporters] .'</td><td>'. $row[mysupport] .'</td></tr>';
	$index++;
}
?>
		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>