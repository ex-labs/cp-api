<?php
$dbh = mysql_connect ("64.91.249.141", "custompl_poptriv", "Cust0mPl@y!") or die ('Cannot connect db: '. mysql_error());
mysql_select_db ("custompl_971786_db") or die('Cannot select db: ' . mysql_error());


    
if($_POST["request"] == "allMovies") {
    $query = "SELECT id, version, active, excludetv, popcornactive, popcornbonus FROM movies WHERE 1";
    $result = mysql_query($query) or die ('error: '. mysql_error());
    $movies = array();
    while($row = mysql_fetch_array($result)) {
        $movie = array();
        $movie['id'] = $row['id'];
        $movie['version'] = $row['version'];
        $movie['active'] = $row['active'];
        $movie['excludetv'] = $row['excludetv'];
        $movie['popcornactive'] = $row['popcornactive'];
        $movie['popcornbonus'] = $row['popcornbonus'];

        $movies[$row['id']] = $movie;
    }
	echo json_encode($movies);
}

if($_POST["request"] == "uploadZip") {
	print_r($_POST);
	$targetdir = '../data-tv/'. $_POST[movieid];
	if(!file_exists($targetdir)) {
		mkdir($targetdir);
	}
	$target = $targetdir .'/'. $_POST[movieid] .'.zip';
	move_uploaded_file($_FILES['file_contents']['tmp_name'], $target);
	
	print_r($_FILES);
}

mysql_close($dbh);
?>