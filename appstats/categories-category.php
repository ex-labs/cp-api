<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


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
    $('.updateone').change(function(event) {
		var myid = event.target.id;
		var parts = myid.split(":");
		var movie = parts[0];
		var category = parts[1];
		var state = "off";
		var change = -1;
	
		if($(this).is(':checked')) {
			state = "on";
			change = 1;
		}
	
		var url = "ajax/update-category.php";
		var params = {
			movieid: movie,
			category: category,
			state: state
		};
		$.ajax({
			type: 'POST',
			url: url,
			data: params,
			success: function(res) { },
			error: function() { alert('Ooops... Something went wrong!'); }			
		});
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
    
    <div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / <a href="/appstats/categories.php">Category Manager</a> / <?php echo urldecode($_GET[category]);?></strong></div>

    <table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 600px;">
        <thead>
            <tr>
            	<th width="400">Movie</th>
            	<th width="200">Status</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$query = "SELECT * FROM movies WHERE parentid = 0 ORDER BY title";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$subquery = "SELECT * FROM challenge_categories_mov WHERE mid = $row[id] AND categoryid = $_GET[categoryid]";
	$stmt2 = $mysql->query($subquery);
	$count = $stmt2->rowCount();
	if($count > 0) $check = 'checked';
	else $check = '';
	
	echo '<tr><td>'. $row[title] .'</td><td><input class="updateone" type="checkbox" id="'. $row[id] .':'. $_GET[categoryid] .'" '. $check .' /></td></tr>';
}
?>
		</tbody>
    </table>
	</div>    
</div>
</body>
</html>