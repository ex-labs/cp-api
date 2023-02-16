<?php
error_reporting(0);

$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();


	if($_POST["request"] == "androidUserdataSave") {
		$query = "SELECT tvuid, hash FROM tvuserdata WHERE hash = '$_POST[hash]'";
		$stmt = $mysql->query($query);
		$count = $stmt->rowCount();

		if($count > 0) {
			$query = "UPDATE tvuserdata SET data = '$_POST[data]' WHERE hash = '$_POST[hash]'";
			$stmt = $mysql->query($query);
		} else {
			$query = "INSERT INTO tvuserdata (hash, data) VALUES ('$_POST[hash]', '$_POST[data]')";
			$stmt = $mysql->query($query);
		}
		echo "done";
	}
	if($_POST["request"] == "androidUserdataGet") {
		$query = "SELECT hash, data FROM tvuserdata WHERE hash = '$_POST[hash]'";
		$stmt = $mysql->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		echo $row[data];
	}
	
	if($_POST["request"] == "updateCheck") {
		$output = array();
		$output[status] = '';
		echo json_encode($output);
	}

	if($_POST["request"] == "userChoice") {
        $query = "SELECT tid FROM tvaudience WHERE mid = $_POST[movie] AND typeid = $_POST[type] AND linkid = $_POST[linkID] AND hash = '$_POST[hash]'";
        $stmt = $mysql->query($query);
		$count = $stmt->rowCount();
        if($count > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $query = "UPDATE tvaudience SET count = count + 1 WHERE tid = $row[tid]";
            $stmt = $mysql->query($query);
        } else {
            $query = "INSERT INTO tvaudience (mid, typeid, linkid, hash, count) VALUES ($_POST[movie], $_POST[type], $_POST[linkID], '$_POST[hash]', 1)";
            $stmt = $mysql->query($query);
        }
    }
    if($_POST["request"] == "dilemmaRecap") {
		$query = "SELECT * FROM useraverages WHERE movieid = $_POST[movieid] AND itemid = $_POST[itemid] AND qtype = 2";
        $stmt = $mysql->query($query);
		$count = $stmt->rowCount();
		
		$output = array();
        if($count > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $column = "choice". $_POST[choice];
            $query = "UPDATE useraverages SET $column = $column + 1 WHERE iid = $row[iid]";
            $stmt = $mysql->query($query);
			
			$count = $row[choice2] + $row[choice3] + 1;
			$fmr = $row[choice2];
			$smr = $row[choice3];
			if($_POST[choice] == 2) {
				$fmr++;
			} else {
				$smr++;
			}
			$output[FMR] = round($fmr * 100 / $count);
			$output[SMR] = round($smr * 100 / $count);
        } else {
            $column = "choice". $_POST[choice];
            $query = "INSERT INTO useraverages (qtype, movieid, itemid, $column) VALUES (2, $_POST[movieid], $_POST[itemid], 1)";
            $stmt = $mysql->query($query);
			
			$fmr = 0;
			$smr = 0;
			if($_POST[choice] == 2) {
				$fmr++;
			} else {
				$smr++;
			}
			$output[FMR] = round($fmr * 100);
			$output[SMR] = round($smr * 100);
        }
		
		echo json_encode($output);
    }
    if($_POST["request"] == "audienceHelp") {
        $query = "SELECT hash, count FROM tvaudience WHERE mid = $_POST[movie] AND typeid = $_POST[type] AND linkid = $_POST[linkID]";
        $stmt = $mysql->query($query);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item = array();
            $item[hash] = $row[hash];
            $item[count] = (string)$row[count];
            $data[] = $item;
        }
        $var[data] = $data;
        
        echo json_encode($var);
    }
    
    if($_POST["request"] == "logEvent") {
        $now = time();
        $query = "INSERT INTO tvactivity (uuid, device, platform, movieid, act, event, timestamp, score, popspent, popearned) VALUES ('$_POST[uuid]', '$_POST[device]', $_POST[platform], $_POST[movieid], $_POST[act], $_POST[event], $now, $_POST[score], $_POST[popspent], $_POST[popearned])";
        $stmt = $mysql->query($query);
        echo 'done';
    }
    if($_POST["request"] == "logEventUpd") {
        $now = time();
        $query = "INSERT INTO tvactivity (uuid, device, platform, movieid, act, event, timestamp, score, popspent, popearned, moviepicked) VALUES ('$_POST[uuid]', '$_POST[device]', $_POST[platform], $_POST[movieid], $_POST[act], $_POST[event], $now, $_POST[score], $_POST[popspent], $_POST[popearned], $_POST[moviepicked])";
        $stmt = $mysql->query($query);
        echo 'done';
    }
	if($_POST["request"] == "logEventWithLifelines") {
		if($_POST[uid]) {
			$uid = $_POST[uid];
		} else {
			$query = "SELECT uid FROM pt_devices WHERE uuid = '$_POST[uuid]'";
			$stmt = $mysql->query($query);
			if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$uid = $row[uid];
			} else {
				$uid = 0;
			}
		}
		
        $now = time();
        $query = "INSERT INTO tvactivity (uuid, uid, device, platform, movieid, act, event, timestamp, score, popspent, popearned, lifeline_5050, lifeline_audience, lifeline_undo, moviepicked) VALUES ('$_POST[uuid]', $uid, '$_POST[device]', $_POST[platform], $_POST[movieid], $_POST[act], $_POST[event], $now, $_POST[score], $_POST[popspent], $_POST[popearned], $_POST[lifeline_5050], $_POST[lifeline_audience], $_POST[lifeline_undo], $_POST[moviepicked])";
        $stmt = $mysql->query($query);
        echo 'done';
    }
	if($_POST["request"] == "logEventLogs") {
        $now = time();
        $query = "INSERT INTO tvlogs (uuid, eventid, device, platform, timestamp) VALUES ('$_POST[uuid]', $_POST[event], '$_POST[device]', $_POST[platform], $now)";
        $stmt = $mysql->query($query);
		
		if($_POST[token]) {
			$query = "REPLACE INTO tvtokens (uuid, platform, token) VALUES ('$_POST[uuid]', $_POST[platform], '$_POST[token]')";
			$stmt = $mysql->query($query);
		}
        echo 'done';
    }
?>
