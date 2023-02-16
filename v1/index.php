<?php

    $dbh = mysql_connect ("mariadb-134.wc1.phx1.stabletransit.com", "2008150_poptriv", "Cust0mPl@y!") or die ('Cannot connect db: '. mysql_error());
    mysql_select_db ("2008150_custompl_db") or die('Cannot select db: ' . mysql_error());
	
    
    //CHECK FOR PERSONA DATA UPDATE
    if($_POST["request"] == "appUpdate") {
        //APPWIDE DATA
        $current_version = 27; //PROBABLY WILL HAVE TO GENERATE SOMEWHERE
    
        if($_POST[version] < $current_version) {
            $array = array('version' => (string)$current_version, 'datapack' => 'http://api.customplay.com/data/common/AppUpdatePack.zip');
            echo json_encode($array);
        }
    }

    //DOWNLOAD ALL MOVIES INFO
    if($_POST["request"] == "allMovies") {
        $query = "SELECT id, title, year, releasedate, itunesid, amazonid, vuduid, windowsid, mapdate, version, hashtags, rating, active FROM movies LEFT JOIN movieratings ON movies.id = movieratings.mid LEFT JOIN ratings ON movieratings.rid = ratings.ratingid WHERE active = 1";
        if($_POST["mode"] == "dev") {
            $query = "SELECT id, title, year, releasedate, itunesid, amazonid, vuduid, windowsid, mapdate, version, hashtags, rating, active FROM movies LEFT JOIN movieratings ON movies.id = movieratings.mid LEFT JOIN ratings ON movieratings.rid = ratings.ratingid WHERE active = 1 OR id = 1876";
        }
        $result = mysql_query($query) or die ('error: '. mysql_error());
        
        $movies = array();
        while($row = mysql_fetch_array($result)) {
            $genres = array();
            $myid = $row['id'];
            $query = "SELECT genre FROM moviegenres LEFT JOIN genres ON moviegenres.gid = genres.genreid WHERE mid = $myid";
            $res = mysql_query($query) or die ('error: '. mysql_error());
            while($r = mysql_fetch_array($res)) {
               $genres[] = $r['genre'];
            }
            $displaytitle = $row['title'];
            if(substr($displaytitle, -5) == ", The") {
                $displaytitle = "The ". substr($displaytitle, 0, -5);
            }
            
            
            $movie = array();
            $movie['id'] = $row['id'];
            $movie['sorttitle'] = $row['title'];
            $movie['title'] = $displaytitle;
            $movie['year'] = $row['year'];
            $movie['releasedate'] = $row['releasedate'];
            $movie['itunesid'] = $row['itunesid'];
            $movie['amazonid'] = $row['amazonid'];
            $movie['vuduid'] = $row['vuduid'];
            $movie['windowsid'] = $row['windowsid'];
            $movie['rating'] = $row['rating'];
            $movie['version'] = $row['version'];
            $movie['mapdate'] = $row['mapdate'];
            $movie['hashtags'] = $row['hashtags'];
            $movie['genres'] = $genres;
            $movie['boxart'] = "http://api.customplay.com/data/". $row['id'] ."/Boxart.jpg";
            $movie['background'] = "http://api.customplay.com/data/". $row['id'] ."/BG.jpg";
            $movie['backgroundlandsc'] = "http://api.customplay.com/data/". $row['id'] ."/BG_Portrait.jpg";
            $movie['datapack'] = "http://api.customplay.com/data/". $row['id'] ."/". $row['id'] .".zip";
            
            $movies[] = $movie;
        }

        
        echo json_encode($movies);
    }
    
    //REGISTER NEW USER
    if($_POST["request"] == "newUser") {
        $now = time();
        if(!$_POST[platform]) $_POST[platform] = 0;
        if($_POST[type] == "new") {
            $query = "INSERT INTO users (device, platform, uuid, token, timestamp) VALUES ('$_POST[device]', $_POST[platform], '$_POST[uuid]', '$_POST[token]', $now)";
            mysql_query($query) or die ('error: '. mysql_error());
            $id = mysql_insert_id();
            if($id) {
                $array = array("uid" => (string)$id, "token" => $_POST[token]);
                echo json_encode($array);
            }
        }
        
        if($_POST[type] == "update") {
            $query = "UPDATE users SET token = '$_POST[token]' WHERE uid = $_POST[uid]";
            mysql_query($query) or die ('error: '. mysql_error());
            $array = array("uid" => $_POST[uid], "token" => $_POST[token]);
            echo json_encode($array);
        }
    }
    
    //LOG EVENTS
    if($_POST["request"] == "logAction") {
        $now = time();
        $deps = explode("-", $_POST[departments]);
        $score = 0;
        if($_POST[score]) {
            $score = $_POST[score];
        }
        $query = "INSERT INTO userlog (uid, timestamp, eventid, movieid, dilemmas, locations, plotinfo, recipes, shopping, superfan, trivia, score) VALUES ($_POST[uid], $now, $_POST[eventid], $_POST[movieid], $deps[0], $deps[1], $deps[2], $deps[3], $deps[4], $deps[5], $deps[6], $score)";
        mysql_query($query) or die ('error: '. mysql_error());
    }
    
    //SAVE QUOTES/RECIPES AVERAGE RATINGS
    if($_POST["request"] == "saveRating") {
        $query = "SELECT * FROM userratings WHERE movieid = $_POST[movieid] AND itemid = $_POST[itemid] AND qtype = $_POST[type]";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        $count = mysql_num_rows($result);
        if($count > 0) {
            $row = mysql_fetch_array($result);
            $query = "UPDATE userratings SET rating = (rating * count + $_POST[rating]) / (count + 1), count = count + 1 WHERE iid = $row[iid]";
            mysql_query($query) or die ('error: '. mysql_error());
        } else {
            $query = "INSERT INTO userratings (qtype, movieid, itemid, rating, count) VALUES ($_POST[type], $_POST[movieid], $_POST[itemid], $_POST[rating], 1)";
            mysql_query($query) or die ('error: '. mysql_error());
        }
    }
    //SAVE TRIVIA/DILEMMAS USER CHOICE
    if($_POST["request"] == "saveSelection") {
        $query = "SELECT * FROM useraverages WHERE movieid = $_POST[movieid] AND itemid = $_POST[itemid] AND qtype = $_POST[type]";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        $count = mysql_num_rows($result);
        if($count > 0) {
            $row = mysql_fetch_array($result);
            $column = "choice". $_POST[choice];
            $query = "UPDATE useraverages SET $column = $column + 1 WHERE iid = $row[iid]";
            mysql_query($query) or die ('error: '. mysql_error());
        } else {
            $column = "choice". $_POST[choice];
            $query = "INSERT INTO useraverages (qtype, movieid, itemid, $column) VALUES ($_POST[type], $_POST[movieid], $_POST[itemid], 1)";
            mysql_query($query) or die ('error: '. mysql_error());
        }
    }
    
    //GET ALL MAP AVERAGE RATINGS
    if($_POST["request"] == "getAverages") {
        $query = "SELECT * FROM userratings WHERE movieid = $_POST[movieid] ORDER BY qtype";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        
        $output = array();
        $quotes = array("blank"=>"0");
        $recipes = array("blank"=>"0");
        $trivia = array("blank"=>"0");
        $dilemmas = array("blank"=>"0");
        while($row = mysql_fetch_array($result)) {
            if($row[qtype] == 1) {
                $itemid = $row[itemid];
                $quotes[$itemid] = number_format($row[rating], 1, '.', '');
            }
            if($row[qtype] == 2) {
                $itemid = $row[itemid];
                $recipes[$itemid] = number_format($row[rating], 1, '.', '');
            }
        }
        $output['quotes'] = $quotes;
        $output['recipes'] = $recipes;
        
        $query = "SELECT * FROM useraverages WHERE movieid = $_POST[movieid] ORDER BY qtype";
        $result = mysql_query($query) or die ('error: '. mysql_error());
        while($row = mysql_fetch_array($result)) {
            $data = array();
            if($row[qtype] == 1) {
                $total = $row[choice0] + $row[choice1] + $row[choice2] + $row[choice3];
                $data['0'] = number_format($row[choice0] * 100 / $total, 0, '.', '') ."%";
                $data['1'] = number_format($row[choice1] * 100 / $total, 0, '.', '') ."%";
                $data['2'] = number_format($row[choice2] * 100 / $total, 0, '.', '') ."%";
                $data['3'] = number_format($row[choice3] * 100 / $total, 0, '.', '') ."%";
                $itemid = $row[itemid];
                $trivia[$itemid] = $data;
            }
            if($row[qtype] == 2) {
                $total1 = $row[choice0] + $row[choice1];
                $total2 = $row[choice2] + $row[choice3];
                $data['0'] = number_format($row[choice0] * 100 / $total1, 0, '.', '') ."%";
                $data['1'] = number_format($row[choice1] * 100 / $total1, 0, '.', '') ."%";
                $data['2'] = number_format($row[choice2] * 100 / $total2, 0, '.', '') ."%";
                $data['3'] = number_format($row[choice3] * 100 / $total2, 0, '.', '') ."%";
                $itemid = $row[itemid];
                $dilemmas[$itemid] = $data;
            }
        }
        $output['trivia'] = $trivia;
        $output['dilemmas'] = $dilemmas;
        
        echo json_encode($output);
    }
    
    
    
    mysql_close($dbh);
?>