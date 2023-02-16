<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();

if(($_POST[action] == 1) && ($_POST[old_uid] > 0) && ($_POST[new_uid] > 0)) {
	$query = "SELECT * FROM pt_users WHERE uid = $_POST[old_uid]";
	$stmt = $mysql->query($query);
	$old_user = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$query = "SELECT * FROM pt_users WHERE uid = $_POST[new_uid]";
	$stmt = $mysql->query($query);
	$new_user = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if(!$old_user[uid]) {
		$message = "Invalid Old UID....";
	} else if(!$new_user[uid]) {
		$message = "Invalid New UID....";
	} else {
	
		$query = "UPDATE pt_users SET name = 'Deleted $now' WHERE uid = $old_user[uid]";
		$result = $mysql->exec($query);
		
		
		$query = "UPDATE pt_users SET legacy_uid	 = '$old_user[legacy_uid]', activedevice = '$old_user[activedevice]', name = '$old_user[name]', name_updated = '$old_user[name_updated]', timestamp = '$old_user[timestamp]', popcorn = '$old_user[popcorn]', sp_points = '$old_user[sp_points]', mp_points = '$old_user[mp_points]', mp_games = '$old_user[mp_games]', mp_wins = '$old_user[mp_wins]', mp_draws = '$old_user[mp_draws]', pops_backdrop = '$old_user[pops_backdrop]', pops_body = '$old_user[pops_body]', pops_eyes = '$old_user[pops_eyes]', pops_mouth = '$old_user[pops_mouth]', pops_hat = '$old_user[pops_hat]', pops_left_arm = '$old_user[pops_left_arm]', pops_right_arm = '$old_user[pops_right_arm]', pops_weapon = '$old_user[pops_weapon]' WHERE uid = $new_user[uid]";
		$result = $mysql->exec($query);
		
		$query = "DELETE FROM pt_acts WHERE uid = $new_user[uid]";
		$result = $mysql->exec($query);
		
		$query = "DELETE FROM pt_movies_purchased WHERE uid = $new_user[uid]";
		$result = $mysql->exec($query);
		
		$query = "DELETE FROM pt_avatar_purchased WHERE uid = $new_user[uid]";
		$result = $mysql->exec($query);
		
		
		$query = "UPDATE pt_acts SET uid = $new_user[uid] WHERE uid = $old_user[uid]";
		$result = $mysql->exec($query);
		
		$query = "UPDATE pt_movies_purchased SET uid = $new_user[uid] WHERE uid = $old_user[uid]";
		$result = $mysql->exec($query);
		
		$query = "UPDATE pt_avatar_purchased SET uid = $new_user[uid] WHERE uid = $old_user[uid]";
		$result = $mysql->exec($query);
		
		$query = "UPDATE pt_gifts SET uid = $new_user[uid] WHERE uid = $old_user[uid]";
		$result = $mysql->exec($query);
		
		$message = "Done...";
	}
} else {
	$message = "Hmm... Something is not right. Please check UIDs...";
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
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Character Transfer</strong></div>
		<div style="padding: 30px 0 20px 0; text-align: center; color: #ff0000; font-weight: bold;">DO NOT USE UNLESS YOU'RE ABSOLUTELY SURE YOU KNOW WHAT YOU'RE DOING!</div>
        <?php if($_POST[action] == 1) {?>
        <div style="padding: 20px;"><?php echo $message;?></div>
        <?php } else {?>
        <form action="character-transfer.php" method="post">
        <input type="hidden" name="action" value="1" />
        <div class="record"><label>Old UID:</label><input type="text" name="old_uid" style="width: 120px;" /></div>
        <div class="record"><label>New UID:</label><input type="text" name="new_uid" style="width: 120px;" /></div>
		<div class="record"><label>&nbsp;</label><input type="submit" value="Update" class="rounded"></div>
        </form>
        <?php }?>
	</div>    
</div>
</body>
</html>