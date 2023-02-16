<?php
error_reporting(0);

$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();


$testuser = false;
//*
if(($_POST[uid] == 1278) || ($_POST[uid] == 1138) || ($_POST[uid] == 1406) || ($_POST[uid] == 1361) || ($_POST[uid] == 10235) || ($_POST[uid] == 30209) || ($_POST[uid] == 15090) || ($_POST[uid] == 1404) || ($_POST[uid] == 1409) || ($_POST[uid] == 10986) || ($_POST[uid] == 53539) || ($_POST[uid] == 145468) || ($_POST[uid] == 154643) || ($_POST[uid] == 160198) || ($_POST[uid] == 201311)) {
	$testuser = true;
}


//DOWNLOAD GAME INFO
if($_POST["request"] == "initInfo") {
	$output = array();
	
	$migrateUser = true;
	if($_POST[uid] == "0") {
		if($_POST[ea_uid]) {
			$query = "SELECT uid FROM pt_users WHERE legacy_uid = $_POST[ea_uid]";
			$stmt = $mysql->query($query);
			$count = $stmt->rowCount();
			if($count > 0) {
				$olduser = $stmt->fetch(PDO::FETCH_ASSOC);
				$_POST[uid] = $olduser[uid];
				$migrateUser = false;
			}
		}
	} else {
		$migrateUser = false;
	}
	
	if($migrateUser) {
		$userHistory = 0;
		$nUserPopcorn = 1000;
		$nUserSPPoints = 0;
		$nUserMPPoints = 0;
		$nUserMPGames = 0;
		$nUserMPWins = 0;
		$nUserMPDraws = 0;
		$nUserName = "";
		$bonusPopcorn = 0;
		
		if($_POST[earlyAdaptor] == "1") {
			$nUserPopcorn = $_POST[ea_popcorn];
			$nUserSPPoints = $_POST[ea_points];
			$userHistory = 1;
			if($_POST[ea_uid]) {
				$query = "SELECT * FROM pt_multiusers WHERE uid = $_POST[ea_uid]";
				$stmt = $mysql->query($query);
				$olduser = $stmt->fetch(PDO::FETCH_ASSOC);
				
				$nUserName = str_replace("'","`", trim(stripslashes(utf8_decode($olduser[first_name]))) ." ". stripslashes(substr(trim(utf8_decode($olduser[last_name])), 0, 1)) .".");
				$nUserMPPoints = intval($olduser[stats_games] * $olduser[stats_points]);
				$nUserMPGames = $olduser[stats_games];
				$nUserMPWins = $olduser[stats_wins];
				$nUserMPDraws = $olduser[stats_draws];
	
				$bonusPopcorn = 500 * $nUserMPWins;
				$userHistory = $_POST[ea_uid];
			}
			
			$bonusPopcorn = $bonusPopcorn + 50000;
			$nUserPopcorn = $nUserPopcorn + $bonusPopcorn;
			$output[add_popcorn] = (string)$bonusPopcorn;
		}
		
		
		$tempname = microtime();
		$query = "INSERT INTO pt_users (legacy_uid, activedevice, name, timestamp, popcorn, sp_points, mp_points, mp_games, mp_wins, mp_draws, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon) VALUES ($userHistory, '$_POST[uuid]', '$tempname', $now, $nUserPopcorn, $nUserSPPoints, $nUserMPPoints, $nUserMPGames, $nUserMPWins, $nUserMPDraws, 169, 1, 0, 0, 0, 106, 107, 0);";
		$result = $mysql->exec($query);
		$uid = $mysql->lastInsertId();
		$_POST[uid] = $uid;
		
		if(strlen($nUserName) == 0) {	
			$query = "UPDATE pt_users SET name = CONCAT('Randy deFault ',$uid) WHERE uid = $uid;";
			$result = $mysql->exec($query);
		} else {
			$query = "SELECT uid FROM pt_users WHERE name = '$nUserName'";
			$stmt = $mysql->query($query);
			$count = $stmt->rowCount();
			if($count > 0) {
				$nUserName = $nUserName ." ". $uid;
			}
			$query = "UPDATE pt_users SET name = '$nUserName' WHERE uid = $uid;";
			$result = $mysql->exec($query);
		}
		
		//UPDATE COMPLETED ACTS
		$completed = json_decode($_POST[completed], true);
		if(count($completed) > 0) {
			$query = "INSERT INTO pt_acts (uid, movieid, actid, points) VALUES";
			$params = array();
			foreach($completed as $act) {
				$item = array($uid, $act[movieid], $act[actid], $act[points]);
				$params = array_merge($params, $item);
				$query .= " (?,?,?,?),";
			}
			$query = substr($query, 0, -1);
			$stmt = $mysql->prepare($query);
			$stmt->execute($params);
		}
		
		$query = "INSERT INTO pt_avatar_purchased (uid, bid) VALUES ($uid, 1), ($uid, 169), ($uid, 106), ($uid, 107);";
		$result = $mysql->exec($query);
		$query = "REPLACE INTO pt_devices (uid, platform, device, uuid, token, timestamp) VALUES ($uid, $_POST[platform], '$_POST[device]', '$_POST[uuid]', '$_POST[token]', $now);";
		$result = $mysql->exec($query);
	} else {
		$uid = $_POST[uid];
		$query = "UPDATE pt_users SET activedevice = '$_POST[uuid]' WHERE uid = $uid;";
		$result = $mysql->exec($query);
		$query = "REPLACE INTO pt_devices (uid, platform, device, uuid, token, timestamp) VALUES ($uid, $_POST[platform], '$_POST[device]', '$_POST[uuid]', '$_POST[token]', $now);";
		$result = $mysql->exec($query);
	}
	
	//BODYPART
	$query = "SELECT * FROM pt_bodyparts WHERE active = 1 ORDER BY category, price, name";
	$stmt = $mysql->query($query);
	
	$parts = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    	$part = array();
		$part[id] = $row[bid];
		$part[category] = $row[category];
		$part[name] = $row[name];
		$part[filename] = $row[filename];
		$part[version] = $row[version];
		$part[bonus] = $row[bonus];
		$part[restriction] = $row[restriction];
		$part[exclusive] = $row[exclusive];
		$part[offsetx] = $row[offsetx];
		$part[offsety] = $row[offsety];
		$part[price] = number_format($row[price]);
		
		$parts[] = $part;
	}
	
	
	
	$query = "SELECT id, parentid, title, itunesid, price, trending, tmdb_version, releasedate, mapdate, popcornseriesactive FROM movies WHERE popcornactive = 1 OR popcornseriesactive = 1 OR popcornbonus > 0";
	
	/*
	if($testuser) {
		$query = "SELECT id, parentid, title, itunesid, price, trending, tmdb_version, releasedate, mapdate, popcornseriesactive FROM movies WHERE id = 2105";
	}
	*/


	$stmt = $mysql->query($query);
	
	$movies = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    	$displaytitle = $row['title'];
        if(substr($displaytitle, -5) == ", The") {
            $displaytitle = "The ". substr($displaytitle, 0, -5);
        }
            
		$mapdate = strtotime($row[mapdate]);
		if((($now - $mapdate) < 3600 * 24 * 14) && ($mapdate > 0)) {
			$newmap = "1";
		} else {
			$newmap = "0";
		}
        
		$movie = array();
		$movie['id'] = $row['id'];
		$movie['title'] = $displaytitle;
		$movie['sorttitle'] = $row['title'];
		$movie['itunesid'] = $row['itunesid'];
		$movie['releasedate'] = $row['releasedate'];
		$movie['mapdate'] = $row['mapdate'];
		$movie['newmap'] = (string)$newmap;
		$movie['version'] = $row['tmdb_version'];
		$movie['price'] = (string)$row[price];
		$movie['trending'] = (string)$row[trending];
		$movie['boxart'] = "http://api.customplay.com/data-tv/". $row['id'] ."/Boxart.jpg";
		$movie['background'] = "http://api.customplay.com/data-tv/". $row['id'] ."/BG.jpg";
        $movie['background_portrait'] = "http://api.customplay.com/data-tv/". $row['id'] ."/BG_Portrait.jpg";
		//$movie['boxart'] = "http://api.customplay.com/data-tv/". $row['id'] ."/Boxart.jpg";
		//$movie['background'] = "http://api.customplay.com/data-tv/". $row['id'] ."/BG.jpg";
        //$movie['background_portrait'] = "http://api.customplay.com/data-tv/". $row['id'] ."/BG_Portrait.jpg";
			
		if(($row[popcornseriesactive] > 0) && ($row[parentid] > 0)) {
			//SERIES....
			$episodes = array();
			$parent = $row[id];
			$subquery = "SELECT id, subtitle FROM movies WHERE parentid = $parent ORDER BY id";        
			$substmt = $mysql->query($subquery);
			while($item = $substmt->fetch(PDO::FETCH_ASSOC)) {
				$episode = array();
				$episode['id'] = $item['id'];
				$episode['title'] = $item['subtitle'];
				$episode['datapack'] = "http://api.customplay.com/data-tv/". $item['id'] ."/". $item['id'] .".zip";
				
				$episodes[] = $episode;
			}
			$movie['episodes'] = $episodes;
			$movie['series'] = "1";
		} else {
			$movie['datapack'] = "http://api.customplay.com/data-tv/". $row['id'] ."/". $row['id'] .".zip";
			$movie['series'] = "0";
		}
			
		$movies[] = $movie;
	}

	
	$popcounts = array("10000","60000","150000","350000","1000000","2500000");
	$rewards = array("25","50","75","100","500");
	$userlog = array("100","102","103","104","105","121","122");
	$settings = array("multirefresh"=>"10","pyramidbucket"=>"1000","pyramidpercent"=>"50");
		
	$promo = array();
	
	
		
	/*DEFAULT VALUES*/
	//*
	$promo[phone] = "http://api.customplay.com/promos/img_promo_phone_default.png";
	$promo[tablet] = "http://api.customplay.com/promos/img_promo_tablet_default.png";
	$promo[android_phone] = "http://api.customplay.com/promos/img_promo_android_phone_default.png";
	$promo[android_tablet] = "http://api.customplay.com/promos/img_promo_android_tablet_default.png";
	$promo[windows_tablet] = "http://api.customplay.com/promos/img_promo_windows_tablet_default.png";
	$promo[url] = "http://www.popcorntrivia.com/contest/cats0217.php";
	$promo[clickable] = "0";
	$promo[popcornsales] = "";
	$promo[title] = "Contest";
	$promo[auto] = "0";
	
	
	
	//SAVE POPCORN ACTION
	/*
	$query = "SELECT timestamp FROM pt_users WHERE uid = $_POST[uid]";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if($row[timestamp] < 1504224000) {
		$promo[phone] = "http://api.customplay.com/promos/img_promo_phone_DrowningPops.png";
		$promo[tablet] = "http://api.customplay.com/promos/img_promo_tablet_DrowningPops.png";
		$promo[android_phone] = "http://api.customplay.com/promos/img_promo_android_phone_DrowningPops.png";
		$promo[android_tablet] = "http://api.customplay.com/promos/img_promo_android_tablet_DrowningPops.png";
		$promo[windows_tablet] = "http://api.customplay.com/promos/img_promo_windows_tablet_DrowningPops.png";
		$promo[url] = "http://www.popcorntrivia.com/contest/helppt.php?platform=". $_POST[platform] ."&uid=". $_POST[uid];
		$promo[clickable] = "1";
		$promo[popcornsales] = "";
		$promo[title] = "Please Help";
		$promo[auto] = "0";
	}
	//*/
	//*/
	
	/*PROMO VALUES*/
	/*
	$promo[phone] = "http://api.customplay.com/promos/img_promo_phone_Doom-1.png";
	$promo[tablet] = "http://api.customplay.com/promos/img_promo_tablet_Doom-1.png";
	$promo[android_phone] = "http://api.customplay.com/promos/img_promo_android_phone_Doom-2 .png";
	$promo[android_tablet] = "http://api.customplay.com/promos/img_promo_android_tablet_Doom-1.png";
	$promo[windows_tablet] = "http://api.customplay.com/promos/img_promo_windows_tablet_Doom.png";
	$promo[url] = "http://www.popcorntrivia.com/contest/sharknado.php?platform=". $_POST[platform] ."&uid=". $_POST[uid];
	$promo[clickable] = "1";
	$promo[popcornsales] = "";
	$promo[title] = "Contest";
	$promo[auto] = "0";
	//*/
		
	/* TESTING UIDs
	if($testuser) {
		//$query = "INSERT INTO pt_contest_tracking (contest, uid, open_app, timestamp) VALUES (6, $_POST[uid], 1, $now)";
		//$mysql->query($query);
	
		$promo[phone] = "http://api.customplay.com/promos/img_promo_phone_DrowningPops.png";
		$promo[tablet] = "http://api.customplay.com/promos/img_promo_tablet_DrowningPops.png";
		$promo[android_phone] = "http://api.customplay.com/promos/img_promo_android_phone_DrowningPops.png";
		$promo[android_tablet] = "http://api.customplay.com/promos/img_promo_android_tablet_DrowningPops.png";
		$promo[windows_tablet] = "http://api.customplay.com/promos/img_promo_windows_tablet_DrowningPops.png";
		$promo[url] = "http://www.popcorntrivia.com/contest/maintenance.php?platform=". $_POST[platform] ."&uid=". $_POST[uid];
		$promo[clickable] = "0";
		$promo[popcornsales] = "";
		$promo[title] = "Contest";
		$promo[auto] = "1";
	}
	//*/
	

	
	//CONTEST WINNER NOTIFICATIONS
	/*
	
	//MISSING POPCORN AWARDS
	$query = "SELECT * FROM pt_contestwinner WHERE uid = $_POST[uid] AND contest = 20 AND timestamp = 0";
	$stmt = $mysql->query($query);
	$count = $stmt->rowCount();
	if($count > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$query = "UPDATE pt_contestwinner SET timestamp = $now WHERE cid = $row[cid]";
		$result = $mysql->exec($query);
		
		$query = "UPDATE pt_users SET popcorn = popcorn + 10000 WHERE uid = $_POST[uid]";
		$result = $mysql->exec($query);
		
		$promo[url] = "http://www.popcorntrivia.com/contest/contestwinners-award.php?platform=". $_POST[platform] ."&uid=". $_POST[uid];
		$promo[clickable] = "0";
		$promo[title] = "Winner!";
		$promo[auto] = "1";
	}
	
	//GOT CONTEST
	$query = "SELECT * FROM pt_contestwinner WHERE uid = $_POST[uid] AND contest > 11 AND contest < 19 AND timestamp = 0";
	$stmt = $mysql->query($query);
	$count = $stmt->rowCount();
	if($count > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$query = "UPDATE pt_contestwinner SET timestamp = $now WHERE cid = $row[cid]";
		$result = $mysql->exec($query);
		
		if($row[contest] == 12) { //HAT - 194
			$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES ($_POST[uid], 194)";
			$stmt = $mysql->query($query);
			
			$query = "UPDATE pt_users SET pops_hat = 194 WHERE uid = $_POST[uid]";
			$stmt = $mysql->query($query);
		}
		if($row[contest] == 13) { //EYES - 236
			$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES ($_POST[uid], 236)";
			$stmt = $mysql->query($query);
			
			$query = "UPDATE pt_users SET pops_eyes = 236 WHERE uid = $_POST[uid]";
			$stmt = $mysql->query($query);
		}
		if($row[contest] == 14) { //BUCKET - 238
			$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES ($_POST[uid], 238)";
			$stmt = $mysql->query($query);
			
			$query = "UPDATE pt_users SET pops_body = 238 WHERE uid = $_POST[uid]";
			$stmt = $mysql->query($query);
		}
		if($row[contest] == 15) { //ICE SWORD - 235 -- REPLACED 242
			$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES ($_POST[uid], 242)";
			$stmt = $mysql->query($query);
			
			$query = "UPDATE pt_users SET pops_weapon = 242 WHERE uid = $_POST[uid]";
			$stmt = $mysql->query($query);
		}
		if($row[contest] == 16) { //BEARD - 237
			$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES ($_POST[uid], 237)";
			$stmt = $mysql->query($query);
			
			$query = "UPDATE pt_users SET pops_mouth = 237 WHERE uid = $_POST[uid]";
			$stmt = $mysql->query($query);
		}
		if($row[contest] == 17) { //ARMS - 240 & 241
			$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES ($_POST[uid], 240), ($_POST[uid], 241)";
			$stmt = $mysql->query($query);
			
			$query = "UPDATE pt_users SET pops_left_arm = 240, pops_right_arm = 241 WHERE uid = $_POST[uid]";
			$stmt = $mysql->query($query);
		}
		if($row[contest] == 18) { //DRAGON - 243
			$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES ($_POST[uid], 243)";
			$stmt = $mysql->query($query);
			
			$query = "UPDATE pt_users SET pops_backdrop = 243 WHERE uid = $_POST[uid]";
			$stmt = $mysql->query($query);
		}
		
		
		
		$promo[url] = "http://www.popcorntrivia.com/contest/gots07-awards.php?platform=". $_POST[platform] ."&uid=". $_POST[uid];
		$promo[clickable] = "0";
		$promo[title] = "Winner!";
		$promo[auto] = "1";
	}
	//*/
	//END CONTEST SECTION
	
	
	
	$delta = 36*3600 - ($now - $row[timer]);
	//CHECK FOR PENDING MULTIPLAYER ACTIVITY
	$query = "SELECT DISTINCT gid FROM pt_gamedata WHERE challenge = $uid AND timestamp > $now - 36*3600 AND statusOne < 2 UNION SELECT DISTINCT gid FROM pt_gamedata WHERE playerOne = $uid AND playerTwo > 0 AND statusOne < 2 UNION SELECT DISTINCT gid FROM pt_gamedata WHERE playerTwo = $uid AND playerOne > 0 AND statusTwo < 2";
	$stmt = $mysql->query($query);
	$mp_action = $stmt->rowCount();
	
		
	
	$update = array();
	
	$version = floatval($_POST[version]);	
	$user = getUserDataForId($uid, $version, $mysql);
	$user[mp_action] = (string)$mp_action;
	
	$favorites = array();
	$query = "SELECT * FROM pt_favorites WHERE uid = $_POST[uid]";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$favorites[] = (string)$row[faveid];
	}
	
	
	
	$output[serverstat] = "api";
	$output[userlog] = $userlog;
	$output[rewards] = $rewards;
	$output[promo] = $promo;
	$output[bodyparts] = $parts;
	$output[movies] = $movies;
	$output[settings] = $settings;
	$output[user] = $user;
	$output[favorites] = $favorites;
	$output[popcounts] = $popcounts;
	$output[resources] = "http://api.customplay.com/";
	//$output[resources] = "http://5f7b44ff735a3829f963-383281fc7e5d3c6540ee89df31449bb7.r39.cf1.rackcdn.com/";
	$output[status] = "success";
	
		
	echo json_encode($output);
}


if($_POST["request"] == "adCheck") {
	if($testuser) {
		//$data = array("web"=>"https://popcorntrivia.com/ad-film-and-fork.php");
		//$data = array("google"=>"yes");
	}
	$data = array("google"=>"yes");
	//$data = array("web"=>"https://api.customplay.com/v2/adtest.html");
    echo json_encode($data);
}
if($_POST["request"] == "logEventLogs") {
    $now = time();
	$ip = $_SERVER['REMOTE_ADDR'];
    $query = "INSERT INTO tvlogs (uid, uuid, eventid, device, platform, version, ip, extra, timestamp) VALUES ($_POST[uid], '$_POST[uuid]', $_POST[event], '$_POST[device]', $_POST[platform], '$_POST[version]', '$ip', '$_POST[extra]', $now)";
    $result = $mysql->exec($query);
    echo 'done';
}
	
//UPDATE USER INFO
if($_POST["request"] == "saveUserInfo") {
	$output = array();
	$activeUser = isActiveUser($_POST[uid], $_POST[uuid], $mysql);
	if($activeUser) {
		
		//print_r($_POST);
			
		//NEED TO UPDATE THIS HERE TO PROPPERLY SET POINTS FOR EVERYONE
		//UPDATE ACTS COMPLETED
		//REPLAY MOVIE REMOVE FROM ACTS PLAYED
		if($_POST[action] == "movieReplay") {
			$query = "DELETE FROM pt_acts WHERE uid = $_POST[uid] AND movieid = $_POST[movieid]";
			$stmt = $mysql->prepare($query);
			$stmt->execute($params);
		}
		
		if($_POST[action] == "actCompleted") {
			$query = "INSERT INTO pt_acts (uid, movieid, actid, points) VALUES ($_POST[uid], $_POST[movieid], $_POST[act], $_POST[points])";
			$stmt = $mysql->prepare($query);
			$stmt->execute($params);
			
			if($_POST[gifts]) {
				$mygifts = json_decode($_POST[gifts], true);
				$query = "REPLACE INTO pt_gifts (uid, giftid, status) VALUES";
				$params = array();
				foreach($mygifts as $key => $value) {
					$item = array($_POST[uid], $key, $value);
					$params = array_merge($params, $item);
					$query .= " (?,?,?),";
				}
				$query = substr($query, 0, -1);
				$stmt = $mysql->prepare($query);
				$stmt->execute($params);
			}
			$query = "SELECT * FROM pt_tips WHERE show_sp = 1 ORDER BY RAND() LIMIT 1";
			$stmt = $mysql->query($query);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if($row[tip]) $output[tip] = $row[tip];
			
			//GAME OF THRONES CONTEST AWARDS
			/*
			if($_POST[movieid] == 2042) {
				//CONTESTWINNERS 12-18 reserved for GOT
				if(($_POST[act] == 1) && ($_POST[points] == 100)) {
					$query = "INSERT INTO pt_contestwinner (uid, contest) VALUES ($_POST[uid], 12)";
					$result = $mysql->exec($query);
				}
				if(($_POST[act] == 2) && ($_POST[points] == 100)) {
					$query = "INSERT INTO pt_contestwinner (uid, contest) VALUES ($_POST[uid], 13)";
					$result = $mysql->exec($query);
				}
				if(($_POST[act] == 3) && ($_POST[points] == 100)) {
					$query = "INSERT INTO pt_contestwinner (uid, contest) VALUES ($_POST[uid], 14)";
					$result = $mysql->exec($query);
				}
				if(($_POST[act] == 4) && ($_POST[points] == 100)) {
					$query = "INSERT INTO pt_contestwinner (uid, contest) VALUES ($_POST[uid], 15)";
					$result = $mysql->exec($query);
				}
				if(($_POST[act] == 5) && ($_POST[points] == 100)) {
					$query = "INSERT INTO pt_contestwinner (uid, contest) VALUES ($_POST[uid], 16)";
					$result = $mysql->exec($query);
				}
				if(($_POST[act] == 6) && ($_POST[points] == 100)) {
					$query = "INSERT INTO pt_contestwinner (uid, contest) VALUES ($_POST[uid], 17)";
					$result = $mysql->exec($query);
				}
				if(($_POST[act] == 7) && ($_POST[points] == 100)) {
					$query = "INSERT INTO pt_contestwinner (uid, contest) VALUES ($_POST[uid], 18)";
					$result = $mysql->exec($query);
				}
			}
			*/
		}
		
		
		//FIXING SP_POITS FOR EVERYONE
		$query = "SELECT SUM(points) as points FROM pt_acts WHERE uid = $_POST[uid]";
		$stmt = $mysql->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$sp_points = $row[points];
		if(!$sp_points) $sp_points = 0;
		
		if(!$_POST[thispopcorn]) $_POST[thispopcorn] = 0;
		
	
		
		//UPDATE ALL THE INFO IN pt_users TABLE, EXCEPT NAME
		$query = "UPDATE pt_users SET popcorn = $_POST[popcorn], weekly_popcorn = weekly_popcorn + $_POST[thispopcorn], sp_points = $sp_points, mp_points = $_POST[mp_points], mp_games = $_POST[mp_games], mp_wins = $_POST[mp_wins], mp_draws = $_POST[mp_draws], pops_backdrop = $_POST[pops_backdrop], pops_body = $_POST[pops_body], pops_eyes = $_POST[pops_eyes], pops_mouth = $_POST[pops_mouth], pops_hat = $_POST[pops_hat], pops_left_arm = $_POST[pops_left_arm], pops_right_arm = $_POST[pops_right_arm], pops_weapon = $_POST[pops_weapon] WHERE uid = $_POST[uid]";
		//$output[debug] = $query;	
		$result = $mysql->exec($query);
		
		//UPDATE OWNED BODYPARTS
		if($_POST[action] == "newBodyparts") {
			$bodyparts = json_decode($_POST[mybodyparts], true);
			$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES";
			$params = array();
			foreach($bodyparts as $bodypart) {
				$item = array($_POST[uid], $bodypart);
				$params = array_merge($params, $item);
				$query .= " (?,?),";
			}
			$query = substr($query, 0, -1);
			$stmt = $mysql->prepare($query);
			$stmt->execute($params);
		}
		
		
		//UPDATE OWNED MOVIES
		if($_POST[action] == "newPremiumContent") {
			$mymovies = json_decode($_POST[mymovies], true);
			$query = "REPLACE INTO pt_movies_purchased (uid, movieid) VALUES";
			$params = array();
			foreach($mymovies as $mymovie) {
				$item = array($_POST[uid], $mymovie);
				$params = array_merge($params, $item);
				$query .= " (?,?),";
			}
			$query = substr($query, 0, -1);
			$stmt = $mysql->prepare($query);
			$stmt->execute($params);
		}
		
		
		//UPDATE GIFTS STATUS
		if($_POST[action] == "giftsUpdate") {
			$mygifts = json_decode($_POST[gifts], true);
			$query = "REPLACE INTO pt_gifts (uid, giftid, status) VALUES";
			$params = array();
			foreach($mygifts as $key => $value) {
				$item = array($_POST[uid], $key, $value);
				$params = array_merge($params, $item);
				$query .= " (?,?,?),";
			}
			$query = substr($query, 0, -1);
			$stmt = $mysql->prepare($query);
			$stmt->execute($params);
			
			if($_POST[mybodyparts]) {
				$bodyparts = json_decode($_POST[mybodyparts], true);
				$query = "REPLACE INTO pt_avatar_purchased (uid, bid) VALUES";
				$params = array();
				foreach($bodyparts as $bodypart) {
					$item = array($_POST[uid], $bodypart);
					$params = array_merge($params, $item);
					$query .= " (?,?),";
				}
				$query = substr($query, 0, -1);
				$stmt = $mysql->prepare($query);
				$stmt->execute($params);
			}
		}
		
		//MULTIPLAYER GAME FINISHED
		if($_POST[action] == "multiGameFinished") {
			$query = "UPDATE pt_gamedata SET scoreOne = $_POST[score], statusOne = 1 WHERE gid = $_POST[gid] AND playerOne = $_POST[uid]; UPDATE pt_gamedata SET scoreTwo = $_POST[score], statusTwo = 1 WHERE gid = $_POST[gid] AND playerTwo = $_POST[uid];";
			$result = $mysql->exec($query);
			
			$query = "SELECT * FROM pt_tips WHERE show_mp = 1 ORDER BY RAND() LIMIT 1";
			$stmt = $mysql->query($query);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if($row[tip]) $output[tip] = $row[tip];
			
			$query = "SELECT playerOne, playerTwo, statusOne, statusTwo, scoreOne, scoreTwo FROM pt_gamedata WHERE gid = $_POST[gid]";
			$stmt = $mysql->query($query);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if($row[playerOne] == $_POST[uid]) {
				if($row[statusTwo] == 1) {
					sendPushToUser($row[playerTwo], 3, $mysql);
				}
			} else {
				if($row[statusOne] == 1) {
					sendPushToUser($row[playerOne], 3, $mysql);
				}
			}
			
			//UPDATE ROBOT STATS
			$robots = array(299712,299713,299714,299715,299716,299717,299718,299719,299720,299721);
			if(in_array($row[playerTwo], $robots)) {
				$mp_wins = 0;
				$mp_draws = 0;
				if($row[scoreOne] < $row[scoreTwo]) $mp_wins = 1;
				if($row[scoreOne] == $row[scoreTwo]) $mp_draws = 1;
				
				$query = "UPDATE pt_users SET mp_games = mp_games + 1, mp_points = mp_points + $row[scoreTwo], mp_wins = mp_wins + $mp_wins, mp_draws = mp_draws + $mp_draws WHERE uid = $row[playerTwo]";
				$stmt = $mysql->query($query);
			}
		}
		
		//MULTIPLAYER GAME VIEWED AND AWARDED
		if($_POST[action] == "multiGameViewed") {
			$query = "UPDATE pt_gamedata SET popcornOne = $_POST[thispopcorn], statusOne = 2 WHERE gid = $_POST[gid] AND playerOne = $_POST[uid]; UPDATE pt_gamedata SET popcornTwo = $_POST[thispopcorn], statusTwo = 2 WHERE gid = $_POST[gid] AND playerTwo = $_POST[uid];";
			$result = $mysql->exec($query);
		}
		
		
		//FUTURE USE
			
	
		$output[status] = "success";	
	} else {
		$output[status] = "fail";
		$output[error] = "Sorry, you're not using the active device for this account, and your request can not be processed... Please either close the app and open it again to become the active device, or use your other device.";
	}
		
	echo json_encode($output);
}

if($_POST["request"] == "isActiveUser") {
	$output = array();
	$activeUser = isActiveUser($_POST[uid], $_POST[uuid], $mysql);
	if($activeUser) {
		$output[status] = "success";
	} else {
		$output[status] = "fail";
		$output[error] = "Sorry, you're not using the active device for this account, and your request can not be processed... Please either close the app and open it again to become the active device, or use your other device.";
	}
	echo json_encode($output);
}
if($_POST["request"] == "androidUserCheck") {
	$query = "SELECT uid FROM pt_androidusers WHERE email LIKE '$_POST[email]'";
	$stmt = $mysql->query($query);
	$count = $stmt->rowCount();

	$output = array();
	if($count == 0) {
		if($_POST[uid] > 0) {
			$query = "INSERT INTO pt_androidusers (uid, email) VALUES ($_POST[uid], '$_POST[email]')";
			$mysql->query($query);
			$output[uid] = $_POST[uid];
		} else {
			$output[uid] = "0";
		}
	} else {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$output[uid] = $row[uid];
	}
	echo json_encode($output);
}

if($_POST["request"] == "multiGameList") {
	$output = array();
	
	$games = array();
	$expired = array();
	
	$query = "SELECT gid, pt_gamedata.category AS categoryid, challenge_categories.category AS categoryname, challenge_game, challenge, playerOne, playerTwo, scoreOne, scoreTwo, statusOne, statusTwo, messageOne, messageTwo, timer, questions FROM pt_gamedata LEFT JOIN challenge_categories ON pt_gamedata.category = challenge_categories.categoryid WHERE (playerOne = $_POST[uid] OR playerTwo = $_POST[uid] OR challenge = $_POST[uid]) AND (statusOne < 2 OR statusTwo < 2) ORDER BY timer";
	
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$game = array();
		$game[gid] = $row[gid];
		$game[categoryid] = $row[categoryid];
		$game[categoryname] = $row[categoryname];
		$game[challenge_game] = $row[challenge_game];
		$game[challenge] = $row[challenge];
		
		if($row[playerOne] == $_POST[uid]) {
			$myScore = $row[scoreOne];
			$myStatus = $row[statusOne];
			$myMessage = stripslashes($row[messageOne]);
			$opId = $row[playerTwo];
			$opScore = $row[scoreTwo];
			$opStatus = $row[statusTwo];
			$opMessage = stripslashes($row[messageTwo]);
		} else {
			$myScore = $row[scoreTwo];
			$myStatus = $row[statusTwo];
			$myMessage = stripslashes($row[messageTwo]);
			$opId = $row[playerOne];
			$opScore = $row[scoreOne];
			$opStatus = $row[statusOne];
			$opMessage = stripslashes($row[messageOne]);
		}
		
		$game[my_score] = $myScore;
		$game[my_status] = $myStatus;
		$game[my_message] = $myMessage;
		$game[op_uid] = $opId;
		$game[op_score] = $opScore;
		$game[op_status] = $opStatus;
		$game[op_message] = $opMessage;
	
		
		//FIX FOR CHALLENGE
		$delta = 36*3600 - ($now - $row[timer]);
		if($game[challenge] > 0)  {
			//CHALLENGE GAME, NOT YET ACCEPTED BY OPPONENT
			if($delta > 0) {
				//NOT EXPIRED
				if($game[challenge] == $_POST[uid]) {
					//I WAS CHALLENGED
					$opponent = getSimpleUserDataForId($opId, $mysql);
					$oponent[op_status] = "0";
					$oponent[op_score] = "0";
					$game[opponent] = (object)$opponent;
					$game[timer] = setTimerAmount($delta);
					$game[statustext] = "Challenge from ". $opponent[name] ."\nin ". $game[categoryname] ." Category";
					$games[] = $game;
				} else {
					//I DID THE CHALLENGING
					$opponent = getSimpleUserDataForId($game[challenge], $mysql);
					$oponent[op_status] = "0";
					$oponent[op_score] = "0";
					$game[opponent] = (object)$opponent;
					$game[timer] = setTimerAmount($delta);
					$game[statustext] = "Waiting for ". $opponent[name] ."\nto Accept Challenge in ". $game[categoryname] ." Category";
					$games[] = $game;
				}
			} else {
				//EXPIRED GAME, NOTHING TO DO FOR THE CHALLENGED PERSON
				if($game[challenge] != $_POST[uid]) {
					//I DID THE CHALLENGING
					//ADD NAME TO EXPIRED ARRAY FOR MESSAGES TO CHALLENGER
					$opponent = getSimpleUserDataForId($game[challenge], $mysql);
					$expired[] = $opponent[name];
					//REMOVE GAME, NOT NEEDED ANY MORE
					$query = "UPDATE pt_gamedata SET statusOne = 2, statusTwo = 2 WHERE gid = $game[gid]";
					$result = $mysql->exec($query);
				}
			}
		} else {
			//RANDOM GAME
			if($opId > 0) {
				//HAVE AN OPPONENT
				if($delta > 0) {
					//NOT EXPIRED
					if($myStatus == 0) {
						//PLAY GAME
						$opponent = getSimpleUserDataForId($opId, $mysql);
						$oponent[op_status] = $opScore;
						$oponent[op_score] = $opStatus;
						$game[opponent] = $opponent;
						$game[timer] = setTimerAmount($delta);
						$game[statustext] = "Play Against ". $opponent[name] ."\nin ". $game[categoryname] ." Category";
						$games[] = $game;
					} else if(($myStatus == 1) && ($opStatus == 0)) {
						//WAITING FOR OPPONENT
						$opponent = getSimpleUserDataForId($opId, $mysql);
						$oponent[op_status] = $opScore;
						$oponent[op_score] = $opStatus;
						$game[opponent] = (object)$opponent;
						$game[timer] = setTimerAmount($delta);
						$game[statustext] = "Waiting for ". $opponent[name] ."\nto Play ". $game[categoryname] ." Category";
						$games[] = $game;
					} else if(($myStatus == 1) && ($opStatus > 0)) {
						//VIEW RESULTS
						$opponent = getSimpleUserDataForId($opId, $mysql);
						$oponent[op_status] = $opScore;
						$oponent[op_score] = $opStatus;
						$game[opponent] = (object)$opponent;
						$game[timer] = "";
						$game[statustext] = "View Results of Game\nwith ". $opponent[name];
						$games[] = $game;
					} 
				} else {
					//TIMER HAS EXPIRED
					if($myStatus < 2) {
						$opponent = getSimpleUserDataForId($opId, $mysql);
						$oponent[op_status] = $opScore;
						$oponent[op_score] = $opStatus;
						$game[opponent] = (object)$opponent;
						$game[timer] = "Expired";
						$game[statustext] = "View Results of Game\nwith ". $opponent[name];
						$games[] = $game;
					}
					
					//UPDATE GAME SATUS TO REFLECT EXPIRED GAME
					//$query = "UPDATE pt_gamedata SET statusOne = 1 WHERE gid = $game[gid] AND playerOne = $_POST[uid];";
					//$result = $mysql->exec($query);
				}
			} else {
				//WAITING FOR OPPONENT
				$opponent = array();
				$oponent[op_status] = "0";
				$oponent[op_score] = "0";
				$game[opponent] = (object)$opponent;
				$game[timer] = "";
				$game[statustext] = "Waiting for Opponent\nin ". $game[categoryname] ." Category";
				$games[] = $game;	
			}
		}
	}
	
	
	$categories = array();
	$query = "SELECT categoryid, category FROM challenge_categories WHERE active = 1 ORDER BY orderlist";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$category = array();
		$category[id] = (string)$row[categoryid];
		$category[category] = $row[category];
		
		$categories[] = $category;
	}	
	
	$output[expired] = $expired;
	$output[games] = $games;
	$output[categories] = $categories;
	
	
	echo json_encode($output);
}
if($_POST["request"] == "multiGameStart") {
	$output = array();
	
	//SMART MATCH SYSTEM... FOR NOW EXCLUDING ONE EYED WILLY
	$uberplayers = array("1692","34873");
	//$uberplayers = array("1278");
	
	if(in_array($_POST[uid], $uberplayers)) {
		$questions = generateQuestionData($_POST[category], $mysql);
		$query = "INSERT INTO pt_gamedata (category, challenge_game, challenge, playerOne, questions, timestamp) VALUES ($_POST[category], 0, 0, $_POST[uid], '$questions', $now)";
		$result = $mysql->exec($query);
		$gid = $mysql->lastInsertId();
		
		$message = "Looking for Opponent...";
		$output[status] = "waiting";
		$output[message] = $message;
		$output[gid] = (string)$gid;
	} else {
		$query = "SELECT uid, mp_games FROM pt_users WHERE uid = $_POST[uid]";
		$stmt = $mysql->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$query = "UPDATE pt_gamedata SET playerTwo = ?, timer = ? WHERE category = ? AND playerOne != ? AND playerTwo = 0 AND challenge_game = 0 LIMIT 1;";
		
		$stmt = $mysql->prepare($query);
		$stmt->execute(array($_POST[uid], $now, $_POST[category], $_POST[uid]));
		$success = $stmt->rowCount();
		
		if($success > 0) {
			$query = "SELECT gid, playerOne FROM pt_gamedata WHERE category = $_POST[category] AND playerOne != $_POST[uid] AND playerTwo = $_POST[uid] AND challenge_game = 0 AND timer = $now LIMIT 1";
			$stmt = $mysql->query($query);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
			$output[status] = "game";
			$output[message] = "";
			$output[gid] = (string)$row[gid];
			
			sendPushToUser($row[playerOne], 1, $mysql);
		} else {
			$questions = generateQuestionData($_POST[category], $mysql);
			$query = "INSERT INTO pt_gamedata (category, challenge_game, challenge, playerOne, questions, timestamp) VALUES ($_POST[category], 0, 0, $_POST[uid], '$questions', $now)";
			$result = $mysql->exec($query);
			$gid = $mysql->lastInsertId();
			
			$message = "Looking for Opponent...";
			$output[status] = "waiting";
			$output[message] = $message;
			$output[gid] = (string)$gid;
		}
	}
	
	echo json_encode($output);
}
if($_POST["request"] == "multiGameStartV2") {
	$output = array();
	
	$query = "SELECT uid, mp_games, mp_points FROM pt_users WHERE uid = $_POST[uid]";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if($row[mp_games] > 0) {
		$myAverage = floor($row[mp_points] / $row[mp_games]);
	} else {
		$myAverage = 60;
	}
	
	if($myAverage > 84) {
		$query = "UPDATE pt_gamedata SET playerTwo = ?, averageTwo = ?, timer = ? WHERE category = ? AND playerOne != ? AND playerTwo = 0 AND averageOne > 84 AND challenge_game = 0 LIMIT 1;";
	} else {
		$query = "UPDATE pt_gamedata SET playerTwo = ?, averageTwo = ?, timer = ? WHERE category = ? AND playerOne != ? AND playerTwo = 0 AND averageOne < 85 AND challenge_game = 0 LIMIT 1;";
	}
		
	$stmt = $mysql->prepare($query);
	$stmt->execute(array($_POST[uid], $myAverage, $now, $_POST[category], $_POST[uid]));
	$success = $stmt->rowCount();
	
	
	if($success > 0) {
		$query = "SELECT gid, playerOne FROM pt_gamedata WHERE category = $_POST[category] AND playerOne != $_POST[uid] AND playerTwo = $_POST[uid] AND challenge_game = 0 AND timer = $now LIMIT 1";
		$stmt = $mysql->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$output[status] = "game";
		$output[message] = "";
		$output[gid] = (string)$row[gid];
			
		sendPushToUser($row[playerOne], 1, $mysql);
	} else {
		$questions = generateQuestionData($_POST[category], $mysql);
		$query = "INSERT INTO pt_gamedata (category, challenge_game, challenge, playerOne, averageOne, questions, timestamp) VALUES ($_POST[category], 0, 0, $_POST[uid], $myAverage, '$questions', $now)";
		$result = $mysql->exec($query);
		$gid = $mysql->lastInsertId();
			
		$message = "Looking for Opponent...";
		$output[status] = "waiting";
		$output[message] = $message;
		$output[gid] = (string)$gid;
	}
	
	echo json_encode($output);
}
if($_POST["request"] == "multiGameRobot") {
	$output = array();
	
	$robots = array(array("uid"=>"299712", "offset"=>"10"),
					array("uid"=>"299713", "offset"=>"7"),
					array("uid"=>"299714", "offset"=>"5"),
					array("uid"=>"299715", "offset"=>"3"),
					array("uid"=>"299716", "offset"=>"0"),
					array("uid"=>"299717", "offset"=>"0"),
					array("uid"=>"299718", "offset"=>"-3"),
					array("uid"=>"299719", "offset"=>"-5"),
					array("uid"=>"299720", "offset"=>"-7"),
					array("uid"=>"299721", "offset"=>"-10"));
	$robot = $robots[array_rand($robots)];
	
	
	$query = "UPDATE pt_gamedata SET playerTwo = ?, timer = ? WHERE gid = ?;";
	$stmt = $mysql->prepare($query);
	$stmt->execute(array($robot[uid], $now, $_POST[gid]));
	$success = $stmt->rowCount();
	if($success > 0) {
		$query = "SELECT uid, mp_games, mp_points FROM pt_users WHERE uid = $_POST[uid]";
		$stmt = $mysql->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if($row[mp_games] > 0) {
			$myAverage = floor($row[mp_points] / $row[mp_games]);
		} else {
			$myAverage = 60;
		}
		
		
		$robotRoll = mt_rand(-10,10);
		if($robot[uid] == "299717") $robotRoll = mt_rand(-2,2); //DARTH WHO'S ALWAYS DEAD ON YOU....
		$robotDelta = $robotRoll + $robot[offset];
		
		$robotScore = $myAverage + $robotDelta;
		$robotRealScore = 0;
		$valid_scores = array('0','2','4','6','8','10','12','14','16','18','20','22','24','26','28','30','32','34','36','38','40','42','44','46','48','50','52','54','56','58','60','62','64','66','68','70','72','74','76','78','80','82','84','86','88','90','92','96','100');
		for($i=0; $i<49; $i++) {
			if($robotScore >= $valid_scores[$i]) {
				$robotRealScore = $valid_scores[$i];
			}
		}
		
		$query = "UPDATE pt_gamedata SET scoreTwo = $robotRealScore, statusTwo = 1 WHERE gid = $_POST[gid]";
		$stmt = $mysql->query($query);
		
		$output[status] = "robot";
	} else {
		//ALREADY MATCHED....
		$output[status] = "matched";
	}
	
	
	echo json_encode($output);
}
if($_POST["request"] == "multiGameData") {
	$query = "SELECT questions FROM pt_gamedata WHERE gid = $_POST[gameid]";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
	$datapath = "../data-tv/";
	$game = array();
	
	$array = explode(",", $row[questions]);
	foreach($array as $item) {
		$pointers = explode(":", $item);
		$dbfile = $datapath . $pointers[0] ."/". $pointers[0] .".s3db";
		$dbname = 'sqlite:'. $dbfile;
		$dbh = new PDO($dbname);
			
		$query = "SELECT FK_GameTypeID, (SELECT MovieTitle FROM TblMovie) as MovieTitle, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder from TblGameMasterLU WHERE PlayOrder = ". $pointers[1];
		$stm = $dbh->query($query);
		$info = $stm->fetch(PDO::FETCH_ASSOC);
		$info[movieid] = $pointers[0];
			
		$displaytitle = $info[MovieTitle];
        if(substr($displaytitle, -5) == ", The") {
            $displaytitle = "The ". substr($displaytitle, 0, -5);
			$info[MovieTitle] = $displaytitle;
        }
				
		if($info[FK_GameTypeID] == 1) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				if($row[IsCorrect]  == 1) {
					$who_id = $row[Answer];
				}
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
			
			$followup = array();
			
			$query = "select FMR, SMR, FMRPercent, SMRPercent, Recap from TblDilemmas where DilemmaID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[FMR] = $fdata[FMR];
			$followup[SMR] = $fdata[SMR];
			$followup[FMRPercent] = $fdata[FMRPercent];
			$followup[SMRPercent] = $fdata[SMRPercent];
			$followup[Recap] = $fdata[Recap];
			
			
			$query = "select WhoLUID, CharacterName from TblWhoLU where WhoLUID = ". $who_id;
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[WhoLUID] = $fdata[WhoLUID];
			$followup[CharacterName] = $fdata[CharacterName];
				
			$query = "select DilMoralReqName from TblDilMoralReq where DilMoralReqID = ". $followup[FMR];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[FMRName] = $fdata[DilMoralReqName];
				
			$query = "select DilMoralReqName from TblDilMoralReq where DilMoralReqID = ". $followup[SMR];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[SMRName] = $fdata[DilMoralReqName];
			
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 2) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
			
			$followup = array();
			$query = "select IdsLUID, IdsLUName, PrimaryLink, SecondaryLink from TblIdsLU where IdsLUID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[IdsLUID] = $fdata[IdsLUID];
			$followup[IdsLUName] = $fdata[IdsLUName];
			$followup[PrimaryLink] = $fdata[PrimaryLink];
			$followup[SecondaryLink] = $fdata[SecondaryLink];
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 3) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
			
			$followup = array();
			$query = "select IdsLUID, IdsLUName, PrimaryLink, SecondaryLink from TblIdsLU where IdsLUID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[IdsLUID] = $fdata[IdsLUID];
			$followup[IdsLUName] = $fdata[IdsLUName];
			$followup[PrimaryLink] = $fdata[PrimaryLink];
			$followup[SecondaryLink] = $fdata[SecondaryLink];
	
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 4) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
				
			$followup = array();
			$query = "select Title, SnapShotFrame, WriteUp, MapLink, InterestLink, Depicted, Actual, Latitude, Longitude, StreetLatitude, StreetLongitude, Zoom, CameraZoom, Heading, Pitch, Type, MapImage from TblLocations where LocationsID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[Title] = $fdata[Title];
			$followup[SnapShotFrame] = $fdata[SnapShotFrame];
			$followup[WriteUp] = $fdata[WriteUp];
			$followup[MapLink] = $fdata[MapLink];
			$followup[InterestLink] = $fdata[InterestLink];
			$followup[Depicted] = $fdata[Depicted];
			$followup[Actual] = $fdata[Actual];
			$followup[Latitude] = $fdata[Latitude];
			$followup[Longitude] = $fdata[Longitude];
			$followup[StreetLatitude] = $fdata[StreetLatitude];
			$followup[StreetLongitude] = $fdata[StreetLongitude];
			$followup[Zoom] = $fdata[Zoom];
			$followup[CameraZoom] = $fdata[CameraZoom];
			$followup[Heading] = $fdata[Heading];
			$followup[Pitch] = $fdata[Pitch];
			$followup[Type] = $fdata[Type];
			$followup[MapImage] = $fdata[MapImage];
	
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 5) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
				
			$followup = array();
			$query = "select MusicID, Song, Artist, Album, AlbumCover, SongYear, SnapShotFrame, Substitute, SubSong, SubArtist from TblMusic where MusicID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[MusicID] = $fdata[MusicID];
			$followup[Song] = $fdata[Song];
			$followup[Artist] = $fdata[Artist];
			$followup[Album] = $fdata[Album];
			$followup[AlbumCover] = $fdata[AlbumCover];
			$followup[SongYear] = $fdata[SongYear];
			$followup[SnapShotFrame] = $fdata[SnapShotFrame];
			$followup[Substitute] = $fdata[Substitute];
			$followup[SubSong] = $fdata[SubSong];
			$followup[SubArtist] = $fdata[SubArtist];
		
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 6) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
				
			$followup = array();
			$query = "select WhyID, WhyName, WriteUp from TblWhy where WhyID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[WhyID] = $fdata[WhyID];
			$followup[WhyName] = $fdata[WhyName];
			$followup[WriteUp] = $fdata[WriteUp];
	
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 7) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				if($row[IsCorrect]  == 1) {
					$who_id = $row[Answer];
				}
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
			
			$followup = array();
				
			$query = "select QuoteID, LineTop, LineBottom, Font, FontSize, SnapShotFrame, QuoteRating from TblQuote where QuoteID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$info[QuoteID] = $fdata[QuoteID];
			$info[LineTop] = $fdata[LineTop];
			$info[LineBottom] = $fdata[LineBottom];
			$info[Font] = $fdata[Font];
			$info[FontSize] = $fdata[FontSize];
			$info[SnapShotFrame] = $fdata[SnapShotFrame];
			$info[QuoteRating] = $fdata[QuoteRating];
				
			$query = "select WhoLUID, CharacterName, BioLink from TblWhoLU where WhoLUID = ". $who_id;
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[WhoLUID] = $fdata[WhoLUID];
			$followup[CharacterName] = $fdata[CharacterName];
			$followup[BioLink] = $fdata[BioLink];
	
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 8) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
			
			$followup = array();
			$query = "select RecipeID, RecipeName, SnapShotFrame, Quote, RecipeRating, Inspiration, PrepTime, CookTime, Servings, Ingredients, Directions, PhotoFile, RecipeCategoryName from TblRecipe left join TblRecipeCategory on TblRecipe.RecipeType = TblRecipeCategory.RecipeCategoryID where RecipeID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[RecipeID] = $fdata[RecipeID];
			$followup[RecipeName] = $fdata[RecipeName];
			$followup[SnapShotFrame] = $fdata[SnapShotFrame];
			$followup[Quote] = $fdata[Quote];
			$followup[RecipeRating] = $fdata[RecipeRating];
			$followup[Inspiration] = $fdata[Inspiration];
			$followup[PrepTime] = $fdata[PrepTime];
			$followup[CookTime] = $fdata[CookTime];
			$followup[Servings] = $fdata[Servings];
			$followup[Ingredients] = $fdata[Ingredients];
			$followup[Directions] = $fdata[Directions];
			$followup[PhotoFile] = $fdata[PhotoFile];
			$followup[RecipeCategoryName] = $fdata[RecipeCategoryName];
				
			$query = "select Name, Link from TblRecipeLinks where FK_RecipeID = ". $followup[RecipeID];
			$shoppinglinks = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Name] = $row[Name];
				$choice[Link] = $row[Link];
				$shoppinglinks[] = $choice;
			}
			$followup[ShoppingLinks] = $shoppinglinks;
		
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 9) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
			
			$followup = array();
			$query = "select ShoppingLUID, ShoppingItemName, TblShoppingLU.SnapShotFrame, StoreTitleActual, BuyLinkActual, ImageFileNameActual, StoreTitleSubstitute, BuyLinkSubstitute, ImageFileNameSubstitute, FK_WhoLUID from TblShoppingLU where ShoppingLUID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[ShoppingLUID] = $fdata[ShoppingLUID];
			$followup[ShoppingItemName] = $fdata[ShoppingItemName];
			$followup[SnapShotFrame] = $fdata[SnapShotFrame];
			$followup[StoreTitleActual] = $fdata[StoreTitleActual];
			$followup[BuyLinkActual] = $fdata[BuyLinkActual];
			$followup[ImageFileNameActual] = $fdata[ImageFileNameActual];
			$followup[StoreTitleSubstitute] = $fdata[StoreTitleSubstitute];
			$followup[BuyLinkSubstitute] = $fdata[BuyLinkSubstitute];
			$followup[ImageFileNameSubstitute] = $fdata[ImageFileNameSubstitute];
			$followup[FK_WhoLUID] = $fdata[FK_WhoLUID];
			
			if($followup[FK_WhoLUID] != -1) {
				$query = "select WhoLUID, CharacterName from TblWhoLU where WhoLUID = ". $followup[FK_WhoLUID];
				$stm = $dbh->query($query);
				$fdata = $stm->fetch(PDO::FETCH_ASSOC);
				$followup[WhoLUID] = $fdata[WhoLUID];
				$followup[CharacterName] = $fdata[CharacterName];
			}
	
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 10) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
			
			$followup = array();
			$query = "select SuperFanID, SuperFanName, SnapShotFrame, WriteUp from TblSuperFan where SuperFanID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[SuperFanID] = $fdata[SuperFanID];
			$followup[SuperFanName] = $fdata[SuperFanName];
			$followup[SnapShotFrame] = $fdata[SnapShotFrame];
			$followup[WriteUp] = $fdata[WriteUp];
	
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 11) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
			}
			$info[Choices] = $choices;
			
			$followup = array();
			$query = "select TriviaID, Question, Fact, SnapShotFrame from TblTriviaLU where TriviaID = ". $info[LinkID];
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[TriviaID] = $fdata[TriviaID];
			$followup[Question] = $fdata[Question];
			$followup[Fact] = $fdata[Fact];
			$followup[SnapShotFrame] = $fdata[SnapShotFrame];
	
			$info[Followup] = $followup;
		}
		if($info[FK_GameTypeID] == 12) {
			$query = "select Answer, AnswerID, IsCorrect from TblGameMasterSeg where FK_GameMasterPlayID = ". $info[PlayOrder] ." order by AnswerID";
			$choices = array();
			foreach ($dbh->query($query) as $row) {
				$choice = array();
				$choice[Answer] = $row[Answer];
				$choice[AnswerID] = $row[AnswerID];
				$choice[IsCorrect] = $row[IsCorrect];
				$choices[] = $choice;
				
				if($row[IsCorrect]  == 1) {
					$who_id = $row[Answer];
				}
			}
			$info[Choices] = $choices;
			
			$followup = array();
			$query = "select WhoLUID, CharacterName, PerformerName, SnapShotFrame, BioLink, WhoApiID from TblWhoLU where WhoLUID = ". $who_id;
			$stm = $dbh->query($query);
			$fdata = $stm->fetch(PDO::FETCH_ASSOC);
			$followup[WhoLUID] = $fdata[WhoLUID];
			$followup[CharacterName] = $fdata[CharacterName];
			$followup[PerformerName] = $fdata[PerformerName];
			$followup[SnapShotFrame] = $fdata[SnapShotFrame];
			$followup[BioLink] = $fdata[BioLink];
			$followup[WhoApiID] = $fdata[WhoApiID];
		
			$info[Followup] = $followup;
		}
	
		$game[] = $info;
			
	
	}
	echo json_encode($game);
}
if($_POST["request"] == "multiGamePlay") {
	$query = "SELECT playerOne, playerTwo, deviceOne, deviceTwo FROM pt_gamedata WHERE gid = $_POST[gid]";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$output = array();
	if($_POST[uid] == $row[playerOne]) {
		if($row[deviceOne] == "") {
			$query = "UPDATE pt_gamedata SET deviceOne = '$_POST[uuid]' WHERE gid = $_POST[gid]";
			$mysql->query($query);
			$output[status] = "success";
			$output[message] = "";
		} else if($row[deviceOne] == $_POST[uuid]) {
			$output[status] = "success";
			$output[message] = "";
		} else {
			$output[status] = "error";
			$output[message] = "Sorry, you can only play this game from the device you started it on...";
		}
	}
	if($_POST[uid] == $row[playerTwo]) {
		if($row[deviceTwo] == "") {
			$query = "UPDATE pt_gamedata SET deviceTwo = '$_POST[uuid]' WHERE gid = $_POST[gid]";
			$mysql->query($query);
			$output[status] = "success";
			$output[message] = "";
		} else if($row[deviceTwo] == $_POST[uuid]) {
			$output[status] = "success";
			$output[message] = "";
		} else {
			$output[status] = "error";
			$output[message] = "Sorry, this game is already in progress on your other device.";
		}
	}
	
	echo json_encode($output);
}
if($_POST["request"] == "multiGameMessage") {
	$output = array();
	
	$params = array($_POST[message], $_POST[gid], $_POST[uid]);
	
	$query = "UPDATE pt_gamedata SET messageOne = ? WHERE gid = ? AND playerOne = ?";
	$stmt = $mysql->prepare($query);
	$stmt->execute($params);
	
	$query = "UPDATE pt_gamedata SET messageTwo = ? WHERE gid = ? AND playerTwo = ?";
	$stmt = $mysql->prepare($query);
	$stmt->execute($params);
	
	$output[status] = "success";
	
	echo json_encode($output);
}

if($_POST["request"] == "multiGameChallengeRequest") {
	$questions = generateQuestionData($_POST[category], $mysql);
	$query = "INSERT INTO pt_gamedata (category, challenge_game, challenge, playerOne, questions, timer, timestamp) VALUES ($_POST[category], 1, $_POST[opponent], $_POST[uid], '$questions', $now, $now)";
	$result = $mysql->exec($query);
	$gid = $mysql->lastInsertId();
	
	sendPushToUser($_POST[opponent], 2, $mysql);
	
	$output[status] = "success";
	echo json_encode($output);
}
if($_POST["request"] == "multiGameChallengeAccept") {
	$now = time();
	$query = "UPDATE pt_gamedata SET challenge = 0, timer = $now, playerTwo = $_POST[uid] WHERE gid = $_POST[gid]";
	$result = $mysql->exec($query);
	
	$query = "SELECT playerOne FROM pt_gamedata WHERE gid = $_POST[gid]";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	sendPushToUser($row[playerOne], 1, $mysql);
	
	$output[status] = "success";
	echo json_encode($output);
}
if($_POST["request"] == "multiGameChallengeDecline") {
	$query = "UPDATE pt_gamedata SET timer = 0 WHERE gid = $_POST[gid]";
	$result = $mysql->exec($query);
	
	$output[status] = "success";
	echo json_encode($output);
}


if($_POST[request] == "userFavorites") {
	$output = array();
	
	$query = "SELECT pt_users.uid, name, popcorn, sp_points, mp_points, sp_points + mp_points as points, mp_games, mp_wins, mp_draws, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon FROM pt_users JOIN pt_favorites ON pt_users.uid = pt_favorites.faveid WHERE pt_favorites.uid = $_POST[uid] ORDER BY name LIMIT $_POST[start], $_POST[step]";
	$stmt = $mysql->query($query);
	$users = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$user = array("uid"=>$row[uid], "name"=>stripslashes($row[name]), "position"=>(string)$position, "popcorn"=>(string)$row[popcorn], "points"=>(string)$row[points], "sp_points"=>(string)$row[sp_points], "mp_points"=>(string)$row[mp_points], "mp_games"=>(string)$row[mp_games], "mp_wins"=>(string)$row[mp_wins], "mp_draws"=>(string)$row[mp_draws], "pops_backdrop"=>(string)$row[pops_backdrop], "pops_body"=>(string)$row[pops_body], "pops_eyes"=>(string)$row[pops_eyes], "pops_mouth"=>(string)$row[pops_mouth], "pops_hat"=>(string)$row[pops_hat], "pops_left_arm"=>(string)$row[pops_left_arm], "pops_right_arm"=>(string)$row[pops_right_arm], "pops_weapon"=>(string)$row[pops_weapon]);
		$users[] = $user;
	}
	
	$output = $users;
	
	echo json_encode($output);
}
if($_POST[request] == "userFavoritesAdd") {
	$output = array();
	
	$query = "REPLACE INTO pt_favorites (uid, faveid) VALUES ($_POST[uid], $_POST[faveid])";
	$result = $mysql->exec($query);
	
	$output[status] = "success";
	
	echo json_encode($output);
}
if($_POST[request] == "userFacebook") {
	$output = array();
	
	$query = "UPDATE pt_users SET facebook = '$_POST[fbid]' WHERE uid = $_POST[uid]";
	$result = $mysql->exec($query);
	$output[status] = "success";
	
	echo json_encode($output);
}
if($_POST[request] == "userUpdateFacebookInvites") {
	$output = array();

	$query = "REPLACE INTO pt_pyramid (uid, fid) VALUES ((SELECT uid FROM pt_users WHERE facebook = '$_POST[inviter]'), $_POST[uid])";
	$result = $mysql->exec($query);
	$output[status] = "success";
	
	echo json_encode($output);
}
if($_POST[request] == "userPyramidStats") {
	$output = array();

	$query = "SELECT fid, name, facebook, weekly_popcorn FROM pt_pyramid LEFT JOIN pt_users ON pt_pyramid.fid = pt_users.uid WHERE pt_pyramid.uid = $_POST[uid]";
	$stmt = $mysql->query($query);
	$users = array();
	
	$popcorn_earned = 0;
	$friends_joined = 0;
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$user = array("uid"=>$row[fid], "name"=>stripslashes($row[name]), "fbid"=>$row[facebook], "popcorn"=>(string)ceil($row[weekly_popcorn] * 0.5));
		$users[] = $user;
		$friends_joined++;
		$popcorn_earned = $popcorn_earned + ceil($row[weekly_popcorn] * 0.5); //EARN 50% OF POPCORN, ADJUST TO CORRESPOND TO USERSETTINGS
	}
	if($popcorn_earned > 1000) $popcorn_earned = 1000; //UPDATE TO CORRESPOND TO BUCKET SIZE IN USERSETTINGS
	
	$output[status] = "success";
	$output[popcorn_earned] = (string)$popcorn_earned;
	$output[friends_joined] = (string)$friends_joined;
	$output[users] = $users;
	
	echo json_encode($output);
}
if($_POST[request] == "userPyramidPopcorn") {
	$output = array();

	$query = "SELECT SUM(weekly_popcorn) as popcorn FROM pt_pyramid LEFT JOIN pt_users ON pt_pyramid.fid = pt_users.uid WHERE pt_pyramid.uid = $_POST[uid]";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$popcorn = $row[popcorn];
	
	$query = "UPDATE pt_users SET weekly_popcorn = 0 WHERE uid IN (SELECT fid FROM pt_pyramid WHERE uid = $_POST[uid])";
	$result = $mysql->exec($query);
	
	//MATH TO BE ADJUSTED FOR AWARD....
	$popaward = ceil($popcorn * 0.5);
	if($popaward > 1000) $popaward = 1000;
	
	$query = "UPDATE pt_users SET popcorn = popcorn + $popaward WHERE uid = $_POST[uid]";
	$result = $mysql->exec($query);
	
	$output[popaward] = (string)$popaward;
	$output[status] = "success";
	
	echo json_encode($output);
}
if($_POST[request] == "userFriends") {
	$output = array();
	
	$query = "UPDATE pt_users SET facebook = '$_POST[fbid]' WHERE uid = $_POST[uid]";
	$result = $mysql->exec($query);
	
	$friends = json_decode($_POST[friends]);
	if(count($friends) > 0) {
		$query = "SELECT uid, name, facebook, mp_points, mp_games, mp_wins, mp_draws, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon FROM pt_users WHERE (facebook = ";
		foreach($friends as $friend) {
			$query = $query ."'$friend' OR facebook = "; 
		}
		$query = substr($query, 0, -15);
		$query = $query .") ORDER BY name LIMIT $_POST[start], $_POST[step]";
		
		$stmt = $mysql->query($query);
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$user = array("uid"=>$row[uid], "fbid"=>(string)$row[facebook], "name"=>stripslashes($row[name]), "mp_points"=>(string)$row[mp_points], "mp_games"=>(string)$row[mp_games], "mp_wins"=>(string)$row[mp_wins], "mp_draws"=>(string)$row[mp_draws], "pops_backdrop"=>(string)$row[pops_backdrop], "pops_body"=>(string)$row[pops_body], "pops_eyes"=>(string)$row[pops_eyes], "pops_mouth"=>(string)$row[pops_mouth], "pops_hat"=>(string)$row[pops_hat], "pops_left_arm"=>(string)$row[pops_left_arm], "pops_right_arm"=>(string)$row[pops_right_arm], "pops_weapon"=>(string)$row[pops_weapon]);
			$output[] = $user;
		}
	}

	echo json_encode($output);
}
if($_POST[request] == "userSearch") {
	$leaderboard = array();
	$query = "SELECT uid, name, mp_points, mp_games, mp_wins, mp_draws, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon FROM pt_users WHERE uid != $_POST[uid] AND name NOT LIKE 'DELETED %' AND robot = 0 AND name LIKE '%$_POST[name]%' ORDER BY mp_points DESC, name LIMIT $_POST[start], $_POST[step]";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$user = array("uid"=>$row[uid], "name"=>stripslashes($row[name]), "mp_points"=>(string)$row[mp_points], "mp_games"=>(string)$row[mp_games], "mp_wins"=>(string)$row[mp_wins], "mp_draws"=>(string)$row[mp_draws], "pops_backdrop"=>(string)$row[pops_backdrop], "pops_body"=>(string)$row[pops_body], "pops_eyes"=>(string)$row[pops_eyes], "pops_mouth"=>(string)$row[pops_mouth], "pops_hat"=>(string)$row[pops_hat], "pops_left_arm"=>(string)$row[pops_left_arm], "pops_right_arm"=>(string)$row[pops_right_arm], "pops_weapon"=>(string)$row[pops_weapon]);
		$leaderboard[] = $user;
	}
	echo json_encode($leaderboard);
}
if($_POST[request] == "getLeaderboard") {
	$output = array();
	
	$leaderboard = array();
	$position = $_POST[start] + 1;
	
	
	if($_POST[order] == "mp_points") {
		$query = "SELECT uid, name, popcorn, sp_points, mp_points, sp_points + mp_points as points, mp_games, mp_wins, mp_draws, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon FROM pt_users WHERE mp_points > 0 AND robot = 0 AND name NOT LIKE 'DELETED %' ORDER BY $_POST[order] DESC, name LIMIT $_POST[start], $_POST[step]";
	} else if($_POST[order] == "sp_points") {
		$query = "SELECT uid, name, popcorn, sp_points, mp_points, sp_points + mp_points as points, mp_games, mp_wins, mp_draws, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon FROM pt_users WHERE sp_points > 0 AND robot = 0 AND name NOT LIKE 'DELETED %' ORDER BY $_POST[order] DESC, name LIMIT $_POST[start], $_POST[step]";
	} else {
		$query = "SELECT uid, name, popcorn, sp_points, mp_points, sp_points + mp_points as points, mp_games, mp_wins, mp_draws, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon FROM pt_users WHERE sp_points + mp_points > 0 AND robot = 0 AND name NOT LIKE 'DELETED %' ORDER BY $_POST[order] DESC, name LIMIT $_POST[start], $_POST[step]";
	}
	
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$user = array("uid"=>$row[uid], "name"=>stripslashes($row[name]), "position"=>(string)$position, "popcorn"=>(string)$row[popcorn], "points"=>(string)$row[points], "sp_points"=>(string)$row[sp_points], "mp_points"=>(string)$row[mp_points], "mp_games"=>(string)$row[mp_games], "mp_wins"=>(string)$row[mp_wins], "mp_draws"=>(string)$row[mp_draws], "pops_backdrop"=>(string)$row[pops_backdrop], "pops_body"=>(string)$row[pops_body], "pops_eyes"=>(string)$row[pops_eyes], "pops_mouth"=>(string)$row[pops_mouth], "pops_hat"=>(string)$row[pops_hat], "pops_left_arm"=>(string)$row[pops_left_arm], "pops_right_arm"=>(string)$row[pops_right_arm], "pops_weapon"=>(string)$row[pops_weapon]);
		$leaderboard[] = $user;
		$position++;
	}
	
	if($_POST[order] == "points") {
		$query = "SELECT count(*) AS position FROM pt_users WHERE robot = 0 AND name NOT LIKE 'DELETED %' AND ((mp_points + sp_points) > ?) OR (((mp_points + sp_points) = ?) AND (name < ?) AND name NOT LIKE 'DELETED %')";
		$stmt = $mysql->prepare($query);
		$stmt->execute(array($_POST[points], $_POST[points], $_POST[name]));
	}
	if($_POST[order] == "sp_points") {
		$query = "SELECT count(*) AS position FROM pt_users WHERE robot = 0 AND name NOT LIKE 'DELETED %' AND (sp_points > ?) OR ((sp_points = ?) AND (name < ?) AND name NOT LIKE 'DELETED %')";
		$stmt = $mysql->prepare($query);
		$stmt->execute(array($_POST[sp_points], $_POST[sp_points], $_POST[name]));
	}
	if($_POST[order] == "mp_points") {
		$query = "SELECT count(*) AS position FROM pt_users WHERE robot = 0 AND name NOT LIKE 'DELETED %' AND (mp_points > ?) OR ((mp_points = ?) AND (name < ?) AND name NOT LIKE 'DELETED %')";
		$stmt = $mysql->prepare($query);
		$stmt->execute(array($_POST[mp_points], $_POST[mp_points], $_POST[name]));
	}
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	
	$output[myposition] = (string)($row[position] + 1);
	$output[users] = $leaderboard;
	
	echo json_encode($output);
}
if($_POST[request] == "updateUsername") {
	$output = array();
	if(strlen($_POST[name]) > 20) {
		$output[status] = "fail";
		$output[error] = "Sorry, username exceeds maximum of 20 characters...";
	} else {
		$query = "UPDATE pt_users SET name = ?, name_updated = ? WHERE uid = $_POST[uid]";
		$stmt = $mysql->prepare($query);
		$result = $stmt->execute(array($_POST[name], 1));
		
		if($result) {
			$output[status] = "success";
			$query = "INSERT INTO pt_gifts (uid, giftid, status) VALUES ($_POST[uid], 19, 0)";
			$result = $mysql->exec($query);
		} else {
			$output[status] = "fail";
			$output[error] = "Sorry, this username is not available. Please choose a different one...";
		}
	}
	
	echo json_encode($output);
}

if($_POST[request] == "movieVotes") {
	$query = "SELECT mid, endtime, movie0, boxart0, votes0, movie1, boxart1, votes1, movie2, boxart2, votes2, movie3, boxart3, votes3 FROM pt_movievote WHERE active = 1 ORDER BY endtime DESC";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$output = array();
	
	$output[voteid] = (string)$row[mid];
	$output[endtime] = (string)($row[endtime] - $now);
	if(($row[endtime] - $now) > 0) {
		$output[text] = "WHICH MOVIE SHOULD WE MAKE NEXT?";
	} else {
		$output[text] = "PLAYER'S CHOICE";
	}
	
	
	
	
	$movies = array();
	
	$data0 = array();
	$data0[title] = $row[movie0];
	$data0[boxart] = "http://www.popcorntriviadownloads.com/voteimages/". $row[boxart0];
	$data0[votes] = (string)$row[votes0];
	$movies[] = $data0;
	
	$data1 = array();
	$data1[title] = $row[movie1];
	$data1[boxart] = "http://www.popcorntriviadownloads.com/voteimages/". $row[boxart1];
	$data1[votes] = (string)$row[votes1];
	$movies[] = $data1;
	
	$data2 = array();
	$data2[title] = $row[movie2];
	$data2[boxart] = "http://www.popcorntriviadownloads.com/voteimages/". $row[boxart2];
	$data2[votes] = (string)$row[votes2];
	$movies[] = $data2;
	
	$data3 = array();
	$data3[title] = $row[movie3];
	$data3[boxart] = "http://www.popcorntriviadownloads.com/voteimages/". $row[boxart3];
	$data3[votes] = (string)$row[votes3];
	$movies[] = $data3;
	
	$previous = array();
	$query = "SELECT mid, endtime, movie0, boxart0, votes0, movie1, boxart1, votes1, movie2, boxart2, votes2, movie3, boxart3, votes3 FROM pt_movievote WHERE active = 2 ORDER BY endtime DESC LIMIT 0, 10";
	
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$vals = array($row[votes0],$row[votes1],$row[votes2],$row[votes3]);
		$max = max($vals);
		$index = array_search($max, $vals);
		$movie_field = "movie". $index;
		$boxart_field = "boxart". $index;
		$votes_field = "votes". $index;
		
		$data = array();
		$data[title] = $row[$movie_field];
		$data[boxart] = "http://www.popcorntriviadownloads.com/voteimages/". $row[$boxart_field];
		$data[vote] = number_format($row[$votes_field] * 100 / ($row[votes0] + $row[votes1] + $row[votes2] + $row[votes3]));
		
		$previous[] = $data;
	}
	
	
	
	$output[movies] = $movies;
	$output[previous] = $previous;
	$output[status] = "success";
		
	
	echo json_encode($output);
}
if($_POST[request] == "movieVotesSubmit") {
	$vote = "votes". $_POST[vote];
	$query = "UPDATE pt_movievote SET $vote = $vote + 1 WHERE mid = $_POST[voteid] AND endtime > $now";
	$mysql->query($query);
	
	$output = array();
	$output[status] = "success";
	echo json_encode($output);
}

if($_POST[request] == "movieSponsor") {
	$output = array();
	$output[movies] = getSponsoredMovieList($mysql, $_POST[uid]);
	
	$query = "SELECT DISTINCT itunesid FROM pt_sponsor_reserved WHERE 1 UNION SELECT DISTINCT itunesid FROM pt_sponsor WHERE status = 1 UNION SELECT DISTINCT itunesid FROM movies WHERE popcornactive = 0";
	$stmt = $mysql->query($query);

	$exclude_prod = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$exclude_prod[] = (string)trim($row[itunesid]);
	}
	
	$query = "SELECT DISTINCT itunesid FROM pt_sponsor WHERE status = 0";
	$stmt = $mysql->query($query);

	$exclude_vote = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$exclude_vote[] = (string)trim($row[itunesid]);
	}
	
	$query = "SELECT DISTINCT itunesid FROM movies WHERE popcornactive = 1 OR popcornseriesactive = 1 OR popcornbonus > 0 UNION SELECT DISTINCT itunesid FROM pt_sponsor_reserved WHERE alternate = 1";
	$stmt = $mysql->query($query);

	$exclude_active = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$exclude_active[] = (string)trim($row[itunesid]);
	}
	
	//$endtime = strtotime("next Saturday");
	//$todaytime = strtotime("today");
	//if($endtime == ($todaytime + 7*24*3600)) $endtime = $todaytime;
	//$endtime = 1492362000;
	$endtime = 1505750400;
	while($now > $endtime) {
		$endtime = $endtime + 7 * 24 * 3600;
	}
	
	
	
	$output[exclude_active] = $exclude_active;
	$output[exclude_vote] = $exclude_vote;
	$output[exclude_prod] = $exclude_prod;
	//$output[endtime] = (string)($endtime - $now + 3600 * 23); //ACCOUNT FOR ONE HOUR DIFFERENCE FROM SERVER TO EST
	$output[endtime] = (string)($endtime - $now); //ACCOUNT FOR ONE HOUR DIFFERENCE FROM SERVER TO EST
	$output[text] = "YOU HAVE 3 VOTES PER MOVIE EACH DAY";
	$output[status] = "success";	
	
	$settings = array();
	$settings[price_sponsor] = "50000";
	$settings[price_vote] = "100";
	$settings[winner_votes] = "1"; //0 - SHOW HOW MANY VOTES WINNER GOT, 1 - HIDE WINNER VOTE COUNT
	$settings[period] = (string)$endtime;
	$settings[sponsor_no_message] = "Sorry, you can sponsor only one movie per week...";
	$settings[sponsor_frequency] = "1"; //0 - NO RESTRICTION, 1 - ONCE A WEEK, 2 - ONLY ONE MOVIE IN VOTING, 3 - ONE MOVIE EVER.....
	$settings[sponsor_filter] = "2"; //0 - Vote Count, 1 - A-Z, 2 - LATEST ADDED
	
	$output[settings] = $settings;
		
		
	echo json_encode($output);
}
if($_POST[request] == "movieSponsorAdd") {
	$date = explode("-",$_POST[year]);
	$year = $date[0];
	$boxart = str_replace("100x100", "250x250", $_POST[boxart]);
	
	$query = "INSERT INTO pt_sponsor (itunesid, title, year, boxart, uid, timestamp) VALUES (?,?,?,?,?,?)";
	$params = array($_POST[itunesid],$_POST[title],$year,$boxart,$_POST[uid],$now);
	$stmt = $mysql->prepare($query);
	$result = $stmt->execute($params);
	
	if($result) {
		$output[status] = "success";	
	} else {
		$output[status] = "failed";	
	}
	$output[movies] = getSponsoredMovieList($mysql, $_POST[uid]);
	
	echo json_encode($output);
}
if($_POST[request] == "movieSponsorVote") {
	//$date = explode("-",$_POST[year]);
	//$year = $date[0];
	//$boxart = str_replace("100x100", "250x250", $_POST[boxart]);
	
	//CHECK FOR SELF VOTES
	$query = "SELECT supporters, mysupport, uid, timestamp FROM pt_sponsor WHERE sid = $_POST[sid]";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$debug = array();
	
	$can_vote = true;
	if($row[uid] == $_POST[uid]) {
		$debug[] = "My Movie";
		$days = ($now - $row[timestamp]) / (3600 * 24);
		$debug[] = $days ." days";
		if(($row[mysupport] / $days) > 3) {
			$can_vote = false;
			$debug[] = "shouldn't vote";
		}
	}
	
	if($can_vote) {
		$query = "UPDATE pt_sponsor SET supporters = supporters + 1, vote_pop = vote_pop + $_POST[vote_pop], vote_social = vote_social + $_POST[vote_social], vote_ad = vote_ad + $_POST[vote_ad]  WHERE sid = $_POST[sid]";
		$mysql->query($query);
		
		$query = "UPDATE pt_sponsor SET mysupport = mysupport + 1 WHERE sid = $_POST[sid] AND uid = $_POST[uid]";
		$mysql->query($query);
	}
	
	$output = array();
	$output[status] = "success";
	$output[debug] = $debug;
	$output[movies] = getSponsoredMovieList($mysql, $_POST[uid]);
	
	echo json_encode($output);
}
if($_POST[request] == "movieSponsorVoteV2") {
	$activeUser = isActiveUser($_POST[uid], $_POST[uuid], $mysql);
	$output = array();
	if($activeUser) {
		$query = "UPDATE pt_sponsor SET supporters = supporters + 1, vote_pop = vote_pop + $_POST[vote_pop], vote_social = vote_social + $_POST[vote_social], vote_ad = vote_ad + $_POST[vote_ad]  WHERE sid = $_POST[sid]";
		$mysql->query($query);
			
		$query = "UPDATE pt_sponsor SET mysupport = mysupport + 1 WHERE sid = $_POST[sid] AND uid = $_POST[uid]";
		$mysql->query($query);
		
		
		if($_POST[vote_pop] == 1) $date_to_update = "date_pop";
		if($_POST[vote_social] == 1) $date_to_update = "date_social";
		if($_POST[vote_ad] == 1) $date_to_update = "date_ad";
		
		$query = "SELECT vid FROM pt_sponsor_votes WHERE uid = $_POST[uid] AND itunesid = '$_POST[itunesid]'";
		$stmt = $mysql->query($query);
		$count = $stmt->rowCount();
	
		if($count == 0) {
			$query = "INSERT INTO pt_sponsor_votes (uid, itunesid, $date_to_update) VALUES ($_POST[uid], '$_POST[itunesid]','$_POST[today]')";
			$mysql->query($query);
		} else {
			$query = "UPDATE pt_sponsor_votes SET $date_to_update = '$_POST[today]' WHERE uid = $_POST[uid] AND itunesid = '$_POST[itunesid]'";
			$mysql->query($query);
		}
		
		$output[status] = "success";
	} else {
		$output[status] = "error";
		$output[error] = "Sorry, you're not using the active device for this account, and your request can not be processed... Please either close the app and open it again to become the active device, or use your other device.";
	}
	
	//$output[debug] = $debug;
	$output[movies] = getSponsoredMovieList($mysql, $_POST[uid]);
	
	echo json_encode($output);
}


function isActiveUser($uid, $uuid, $mysql) {
	$query = "SELECT uid, activedevice FROM pt_users WHERE uid = $uid AND activedevice = '$uuid'";
	$stmt = $mysql->query($query);
	$count = $stmt->rowCount();

	if($count == 0) {
		return false;
	} else {
		return true;
	}
}
function getUserDataForId($uid, $version, $mysql) {
	$output = array();
	
	$query = "SELECT * FROM pt_users WHERE uid = $uid";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$output[uid] = (string)$row[uid];
	$output[name] = stripslashes($row[name]);
	$output[fbid] = (string)$row[facebook];
	$output[name_updated] = (string)$row[name_updated];
	//FIX FOR RANDY deFAUL:T SAVE
	if(strpos($output[name], "Randy deFault") !== false) {
		$output[name_updated] = "0";
	}
	$output[popcorn] = (string)$row[popcorn];
	$output[points] = (string)($row[sp_points] + $row[mp_points]);
	$output[sp_points] = (string)$row[sp_points];
	$output[mp_points] = (string)$row[mp_points];
	$output[mp_games] = (string)$row[mp_games];
	$output[mp_wins] = (string)$row[mp_wins];
	$output[mp_draws] = (string)$row[mp_draws];
	
	if(($row[pops_backdrop] == 169) && ($row[pops_body] == 1) && ($row[pops_eyes] == 0) && ($row[pops_mouth] == 0) && ($row[pops_hat] == 0) && ($row[pops_left_arm] == 106) && ($row[pops_right_arm] == 107) && ($row[pops_weapon] == 0)) {
		$output[custom_avatar] = "0";
	} else {
		$output[custom_avatar] = "1";
	}
	
	$avatar = array();
	$avatar['Backdrop'] = (string)$row[pops_backdrop];
	$avatar['Body'] = (string)$row[pops_body];
	$avatar['Eyes'] = (string)$row[pops_eyes];
	$avatar['Mouth'] = (string)$row[pops_mouth];
	$avatar['Hat'] = (string)$row[pops_hat];
	$avatar['Left Arm'] = (string)$row[pops_left_arm];
	$avatar['Right Arm'] = (string)$row[pops_right_arm];
	$avatar['Weapon'] = (string)$row[pops_weapon];
		
	$output[avatar] = $avatar;
	
	
	$purchased = array();
	$query = "SELECT bid FROM pt_avatar_purchased WHERE uid = $uid";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$purchased[] = (string)$row[bid];
	}
	$output[mybodyparts] = $purchased;
	
	
	$mymovies = array();
	$query = "SELECT movieid FROM pt_movies_purchased WHERE uid = $uid";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$mymovies[] = (string)$row[movieid];
	}
	$output[mymovies] = $mymovies;
	
	
	$completed = array();
	$query = "SELECT movieid	, actid, points FROM pt_acts WHERE uid = $uid";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$key = (string)$row[movieid];
		$act = "act". $row[actid];
		$completed[$key][$act] = (string)$row[points];
	}
	$output[completed] = (object)$completed;
	
	$gifts = array();
	if($version >= 3.6) {
		$query = "SELECT giftid, status FROM pt_gifts WHERE uid = $uid";
	} else {
		$query = "SELECT giftid, status FROM pt_gifts WHERE uid = $uid AND giftid < 13";
	}
	
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$key = $row[giftid];
		$gifts[$key] = $row[status];
	}
	$output[gifts] = (object)$gifts;
	
	return $output;
}
function getSimpleUserDataForId($uid, $mysql) {
	$output = array();
	
	$query = "SELECT * FROM pt_users WHERE uid = $uid";
	$stmt = $mysql->query($query);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$output[uid] = (string)$row[uid];
	$output[name] = stripslashes($row[name]);
	$output[name_updated] = (string)$row[name_updated];
	$output[popcorn] = (string)$row[popcorn];
	$output[points] = (string)($row[sp_points] + $row[mp_points]);
	$output[sp_points] = (string)$row[sp_points];
	$output[mp_points] = (string)$row[mp_points];
	$output[mp_games] = (string)$row[mp_games];
	$output[mp_wins] = (string)$row[mp_wins];
	$output[mp_draws] = (string)$row[mp_draws];
	$output[robot] = (string)$row[robot];
	
	$avatar = array();
	$avatar['Backdrop'] = (string)$row[pops_backdrop];
	$avatar['Body'] = (string)$row[pops_body];
	$avatar['Eyes'] = (string)$row[pops_eyes];
	$avatar['Mouth'] = (string)$row[pops_mouth];
	$avatar['Hat'] = (string)$row[pops_hat];
	$avatar['Left Arm'] = (string)$row[pops_left_arm];
	$avatar['Right Arm'] = (string)$row[pops_right_arm];
	$avatar['Weapon'] = (string)$row[pops_weapon];
		
	$output[avatar] = $avatar;
	
	return $output;
}	
function generateQuestionData($category, $mysql) {
	$movies = array();
	$dupecheck = array();
	$query = "SELECT id FROM movies JOIN challenge_categories_mov ON movies.id = challenge_categories_mov.mid WHERE DATEDIFF(CURDATE(),streetdate) >= 0 AND (popcornactive = 1 OR popcornbonus > 0) AND categoryid = $category";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$movies[] = $row[id];
	}
	$questions = "";
	for($i=0; $i<10; $i++) {
		$random = mt_rand(0,count($movies) - 1);
		while(in_array($random, $dupecheck)) {
			$random = mt_rand(0,count($movies) - 1);
		}
		$dupecheck[] = $random;
		$questions .= $movies[$random] .":". mt_rand(1,30) .",";
	}
	$questions = substr($questions, 0, -1);
	
	return $questions;
}
function sendPushToUser($uid, $message, $mysql) {
	if(is_int($message)) {
		$query = "SELECT * FROM pt_pushmessages WHERE msgtype = $message ORDER BY RAND() LIMIT 1";
		$stmt = $mysql->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$message = $row[msgtext];
	}
	$query = "SELECT token, platform FROM pt_users LEFT JOIN pt_devices ON pt_users.activedevice = pt_devices.uuid WHERE pt_users.uid = $uid";
	$stmt = $mysql->query($query);
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		if($row[platform] == 1) {
			sendMessageApple($row[token], $message);
		}
		if($row[platform] == 2) {
			sendMessageAndroid($row[token], $message);
		}
		if($row[platform] == 3) {
			$auth = authorizePushWindows();
			sendMessageWindows($auth,$row[token],$message);
		}
	}
}
function setTimerAmount($delta) {
	$timer = "";
	if($delta > 0) {
		$timer = (string)intval($delta / 3600) ."h";
		if($timer == "0h") {
			$timer = "<1h";
		}
	} else {
		$timer = "Expired";
	}
	return $timer;
}
function getSponsoredMovieList($mysql, $uid) {
	//$query = "SELECT sid	, itunesid, title, year, boxart, supporters, pt_sponsor.timestamp, status, ourpick, timestamp_status, name, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon FROM pt_sponsor LEFT JOIN pt_users ON pt_sponsor.uid = pt_users.uid LEFT JOIN pt_sponsor_votes ON pt_sponsor.uid = pt_sponsor_votes.uid AND pt_sponsor.itunesid = pt_sponsor_votes.itunesid WHERE 1";
	$query = "SELECT sid, pt_sponsor.itunesid, title, year, boxart, trailer, supporters, pt_sponsor.timestamp, status, ourpick, timestamp_status, date_pop, date_social, date_ad, name, pops_backdrop, pops_body, pops_eyes, pops_mouth, pops_hat, pops_left_arm, pops_right_arm, pops_weapon, sp_points, mp_points, mp_games, mp_wins, mp_draws FROM pt_sponsor LEFT JOIN pt_users ON pt_sponsor.uid = pt_users.uid LEFT JOIN pt_sponsor_votes ON (pt_sponsor.itunesid = pt_sponsor_votes.itunesid AND pt_sponsor_votes.uid = $uid) WHERE status < 3";
	$stmt = $mysql->query($query);
	
	$movies = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$movie = array();
		$movie[sid] = (string)$row[sid];
		$movie[itunesid] = $row[itunesid];
		$movie[title] = $row[title];
		$movie[year] = $row[year];
		$movie[boxart] = $row[boxart];
		if($row[date_pop]) {
			$movie[date_pop] = $row[date_pop];
		} else {
			$movie[date_pop] = "";
		}
		if($row[date_social]) {
			$movie[date_social] = $row[date_social];
		} else {
			$movie[date_social] = "";
		}
		if($row[date_ad]) {
			$movie[date_ad] = $row[date_ad];
		} else {
			$movie[date_ad] = "";
		}
		//$movie[date_pop] = $row[date_pop];
		//$movie[date_social] = $row[date_social];
		//$movie[date_ad] = $row[date_ad];
		$movie[supporters] = $row[supporters]; //NEED TO FIX IN APP TO RETURN TO FORMATED NUMBERS... number_format($row[supporters]);
		$movie[supporters_sort] = (string)$row[supporters];
		$movie[status] = (string)$row[status];
		$movie[ourpick] = (string)$row[ourpick];
		$movie[timestamp] = (string)$row[timestamp];
		$movie[timestamp_status] = (string)$row[timestamp_status];
		$movie[name] = (string)$row[name];
		$avatar = array();
		$avatar['Backdrop'] = (string)$row[pops_backdrop];
		$avatar['Body'] = (string)$row[pops_body];
		$avatar['Eyes'] = (string)$row[pops_eyes];
		$avatar['Mouth'] = (string)$row[pops_mouth];
		$avatar['Hat'] = (string)$row[pops_hat];
		$avatar['Left Arm'] = (string)$row[pops_left_arm];
		$avatar['Right Arm'] = (string)$row[pops_right_arm];
		$avatar['Weapon'] = (string)$row[pops_weapon];
		$stats = array();
		$stats[name] = (string)$row[name];
		$stats[points] = (string)($row[sp_points] + $row[mp_points]);
		$stats[sp_points] = (string)$row[sp_points];
		$stats[mp_points] = (string)$row[mp_points];
		$stats[mp_games] = (string)$row[mp_games];
		$stats[mp_wins] = (string)$row[mp_wins];
		$stats[mp_draws] = (string)$row[mp_draws];
		
		$movie[stats] = $stats;
		$movie[avatar] = $avatar;
		
		$movies[] = $movie;
	}
	
	return $movies;
}

	
function sendMessageApple($token, $message) {
    $apnsHost = 'gateway.push.apple.com';
    $apnsPort = 2195;
    $apnsCert = 'popcorn-prod-cert2.pem';
  
    $payload['aps'] = array('alert' => $message , 'sound' => 'pushnotify.wav', 'badge' => 1);
    $payload = json_encode($payload);
    
    $streamContext = stream_context_create();
    stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
    $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
    
    $apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $token)) . chr(0) . chr(strlen($payload)) . $payload;
    fwrite($apns, $apnsMessage);
	
    fclose($apns);
}
function sendMessageAndroid($token, $message) {
    $apiKey = 'AIzaSyAoIDQCigW-Dd5e1qptKaSQ9s97j0Fb-IA';
    $registrationIDs = array( $token );
    $data = array( 'message' => urldecode($message) );
    
    $url = 'https://android.googleapis.com/gcm/send';
    $fields = array(
                    'registration_ids'  => $registrationIDs,
                    'data'              => $data,
                    );
    
    $headers = array(
                     'Authorization: key=' . $apiKey,
                     'Content-Type: application/json'
                     );
    
    // Open connection
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
    $result = curl_exec($ch);
    curl_close($ch);
}
function authorizePushWindows(){
	$apiKey = urlencode('Th3rMfOSumhq6Fq6uzDagUm');	
	$appId = urlencode('ms-app://s-1-15-2-981807083-30848534-1972440605-4155331106-985848221-2782754458-2623629205');
	$url = 'https://login.live.com/accesstoken.srf';
	$token_headers = array(
		'Content-Type'=>'application/x-www-form-urlencoded'
	);
	$token_body='grant_type=client_credentials&client_id='. $appId .'&client_secret=Th3rMfOSumhq6Fq6uzDagUm&scope=notify.windows.com';
	
	$tokenOptions = array(
		CURLOPT_POST              =>    true,
		CURLOPT_URL	              =>    $url,
		CURLOPT_RETURNTRANSFER    =>    true,
		CURLOPT_POSTFIELDS		  =>    $token_body,
		CURLOPT_HTTPHEADER     	  =>    $token_headers,
		CURLOPT_VERBOSE        =>    true
	);	

	$ch = curl_init();
	curl_setopt_array($ch,$tokenOptions);
	$response = curl_exec($ch);
	$json = json_decode($response,true);
	
	curl_close($ch);
	return $json["access_token"];
}
function sendMessageWindows($authToken,$userToken,$message){
	//for examples of all the crazy stuff you can do with windows push notifications
	//https://msdn.microsoft.com/windows/uwp/controls-and-patterns/tiles-and-notifications-adaptive-interactive-toasts
	$toast ='<toast><visual lang="en-US"><binding template="ToastGeneric"><text id="1">'.$message.'</text></binding></visual></toast>';

	$length =strlen($toast);
	$headers = array('Content-Type: text/xml', "Content-Length: " . strlen($toast), "X-WNS-Type: wns/toast", "Authorization: Bearer $authToken",'X-WNS-RequestForStatus: true');
	
	$pushOptions=array(
	    CURLOPT_POST           =>    true,
		CURLOPT_RETURNTRANSFER =>    true,
		CURLOPT_SSL_VERIFYPEER =>    false,
		CURLOPT_HTTPHEADER     =>    $headers,
		CURLOPT_POSTFIELDS     =>    "$toast",
		CURLOPT_SSL_VERIFYHOST =>    false
	);
	$ch = curl_init($userToken);
	curl_setopt_array($ch,$pushOptions);
	$response = curl_exec($ch);
	$info = curl_getinfo( $ch );
	
	curl_close($ch);
	return $info;
}	
	
	
?>
