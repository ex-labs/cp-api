<?php
$host = "64.91.249.141";
$db = "custompl_971786_db";
$user = "custompl_db";
$passwd = "Cust0mP1ay#)";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);

$categories = array();
$catheaders = "";
$query = "SELECT * FROM challenge_categories WHERE 1 ORDER BY categoryid";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$query = "SELECT id FROM movies JOIN challenge_categories_mov ON movies.id = challenge_categories_mov.mid WHERE categoryid = $row[categoryid]";
	$stmt2 = $mysql->query($query);
	$count = $stmt2->rowCount();
	
	$catheaders .= '<th width="33"><div class="rotate">'. $row[abreviation] .' (<span class="count_'. $row[categoryid] .'" id="count_'. $row[categoryid] .'">'. $count .'</span>)</div></th>';
	$categories[] = $row[categoryid];
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow">
<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<title>Category Management Thingy</title>
<link rel="stylesheet" href="css/pe.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/tables.css" type="text/css" media="all" />
<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    
});
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <div class="grad1 rounded module">
    
    <div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Category Manager</strong></div>

    <table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 600px;">
        <thead>
            <tr>
            	<th width="400">Category</th>
            	<th width="200">Movies</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$query = "SELECT category, COUNT(*) as count, challenge_categories.categoryid FROM challenge_categories LEFT JOIN challenge_categories_mov ON challenge_categories.categoryid = challenge_categories_mov.categoryid GROUP BY challenge_categories_mov.categoryid";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<tr><td><a href="categories-category.php?categoryid='. $row[categoryid] .'&category='. urlencode($row[category]) .'">'. $row[category] .'</td><td>'. $row[count] .'</td></tr>';
}
?>
		</tbody>
    </table>
	</div>    
</div>
</body>
</html>