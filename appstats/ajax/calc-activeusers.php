<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();

$thisDay = $_POST[day];
$oneDay = 3600 * 24;
	
$query = "SELECT uid FROM pt_users WHERE legacy_uid = 0 AND sp_points + mp_points > 0 AND timestamp > $thisDay AND timestamp < $thisDay + $oneDay";
$stmt = $mysql->query($query);
$count_new = $stmt->rowCount();

/*
$query = "SELECT DISTINCT uid FROM tvactivity WHERE timestamp > $thisDay AND timestamp < $thisDay + $oneDay";
$stmt = $mysql->query($query);
$count_active_day = $stmt->rowCount();

$query = "SELECT DISTINCT uid FROM tvactivity WHERE timestamp > $thisDay - $oneDay * 6 AND timestamp < $thisDay + $oneDay";
$stmt = $mysql->query($query);
$count_active_week = $stmt->rowCount();

$query = "SELECT DISTINCT uid FROM tvactivity WHERE timestamp > $thisDay - $oneDay * 30 AND timestamp < $thisDay + $oneDay";
$stmt = $mysql->query($query);
$count_active_month = $stmt->rowCount();
*/


$query = "SELECT DISTINCT tvlogs.uid FROM tvlogs LEFT JOIN pt_users ON pt_users.uid = tvlogs.uid WHERE tvlogs.timestamp > $thisDay AND tvlogs.timestamp < $thisDay + $oneDay AND sp_points + mp_points > 0";
$stmt = $mysql->query($query);
$count_active_day = $stmt->rowCount();
	
$query = "SELECT DISTINCT tvlogs.uid FROM tvlogs LEFT JOIN pt_users ON pt_users.uid = tvlogs.uid WHERE tvlogs.timestamp > $thisDay - $oneDay * 6 AND tvlogs.timestamp < $thisDay + $oneDay";
$stmt = $mysql->query($query);
$count_active_week = $stmt->rowCount();
	
$query = "SELECT DISTINCT tvlogs.uid FROM tvlogs LEFT JOIN pt_users ON pt_users.uid = tvlogs.uid WHERE tvlogs.timestamp > $thisDay - $oneDay * 30 AND tvlogs.timestamp < $thisDay + $oneDay";
$stmt = $mysql->query($query);
$count_active_month = $stmt->rowCount();


echo $count_new .'-'. $count_active_day .'-'. $count_active_week .'-'. $count_active_month .'-'. $thisDay;
?>