<?php

    $dbh = mysql_connect ("mysql51-063.wc1.ord1.stabletransit.com", "971786_db", "Cust0mP1ay#)") or die ('Cannot connect db: '. mysql_error());
    mysql_select_db ("971786_db") or die('Cannot select db: ' . mysql_error());
	mysql_set_charset('utf8');
	
	$cpplayers = array();
	$query = "SELECT uid, first_name, last_name, token FROM pt_multiusers WHERE player_cp = 1";
	$result = mysql_query($query) or die ('error: '. mysql_error());
	while($row = mysql_fetch_array($result)) {
		$data = array();
		$data[uid] = (string)$row[uid];
		$data[name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
		$data[token] = $row[token];
		$cpplayers[] = $data;
	}
	
	
    
    //DOWNLOAD ALL MOVIES INFO
    if($_POST["request"] == "allMovies") {
        $query = "SELECT id, title, version, releasedate, active FROM movies WHERE popcornactive = 1";
        if($_POST["mode"] == "dev") {
            $query = "SELECT id, title, version, releasedate, active FROM movies WHERE 1";
        }
        $result = mysql_query($query) or die ('error: '. mysql_error());
        
        $movies = array();
        while($row = mysql_fetch_array($result)) {
            $displaytitle = $row['title'];
            if(substr($displaytitle, -5) == ", The") {
                $displaytitle = "The ". substr($displaytitle, 0, -5);
            }
            
            
            $movie = array();
            $movie['id'] = $row['id'];
            $movie['title'] = $displaytitle;
            $movie['sorttitle'] = $row['title'];
            $movie['releasedate'] = $row['releasedate'];
            $movie['version'] = $row['version'];
            $movie['boxart'] = "http://api.customplay.com/data-tv/". $row['id'] ."/Boxart.jpg";
            $movie['datapack'] = "http://api.customplay.com/data-tv/". $row['id'] ."/". $row['id'] .".zip";
            
            $movies[] = $movie;
        }

        
        echo json_encode($movies);
    }
    //DOWNLOAD ALL MOVIES UPDATED FOR BONUS MOPVIES
    if($_POST["request"] == "allMoviesAndBonus") {
        $query = "SELECT id, title, version, releasedate, mapdate, popcornbonus, popcornbonustitle, popcornbonusdesc, active FROM movies WHERE popcornactive = 1 OR popcornbonus > 0";
        if($_POST[device] == "AppleTV") {
            $query = "SELECT id, title, version, releasedate, mapdate, popcornbonus, popcornbonustitle, popcornbonusdesc, active FROM movies WHERE (popcornactive = 1 OR popcornbonus > 0) AND excludetv = 0";
        }
        if($_POST["mode"] == "dev") {
            $query = "SELECT id, title, version, releasedate, mapdate, popcornbonus, popcornbonustitle, popcornbonusdesc, active FROM movies WHERE 1";
        }
        
        $result = mysql_query($query) or die ('error: '. mysql_error());
        
        $movies = array();
        while($row = mysql_fetch_array($result)) {
            $displaytitle = $row['title'];
            if(substr($displaytitle, -5) == ", The") {
                $displaytitle = "The ". substr($displaytitle, 0, -5);
            }
            
			$mapdate = strtotime($row[mapdate]);
			$now = time();
			
			if((($now - $mapdate) < 3600 * 24 * 14) && ($mapdate > 0)) {
				$newmap = "1";
			} else {
				$newmap = "0";
			}
            
            $movie = array();
            $movie['id'] = $row['id'];
            $movie['title'] = $displaytitle;
            $movie['sorttitle'] = $row['title'];
            $movie['releasedate'] = $row['releasedate'];
			$movie['newmap'] = (string)$newmap;
            $movie['version'] = $row['version'];
            $movie['bonus'] = $row['popcornbonus'];
            $movie['bonusdesc'] = $row['popcornbonusdesc'];
            $movie['bonustitle'] = $row['popcornbonustitle'];
            $movie['boxart'] = "http://api.customplay.com/data-tv/". $row['id'] ."/Boxart.jpg";
            $movie['datapack'] = "http://api.customplay.com/data-tv/". $row['id'] ."/". $row['id'] .".zip";
            
            $movies[] = $movie;
        }
        
        
        echo json_encode($movies);
    }
    
    if($_POST["request"] == "userChoice") {
        $query = "SELECT tid FROM tvaudience WHERE mid = $_POST[movie] AND typeid = $_POST[type] AND linkid = $_POST[linkID] AND hash = '$_POST[hash]'";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        $count = mysql_num_rows($result);
        if($count > 0) {
            $row = mysql_fetch_array($result);
            $query = "UPDATE tvaudience SET count = count + 1 WHERE tid = $row[tid]";
            mysql_query($query) or die ('error: '. mysql_error());
        } else {
            $query = "INSERT INTO tvaudience (mid, typeid, linkid, hash, count) VALUES ($_POST[movie], $_POST[type], $_POST[linkID], '$_POST[hash]', 1)";
            mysql_query($query) or die ('error: '. mysql_error());
        }
    }
    
    if($_POST["request"] == "audienceHelp") {
        $query = "SELECT hash, count FROM tvaudience WHERE mid = $_POST[movie] AND typeid = $_POST[type] AND linkid = $_POST[linkID]";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        $data = array();
        while($row = mysql_fetch_array($result)) {
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
        $result = mysql_query($query) or die ('error: '. mysql_error());
        echo 'done';
    }
    if($_POST["request"] == "logEventUpd") {
        $now = time();
        $query = "INSERT INTO tvactivity (uuid, device, platform, movieid, act, event, timestamp, score, popspent, popearned, moviepicked) VALUES ('$_POST[uuid]', '$_POST[device]', $_POST[platform], $_POST[movieid], $_POST[act], $_POST[event], $now, $_POST[score], $_POST[popspent], $_POST[popearned], $_POST[moviepicked])";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        echo 'done';
    }
	if($_POST["request"] == "logEventWithLifelines") {
        $now = time();
        $query = "INSERT INTO tvactivity (uuid, device, platform, movieid, act, event, timestamp, score, popspent, popearned, lifeline_5050, lifeline_audience, lifeline_undo, moviepicked) VALUES ('$_POST[uuid]', '$_POST[device]', $_POST[platform], $_POST[movieid], $_POST[act], $_POST[event], $now, $_POST[score], $_POST[popspent], $_POST[popearned], $_POST[lifeline_5050], $_POST[lifeline_audience], $_POST[lifeline_undo], $_POST[moviepicked])";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        echo 'done';
    }
    
    if($_POST["request"] == "adCheck") {
        $now = time();
        $query = "SELECT * FROM tvaddisplays WHERE uuid = '$_POST[uuid]'";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        $count = mysql_num_rows($result);
        
        if($_POST[mode] == "testing") {
            $data = array("text"=>"From the creators of PopcornTrivia\nThe CustomPlay App\nAvailable for iPad and iPhone", "url"=>"http://api.customplay.com/video/videoad1.mp4");
            //$data = array("google"=>"yes");
			//$data = array("web"=>"yes", "url"=>"http://popcorntrivia.com/app-help.php");
            echo json_encode($data);
        } else {
            if($count > 0) {
                //FOR FUTURE USE, LOGID FOR REPEATED AD SHOWS
                //$row = mysql_fetch_array($result);
                //echo $row;
                //if(($row[timestamp] < ($now - 60*15)) && ($row[device] != "Apple TV")) {
                //    $query = "UPDATE tvaddisplay SET timestamp = $now WHERE aid = $row[aid]";
                //    mysql_query($query);
                //    $data = array("google"=>"yes");
                //    echo json_encode($data);
                //}
            } else {
                //$query = "INSERT INTO tvaddisplays (uuid, token, device, timestamp, adid) VALUES ('$_POST[uuid]', '$_POST[token]', '$_POST[device]', $now, 1)";
                //mysql_query($query) or die ('error: '. mysql_error());
                
                //$data = array("text"=>"From the creators of PopcornTrivia\nThe CustomPlay App\nAvailable for iPad and iPhone", "url"=>"http://api.customplay.com/video/videoad1.mp4");
                //echo json_encode($data);
            }
        }
    }
	
	if($_POST["request"] == "androidUserdataSave") {
		$query = "SELECT tvuid, hash FROM tvuserdata WHERE hash = '$_POST[hash]'";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		if($count > 0) {
			$query = "UPDATE tvuserdata SET data = '$_POST[data]' WHERE hash = '$_POST[hash]'";
			mysql_query($query) or die ('error: '. mysql_error());
		} else {
			$query = "INSERT INTO tvuserdata (hash, data) VALUES ('$_POST[hash]', '$_POST[data]')";
			mysql_query($query) or die ('error: '. mysql_error());
		}
		echo "done";
	}
	if($_POST["request"] == "androidUserdataGet") {
		$query = "SELECT hash, data FROM tvuserdata WHERE hash = '$_POST[hash]'";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$row = mysql_fetch_array($result);
		echo $row[data];
	}
    if($_POST["request"] == "spGameUpdatePoints") {
		$query = "UPDATE pt_multiusers SET stats_sp_points = $_POST[points] WHERE uid = $_POST[uid]";
		mysql_query($query);
	}
	
	
	
	//MULTIUSER SECTION
	if($_POST["request"] == "loginUser") {
		if(!$_POST[points]) $_POST[points] = 0;
		
		$query = "SELECT uid, first_name, last_name, image FROM pt_multiusers WHERE network_id = '$_POST[userid]'";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		if($count > 0) {
			$row = mysql_fetch_array($result);
			$login = array();
			$login[uid] = $row[uid];
			$login[first_name] = $row[first_name];
			$login[last_name] = $row[last_name];
			$login[image] = $row[image];
			
			$query = "UPDATE pt_multiusers SET token = '$_POST[token]', stats_sp_points = $_POST[points] WHERE uid = $row[uid]";
			mysql_query($query);
		} else {
			$now = time();
			$_POST[first_name] = mysql_real_escape_string(trim($_POST[first_name]));
			$_POST[last_name] = mysql_real_escape_string(trim($_POST[last_name]));
			$_POST[email] = mysql_real_escape_string($_POST[email]);
			
			$query = "INSERT INTO pt_multiusers (network, network_id, first_name, last_name, email, image, stats_sp_points, token, timestamp) VALUES ('$_POST[network]', '$_POST[userid]', '$_POST[first_name]', '$_POST[last_name]', '$_POST[email]', '$_POST[image]', $_POST[points], '$_POST[token]', $now)";
			mysql_query($query) or die ('error: '. mysql_error());
			$uid = (string)mysql_insert_id();
			
			$login = array();
			$login[uid] = $uid;
			$login[first_name] = stripslashes($_POST[first_name]);
			$login[last_name] = stripslashes($_POST[last_name]);
			$login[image] = $_POST[image];
		}
		echo json_encode($login);
	}
	if($_POST["request"] == "updatetoken") {
		if(!$_POST[points]) $_POST[points] = 0;
		
		
		$query = "SELECT uid FROM pt_multiusers WHERE uid = '$_POST[uid]'";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		if($count > 0) {
			$row = mysql_fetch_array($result);
			$login = array();
			$login[uid] = $row[uid];
			
			$query = "UPDATE pt_multiusers SET token = '$_POST[token]' WHERE uid = $row[uid] LIMIT 1";
			mysql_query($query);
		} else {
			
			$login = array();
		}
		echo json_encode($login);
	}
	if($_POST['request'] == "addWindowsToken"){
		$token = $_POST['client_url'];
		$uid = $_POST['uid'];
		$query = "UPDATE pt_multiusers SET token = '$token' WHERE uid = $uid";
		$result=mysql_query($query) or die (json_encode(array('status'=>"not saved" ,'error'=>mysql_error()))); 
		echo(json_encode(array("status"=>"saved")));
	}
	
	if($_POST["request"] == "multiGameList") {
		$games = array();
		$query = "SELECT gid, bet, playerOne, playerTwo, scoreOne, scoreTwo, status, status_updated, timer, questions FROM pt_games WHERE (playerOne = $_POST[uid] OR playerTwo = $_POST[uid]) AND status < 5 AND challenge = 0 ORDER BY bet DESC";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$now = time();
		
		while($row = mysql_fetch_array($result)) {
			$game = $row;
	
			$game = array();
			$oponentId = 0;
			if($row[playerOne] == $_POST[uid]) {
				$oponentId = $row[playerTwo];
				$myscore = $row[scoreOne];
				$opscore = $row[scoreTwo];
			} else {
				$oponentId = $row[playerOne];
				$myscore = $row[scoreTwo];
				$opscore = $row[scoreOne];
			}
			
			$game[gid] = $row[gid];
			$game[bet] = $row[bet];
			//$game[status] = $row[status];
			
			if($oponentId > 0) {
				$subquery = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_multiusers WHERE uid = $oponentId";
				$subresult = mysql_query($subquery) or die ('error: '. mysql_error());
				$subrow = mysql_fetch_array($subresult);
				
				$game[op_uid] = $subrow[uid];
				$game[op_name] = trim(stripslashes($subrow[first_name])) ." ". stripslashes(substr(trim($subrow[last_name]), 0, 1)) .".";
				$game[op_image] = $subrow[image];
				$game[op_stats_games] = $subrow[stats_games];
				$game[op_stats_wins] = $subrow[stats_wins];
				$game[op_stats_points] = $subrow[stats_points];
				$game[op_stats_popcorn] = $subrow[stats_popcorn];
				$game[op_score] = (string)$opscore;
			}
			
			//MY STATS
			$subquery = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_multiusers WHERE uid = $_POST[uid]";
			$subresult = mysql_query($subquery) or die ('error: '. mysql_error());
			$subrow = mysql_fetch_array($subresult);
				
			$game[my_uid] = $subrow[uid];
			$game[my_name] = trim(stripslashes($subrow[first_name])) ." ". stripslashes(substr(trim($subrow[last_name]), 0, 1)) .".";
			$game[my_image] = $subrow[image];
			$game[my_stats_games] = $subrow[stats_games];
			$game[my_stats_wins] = $subrow[stats_wins];
			$game[my_stats_points] = $subrow[stats_points];
			$game[my_stats_popcorn] = $subrow[stats_popcorn];
			$game[my_score] = (string)$myscore;
	
			if($row[status] == 0) {
				$game[statustext] = "Waiting for opponent";
				$game[status] = "0";
				$game[timer] = "";
			} else if(($row[status] == 1) || ($row[status] == 2)) {
				$delta = 36*3600 - ($now - $row[timer]);
				if($delta < 0) {
					//TIMER EXPIRED
					$query2 = "UPDATE pt_games SET status = 3 WHERE gid = $row[gid]";
					mysql_query($query2) or die ('error: '. mysql_error());
					
					$game[statustext] = "View results";
					$game[timer] = "";
					$game[status] = "3";
				} else {
					if($myscore > 0) {
						$game[statustext] = "Waiting for ". $game[op_name] ." to finish";
						$game[status] = "2";
						$game[timer] = (string)intval($delta / 3600) ."h";
					} else {
						$game[statustext] = "Play against ". $game[op_name];
						$game[status] = "1";
						$game[timer] = (string)intval($delta / 3600) ."h";
					}
				}
			} else {
				$game[statustext] = "View results";
				$game[timer] = "";
				$game[status] = "3";
			}
			if($row[status_updated] != $_POST[uid]) {
				$games[] = $game;
			}
		}
		
		echo json_encode($games);
	}
	if($_POST["request"] == "multiGameListv2") {
		echo 'here we go.....<pre>';
		$data = array();
		$games = array();
		$query = "SELECT gid, bet, category, playerOne, playerTwo, scoreOne, scoreTwo, status, status_updated, timer, questions FROM pt_games WHERE (playerOne = $_POST[uid] OR playerTwo = $_POST[uid]) AND status < 5 AND status > 0 AND challenge = 0 ORDER BY timer"; echo $query; echo '<br>';
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$now = time();
		
		while($row = mysql_fetch_array($result)) {
			$game = $row;
	
			$game = array();
			$oponentId = 0;
			if($row[playerOne] == $_POST[uid]) {
				$oponentId = $row[playerTwo];
				$myscore = $row[scoreOne];
				$opscore = $row[scoreTwo];
			} else {
				$oponentId = $row[playerOne];
				$myscore = $row[scoreTwo];
				$opscore = $row[scoreOne];
			}
			
			$game[gid] = $row[gid];
			$game[bet] = $row[bet];
			$game[category] = $row[category];
			//$game[status] = $row[status];
			
			if($oponentId > 0) {
				$subquery = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_multiusers WHERE uid = $oponentId"; echo $query; echo '<br>';
				$subresult = mysql_query($subquery) or die ('error: '. mysql_error());
				$subrow = mysql_fetch_array($subresult);
				
				$game[op_uid] = $subrow[uid];
				$game[op_name] = trim(stripslashes($subrow[first_name])) ." ". stripslashes(substr(trim($subrow[last_name]), 0, 1)) .".";
				$game[op_image] = $subrow[image];
				$game[op_stats_games] = $subrow[stats_games];
				$game[op_stats_wins] = $subrow[stats_wins];
				$game[op_stats_points] = $subrow[stats_points];
				$game[op_stats_popcorn] = $subrow[stats_popcorn];
				$game[op_score] = (string)$opscore;
			}
			
			//MY STATS
			$subquery = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_multiusers WHERE uid = $_POST[uid]"; echo $query; echo '<br>';
			$subresult = mysql_query($subquery) or die ('error: '. mysql_error());
			$subrow = mysql_fetch_array($subresult);
				
			$game[my_uid] = $subrow[uid];
			$game[my_name] = trim(stripslashes($subrow[first_name])) ." ". stripslashes(substr(trim($subrow[last_name]), 0, 1)) .".";
			$game[my_image] = $subrow[image];
			$game[my_stats_games] = $subrow[stats_games];
			$game[my_stats_wins] = $subrow[stats_wins];
			$game[my_stats_points] = $subrow[stats_points];
			$game[my_stats_popcorn] = $subrow[stats_popcorn];
			$game[my_score] = (string)$myscore;
	
			if(($row[status] == 1) || ($row[status] == 2)) {
				$delta = 36*3600 - ($now - $row[timer]);
				if($delta < 0) {
					//TIMER EXPIRED
					$query2 = "UPDATE pt_games SET status = 3 WHERE gid = $row[gid]"; echo $query; echo '<br>';
					mysql_query($query2) or die ('error: '. mysql_error());
					
					$game[statustext] = "View results";
					$game[timer] = "";
					$game[status] = "3";
				} else {
					if($myscore > 0) {
						$game[statustext] = "Waiting for ". $game[op_name] ." to finish";
						$game[status] = "2";
						$game[timer] = (string)intval($delta / 3600) ."h";
					} else {
						$game[statustext] = "Play against ". $game[op_name];
						$game[status] = "1";
						$game[timer] = (string)intval($delta / 3600) ."h";
					}
					if($game[timer] == "0h") {
						$game[timer] = "<1h";
					}
				}
			} else {
				$game[statustext] = "View results";
				$game[timer] = "";
				$game[status] = "3";
			}
			if($row[status_updated] != $_POST[uid]) {
				$games[] = $game;
			}
		}
		
		$data[games] = $games;
		
	
		$games = array();
		$query = "SELECT gid, bet, pt_games.category, challenge_categories.category as categoryname, playerOne, playerTwo, scoreOne, scoreTwo, status, status_updated, timer, questions FROM pt_games LEFT JOIN challenge_categories ON pt_games.category = challenge_categories.categoryid WHERE (playerOne = $_POST[uid] OR playerTwo = $_POST[uid]) AND status = 0 AND challenge = 0 ORDER BY bet DESC, timestamp"; echo $query; echo '<br>';
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$now = time();
		
		while($row = mysql_fetch_array($result)) {
			$game = $row;
	
			$game = array();
			$oponentId = 0;
			if($row[playerOne] == $_POST[uid]) {
				$oponentId = $row[playerTwo];
				$myscore = $row[scoreOne];
				$opscore = $row[scoreTwo];
			} else {
				$oponentId = $row[playerOne];
				$myscore = $row[scoreTwo];
				$opscore = $row[scoreOne];
			}
			
			$game[gid] = $row[gid];
			$game[bet] = $row[bet];
			$game[category] = $row[category];
			//$game[status] = $row[status];
			
			if($oponentId > 0) {
				$subquery = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_multiusers WHERE uid = $oponentId"; echo $query; echo '<br>';
				$subresult = mysql_query($subquery) or die ('error: '. mysql_error());
				$subrow = mysql_fetch_array($subresult);
				
				$game[op_uid] = $subrow[uid];
				$game[op_name] = trim(stripslashes($subrow[first_name])) ." ". stripslashes(substr(trim($subrow[last_name]), 0, 1)) .".";
				$game[op_image] = $subrow[image];
				$game[op_stats_games] = $subrow[stats_games];
				$game[op_stats_wins] = $subrow[stats_wins];
				$game[op_stats_points] = $subrow[stats_points];
				$game[op_stats_popcorn] = $subrow[stats_popcorn];
				$game[op_score] = (string)$opscore;
			}
			
			//MY STATS
			$subquery = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_multiusers WHERE uid = $_POST[uid]"; echo $query; echo '<br>';
			$subresult = mysql_query($subquery) or die ('error: '. mysql_error());
			$subrow = mysql_fetch_array($subresult);
				
			$game[my_uid] = $subrow[uid];
			$game[my_name] = trim(stripslashes($subrow[first_name])) ." ". stripslashes(substr(trim($subrow[last_name]), 0, 1)) .".";
			$game[my_image] = $subrow[image];
			$game[my_stats_games] = $subrow[stats_games];
			$game[my_stats_wins] = $subrow[stats_wins];
			$game[my_stats_points] = $subrow[stats_points];
			$game[my_stats_popcorn] = $subrow[stats_popcorn];
			$game[my_score] = (string)$myscore;
			
			$game[statustext] = "\"". $row[categoryname] ."\" Game Pending";
			$game[status] = "0";
			$game[timer] = "";
				
			$games[] = $game;
		}
		
		$data[waiting] = $games;
		
		
		$challenges = array();
		$query = "SELECT gid, uid, image, bet, pt_games.timestamp, pt_games.category, challenge_categories.category AS categoryname, playerOne, first_name, last_name, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_games LEFT JOIN pt_multiusers ON pt_games.playerOne = pt_multiusers.uid LEFT JOIN challenge_categories ON pt_games.category = challenge_categories.categoryid WHERE challenge = $_POST[uid] AND status < 5 ORDER BY timestamp"; echo $query; echo '<br>';
		$result = mysql_query($query) or die ('error: '. mysql_error());
	
		while($row = mysql_fetch_array($result)) {
			$game = array();
			$game[gid] = (string)$row[gid];
			$game[category] = (string)$row[category];
			$game[categoryname] = $row[categoryname];
			$game[bet] = (string)$row[bet];
			
			$game[op_uid] = $row[uid];
			$game[op_name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			$game[op_image] = $row[image];
			$game[op_stats_games] = $row[stats_games];
			$game[op_stats_wins] = $row[stats_wins];
			$game[op_stats_points] = $row[stats_points];
			$game[op_stats_popcorn] = $row[stats_popcorn];	
			
			$game[statustext] = "Challenge from ". trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			
			$delta = 36*3600 - ($now - $row[timestamp]);
			if($delta < 0) {
				$game[statustext] = "";
			} else {
				$game[timer] = (string)intval($delta / 3600) ."h";
				if($game[timer] == "0h") {
					$game[timer] = "<1h";
				}
			}
			if(strlen($game[statustext]) > 0) {
				$challenges[] = $game;
			}
		}
		
		
		//MY CHALLENGES SENT
		$expired = array();
		$query = "SELECT gid, uid, image, bet, pt_games.timestamp, pt_games.category, challenge_categories.category AS categoryname, playerOne, first_name, last_name, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_games LEFT JOIN pt_multiusers ON pt_games.challenge = pt_multiusers.uid LEFT JOIN challenge_categories ON pt_games.category = challenge_categories.categoryid WHERE playerOne = $_POST[uid] AND challenge != 0 AND status = 0 ORDER BY timestamp"; echo $query; echo '<br>';
		$result = mysql_query($query) or die ('error: '. mysql_error());
	
		while($row = mysql_fetch_array($result)) {
			$game = array();
			$game[gid] = (string)$row[gid];
			$game[category] = (string)$row[category];
			$game[categoryname] = $row[categoryname];
			$game[bet] = (string)$row[bet];
			
			$game[op_uid] = $row[uid];
			$game[op_name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			$game[op_image] = $row[image];
			$game[op_stats_games] = $row[stats_games];
			$game[op_stats_wins] = $row[stats_wins];
			$game[op_stats_points] = $row[stats_points];
			$game[op_stats_popcorn] = $row[stats_popcorn];
			$game[waiting] = "yes";
			
			$game[statustext] = "Waiting for ". trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			$delta = 36*3600 - ($now - $row[timestamp]);
			if($delta < 0) {
				//TIMER EXPIRED
				$query2 = "UPDATE pt_games SET status = 5 WHERE gid = $row[gid]";
				mysql_query($query2) or die ('error: '. mysql_error());
				
				$expired[] = $game;
				$game[statustext] = "";
			} else {
				$game[timer] = (string)intval($delta / 3600) ."h";
				if($game[timer] == "0h") {
					$game[timer] = "<1h";
				}
			}
			if(strlen($game[statustext]) > 0) {
				$challenges[] = $game;
			}
		}
		
		
		
		$data[challenges] = $challenges;
		$data[expired] = $expired;
	
	
			
		$categories = array();
		$allcat = array("id"=>"0","category"=>"All","maxbet"=>"5000");
		$categories[] = $allcat;
		$query = "SELECT categoryid, category, maxbet FROM challenge_categories WHERE active = 1 AND categoryid > 0 ORDER BY category"; echo $query; echo '<br>';
		$result = mysql_query($query) or die ('error: '. mysql_error());
	
		while($row = mysql_fetch_array($result)) {
			$category = array();
			$category[id] = (string)$row[categoryid];
			$category[category] = $row[category];
			$category[maxbet] = (string)$row[maxbet];
			
			$categories[] = $category;
		}
		$data[categories] = $categories;

		print_r($data);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		
	}
	
	if($_POST["request"] == "multiGamePlayed") {
		$query = "SELECT playerOne, playerTwo FROM pt_games WHERE gid = $_POST[gid]";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$row = mysql_fetch_array($result);
		
		//SOMEWHERE HERE SHOUDL SEND ANOTHER NOTIFICATION
		if($row[playerOne] == $_POST[uid]) {
			$updateScore = "scoreOne";
		} else {
			$updateScore = "scoreTwo";
		}
		
		$query = "UPDATE pt_games SET $updateScore = $_POST[score], status = status + 1 WHERE gid = $_POST[gid] AND $updateScore = 0";
		mysql_query($query) or die ('error: '. mysql_error());
		
		if($_POST[points]) {
			$query = "UPDATE pt_multiusers SET stats_sp_points = $_POST[points] WHERE uid = $_POST[uid]";
			mysql_query($query) or die ('error: '. mysql_error());
		}
		
		$game[result] = "success";
		echo json_encode($game);
		
	}
	if($_POST["request"] == "multiGameStart") {
		$query = "SELECT gid, questions, first_name, last_name, image, token FROM pt_games LEFT JOIN pt_multiusers ON pt_games.playerOne = pt_multiusers.uid WHERE bet = $_POST[bet] AND playerOne != $_POST[uid] AND playerTwo = 0 AND category = 0 AND challenge = 0 AND ((SELECT player_cp FROM pt_multiusers WHERE uid = $_POST[uid]) + player_cp) < 2";
		//$query = "SELECT gid, questions, first_name, last_name, image, token FROM pt_games LEFT JOIN pt_multiusers ON pt_games.playerOne = pt_multiusers.uid WHERE bet = $_POST[bet] AND playerOne != $_POST[uid] AND playerTwo = 0 AND gid = 1077";
		//$query = "SELECT gid, questions, first_name, last_name, image, player_cp, token FROM pt_games LEFT JOIN pt_multiusers ON pt_games.playerOne = pt_multiusers.uid WHERE bet = $_POST[bet] AND playerOne != $_POST[uid] AND playerTwo = 0";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		$now = time();
		
		
		
		if($count > 0) {
			$row = mysql_fetch_array($result);
			$query = "UPDATE pt_games SET playerTwo = $_POST[uid], status = 1, timer = $now WHERE gid = $row[gid]";
			mysql_query($query) or die ('error: '. mysql_error());
			$game = array();
			
			$game[gid] = $row[gid];
			$game[status] = "1";	
			
			//SEND PUSH MESSAGE TO PLAYER ONE
			$message = "Your game has started!";
			if(strpos($row[token], "https://") !== false) {
				$auth = authorizePushWindows();
				sendMessageWindows($auth,$row[token],$message);
			} else if(strlen($row[token]) == 64) {
				sendMessageApple($row[token], $message);
			} else {
				sendMessageAndroid($row[token], $message);
			}	
		} else {
			$now = time();
			$movies = array();
			$dupecheck = array();
			$query = "SELECT id FROM movies WHERE popcornactive = 1 OR popcornbonus > 0";
			$result = mysql_query($query) or die ('error: '. mysql_error());
	        while($row = mysql_fetch_array($result)) {
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
			$query = "INSERT INTO pt_games (playerOne, bet, questions, timestamp) VALUES ($_POST[uid], $_POST[bet], '$questions', $now)";
			mysql_query($query) or die ('error: '. mysql_error());
			$game_id = mysql_insert_id();
			
			$game = array();
			$game[gid] = (string)$game_id;
			$game[status] = "0";
			
			//ADD A PLAYER
			$random = mt_rand(0,count($cpplayers) - 1);
			$lucky = $cpplayers[$random];
			$query = "UPDATE pt_games SET playerTwo = $lucky[uid], status = 1, timer = $now WHERE gid = $game_id";
			mysql_query($query) or die ('error: '. mysql_error());
			
			//MESSAGE TO CP PLAYER
			$message = "This is your captain speaking... Please fasten your seatbelt and play a game!";
			
			if(strpos($lucky[token], "https://") !== false) {
				$auth = authorizePushWindows();
				sendMessageWindows($auth,$lucky[token],$message);
			} else if(strlen($lucky[token]) == 64) {
				sendMessageApple($lucky[token], $message);
			} else {
				sendMessageAndroid($lucky[token], $message);
			}
		}
		echo json_encode($game);
		
	}
	if($_POST["request"] == "multiGameChallengeRequest") {
		$now = time();
		$movies = array();
		$dupecheck = array();
		if($_POST[category] == 0) {
			$query = "SELECT id FROM movies WHERE popcornactive = 1 OR popcornbonus > 0";
		} else {
			$query = "SELECT id FROM movies JOIN challenge_categories_mov ON movies.id = challenge_categories_mov.mid WHERE (popcornactive = 1 OR popcornbonus > 0) AND categoryid = $_POST[category]";
		}
		$result = mysql_query($query) or die ('error: '. mysql_error());
	    while($row = mysql_fetch_array($result)) {
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
		$query = "INSERT INTO pt_games (playerOne, bet, category, challenge, questions, timestamp) VALUES ($_POST[uid], $_POST[bet], $_POST[category], $_POST[oponent], '$questions', $now)";
		mysql_query($query) or die ('error: '. mysql_error());
		$game_id = mysql_insert_id();
		
		if($_POST[oponent]) {
			$query = "SELECT token from pt_multiusers WHERE uid = $_POST[oponent]";
			$result = mysql_query($query);
	    	while($row = mysql_fetch_array($result)) {
				$message = "You have a new challenge game!";
				if(strpos($row[token], "https://") !== false) {
					$auth = authorizePushWindows();
					sendMessageWindows($auth,$row[token],$message);
				} else if(strlen($row[token]) == 64) {
					sendMessageApple($row[token], $message);
				} else {
					sendMessageAndroid($row[token], $message);
				}
			}
		}
		
		$game = array();
		$game[gid] = (string)$game_id;
		$game[status] = "0";
		
		echo json_encode($game);
	}
	if($_POST["request"] == "multiGameChallengeAccept") {
		$now = time();
		$query = "UPDATE pt_games SET status = 1, challenge = 0, timer = $now, playerTwo = $_POST[uid] WHERE gid = $_POST[gid]";
		mysql_query($query) or die ('error: '. mysql_error());
	}
	if($_POST["request"] == "multiGameChallengeDecline") {
		$query = "UPDATE pt_games SET timestamp = 1, status_updated = $_POST[uid] WHERE gid = $_POST[gid]";
		mysql_query($query) or die ('error: '. mysql_error());
	}
	
	if($_POST["request"] == "multiGameStartv2") {
		//$query = "SELECT gid, questions, first_name, last_name, image, token FROM pt_games LEFT JOIN pt_multiusers ON pt_games.playerOne = pt_multiusers.uid WHERE bet = $_POST[bet] AND category = $_POST[category] AND playerOne != $_POST[uid] AND playerTwo = 0 AND ((SELECT player_cp FROM pt_multiusers WHERE uid = $_POST[uid]) + player_cp) < 2";
		$query = "SELECT gid, questions, first_name, last_name, image, token FROM pt_games LEFT JOIN pt_multiusers ON pt_games.playerOne = pt_multiusers.uid WHERE bet = $_POST[bet] AND category = $_POST[category] AND playerOne != $_POST[uid] AND playerTwo = 0 AND challenge = 0 AND ((SELECT player_cp FROM pt_multiusers WHERE uid = $_POST[uid]) + player_cp) < 2";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		$now = time();
			
		if($count > 0) {
			$row = mysql_fetch_array($result);
			$query = "UPDATE pt_games SET playerTwo = $_POST[uid], status = 1, timer = $now WHERE gid = $row[gid]";
			mysql_query($query) or die ('error: '. mysql_error());
			$game = array();
			
			$game[gid] = $row[gid];
			$game[status] = "1";	
				
			//SEND PUSH MESSAGE TO PLAYER ONE
			$message = "Your game has started!";
			if(strpos($row[token], "https://") !== false) {
				$auth = authorizePushWindows();
				sendMessageWindows($auth,$row[token],$message);
			} else if(strlen($row[token]) == 64) {
				sendMessageApple($row[token], $message);
			} else {
				sendMessageAndroid($row[token], $message);
			}	
		} else {
			$now = time();
			$movies = array();
			$dupecheck = array();
			if($_POST[category] == 0) {
				$query = "SELECT id FROM movies WHERE popcornactive = 1 OR popcornbonus > 0";
			} else {
				$query = "SELECT id FROM movies JOIN challenge_categories_mov ON movies.id = challenge_categories_mov.mid WHERE (popcornactive = 1 OR popcornbonus > 0) AND categoryid = $_POST[category]";
			}
			
			
			
			$result = mysql_query($query) or die ('error: '. mysql_error());
	        while($row = mysql_fetch_array($result)) {
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
			$query = "INSERT INTO pt_games (playerOne, bet, category, questions, timestamp) VALUES ($_POST[uid], $_POST[bet], $_POST[category], '$questions', $now)";
			mysql_query($query) or die ('error: '. mysql_error());
			$game_id = mysql_insert_id();
			
			$game = array();
			$game[gid] = (string)$game_id;
			$game[status] = "0";
			
			//ADD A PLAYER
			$random = mt_rand(0,count($cpplayers) - 1);
			$lucky = $cpplayers[$random];
			$query = "UPDATE pt_games SET playerTwo = $lucky[uid], status = 1, timer = $now WHERE gid = $game_id";
			mysql_query($query) or die ('error: '. mysql_error());
			
			//MESSAGE TO CP PLAYER
			$message = "This is your captain speaking... Please fasten your seatbelt and play a game!";
			
			if(strpos($lucky[token], "https://") !== false) {
				$auth = authorizePushWindows();
				sendMessageWindows($auth,$lucky[token],$message);
			} else if(strlen($lucky[token]) == 64) {
				sendMessageApple($lucky[token], $message);
			} else {
				sendMessageAndroid($lucky[token], $message);
			}
			
		}
		echo json_encode($game);
		
	}
	if($_POST["request"] == "multiGameViewed") {
		$query = "UPDATE pt_games SET status = status + 1, status_updated = $_POST[uid] WHERE gid = $_POST[gid]";
		mysql_query($query) or die ('error: '. mysql_error());
		
		if(!$_POST[draw]) $_POST[draw] = 0;
		
		$query = "UPDATE pt_multiusers SET stats_wins = stats_wins + $_POST[win], stats_draws = stats_draws + $_POST[draw], stats_points = (((stats_points * stats_games) + $_POST[points]) / (stats_games + 1)), stats_popcorn = stats_popcorn + $_POST[balance], stats_games = stats_games + 1 WHERE uid = $_POST[uid]";
		mysql_query($query) or die ('error: '. mysql_error());
		
		$game[result] = "success";
		echo json_encode($game);
	}
	
	
	if($_POST["request"] == "multiGameData") {
		//SHOUDL BE REQUESTING SPECIFIC, I'M PULLING A RANDOM AVAILABLE
		$query = "SELECT questions FROM pt_games WHERE gid = $_POST[gameid]";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$row = mysql_fetch_array($result);
		
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
    if($_POST["request"] == "multiGameStats") {
		$query = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_draws, stats_points, stats_popcorn FROM pt_multiusers WHERE uid = $_POST[uid]";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$row = mysql_fetch_array($result);
		
		$data[uid] = (string)$row[uid];
		$data[name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
		$data[image] = $row[image];
		$data[games] = (string)$row[stats_games];
		$data[wins] = (string)$row[stats_wins];
		$data[draws] = (string)$row[stats_draws];
		$data[score] = (string)$row[stats_points];
		$data[popcorns] = (string)$row[stats_popcorn];

		
		echo json_encode($data);
	}
    if($_POST["request"] == "multiGameStatsReset") {
		$query = "UPDATE pt_multiusers SET stats_games = 0, stats_wins = 0, stats_draws = 0, stats_points = 0, stats_popcorn = 0 WHERE uid = $_POST[uid]";
		mysql_query($query) or die ('error: '. mysql_error());
		echo "done";
	}
	if($_POST["request"] == "multiGameLeaderboard") {
		$query = "SELECT uid, first_name, last_name, image, stats_popcorn FROM pt_multiusers WHERE uid = $_POST[uid]";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$row = mysql_fetch_array($result);
		
		$output = array();
		
		$data[uid] = (string)$row[uid];
		$data[name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
		$data[image] = $row[image];
		$data[popcorns] = (string)$row[stats_popcorn];
		$data[wins] = (string)$row[stats_wins];
		$data[points] = (string)$row[stats_sp_points];
		
		if($_POST[boardtype] == "sp") {
			$query = "select count(*) as sp_position from pt_multiusers ptm,
(select * from pt_multiusers  where uid=$row[uid]) as ptmmine
where ptm.stats_sp_points>0 and
(
ptm.stats_sp_points>ptmmine.stats_sp_points or
(ptm.stats_sp_points=ptmmine.stats_sp_points and ptm.first_name>=ptmmine.first_name)
)
order by ptm.stats_sp_points desc, ptm.first_name, ptm.uid";
			$result = mysql_query($query) or die ('error: '. mysql_error());
			$row = mysql_fetch_array($result);
			$sp_position = $row[sp_position];
			$data[sp_position] = (string)$sp_position;
		} else {
			$query = "select count(*) as mp_position from pt_multiusers ptm,
(select * from pt_multiusers  where uid=$row[uid]) as ptmmine
where ptm.stats_games>0 and
(
ptm.stats_popcorn>ptmmine.stats_popcorn or
(ptm.stats_popcorn=ptmmine.stats_popcorn and ptm.stats_wins>=ptmmine.stats_wins) or
(ptm.stats_popcorn=ptmmine.stats_popcorn and ptm.stats_wins=ptmmine.stats_wins and ptm.stats_games>=ptmmine.stats_games) or
(ptm.stats_popcorn=ptmmine.stats_popcorn and ptm.stats_wins=ptmmine.stats_wins and ptm.stats_games=ptmmine.stats_games and ptm.first_name>ptmmine.first_name)
)
order by  ptm.stats_popcorn desc,  ptm.stats_wins desc,  ptm.stats_games desc,  ptm.first_name,  ptm.uid";
			$result = mysql_query($query) or die ('error: '. mysql_error());
			$row = mysql_fetch_array($result);
			$mp_position = $row[mp_position];
			$data[mp_position] = (string)$mp_position;
		}
		
		
		$output[me] = $data;
		
		$users = array();
		$position = $_POST[start] + 1;
		
		if($_POST[boardtype] == "sp") {
			$query = "SELECT uid, first_name, last_name, image, stats_popcorn, stats_games, stats_sp_points, stats_wins FROM pt_multiusers WHERE stats_sp_points > 0 ORDER BY stats_sp_points DESC LIMIT $_POST[start], 10";
		} else {
			$query = "SELECT uid, first_name, last_name, image, stats_popcorn, stats_games, stats_sp_points, stats_wins FROM pt_multiusers WHERE stats_games > 0 ORDER BY stats_popcorn DESC, stats_wins DESC, stats_games DESC, first_name, uid LIMIT $_POST[start], 10";
		}
		
		$result = mysql_query($query) or die ('error: '. mysql_error());
		while($row = mysql_fetch_array($result)) {
			$data[uid] = (string)$row[uid];
			$data[name] = $position .". ". trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			$data[image] = $row[image];
			$data[popcorns] = (string)$row[stats_popcorn];
			$data[wins] = (string)$row[stats_wins];
			$data[points] = (string)$row[stats_sp_points];
			
			$users[] = $data;
			$position++;
		}
		$output[users] = $users;
	
		echo json_encode($output);
	}
	if($_POST["request"] == "multiNameSearch") {
		$users = array();
		$sterms = explode(" ", $_POST[query]);
		$query = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_multiusers WHERE ";
		foreach($sterms as $term) {
			$query .= "(first_name LIKE '$term%' OR last_name LIKE '$term%') AND ";
		}
		$query .= "uid != $_POST[uid] ORDER BY stats_popcorn DESC LIMIT $_POST[start], 10";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		while($row = mysql_fetch_array($result)) {
			$data = array();
			$data[uid] = (string)$row[uid];
			$data[name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			$data[image] = $row[image];
			$data[popcorns] = (string)$row[stats_popcorn];
			$data[wins] = (string)$row[stats_wins];
			$data[points] = (string)$row[stats_sp_points];
			
			$users[] = $data;
		}
		$matches[users] = $users;
		echo json_encode($matches);
	}
	if($_POST["request"] == "multiLeaderSearch") {
		$users = array();
		$sterms = explode(" ", $_POST[query]);
		$query = "SELECT uid, first_name, last_name, image, stats_games, stats_wins, stats_points, stats_popcorn FROM pt_multiusers WHERE uid != $_POST[uid] ORDER BY stats_popcorn DESC LIMIT $_POST[start], 10";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		while($row = mysql_fetch_array($result)) {
			$data = array();
			$data[uid] = (string)$row[uid];
			$data[name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			$data[image] = $row[image];
			$data[popcorns] = (string)$row[stats_popcorn];
			$data[wins] = (string)$row[stats_wins];
			$data[points] = (string)$row[stats_sp_points];
			
			$users[] = $data;
		}
		$matches[users] = $users;
		echo json_encode($matches);
	}
	
	if($_POST["request"] == "multiGameUserLookup") {
		$query = "SELECT uid, first_name, last_name, image, token FROM pt_multiusers WHERE uid = $_POST[uid]";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		$login = array();
		if($count > 0) {
			$row = mysql_fetch_array($result);
			$login = array();
			$login[uid] = $row[uid];
			$login[name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			$login[image] = $row[image];
		}
		echo json_encode($login);
	}
	
	if($_POST["request"] == "multiSponsoredLookup") {
		$query = "SELECT sid	, itunesid, title, year, pt_sponsor.image as image, supporters, pt_sponsor.timestamp as timestamp, pt_sponsor.uid, first_name, last_name, pt_multiusers.image as userimage FROM pt_sponsor LEFT JOIN pt_multiusers ON pt_multiusers.uid = pt_sponsor.uid WHERE status = 0 ORDER BY supporters DESC, pt_sponsor.timestamp";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$movies = array();
		while($row = mysql_fetch_array($result)) {
			$movie = array();
			$movie[sid] = (string)$row[sid];
			$movie[itunesid] = $row[itunesid];
			$movie[title] = $row[title];
			$movie[year] = $row[year];
			$movie[image] = $row[image];
			$movie[supporters] = (string)$row[supporters];
			$movie[status] = (string)$row[status];
		
			$movie[uid] = (string)$row[uid];
			$movie[name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
			$movie[userimage] = $row[userimage];
			
			$movies[] = $movie;
		}
		$output = array();
		$output[movies] = $movies;
		
		
		$timeout = strtotime("10:00PM August 20, 2016");
		$now = time();
		$delta = $timeout - $now;
		$output[countdown] = (string)$delta;
		$output[treshold] = "10";
		
		echo json_encode($output);
	}
	
	if($_POST["request"] == "multiSponsoredNew") {
		$valid = true;
		$query = "SELECT rid FROM pt_sponsor_reserved WHERE itunesid = '$_POST[itunesid]'";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		if($count > 0) {
			$valid = false;
			$status = "fail";
			$reason = "This movie is already being considered for PopcornTrivia!";
		}
		$query = "SELECT mid FROM movies WHERE itunesid = '$_POST[itunesid]'";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		if($count > 0) {
			$valid = false;
			$status = "fail";
			$reason = "This movie is already in PopcornTrivia!";
		}
		$query = "SELECT sid FROM pt_sponsor WHERE itunesid = '$_POST[itunesid]'";
		$result = mysql_query($query) or die ('error: '. mysql_error());
		$count = mysql_num_rows($result);
		if($count > 0) {
			$valid = false;
			$status = "fail";
			$reason = "This movie is already up for voting!";
		}
		
		if($valid) {
			$now = time();
			$_POST[title] = mysql_real_escape_string($_POST[title]);
			$query = "INSERT into pt_sponsor (itunesid, title, year, image, uid, timestamp, supporters, status) VALUES ('$_POST[itunesid]', '$_POST[title]', '$_POST[year]', '$_POST[image]', $_POST[uid], $now, 0, 0)";
			mysql_query($query) or die ('error: '. mysql_error());
			$status = "success";
			$reason = "Yay! ". stripslashes($_POST[title]) ." is now added to Sponsor a Movie voting list!";
		}
		
		$output = array("status"=>$status, "message"=>$reason);
		echo json_encode($output);
	}
	if($_POST["request"] == "multiSponsoredPromote") {
		$query = "UPDATE pt_sponsor SET supporters = supporters + 1 WHERE sid = $_POST[sid]";
		mysql_query($query) or die ('error: '. mysql_error());
		$output = array("status"=>"done");
		echo json_encode($output);
	}
	
    mysql_close($dbh);
	
	
	
	
	
	
	
	
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
	$token_body='grant_type=client_credentials&client_id=ms-app://s-1-15-2-981807083-30848534-1972440605-4155331106-985848221-2782754458-2623629205&client_secret=Th3rMfOSumhq6Fq6uzDagUm&scope=notify.windows.com';
	
	$tokenOptions = array(
		CURLOPT_POST              =>    true,
		CURLOPT_URL	              =>    $url,
		CURLOPT_RETURNTRANSFER    =>    true,
		CURLOPT_POSTFIELDS		  =>    $token_body,
		CURLOPT_HTTPHEADER     	  =>    $token_headers,
		CURLOPT_VERBOSE        =>    true,
		CURLOPT_STDERR         =>    $f
	);	

	$ch = curl_init();
	curl_setopt_array($ch,$tokenOptions);
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