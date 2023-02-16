<?php
include('includes/config.php');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow">
<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<title>Data Management Thingy</title>
<link rel="stylesheet" href="css/pe.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/tables.css" type="text/css" media="all" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $.tablesorter.defaults.sortList = [[1,0]]; 
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
    
    <div style="padding: 0 0 10px 0;">
		<a href="act-editmovie.php"><input type="submit" value="Add New Movie" class="rounded"></a>
	</div>
    
    <table class="tablesorter" border="0" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th width="70">Video ID</th>
                <th width="400">Title</th>
                <th width="70">Year</th>
                <th width="100">Active</th>
                <th width="200"></th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$query = "SELECT * FROM movies ORDER BY title";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$active = "No";
	if(($row[popcornactive] > 0) || ($row[popcornseriesactive] > 0) || ($row[popcornbonus] > 0)) {
		$active = "Active";
	}
	
	echo '<tr><td>'. $row[id] .'</td><td><a href="act-editmovie.php?movieid='. $row[id] .'">'. $row[title] .'</a></td><td>'. $row[year] .'</td><td>'. $active .'</td><td><a href="dashboard-moviemaster.php?movieid='. $row[id] .'">PopcornTrivia Game</a></td></tr>';
}
?>
		</tbody>
    </table>
	</div>    
</div>
</body>
</html>