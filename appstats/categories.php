<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);

if($_POST[action] == 1) {
	$query = "SELECT categoryid FROM challenge_categories WHERE 1 ORDER BY categoryid DESC LIMIT 1";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$catid = $row[categoryid] + 1;
	$params = array($catid,0,$_POST[catName]);
	
	$query = "INSERT INTO challenge_categories (categoryid, active, category) VALUES (?,?,?)";
	$stmt = $mysql->prepare($query);
	$stmt->execute($params);
}



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
    $("#movieid").change(function() {
		$("#movietitle").val($("#movieid option:selected").text());
	});
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

	<form action="categories-movie.php" method="get">
    <?php
	$query = "SELECT * FROM movies WHERE parentid = 0 ORDER BY title";
	$stmt = $mysql->query($query);
	$count = $stmt->rowCount();
	echo '<strong style="float: left; padding: 7px 0 0 0;">'. $count .' Movies</strong>';	
	?>
    	<input type="hidden" name="title" id="movietitle" />
        <div style="width: 600px; padding: 0 0 5px 0; text-align: right;">
        	<select name="movieid" id="movieid">
            	<?php
				while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					echo '<option value="'. $row[id] .'">'. $row[title] .' ('. $row[year] .')</option>';
				}
				?>
            </select>
            <input type="submit" value="Edit Movie" class="rounded">
        </div>
    </form>
    
    <div style="float:right;">
    <table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 600px;">
        <thead>
            <tr>
            	<th width="600">Add New Category</th>
            </tr>
        </thead>
        <tbody class="zebra">
        <tr><td>
        <form action="categories.php" method="post">
        	<input type="hidden" name="action" value="1" />
            <div class="record"><label>Category Name:</label><input type="text" name="catName" style="width: 300px;" /></div>
        	<div class="record"><label>&nbsp;</label><input type="submit" value="Add Category" class="rounded"></div>
        
        </form>
        </td></tr>
        </tbody>
    </table>
    </div>
        
    <table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 600px;">
        <thead>
            <tr>
            	<th width="400">Category</th>
            	<th width="200">Movies</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$query = "SELECT category, COUNT(*) as count, challenge_categories.categoryid, mid FROM challenge_categories LEFT JOIN challenge_categories_mov ON challenge_categories.categoryid = challenge_categories_mov.categoryid GROUP BY challenge_categories_mov.categoryid ORDER BY challenge_categories.categoryid";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$count = $row[count];
	if(!$row[mid]) $count = 0;
    echo '<tr><td><a href="categories-category.php?categoryid='. $row[categoryid] .'&category='. urlencode($row[category]) .'">'. $row[category] .'</td><td>'. $count .'</td></tr>';
}
?>
		</tbody>
    </table>
	</div>    
</div>
</body>
</html>