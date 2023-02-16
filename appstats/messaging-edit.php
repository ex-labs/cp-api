<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();

if($_POST[action] == "delete") {
	$query = "DELETE FROM pt_scheduled WHERE sid = $_GET[sid]";
	$mysql->query($query);
	
	$url = "messaging.php";
	header("Location: $url");
	exit();
}
if($_POST[action] == "create") {
	if($_GET[sid]) {
		$query = "UPDATE pt_scheduled SET message = ?, timestamp = ?, status = ?, target_apple = ?, target_android = ?, target_windows = ? WHERE sid = $_GET[sid]";
	} else {
		$query = "INSERT INTO pt_scheduled (message, timestamp, status, target_apple, target_android, target_windows) VALUES (?, ?, ?, ?, ?, ?)";
	}
	$stm = $mysql->prepare($query);
	if($_POST[apple] == "on") {
		$apple = 1;
	} else {
		$aple = 0;
	}
	if($_POST[droid] == "on") {
		$droid = 1;
	} else {
		$droid = 0;
	}
	if($_POST[win] == "on") {
		$win = 1;
	} else {
		$win = 0;
	}
	$timestamp = strtotime($_POST['date'] ." ". $_POST['time']);
	$array = array("$_POST[message]","$timestamp","0","$apple","$droid","$win");
	$stm->execute($array);
	
	$url = "messaging.php";
	header("Location: $url");
	exit();
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
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<link rel="stylesheet" href="css/pe.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/tables.css" type="text/css" media="all" />
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(function() {
    $("#datepicker").datepicker();
	$("#timepicker").timepicker({});
});
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <div class="grad1 rounded module">
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / New Push Message</strong></div>
	
<?php
$apple = "Yes";
$droid = "Yes";
$win = "Yes";

if($_GET[sid]) {
	$query = "SELECT * FROM pt_scheduled WHERE sid = $_GET[sid]";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$date = date("m/d/Y", $row[timestamp]);
	$time = date("h:iA", $row[timestamp]);
	if($row[target_apple] == 0) $apple = "No";
	if($row[target_android] == 0) $droid = "No";
	if($row[target_windows] == 0) $win = "No";
} else {
	$date = date("m/d/Y", $now);
	$time = date("h:00A", $now + 3600 * 2);
}
?>
		<form method="post" action="messaging-edit.php?sid=<?php echo $_GET[sid];?>">
        <input type="hidden" name="action" value="create">
        <div class="record"><label>Message:</label><input type="text" name="message" value="<?php echo $row[message];?>" maxlength="255" style="width: 600px;"></div>
        <div class="record"><label>Date:</label><input type="text" id="datepicker" name="date" value="<?php echo $date;?>" style="width: 100px;"></div>
        <div class="record"><label>Time:</label><input type="text" id="timepicker" name="time" value="<?php echo $time;?>" style="width: 100px;"></div>
        <div class="record"><label>Apple:</label><input type="checkbox" name="apple" <?php if($apple == "Yes") echo 'checked';?>></div>
        <div class="record"><label>Android:</label><input type="checkbox" name="droid" <?php if($droid == "Yes") echo 'checked';?>></div>
        <div class="record"><label>Windows:</label><input type="checkbox" name="win" <?php if($win == "Yes") echo 'checked';?>></div>
        <div class="record"><label>&nbsp;</label><input type="submit" value="Save Message" class="rounded"></div>
        </form>
        <p></p>
        <?php if($_GET[sid]) {?>
        <form method="post" action="messaging-edit.php?sid=<?php echo $_GET[sid];?>">
        <input type="hidden" name="action" value="delete">
        <div class="record"><label>&nbsp;</label><input type="submit" value="Delete Message" class="rounded"></div>
        </form>
        <?php }?>
	</div>    
</div>
</body>
</html>