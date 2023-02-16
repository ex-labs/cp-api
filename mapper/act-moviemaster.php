<?php
include('includes/config.php');

$dbfile = '../data-tv/'. $_GET[movieid] .'/'. $_GET[movieid] .'.s3db';
$dbname = 'sqlite:'. $dbfile;
$dbh = new PDO($dbname);


$query = "SELECT * FROM movies WHERE id = ". $_GET[movieid];
$stmt = $mysql->query($query);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

$now = time();

//UPDATE AND EXIT IF SAVING
if($_POST[action] == 1) {
	//print_r($_POST);
	
	//DILEMMAS
	if($_POST[departament] == 1) {
		if($_POST[select_character_1] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_1] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_1]", "$_POST[new_performer_1]", "$_POST[new_character_1]", "$_POST[new_imdb_1]");
			$stm->execute($array);
		}
		if($_POST[select_character_2] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_2] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_2]", "$_POST[new_performer_2]", "$_POST[new_character_2]", "$_POST[new_imdb_2]");
			$stm->execute($array);
		}
		if($_POST[select_character_3] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_3] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_3]", "$_POST[new_performer_3]", "$_POST[new_character_3]", "$_POST[new_imdb_3]");
			$stm->execute($array);
		}
		if($_POST[select_character_4] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_4] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_4]", "$_POST[new_performer_4]", "$_POST[new_character_4]", "$_POST[new_imdb_4]");
			$stm->execute($array);
		}
		
		
		if($_GET[questionid]) {
			$query = "SELECT LinkID FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$query = "UPDATE TblGameMasterLU SET Question = ? WHERE PK_GameMasterLU = $_GET[questionid]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[question]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_4]");
			$stm->execute($array);
			
			$query = "UPDATE TblDilemmas SET Recap = ?, FMR = ?, SMR = ? WHERE DilemmaID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[recap]", "$_POST[fmr]", "$_POST[smr]");
			$stm->execute($array);
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, Question, PlayOrder) VALUES (?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "$_POST[question]", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_4]", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblDilemmas (DilemmaID, DilemmaName, SnapShotFrame, Relationship, FMR, SMR, Recap, Question, Notify, FMRPercent, SMRPercent, QuestionYesPercent, QuestionNoPercent) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$linkid", "", "0", "0", "$_POST[fmr]", "$_POST[smr]", "$_POST[recap]", "", "0", "50", "50", "50", "50",);
			$stm->execute($array);
		}

		if($_FILES[performer_snap_1]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_1] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_1']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_2]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_2] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_2']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_3]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_3] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_3']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_4]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_4] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_4']['tmp_name'], $file);
		}

		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//VEHICLES & WEAPONS
	if(($_POST[departament] == 2) || ($_POST[departament] == 3)) {
		if($_GET[questionid]) {
			$query = "SELECT LinkID, SnapShotFrame, SnapShotFrame2 FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			$linkid = $question[LinkID];
			
			$query = "UPDATE TblGameMasterLU SET Question = ? WHERE PK_GameMasterLU = $_GET[questionid]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[question]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer4]");
			$stm->execute($array);
			
			$query = "UPDATE TblIdsLU SET IdsLUName = ?, PrimaryLink = ?, SecondaryLink = ? WHERE IdsLUID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[item_name]", "$_POST[primary_link]", "$_POST[secondary_link]");
			$stm->execute($array);
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe2 = $mysql->lastInsertId();
			
			$question[SnapShotFrame] = $snapshotframe;
			$question[SnapShotFrame2] = $snapshotframe2;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "$snapshotframe", "$snapshotframe2", "$_POST[question]", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer4]", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblIdsLU (IdsLUID, IdsLUName, SnapShotFrame, FK_IdsCategoryID, PrimaryLink, SecondaryLink) VALUES (?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			if($_POST[departament] == 2) $fk_id = 1;
			else $fk_id = 2; 
			$array = array("$linkid", "$_POST[item_name]", "0", "$fk_id", "$_POST[primary_link]", "$_POST[secondary_link]");
			$stm->execute($array);
		}

		if($_FILES[question_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg';
			move_uploaded_file($_FILES['question_snap']['tmp_name'], $file);
		}
		if($_FILES[answer_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame2] .'.jpg';
			move_uploaded_file($_FILES['answer_snap']['tmp_name'], $file);
		}
		
		$dir = '../api.customplay.com/data/'. $_GET[movieid];
		if(!file_exists($dir)) {
			mkdir($dir);
		}
		$dir = '../api.customplay.com/data/'. $_GET[movieid] .'/WebSC';
		if(!file_exists($dir)) {
			mkdir($dir);
		}
		if($_FILES[primary_link_snap]) {
			$file = '../api.customplay.com/data/'. $_GET[movieid] .'/WebSC/'. $linkid .'_1.jpg';
			move_uploaded_file($_FILES['primary_link_snap']['tmp_name'], $file);
		}
		if($_FILES[secondary_link_snap]) {
			$file = '../api.customplay.com/data/'. $_GET[movieid] .'/WebSC/'. $linkid .'_2.jpg';
			move_uploaded_file($_FILES['secondary_link_snap']['tmp_name'], $file);
		}
		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}	
	
	//LOCATIONS
	if($_POST[departament] == 4) {
		$lat = 0;
		$lon = 0;
		$lat_str = 0;
		$lon_str = 0;
		$zoom = 19;
		$zoom_str = 2;
		$heading = 0;
		$pitch = 0;
		$maptype = "";
		$mapurl = $_POST[map_link];
			
			
		//NEED TO PARSE MAP URL
		if(strpos($mapurl, "maps.google") !== false) { //OLD STYLE GOOGLE LINK
			$mapurl = str_replace("https://maps.google.com/maps?", "", $mapurl);
			$mapurl = str_replace("https://maps.google.ca/maps?", "", $mapurl);
			$maptype = "Map";
			$data = explode("&",$mapurl);
			foreach($data as $string) {
				$vars = explode('=', $string);
				if($vars[0] == "ll") {
					$coords = explode(',', $vars[1]);
					$lat = $coords[0];
					$lon = $coords[1];
				}
				if($vars[0] == "z") {
					$zoom = $vars[1];
				}
				if($vars[0] == "cbll") {
					$coords = explode(',', $vars[1]);
					if(($lat == 0) || ($lon == 0)) {
						$lat = $coords[0];
						$lon = $coords[1];
					}
					$lat_str = $coords[0];
					$lon_str = $coords[1];
					$maptype = "Street";
				}
				if($vars[0] == "cbp") {
					$coords = explode(',', $vars[1]);
					$zoom_str = 2;
					$heading = $coords[1];
					$pitch = round((-1)*$coords[4], 2);
				}
			}
		} else { //NEW STYLE GOOGLE LINK
			$data = explode("/", $mapurl);
			foreach($data as $string) {
				if(substr($string, 0, 1) == "@") {
					$string = substr($string, 1, strlen($string));
					$coords = explode(",", $string);
					if(count($coords) > 3) { //SHOULD BE STREETVIEW
						$lat = $coords[0];
						$lon = $coords[1];
						$lat_str = $coords[0];
						$lon_str = $coords[1];
						$tmp = str_replace("y","",$coords[3]);
						if($tmp < 40) $zoom_str = 4;
						else if($tmp < 64) $zoom_str = 3;
						else if($tmp < 80) $zoom_str = 2;
						else $zoom_str = 1;	
						$heading = str_replace("h","",$coords[4]);
						$pitch = round(str_replace("t","",$coords[5])-90, 2);
						$maptype = "Street";
					} else { //MAPVIEW
						$lat = $coords[0];
						$lon = $coords[1];
						$tmp = str_replace("m","",$coords[2]);
						if($tmp < 300) $zoom = 19;
						else if($tmp < 600) $zoom = 18;
						else if($tmp < 900) $zoom = 17;
						else if($tmp < 1700) $zoom = 16;
						else if($tmp < 3400) $zoom = 15;
						else if($tmp < 7000) $zoom = 14;
						else if($tmp < 13800) $zoom = 13;
						else if($tmp < 28000) $zoom = 12;
						else if($tmp < 55000) $zoom = 11;
						else $zoom = 10;
						$maptype = "Map";
					}
				}
			}
		}

		
		if($_GET[questionid]) {
			$query = "SELECT LinkID FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			
			
			$query = "UPDATE TblGameMasterLU SET Question = ? WHERE PK_GameMasterLU = $_GET[questionid]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[question]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer4]");
			$stm->execute($array);
			
			$query = "UPDATE TblLocations SET WriteUp = ?, MapLink = ?, Latitude = ?, Longitude = ?, StreetLatitude = ?, StreetLongitude = ?, Zoom = ?, CameraZoom = ?, Heading = ?, Pitch = ?, MapImage = ?, Type = ?, InterestLink = ?, Depicted = ?, Actual = ?, Notify = ? WHERE LocationsID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[writeup]", "$_POST[map_link]", "$lat", "$lon", "$lat_str", "$lon_str", "$zoom", "$zoom_str", "$heading", "$pitch", "", "$maptype", "$_POST[interest_link]", "$_POST[depicted]", "$_POST[actual]", "");
			$stm->execute($array);	
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe = $mysql->lastInsertId();
			$question[SnapShotFrame] = $snapshotframe;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "0", "0", "$_POST[question]", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer4]", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblLocations (LocationsID, Title, SnapShotFrame, WriteUp, MapLink, Latitude, Longitude, StreetLatitude, StreetLongitude, Zoom, CameraZoom, Heading, Pitch, MapImage, Type, InterestLink, Depicted, Actual, Notify) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$linkid", "", "$question[SnapShotFrame]", "$_POST[writeup]", "$_POST[map_link]", "$lat", "$lon", "$lat_str", "$lon_str", "$zoom", "$zoom_str", "$heading", "$pitch", "", "$maptype", "$_POST[interest_link]", "$_POST[depicted]", "$_POST[actual]", "");
			$stm->execute($array);
		}

		if($_FILES[answer_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg';
			move_uploaded_file($_FILES['answer_snap']['tmp_name'], $file);
		}
		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//MUSIC
	if($_POST[departament] == 5) {
		if($_GET[questionid]) {
			$query = "SELECT LinkID FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
					
			$query = "UPDATE TblMusic SET Song = ?, Artist = ?, Album = ?, SongYear = ?, SubSong = ?, SubArtist = ?, SubAlbum = ?, SubSongYear = ?, Substitute = ? WHERE MusicID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[song]", "$_POST[artist]", "$_POST[album]", "$_POST[songyear]", "$_POST[subsong]", "$_POST[subartist]", "$_POST[subalbum]", "$_POST[subsongyear]", "$_POST[substitute]");
			$stm->execute($array);
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$answer1 = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$answer2 = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$answer3 = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$answer4 = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$cover1 = "ALB_". $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$cover2 = "ALB_". $mysql->lastInsertId();
		
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "0", "0", "", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$answer1", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$answer2", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$answer3", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$answer4", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblMusic (MusicID, Song, Artist, Album, AlbumCover, SongYear, FK_MusicCategoryID, SubSong, SubArtist, SubAlbum, SubAlbumCover, SubSongYear, FK_SubMusicCategoryID, Unknown, Substitute, Notify, SnapShotFrame) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$linkid", "$_POST[song]", "$_POST[artist]", "$_POST[album]", "$cover1", "$_POST[songyear]", "0", "$_POST[subsong]", "$_POST[subartist]", "$_POST[subalbum]", "$cover2", "$_POST[subsongyear]", "0", "0", "$_POST[substitute]", "0", "0");
			$stm->execute($array);
			
			$_POST[answer1] = $answer1;
			$_POST[answer2] = $answer2;
			$_POST[answer3] = $answer3;
			$_POST[answer4] = $answer4;
			$_POST[cover1] = $cover1;
			$_POST[cover2] = $cover2;
		}

		if($_FILES[scene_snap_1]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $_POST[answer1] .'.jpg';
			move_uploaded_file($_FILES['scene_snap_1']['tmp_name'], $file);
		}
		if($_FILES[scene_snap_2]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $_POST[answer2] .'.jpg';
			move_uploaded_file($_FILES['scene_snap_2']['tmp_name'], $file);
		}
		if($_FILES[scene_snap_3]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $_POST[answer3] .'.jpg';
			move_uploaded_file($_FILES['scene_snap_3']['tmp_name'], $file);
		}
		if($_FILES[scene_snap_4]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $_POST[answer4] .'.jpg';
			move_uploaded_file($_FILES['scene_snap_4']['tmp_name'], $file);
		}
		
		if($_FILES[album_cover]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $_POST[cover1] .'.jpg';
			move_uploaded_file($_FILES['album_cover']['tmp_name'], $file);
		}
		if($_FILES[subalbum_cover]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $_POST[cover2] .'.jpg';
			move_uploaded_file($_FILES['subalbum_cover']['tmp_name'], $file);
		}
		
		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//PLOTINFO
	if($_POST[departament] == 6) {
		if($_GET[questionid]) {
			$query = "SELECT LinkID, SnapShotFrame, SnapShotFrame2 FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			
			
			$query = "UPDATE TblGameMasterLU SET Question = ? WHERE PK_GameMasterLU = $_GET[questionid]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[question]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer4]");
			$stm->execute($array);
			
			$query = "UPDATE TblWhy SET WriteUp = ? WHERE WhyID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[writeup]");
			$stm->execute($array);
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe2 = $mysql->lastInsertId();
			
			$question[SnapShotFrame] = $snapshotframe;
			$question[SnapShotFrame2] = $snapshotframe2;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "$snapshotframe", "$snapshotframe2", "$_POST[question]", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer4]", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblWhy (WhyID, WhyName, SnapShotFrame, WriteUp, Clue, Credit, Recap, Plot, Notify) VALUES (?,?,?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$linkid", "", "$snapshotframe", "$_POST[writeup]", "0", "0", "0", "1", "0");
			$stm->execute($array);
		}

		if($_FILES[question_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg';
			move_uploaded_file($_FILES['question_snap']['tmp_name'], $file);
		}
		if($_FILES[answer_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame2] .'.jpg';
			move_uploaded_file($_FILES['answer_snap']['tmp_name'], $file);
		}
		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//QUOTES
	if($_POST[departament] == 7) {
		if($_POST[select_character_1] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_1] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_1]", "$_POST[new_performer_1]", "$_POST[new_character_1]", "$_POST[new_imdb_1]");
			$stm->execute($array);
		}
		if($_POST[select_character_2] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_2] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_2]", "$_POST[new_performer_2]", "$_POST[new_character_2]", "$_POST[new_imdb_2]");
			$stm->execute($array);
		}
		if($_POST[select_character_3] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_3] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_3]", "$_POST[new_performer_3]", "$_POST[new_character_3]", "$_POST[new_imdb_3]");
			$stm->execute($array);
		}
		if($_POST[select_character_4] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_4] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_4]", "$_POST[new_performer_4]", "$_POST[new_character_4]", "$_POST[new_imdb_4]");
			$stm->execute($array);
		}
		
		
		if($_GET[questionid]) {
			$query = "SELECT LinkID FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_4]");
			$stm->execute($array);
			
			$query = "UPDATE TblQuote SET LineTop = ?, LineBottom = ?, FK_WhoLUID = ? WHERE QuoteID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[quote_top]", "$_POST[quote_bottom]", "$_POST[select_character_1]");
			$stm->execute($array);
			
			$question[SnapShotFrame] = $_POST[snapshotframe];
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, Question, PlayOrder) VALUES (?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_4]", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblQuote (QuoteID, LineTop, LineBottom, Font, FontSize, SnapShotFrame, FK_WhoLUID, Notify, QuoteRating) VALUES (?,?,?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$linkid", "$_POST[quote_top]", "$_POST[quote_bottom]", "Coda", "40", "$snapshotframe", "$_POST[select_character_1]", "0", "3.5");
			$stm->execute($array);
			
			$question[SnapShotFrame] = $snapshotframe;
		}

		if($_FILES[performer_snap_1]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_1] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_1']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_2]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_2] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_2']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_3]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_3] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_3']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_4]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_4] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_4']['tmp_name'], $file);
		}
		if($_FILES[answer_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg';
			move_uploaded_file($_FILES['answer_snap']['tmp_name'], $file);
		}

		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//RECIPES
	if($_POST[departament] == 8) {
		if($_GET[questionid]) {
			$query = "SELECT LinkID FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
					
			$query = "UPDATE TblRecipe SET RecipeName = ?, RecipeType = ?, Quote = ?, Inspiration = ?, PrepTime = ?, CookTime = ?, Servings = ?, Ingredients = ?, Directions = ? WHERE RecipeID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[recipe_name]", "$_POST[recipe_type]", "$_POST[quote]", "$_POST[inspiration]", "$_POST[prep_time]", "$_POST[cook_time]", "$_POST[servings]", "$_POST[ingredients]", "$_POST[directions]");
			$stm->execute($array);
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$answer1 = "REC". $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$answer2 = "REC". $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$answer3 = "REC". $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$answer4 = "REC". $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe = $mysql->lastInsertId();
						
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "0", "0", "", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$answer1", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$answer2", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$answer3", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$answer4", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblRecipe (RecipeID, RecipeName, SnapShotFrame, RecipeType, Quote, Inspiration, PrepTime, CookTime, Servings, Ingredients, Directions, PhotoFile, Notify, RecipeRating) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$photofile = $answer1 .".jpg";
			$array = array("$linkid", "$_POST[recipe_name]", "$snapshotframe", "$_POST[recipe_type]", "$_POST[quote]", "$_POST[inspiration]", "$_POST[prep_time]", "$_POST[cook_time]", "$_POST[servings]", "$_POST[ingredients]", "$_POST[directions]", "$photofile", "0", "3.5");
			$stm->execute($array);
			
			$_POST[answer1] = $answer1;
			$_POST[answer2] = $answer2;
			$_POST[answer3] = $answer3;
			$_POST[answer4] = $answer4;
			$_POST[snapshotframe] = $snapshotframe;
			$question[LinkID] = $linkid;
		}
		
		if($_POST[links]) {
			$query = "DELETE FROM TblRecipeLinks WHERE FK_RecipeID = $question[LinkID]";
			$result = $dbh->exec($query);
			
			$linkarray = explode("\n", $_POST[links]);
			for($i=0; $i<count($linkarray); $i=$i+2) {
				$name = $linkarray[$i];
				$link = $linkarray[$i + 1];
				$query = "INSERT INTO TblRecipeLinks(FK_RecipeID, Name, Link) VALUES (?,?,?)";
				$stm = $dbh->prepare($query);
				$array = array("$question[LinkID]", "$name", "$link");
			$stm->execute($array);
			}
		}

		if($_FILES[scene_snap_1]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $_POST[answer1] .'.jpg';
			move_uploaded_file($_FILES['scene_snap_1']['tmp_name'], $file);
		}
		if($_FILES[scene_snap_2]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $_POST[answer2] .'.jpg';
			move_uploaded_file($_FILES['scene_snap_2']['tmp_name'], $file);
		}
		if($_FILES[scene_snap_3]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $_POST[answer3] .'.jpg';
			move_uploaded_file($_FILES['scene_snap_3']['tmp_name'], $file);
		}
		if($_FILES[scene_snap_4]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $_POST[answer4] .'.jpg';
			move_uploaded_file($_FILES['scene_snap_4']['tmp_name'], $file);
		}
		
		if($_FILES[answer_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $_POST[snapshotframe] .'.jpg';
			move_uploaded_file($_FILES['answer_snap']['tmp_name'], $file);
		}		
		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//SHOPPING
	if($_POST[departament] == 9) {
		if($_GET[questionid]) {
			$query = "SELECT LinkID, SnapShotFrame, SnapShotFrame2 FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$question[ImageFileNameActual] = $_POST[image_file_name_actual];
			$question[ImageFileNameSubstitute] = $_POST[image_file_name_substitute];
			
			$query = "UPDATE TblGameMasterLU SET Question = ? WHERE PK_GameMasterLU = $_GET[questionid]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[question]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer4]");
			$stm->execute($array);
			
			$query = "UPDATE TblShoppingLU SET ShoppingItemName = ?, StoreTitleActual = ?, BuyLinkActual = ?, StoreTitleSubstitute = ?, BuyLinkSubstitute = ? WHERE ShoppingLUID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[shopping_item_name]", "$_POST[store_title_actual]", "$_POST[buy_link_actual]", "$_POST[store_title_substitute]", "$_POST[buy_link_substitute]");
			$stm->execute($array);
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe2 = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$image_actual = "SHO". $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$image_substitute = "SHO". $mysql->lastInsertId();
			
			$question[SnapShotFrame] = $snapshotframe;
			$question[SnapShotFrame2] = $snapshotframe2;
			$question[ImageFileNameActual] = $image_actual;
			$question[ImageFileNameSubstitute] = $image_substitute;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "$snapshotframe", "$snapshotframe2", "$_POST[question]", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer4]", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblShoppingLU (ShoppingLUID, ShoppingItemName, SnapShotFrame, FK_ShoppingCategoryID, StoreTitleActual, BuyLinkActual, ImageFileNameActual, StoreTitleSubstitute, BuyLinkSubstitute, ImageFileNameSubstitute, Reference, Unexpected, FK_WhoLUID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$linkid", "$_POST[shopping_item_name]", "0", "0", "$_POST[store_title_actual]", "$_POST[buy_link_actual]", "$question[ImageFileNameActual]", "$_POST[store_title_substitute]", "$_POST[buy_link_substitute]", "$question[ImageFileNameSubstitute]", "0", "0", "-1");
			$stm->execute($array);
		}

		if($_FILES[question_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg';
			move_uploaded_file($_FILES['question_snap']['tmp_name'], $file);
		}
		if($_FILES[answer_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame2] .'.jpg';
			move_uploaded_file($_FILES['answer_snap']['tmp_name'], $file);
		}
		if($_FILES[image_actual]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $question[ImageFileNameActual] .'.jpg';
			move_uploaded_file($_FILES['image_actual']['tmp_name'], $file);
		}
		if($_FILES[image_substitute]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $question[ImageFileNameSubstitute] .'.jpg';
			move_uploaded_file($_FILES['image_substitute']['tmp_name'], $file);
		}
		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//SUPERFAN
	if($_POST[departament] == 10) {
		if($_GET[questionid]) {
			$query = "SELECT LinkID FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if($_POST[superfan_character] == 999999) {
				$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
				$mysql->exec($query);
				$_POST[superfan_character] = $mysql->lastInsertId();
				
				$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
				$stm = $dbh->prepare($query);
				$array = array("$_POST[superfan_character]", "$_POST[new_performer]", "$_POST[new_character]", "$_POST[new_imdb]");
				$stm->execute($array);
			}
			$query = "UPDATE TblGameMasterLU SET Question = ?, FK_WhoLUID = ? WHERE PK_GameMasterLU = $_GET[questionid]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[question]", $_POST[superfan_character]);
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer4]");
			$stm->execute($array);
			
			$query = "UPDATE TblSuperFan SET WriteUp = ? WHERE SuperFanID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[writeup]");
			$stm->execute($array);
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe = $mysql->lastInsertId();
						
			$question[SnapShotFrame] = $snapshotframe;
			
			if($_POST[superfan_character] == 999999) {
				$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
				$mysql->exec($query);
				$_POST[superfan_character] = $mysql->lastInsertId();
				
				$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
				$stm = $dbh->prepare($query);
				$array = array("$_POST[superfan_character]", "$_POST[new_performer]", "$_POST[new_character]", "$_POST[new_imdb]");
				$stm->execute($array);
			}
			
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, Question, PlayOrder) VALUES (?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "$_POST[superfan_character]", "$linkid", "$_POST[question]", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer4]", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblSuperFan (SuperFanID, SuperFanName, SnapShotFrame, WriteUp, Error, Fan, Notify) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$linkid", "", "$snapshotframe", "$_POST[writeup]", "0", "1", "0");
			$stm->execute($array);
		}

		if($_FILES[who_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[superfan_character] .'.jpg';
			move_uploaded_file($_FILES['who_snap']['tmp_name'], $file);
		}
		if($_FILES[answer_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg';
			move_uploaded_file($_FILES['answer_snap']['tmp_name'], $file);
		}
		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//TRIVIA
	if($_POST[departament] == 11) {
		if($_GET[questionid]) {
			$query = "SELECT LinkID, SnapShotFrame2 FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$query = "UPDATE TblGameMasterLU SET Question = ? WHERE PK_GameMasterLU = $_GET[questionid]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[question]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[answer4]");
			$stm->execute($array);
			
			$query = "UPDATE TblTriviaLU SET Question = ?, Fact = ? WHERE TriviaID = $question[LinkID]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[question]", "$_POST[fact]");
			$stm->execute($array);
			
			$question[SnapShotFrame] = $_POST[snapshotframe];
			$question[SnapShotFrame2] = $_POST[snapshotframe2];
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe = $mysql->lastInsertId();
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$snapshotframe2 = $mysql->lastInsertId();
			
			$question[SnapShotFrame] = $snapshotframe;
			$question[SnapShotFrame2] = $snapshotframe2;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "0", "$question[SnapShotFrame2]", "$_POST[question]", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[answer4]", "0");
			$stm->execute($array);
			
			
			
			$query = "INSERT INTO TblTriviaLU (TriviaID, TriviaTitle, SnapShotFrame, Question, Fact, List, Poll, StandAlone, Notify, PlayOrder) VALUES (?,?,?,?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$linkid", "", "$question[SnapShotFrame]", "$_POST[question]", "$_POST[fact]", "0", "0", "0", "0", "0");
			$stm->execute($array);
		}

		if($_FILES[question_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg';
			move_uploaded_file($_FILES['question_snap']['tmp_name'], $file);
		}
		if($_FILES[answer_snap]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame2] .'.jpg';
			move_uploaded_file($_FILES['answer_snap']['tmp_name'], $file);
		}

		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
	
	//WHO
	if($_POST[departament] == 12) {
		if($_POST[select_character_1] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_1] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_1]", "$_POST[new_performer_1]", "$_POST[new_character_1]", "$_POST[new_imdb_1]");
			$stm->execute($array);
		}
		if($_POST[select_character_2] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_2] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_2]", "$_POST[new_performer_2]", "$_POST[new_character_2]", "$_POST[new_imdb_2]");
			$stm->execute($array);
		}
		if($_POST[select_character_3] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_3] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_3]", "$_POST[new_performer_3]", "$_POST[new_character_3]", "$_POST[new_imdb_3]");
			$stm->execute($array);
		}
		if($_POST[select_character_4] == 999999) {
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$_POST[select_character_4] = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerformerName, CharacterName, BioLink) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_4]", "$_POST[new_performer_4]", "$_POST[new_character_4]", "$_POST[new_imdb_4]");
			$stm->execute($array);
		}
		
		
		if($_GET[questionid]) {
			$query = "SELECT LinkID FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
			$stmt = $dbh->query($query);
			$question = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer1seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_1]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer2seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_2]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer3seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_3]");
			$stm->execute($array);
			
			$query = "UPDATE TblGameMasterSeg SET Answer = ? WHERE PK_GameMasterSeg = $_POST[answer4seg]";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[select_character_4]");
			$stm->execute($array);
		} else {
			$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
			$stmt = $dbh->query($query);
			$play_order = $stmt->fetchColumn() + 1;
			
			$query = "INSERT INTO pt_uniquecounter (movieid) VALUES ($_GET[movieid])";
			$mysql->exec($query);
			$linkid = $mysql->lastInsertId();
			
			$query = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, Question, PlayOrder) VALUES (?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$_POST[departament]", "0", "$linkid", "", "$play_order");
			$stm->execute($array);
			$pk_gamemasterlu = $dbh->lastInsertId();
			
			
			$usedids = array();
			$random = mt_rand(1,4);
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_1]", "1");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_2]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_3]", "0");
			$stm->execute($array);
			
			$random = mt_rand(1,4);
			while(in_array($random, $usedids)) {
				$random = mt_rand(1,4);
			}
			$usedids[] = $random;
			$query = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$play_order", "$random", "$_POST[select_character_4]", "0");
			$stm->execute($array);
		}

		if($_FILES[performer_snap_1]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_1] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_1']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_2]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_2] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_2']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_3]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_3] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_3']['tmp_name'], $file);
		}
		if($_FILES[performer_snap_4]) {
			$file = '../data-tv/'. $_GET[movieid] .'/AppleTVAssets/WhoLUID_'. $_POST[select_character_4] .'.jpg';
			move_uploaded_file($_FILES['performer_snap_4']['tmp_name'], $file);
		}

		
		$url = "dashboard-moviemaster.php?movieid=". $_GET[movieid];
		header("Location: $url");
		exit();
	}
}


//GET EXISTING QUESTION
$departament = 0;
if($_GET[questionid]) {
	$query = "SELECT PK_GameMasterLU, FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder FROM TblGameMasterLU WHERE PK_GameMasterLU = $_GET[questionid]";
	$stmt = $dbh->query($query);
	$question = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$answers = array();
	$query = "SELECT PK_GameMasterSeg, FK_GameMasterPlayID, AnswerID, Answer, IsCorrect FROM TblGameMasterSeg WHERE FK_GameMasterPlayID = $question[PlayOrder]";
	foreach ($dbh->query($query) as $row) {
		if($row[IsCorrect] == 1) {
			array_unshift($answers, $row);
		} else {
			$answers[] = $row;
		}
	}
	
	$question[Answers] = $answers;
	$departament = $question[FK_GameTypeID];
} else {
	$departament = $_POST[FK_GameTypeID];
	$_SESSION[FK_GameTypeID] = $departament;
}
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function() { 
    $("table").tablesorter({ 
        sortList: [[0,1]] 
    });
	
	
	$("#superfan_character").change(function() {
		var who_id = $(this).val();
		if(who_id == 0) {
			$("#new_performer_character").hide();
			$("#performer_image").hide();
		} else if(who_id == 999999) {
			$("#new_performer_character").show();
			$("#performer_image").show();
		} else {
			$("#new_performer_character").hide();
			$("#performer_image").show();
			var loc = 'http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_' + who_id + '.jpg';
			$("#performer_image_src").attr("src",loc);
		}
		
	});
	
	$("#select_character_1").change(function() {
		var who_id = $(this).val();
		if(who_id == 0) {
			$("#new_performer_character_1").hide();
			$("#performer_image_1").hide();
		} else if(who_id == 999999) {
			$("#new_performer_character_1").show();
			$("#performer_image_1").show();
		} else {
			$("#new_performer_character_1").hide();
			$("#performer_image_1").show();
			var loc = 'http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_' + who_id + '.jpg';
			$("#performer_image_src_1").attr("src",loc);
		}
	});
	$("#select_character_2").change(function() {
		var who_id = $(this).val();
		if(who_id == 0) {
			$("#new_performer_character_2").hide();
			$("#performer_image_2").hide();
		} else if(who_id == 999999) {
			$("#new_performer_character_2").show();
			$("#performer_image_2").show();
		} else {
			$("#new_performer_character_2").hide();
			$("#performer_image_2").show();
			var loc = 'http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_' + who_id + '.jpg';
			$("#performer_image_src_2").attr("src",loc);
		}
	});
	$("#select_character_3").change(function() {
		var who_id = $(this).val();
		if(who_id == 0) {
			$("#new_performer_character_3").hide();
			$("#performer_image_3").hide();
		} else if(who_id == 999999) {
			$("#new_performer_character_3").show();
			$("#performer_image_3").show();
		} else {
			$("#new_performer_character_3").hide();
			$("#performer_image_3").show();
			var loc = 'http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_' + who_id + '.jpg';
			$("#performer_image_src_3").attr("src",loc);
		}
	});
	$("#select_character_4").change(function() {
		var who_id = $(this).val();
		if(who_id == 0) {
			$("#new_performer_character_4").hide();
			$("#performer_image_4").hide();
		} else if(who_id == 999999) {
			$("#new_performer_character_4").show();
			$("#performer_image_4").show();
		} else {
			$("#new_performer_character_4").hide();
			$("#performer_image_4").show();
			var loc = 'http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_' + who_id + '.jpg';
			$("#performer_image_src_4").attr("src",loc);
		}
	});
	$("#substitute").change(function() {
		var sub = $(this).val();
		if(sub == 0) {
			$("#substitute_song").hide();
		} else {
			$("#substitute_song").show();
		}
	});
	
});


</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <a href="dashboard.php">Dashboard</a> / <a href="dashboard-moviemaster.php?movieid=<?php echo $_GET[movieid];?>"><?php echo str_replace(" [AVI]", "", $movie[VidTitle]);?> MovieMaster</a> / <?php echo getDepartamentName($departament)?> Question
    <div class="grad1 rounded module">
 

	<?php if($departament == 1) { //DILLEMA WHO IMAGE TYPE OF QUESTION WITH WRITTEN QUESTION    ?>
   	<?php
	if($_GET[questionid]) {
		$query = "SELECT Recap, FMR, SMR FROM TblDilemmas WHERE DilemmaID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[Recap] = $row[Recap];
		$question[FMR] = $row[FMR];
		$question[SMR] = $row[SMR];
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">
    
    
    
    <div class="record"><label>FMR:</label><select name="fmr"><option value="0">Select FMR</option><?php generateDilemmaPulldowns($dbh, $question[FMR]);?></select></div>
    <div class="record"><label>SMR:</label><select name="smr"><option value="0">Select SMR</option><?php generateDilemmaPulldowns($dbh, $question[SMR]);?></select></div>
    <div class="record"><label>Recap:</label><textarea name="recap" style="width: 800px; height: 120px;"><?php echo htmlspecialchars($question[Recap]);?></textarea></div>
    
    
    <div class="record"><label>Question:</label><input type="text" id="question" name="question" value="<?php echo htmlspecialchars($question[Question]);?>" style="width:800px;"></div>
    <div class="record"><label>Answer:</label><select id="select_character_1" name="select_character_1"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][0][Answer]);?></select></div>
    
    <div id="new_performer_character_1" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_1" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_1" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_1" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_1"<?php if(!$question[Answers][0][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_1" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][0][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_1" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 2:</label><select id="select_character_2" name="select_character_2"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][1][Answer]);?></select></div>
    
    <div id="new_performer_character_2" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_2" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_2" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_2" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_2"<?php if(!$question[Answers][1][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_2" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][1][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_2" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 3:</label><select id="select_character_3" name="select_character_3"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][2][Answer]);?></select></div>
    
    <div id="new_performer_character_3" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_3" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_3" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_3" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_3"<?php if(!$question[Answers][2][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_3" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][2][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_3" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 4:</label><select id="select_character_4" name="select_character_4"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][3][Answer]);?></select></div>
    
    <div id="new_performer_character_4" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_4" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_4" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_4" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_4"<?php if(!$question[Answers][3][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_4" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][3][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_4" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
    
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    <?php if(($departament == 2) || ($departament == 3)) { //VEHICLES & WEAPONS 4 TEXT QUESTIONS, 2 SNAPSHOTS, 2 LINKS WITH IMAGES   ?>
    <?php
	if($_GET[questionid]) {
		$query = "SELECT IdsLUID, IdsLUName, PrimaryLink, SecondaryLink FROM TblIdsLU WHERE IdsLUID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[IdsLUID] = $row[IdsLUID];
		$question[IdsLUName] = $row[IdsLUName];
		$question[PrimaryLink] = $row[PrimaryLink];
		$question[SecondaryLink] = $row[SecondaryLink];
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">

   	<div class="record"><label>Question:</label><input type="text" id="question" name="question" value="<?php echo htmlspecialchars($question[Question]);?>" style="width:800px;"></div>
    <div class="record"><label>Answer:</label><input type="text" id="answer1" name="answer1" value="<?php echo htmlspecialchars($question[Answers][0][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 2:</label><input type="text" id="answer2" name="answer2" value="<?php echo htmlspecialchars($question[Answers][1][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 3:</label><input type="text" id="answer3" name="answer3" value="<?php echo htmlspecialchars($question[Answers][2][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 4:</label><input type="text" id="answer4" name="answer4" value="<?php echo htmlspecialchars($question[Answers][3][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Question Snap:</label>
    	<?php if($question[SnapShotFrame]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="question_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    
		<div class="record"><label>Item Name:</label><input type="text" name="item_name" value="<?php echo htmlspecialchars($question[IdsLUName]);?>" style="width:800px;"></div>
	<div class="record"><label>Answer Snap:</label>
    	<?php if($question[SnapShotFrame2]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame2];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="answer_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    
		<div class="record"><label>Link One:</label><input type="text" name="primary_link" value="<?php echo htmlspecialchars($question[PrimaryLink]);?>" style="width:800px;"></div>
	<div class="record"><label>Image One:</label>
    	<?php if($question[SnapShotFrame2]) {?>
    	<img src="http://api.customplay.com/data/<?php echo $_GET[movieid];?>/WebSC/<?php echo $question[IdsLUID];?>_1.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="primary_link_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*ID image size: 752x423px</span>
    </div>
      
		<div class="record"><label>Link Two:</label><input type="text" name="secondary_link" value="<?php echo htmlspecialchars($question[SecondaryLink]);?>" style="width:800px;"></div>
	<div class="record"><label>Image Two:</label>
    	<?php if($question[SnapShotFrame2]) {?>
    	<img src="http://api.customplay.com/data/<?php echo $_GET[movieid];?>/WebSC/<?php echo $question[IdsLUID];?>_2.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="secondary_link_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*ID image size: 752x423px</span>
    </div>

    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    </form>
    <?php }?>
    
    
    
    
    
    
    <?php if($departament == 4) { //LOCATIONS 4 TEXT QUESTIONS    ?>
    <?php
    if($_GET[questionid]) {
		$query = "SELECT SnapShotFrame, WriteUp, MapLink, InterestLink, Depicted, Actual FROM TblLocations WHERE LocationsID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[SnapShotFrame] = $row[SnapShotFrame];
		$question[WriteUp] = $row[WriteUp];
		$question[MapLink] = $row[MapLink];
		$question[InterestLink] = $row[InterestLink];
		$question[Depicted] = $row[Depicted];
		$question[Actual] = $row[Actual];
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">

    <div class="record"><label>Question:</label><input type="text" id="question" name="question" value="<?php echo htmlspecialchars($question[Question]);?>" style="width:800px;"></div>
    <div class="record"><label>Answer:</label><input type="text" id="answer1" name="answer1" value="<?php echo htmlspecialchars($question[Answers][0][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 2:</label><input type="text" id="answer2" name="answer2" value="<?php echo htmlspecialchars($question[Answers][1][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 3:</label><input type="text" id="answer3" name="answer3" value="<?php echo htmlspecialchars($question[Answers][2][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 4:</label><input type="text" id="answer4" name="answer4" value="<?php echo htmlspecialchars($question[Answers][3][Answer]);?>" style="width:800px;"></div>
    
    <div class="record"><label>Depicted:</label><input type="text" name="depicted" value="<?php echo htmlspecialchars($question[Depicted]);?>" style="width:600px;"></div>
    <div class="record"><label>Actual:</label><input type="text" name="actual" value="<?php echo htmlspecialchars($question[Actual]);?>" style="width:600px;"></div>
    <div class="record"><label>Map Link:</label><input type="text" name="map_link" value="<?php echo htmlspecialchars($question[MapLink]);?>" style="width:800px;"></div>
    <div class="record"><label>Interest Link:</label><input type="text" name="interest_link" value="<?php echo htmlspecialchars($question[InterestLink]);?>" style="width:800px;"></div>
    <div class="record"><label>Writeup:</label><textarea name="writeup" style="width: 800px; height: 120px;"><?php echo htmlspecialchars($question[WriteUp]);?></textarea></div>
    <div class="record"><label>Answer Snap:</label>
    	<?php if($question[SnapShotFrame]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="answer_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>

    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    
    
    <?php if($departament == 5) { //MUSIC 4 IMAGE QUESTIONS    ?>
    <?php
    if($_GET[questionid]) {
		$query = "SELECT Song, Artist, Album, AlbumCover, SongYear, SubSong, SubArtist, SubAlbum, SubAlbumCover, SubSongYear, Substitute FROM TblMusic WHERE MusicID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[Song] = $row[Song];
		$question[Artist] = $row[Artist];
		$question[Album] = $row[Album];
		$question[AlbumCover] = $row[AlbumCover];
		$question[SongYear] = $row[SongYear];
		$question[SubSong] = $row[SubSong];
		$question[SubArtist] = $row[SubArtist];
		$question[SubAlbum] = $row[SubAlbum];
		$question[SubAlbumCover] = $row[SubAlbumCover];
		$question[SubSongYear] = $row[SubSongYear];
		$question[Substitute] = $row[Substitute];
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1" value="<?php echo $question[Answers][0][Answer];?>">
    <input type="hidden" name="answer2" value="<?php echo $question[Answers][1][Answer];?>">
    <input type="hidden" name="answer3" value="<?php echo $question[Answers][2][Answer];?>">
    <input type="hidden" name="answer4" value="<?php echo $question[Answers][3][Answer];?>">
    <input type="hidden" name="cover1" value="<?php echo $question[AlbumCover];?>">
    <input type="hidden" name="cover2" value="<?php echo $question[SubAlbumCover];?>">

    <div class="record"><label>Song:</label><input type="text" name="song" value="<?php echo $question[Song];?>" style="width:500px;"></div>
    <div class="record"><label>Artist:</label><input type="text" name="artist" value="<?php echo $question[Artist];?>" style="width:500px;"></div>
    <div class="record"><label>Album:</label><input type="text" name="album" value="<?php echo $question[Album];?>" style="width:500px;"></div>
    <div class="record"><label>Song Year:</label><input type="text" name="songyear" value="<?php echo $question[SongYear];?>" style="width:100px;"></div>
    <div class="record"><label>Album Cover:</label>
    	<?php if($question[AlbumCover]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/<?php echo $question[AlbumCover];?>.jpg?<?php echo $now;?>" width="150" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="album_cover" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Album image size: 400x400px</span>
    </div>
    
    <div class="record"><label>Answer Snap:</label>
    	<?php if($question[Answers][0][Answer]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[Answers][0][Answer];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="scene_snap_1" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    <div class="record"><label>Snap 2:</label>
    	<?php if($question[Answers][1][Answer]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[Answers][1][Answer];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="scene_snap_2" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    <div class="record"><label>Snap 3:</label>
    	<?php if($question[Answers][2][Answer]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[Answers][2][Answer];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="scene_snap_3" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    <div class="record"><label>Snap 4:</label>
    	<?php if($question[Answers][3][Answer]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[Answers][3][Answer];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="scene_snap_4" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    
    <div class="record"><label>Substitute:</label><select name="substitute" id="substitute">
		<option value="0"<?php if($question[Substitute] == "0") echo ' selected="selected"';?>>No Substitute</option>
		<option value="1"<?php if($question[Substitute] == "1") echo ' selected="selected"';?>>Use Substitute Song</option></select></div>
    
    <div id="substitute_song"<?php if($question[Substitute] == 0) echo ' style="display:none;"';?>>
    <div class="record"><label>Sub Song:</label><input type="text" name="subsong" value="<?php echo $question[SubSong];?>" style="width:500px;"></div>
    <div class="record"><label>SubArtist:</label><input type="text" name="subartist" value="<?php echo $question[SubArtist];?>" style="width:500px;"></div>
    <div class="record"><label>SubAlbum:</label><input type="text" name="subalbum" value="<?php echo $question[SubAlbum];?>" style="width:500px;"></div>
    <div class="record"><label>SubSong Year:</label><input type="text" name="subsongyear" value="<?php echo $question[SubSongYear];?>" style="width:100px;"></div>
    <div class="record"><label>SubAlbum Cover:</label>
    	<?php if($question[SubAlbumCover]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/<?php echo $question[SubAlbumCover];?>.jpg?<?php echo $now;?>" width="150" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="subalbum_cover" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Album image size: 400x400px</span>
    </div>
	</div>
        
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    
    <?php if($departament == 6) { //PLOT 4 TEXT QUESTIONS, 2 SNAPSHOTS    ?>
    <?php
	if($_GET[questionid]) {
		$query = "SELECT WriteUp FROM TblWhy WHERE WhyID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[WriteUp] = $row[WriteUp];
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">

    <div class="record"><label>Question:</label><input type="text" id="question" name="question" value="<?php echo htmlspecialchars($question[Question]);?>" style="width:800px;"></div>
    <div class="record"><label>Answer:</label><input type="text" id="answer1" name="answer1" value="<?php echo htmlspecialchars($question[Answers][0][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 2:</label><input type="text" id="answer2" name="answer2" value="<?php echo htmlspecialchars($question[Answers][1][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 3:</label><input type="text" id="answer3" name="answer3" value="<?php echo htmlspecialchars($question[Answers][2][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 4:</label><input type="text" id="answer4" name="answer4" value="<?php echo htmlspecialchars($question[Answers][3][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Question Snap:</label>
    	<?php if($question[SnapShotFrame]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="question_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
	<div class="record"><label>Writeup:</label><textarea name="writeup" style="width: 800px; height: 120px;"><?php echo htmlspecialchars($question[WriteUp]);?></textarea></div>
	<div class="record"><label>Answer Snap:</label>
    	<?php if($question[SnapShotFrame2]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame2];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="answer_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>    
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    <?php if($departament == 7) { //QUOTES WITH WHO IMAGE TYPE OF QUESTION WITH NO WRITTEN QUESTION    ?>
    <?php
	if($_GET[questionid]) {
		$query = "SELECT LineTop, LineBottom, Font, FontSize, SnapShotFrame, FK_WhoLUID FROM TblQuote WHERE QuoteID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[LineTop] = $row[LineTop];
		$question[LineBottom] = $row[LineBottom];
		$question[Font] = $row[Font];
		$question[FontSize] = $row[FontSize];
		$question[SnapShotFrame] = $row[SnapShotFrame];
		$question[FK_WhoLUID] = $row[FK_WhoLUID];
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">
    <input type="hidden" name="snapshotframe" value="<?php echo $question[SnapShotFrame];?>">
    
    
	<div class="record"><label>Quote Top:</label><input type="text" name="quote_top" value="<?php echo htmlspecialchars(str_replace("\n", " ", $question[LineTop]));?>" style="width:800px;"><br /><span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Please do not use for new questions.</span></div>
    <div class="record"><label>Quote Btm:</label><input type="text" name="quote_bottom" value="<?php echo htmlspecialchars(str_replace("\n", " ", $question[LineBottom]));?>" style="width:800px;"></div>
    
    
    <div class="record"><label>Answer:</label><select id="select_character_1" name="select_character_1"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][0][Answer]);?></select></div>
    
    <div id="new_performer_character_1" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_1" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_1" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_1" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_1"<?php if(!$question[Answers][0][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_1" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][0][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_1" /></div>
   		<span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 2:</label><select id="select_character_2" name="select_character_2"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][1][Answer]);?></select></div>
    
    <div id="new_performer_character_2" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_2" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_2" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_2" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_2"<?php if(!$question[Answers][1][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_2" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][1][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_2" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 3:</label><select id="select_character_3" name="select_character_3"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][2][Answer]);?></select></div>
    
    <div id="new_performer_character_3" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_3" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_3" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_3" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_3"<?php if(!$question[Answers][2][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_3" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][2][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_3" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 4:</label><select id="select_character_4" name="select_character_4"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][3][Answer]);?></select></div>
    
    <div id="new_performer_character_4" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_4" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_4" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_4" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_4"<?php if(!$question[Answers][3][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_4" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][3][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_4" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
    
    <div class="record"><label>Answer Snap:</label>
    	<?php if($question[SnapShotFrame]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="answer_snap" /></div>
   		<span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>    
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    
    <?php if($departament == 8) { //RECIPES QUESTION WITH 4 RECIPE IMAGES    ?>
    <?php
	if($_GET[questionid]) {
		$query = "SELECT RecipeName, SnapShotFrame, RecipeType, Quote, Inspiration, PrepTime, CookTime, Servings, Ingredients, Directions, PhotoFile FROM TblRecipe WHERE RecipeID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[RecipeName] = $row[RecipeName];
		$question[SnapShotFrame] = $row[SnapShotFrame];
		$question[RecipeType] = $row[RecipeType];
		$question[Quote] = $row[Quote];
		$question[Inspiration] = $row[Inspiration];
		$question[PrepTime] = $row[PrepTime];
		$question[CookTime] = $row[CookTime];
		$question[Servings] = $row[Servings];
		$question[Ingredients] = $row[Ingredients];
		$question[Directions] = $row[Directions];
		$question[PhotoFile] = $row[PhotoFile];
		
		$links = "";
		$query = "SELECT Name, Link FROM TblRecipeLinks WHERE FK_RecipeID = $question[LinkID]"; 
		$stmt = $dbh->query($query);
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if(strlen($links) > 0) $links .= "\n";
			$links .= $row[Name] ."\n". $row[Link];
		}
		$question[Links] = $links;
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer1" value="<?php echo $question[Answers][0][Answer];?>">
    <input type="hidden" name="answer2" value="<?php echo $question[Answers][1][Answer];?>">
    <input type="hidden" name="answer3" value="<?php echo $question[Answers][2][Answer];?>">
    <input type="hidden" name="answer4" value="<?php echo $question[Answers][3][Answer];?>">
    <input type="hidden" name="snapshotframe" value="<?php echo $question[SnapShotFrame];?>">
    
    <div class="record"><label>Recipe Type:</label><select name="recipe_type"><option value="0">Select Recipe Type</option>
    	<option value="1"<?php if($question[RecipeType] == 1) echo ' selected="true"';?>>Drink</option>
    	<option value="2"<?php if($question[RecipeType] == 2) echo ' selected="true"';?>>Appetizer</option>
    	<option value="3"<?php if($question[RecipeType] == 3) echo ' selected="true"';?>>Main Course</option>
    	<option value="4"<?php if($question[RecipeType] == 4) echo ' selected="true"';?>>Side Dish</option>
    	<option value="5"<?php if($question[RecipeType] == 5) echo ' selected="true"';?>>Dessert</option>
    </select></div>
    <div class="record"><label>Answer Snap:</label>
    	<?php if($question[Answers][0][Answer]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/<?php echo $question[Answers][0][Answer];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="scene_snap_1" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Recipe image size: 720x540px</span>
    </div>
    <div class="record"><label>Snap 2:</label>
    	<?php if($question[Answers][1][Answer]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/<?php echo $question[Answers][1][Answer];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="scene_snap_2" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Recipe image size: 720x540px</span>
    </div>
    <div class="record"><label>Snap 3:</label>
    	<?php if($question[Answers][2][Answer]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/<?php echo $question[Answers][2][Answer];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="scene_snap_3" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Recipe image size: 720x540px</span>
    </div>
    <div class="record"><label>Snap 4:</label>
    	<?php if($question[Answers][3][Answer]) {?>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/<?php echo $question[Answers][3][Answer];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="scene_snap_4" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Recipe image size: 720x540px</span>
    </div>
    
    <div class="record"><label>Recipe Name:</label><input type="text" name="recipe_name" value="<?php echo htmlspecialchars($question[RecipeName]);?>" style="width:600px;"></div>
    <div class="record"><label>Quote:</label><input type="text" name="quote" value="<?php echo htmlspecialchars($question[Quote]);?>" style="width:800px;"></div>
    <div class="record"><label>Inspiration:</label><textarea name="inspiration" style="width: 800px; height: 120px;"><?php echo htmlspecialchars($question[Inspiration]);?></textarea></div>
    <div class="record"><label>Prep Time:</label><input type="text" name="prep_time" value="<?php echo htmlspecialchars($question[PrepTime]);?>" style="width:200px;"></div>
    <div class="record"><label>Cook Time:</label><input type="text" name="cook_time" value="<?php echo htmlspecialchars($question[CookTime]);?>" style="width:200px;"></div>
    <div class="record"><label>Servings:</label><input type="text" name="servings" value="<?php echo htmlspecialchars($question[Servings]);?>" style="width:100px;"></div>
    <div class="record"><label>Ingredients:</label><textarea name="ingredients" style="width: 800px; height: 120px;"><?php echo htmlspecialchars($question[Ingredients]);?></textarea></div>
    <div class="record"><label>Directions:</label><textarea name="directions" style="width: 800px; height: 120px;"><?php echo htmlspecialchars($question[Directions]);?></textarea></div>
    <div class="record"><label>Recipe Links:</label><textarea name="links" style="width: 800px; height: 120px; float: left;"><?php echo htmlspecialchars($question[Links]);?></textarea>
		<span style="color: #666; font-style: italic; padding: 0 0 0 10px; font-size: 11px;">*Example:<br /></span>
   <span style="color: #666; font-style: italic; padding: 0 0 0 10px; font-size: 11px;">Title of Link<br /></span>
   <span style="color: #666; font-style: italic; padding: 0 0 0 10px; font-size: 11px;">http://www.link.com<br /></span>
   <span style="color: #666; font-style: italic; padding: 0 0 0 10px; font-size: 11px;">Title of Link 2<br /></span>
   <span style="color: #666; font-style: italic; padding: 0 0 0 10px; font-size: 11px;">http://www.link2.com</span>
    </div>
    <div class="record"><label>Snapshot:</label>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="answer_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    
    
    <?php if($departament == 9) { //SHOPPING 4 TEXT QUESTIONS, 2 SNAPSHOTS    ?>
    <?php
    if($_GET[questionid]) {
		$query = "SELECT ShoppingItemName, StoreTitleActual, BuyLinkActual, ImageFileNameActual, StoreTitleSubstitute, BuyLinkSubstitute, ImageFileNameSubstitute FROM TblShoppingLU WHERE ShoppingLUID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[ShoppingItemName] = $row[ShoppingItemName];
		$question[StoreTitleActual] = $row[StoreTitleActual];
		$question[BuyLinkActual] = $row[BuyLinkActual];
		$question[ImageFileNameActual] = $row[ImageFileNameActual];
		$question[StoreTitleSubstitute] = $row[StoreTitleSubstitute];
		$question[BuyLinkSubstitute] = $row[BuyLinkSubstitute];
		$question[ImageFileNameSubstitute] = $row[ImageFileNameSubstitute];
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">
    <input type="hidden" name="image" value="<?php echo $question[ImageFileNameActual];?>">
    <input type="hidden" name="cover2" value="<?php echo $question[ImageFileNameSubstitute];?>">

   
    <div class="record"><label>Question:</label><input type="text" id="question" name="question" value="<?php echo htmlspecialchars($question[Question]);?>" style="width:800px;"></div>
    <div class="record"><label>Answer:</label><input type="text" id="answer1" name="answer1" value="<?php echo htmlspecialchars($question[Answers][0][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 2:</label><input type="text" id="answer2" name="answer2" value="<?php echo htmlspecialchars($question[Answers][1][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 3:</label><input type="text" id="answer3" name="answer3" value="<?php echo htmlspecialchars($question[Answers][2][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 4:</label><input type="text" id="answer4" name="answer4" value="<?php echo htmlspecialchars($question[Answers][3][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Question Snap:</label>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="question_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    <div class="record"><label>Answer Snap:</label>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame2];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="answer_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    
    <div class="record"><label>Item Name:</label><input type="text" name="shopping_item_name" value="<?php echo htmlspecialchars($question[ShoppingItemName]);?>" style="width:800px;"></div>
    <div class="record"><label>Title Actual:</label><input type="text" name="store_title_actual" value="<?php echo htmlspecialchars($question[StoreTitleActual]);?>" style="width:800px;"></div>
    <div class="record"><label>Link Actual:</label><input type="text" name="buy_link_actual" value="<?php echo htmlspecialchars($question[BuyLinkActual]);?>" style="width:800px;"></div>
    <div class="record"><label>Image Actual:</label>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/<?php echo $question[ImageFileNameActual];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="image_actual" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Shopping image size: 640x480px</span>
    </div>
    <div class="record"><label>Title Sub:</label><input type="text" name="store_title_substitute" value="<?php echo htmlspecialchars($question[StoreTitleSubstitute]);?>" style="width:800px;"></div>
    <div class="record"><label>Link Sub:</label><input type="text" name="buy_link_substitute" value="<?php echo htmlspecialchars($question[BuyLinkSubstitute]);?>" style="width:800px;"></div>
    <div class="record"><label>Image Sub:</label>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/<?php echo $question[ImageFileNameSubstitute];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="image_substitute" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Shopping image size: 640x480px</span>
    </div>
    
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    
    
    <?php if($departament == 10) { //SUPERFAN 4 TEXT QUESTIONS, OPTIONAL CHAR ID    ?>
    <?php
	if($_GET[questionid]) {
		$query = "SELECT WriteUp, SnapShotFrame FROM TblSuperFan WHERE SuperFanID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[WriteUp] = $row[WriteUp];
		$question[SnapShotFrame] = $row[SnapShotFrame];
	} else {
		$question[SnapShotFrame] = null;
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">

    <div class="record"><label>Question:</label><input type="text" id="question" name="question" value="<?php echo htmlspecialchars($question[Question]);?>" style="width:800px;"></div>
    <div class="record"><label>Answer:</label><input type="text" id="answer1" name="answer1" value="<?php echo htmlspecialchars($question[Answers][0][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 2:</label><input type="text" id="answer2" name="answer2" value="<?php echo htmlspecialchars($question[Answers][1][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 3:</label><input type="text" id="answer3" name="answer3" value="<?php echo htmlspecialchars($question[Answers][2][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 4:</label><input type="text" id="answer4" name="answer4" value="<?php echo htmlspecialchars($question[Answers][3][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Opt Character ID:</label><select id="superfan_character" name="superfan_character"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[FK_WhoLUID]);?></select></div>
    
    <div id="new_performer_character" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image"<?php if(!$question[FK_WhoLUID]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<?php if($question[FK_WhoLUID]) {?>
    	<img id="performer_image_src" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[FK_WhoLUID];?>.jpg?<?php echo $now;?>" width="180" height="auto" />
    	<?php }?>
		<div style="padding: 0 0 0 140px;"><input type="file" name="who_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
	
	<div class="record"><label>Writeup:</label><textarea name="writeup" style="width: 800px; height: 120px;"><?php echo htmlspecialchars($question[WriteUp]);?></textarea></div>
	<div class="record"><label>Answer Snap:</label>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="answer_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>    
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    <?php if($departament == 11) { //TRIVIA, JUST REFERENCES    ?>
    <?php
	if($_GET[questionid]) {
		$query = "SELECT Fact, SnapShotFrame from TblTriviaLU where TriviaID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$question[Fact] = $row[Fact];
		$question[SnapShotFrame] = $row[SnapShotFrame];
	}
	?>
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">
    <input type="hidden" name="snapshotframe" value="<?php echo $question[SnapShotFrame];?>">
    <input type="hidden" name="snapshotframe2" value="<?php echo $question[SnapShotFrame2];?>">


    <div class="record"><label>Question:</label><input type="text" id="question" name="question" value="<?php echo htmlspecialchars($question[Question]);?>" style="width:800px;"></div>
    <div class="record"><label>Answer:</label><input type="text" id="answer1" name="answer1" value="<?php echo htmlspecialchars($question[Answers][0][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 2:</label><input type="text" id="answer2" name="answer2" value="<?php echo htmlspecialchars($question[Answers][1][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 3:</label><input type="text" id="answer3" name="answer3" value="<?php echo htmlspecialchars($question[Answers][2][Answer]);?>" style="width:800px;"></div>
    <div class="record"><label>Choice 4:</label><input type="text" id="answer4" name="answer4" value="<?php echo htmlspecialchars($question[Answers][3][Answer]);?>" style="width:800px;"></div>
    
    <div class="record"><label>Question Snap:</label>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="question_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    <div class="record"><label>Answer Snap:</label>
    	<img src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/frame_<?php echo $question[SnapShotFrame2];?>.jpg?<?php echo $now;?>" width="220" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="answer_snap" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Snapshot image width: 800px</span>
    </div>
    <div class="record"><label>Fact:</label><textarea name="fact" style="width: 800px; height: 120px;"><?php echo htmlspecialchars($question[Fact]);?></textarea></div>
    
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    
    
    
    
    
    
    
    
    
    <?php if($departament == 12) { //WHO IMAGE TYPE OF QUESTION    ?>
    
    <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?><?php if($_GET[questionid]) echo '&questionid='. $_GET[questionid];?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="1">
    <input type="hidden" name="departament" value="<?php echo $departament;?>">
    <input type="hidden" name="answer1seg" value="<?php echo $question[Answers][0][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer2seg" value="<?php echo $question[Answers][1][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer3seg" value="<?php echo $question[Answers][2][PK_GameMasterSeg];?>">
    <input type="hidden" name="answer4seg" value="<?php echo $question[Answers][3][PK_GameMasterSeg];?>">
    <input type="hidden" name="snapshotframe" value="<?php echo $question[SnapShotFrame];?>">
    
    <div class="record"><label>Answer:</label><select id="select_character_1" name="select_character_1"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][0][Answer]);?></select></div>
    
    <div id="new_performer_character_1" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_1" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_1" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_1" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_1"<?php if(!$question[Answers][0][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_1" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][0][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_1" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 2:</label><select id="select_character_2" name="select_character_2"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][1][Answer]);?></select></div>
    
    <div id="new_performer_character_2" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_2" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_2" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_2" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_2"<?php if(!$question[Answers][1][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_2" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][1][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_2" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 3:</label><select id="select_character_3" name="select_character_3"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][2][Answer]);?></select></div>
    
    <div id="new_performer_character_3" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_3" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_3" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_3" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_3"<?php if(!$question[Answers][2][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_3" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][2][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_3" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
   
   
   <div class="record"><label>Person 4:</label><select id="select_character_4" name="select_character_4"><option value="0">Select Performer/Character</option><option value="999999">Add New Performer/Character</option><?php generateWhoPulldowns($dbh, $question[Answers][3][Answer]);?></select></div>
    
    <div id="new_performer_character_4" style="display: none;">
    <div class="record"><label>Performer:</label><input type="text" name="new_performer_4" value="" style="width:400px;"></div>
    <div class="record"><label>Character:</label><input type="text" name="new_character_4" value="" style="width:400px;"></div>
    <div class="record"><label>Bio Link ID:</label><input type="text" name="new_imdb_4" value="" style="width:400px;"></div>
	</div>
   
   	<div id="performer_image_4"<?php if(!$question[Answers][3][Answer]) echo ' style="display:none;"';?>>
    <div class="record"><label>Who Image:</label>
    	<img id="performer_image_src_4" src="http://api.customplay.com/data-tv/<?php echo $_GET[movieid];?>/AppleTVAssets/WhoLUID_<?php echo $question[Answers][3][Answer];?>.jpg?<?php echo $now;?>" width="140" height="auto" />
		<div style="padding: 0 0 0 140px;"><input type="file" name="performer_snap_4" /></div>
   <span style="color: #666; font-style: italic; padding: 0 0 0 150px; font-size: 11px;">*Who image size: 300x388px</span>
    </div>
	</div>
    
   
    <div class="record"><label></label><input type="submit" value="Update" class="rounded"></div>
    
    </form>
    <?php }?>
    
    
    


	<pre>
    
    
    <?php //print_r($question);?>
    
    
    </pre>
	<div class="clearfix"></div>
	</div>    
</div>
</body>
</html>
<?php
function generateWhoPulldowns($dbh, $FK_WhoLUID) {
	$query = "SELECT PerformerName, CharacterName, WhoLUID FROM TblWhoLU ORDER BY PerformerName";
	$options = "";
  	foreach ($dbh->query($query) as $row) {
		$options .= '<option value="'. $row[WhoLUID] .'"';
		if($row[WhoLUID] == $FK_WhoLUID) $options .= ' selected="selected"';
		$options .= '>'. $row[PerformerName] .' as '. $row[CharacterName] .'</option>';
	}
	
	echo $options;
}

function generateDilemmaPulldowns($dbh, $principle) {
	$query = "SELECT DilMoralReqID, DilMoralReqName FROM TblDilMoralReq ORDER BY DilMoralReqName";
	$options = "";
  	foreach ($dbh->query($query) as $row) {
		$options .= '<option value="'. $row[DilMoralReqID] .'"';
		if($row[DilMoralReqID] == $principle) $options .= ' selected="selected"';
		$options .= '>'. $row[DilMoralReqName] .'</option>';
	}
	
	echo $options;
}

?>