<?php
include('includes/config.php');

$dbfile = '../data-tv/'. $_GET[movieid] .'/'. $_GET[movieid] .'.s3db';
$dbname = 'sqlite:'. $dbfile;
$dbh = new PDO($dbname);


$query = "SELECT * FROM movies WHERE id = ". $_GET[movieid];
$stmt = $mysql->query($query);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

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
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function() { 
    $("table").tablesorter({ 
        sortList: [[0,0]] 
    });
	
	$(".playorder").change(function() {
		var questionid = $(this).attr('id');
		var neworder = $(this).val();
		
		var url = "ajax/update-order.php";
		var params = {
			questionid: questionid,
			neworder: neworder,
			movieid: <?php echo $_GET[movieid];?>
		};
		$.ajax({
			type: 'POST',
			url: url,
			data: params,
			success: function(res) {
				location.reload(true);
			},
			error: function() { alert('Ooops... Something went wrong!'); }			
		});
	});
});
function verifyRandomizer() {
	var result = confirm("Are you absolutely posetively sure you want to randomize the order?");
	if(result) {
		return true;
	}
	return false;
}
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <a href="dashboard.php">Dashboard</a> / <?php echo $movie[title];?> MovieMaster
    <div class="grad1 rounded module">
    
<?php
$query = "SELECT COUNT(*) FROM TblGameMasterLU WHERE 1";
$stmt = $dbh->query($query);
$questionIncludedCount = $stmt->fetchColumn();
?>
    <div style="float:left;"><span style="font-weight:bold;"><?php echo $questionIncludedCount;?> Questions</span></div>
    <div style="float:right; padding: 0 10px 4px 0;">
        <form action="act-moviemaster.php?movieid=<?php echo $_GET[movieid];?>" method="post">
            <div>
                <input type="hidden" name="new" value="1">
                <select name="FK_GameTypeID">
                    <option value="1"<?php if($_SESSION[FK_GameTypeID] == 1) echo ' selected="true"';?>>Dilemmas</option>
                    <option value="4"<?php if($_SESSION[FK_GameTypeID] == 4) echo ' selected="true"';?>>Locations</option>
                    <option value="5"<?php if($_SESSION[FK_GameTypeID] == 5) echo ' selected="true"';?>>Music</option>
                    <option value="6"<?php if($_SESSION[FK_GameTypeID] == 6) echo ' selected="true"';?>>Plot Info</option>
                    <option value="7"<?php if($_SESSION[FK_GameTypeID] == 7) echo ' selected="true"';?>>Quotes</option>
                    <option value="8"<?php if($_SESSION[FK_GameTypeID] == 8) echo ' selected="true"';?>>Recipes</option>
                    <option value="9"<?php if($_SESSION[FK_GameTypeID] == 9) echo ' selected="true"';?>>Shopping</option>
                    <option value="10"<?php if($_SESSION[FK_GameTypeID] == 10) echo ' selected="true"';?>>Superfan</option>
                    <option value="11"<?php if($_SESSION[FK_GameTypeID] == 11) echo ' selected="true"';?>>Trivia</option>
                    <option value="2"<?php if($_SESSION[FK_GameTypeID] == 2) echo ' selected="true"';?>>Vehicles</option>
                    <option value="3"<?php if($_SESSION[FK_GameTypeID] == 3) echo ' selected="true"';?>>Weapons</option>
                    <option value="12"<?php if($_SESSION[FK_GameTypeID] == 12) echo ' selected="true"';?>>Who</option>
                </select>
                <input type="submit" value="New Question »" class="rounded">
            </div>
        </form>
    </div>

	<form action="dashboard-moviemaster.php?movieid=<?php echo $_GET[movieid];?>" method="post">
    <table class="tablesorter" border="0" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="60">Order</th>
                <th width="100">Departament</th>
                <th width="800">Question</th>
                <th width="400">Answer</th>
                <th width="50"></th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php

//OUTER GET ALL QUESTIONS LOOP
$query = "SELECT * FROM TblGameMasterLU LEFT JOIN TblGameMasterSeg ON TblGameMasterLU.PlayOrder = TblGameMasterSeg.FK_GameMasterPlayID WHERE IsCorrect = 1 ORDER BY PlayOrder";


$previousdept = 0;
$questions = array();
foreach ($dbh->query($query) as $question) {
	$questions[] = $question;
}
			
$imagedir = '../data-tv/'. $_GET[movieid] .'/';
			
$allgood = true;
foreach ($questions as $question) {		
	$answer = "";
	$questionwarning = '';
	if($question[FK_GameTypeID] == 1) {
		$query = "SELECT PerformerName, CharacterName FROM TblWhoLU WHERE WhoLUID = $question[Answer]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$answer = $row[CharacterName];
		
		$query = "SELECT Recap, FMR, SMR FROM TblDilemmas WHERE DilemmaID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$question[Question]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[FMR]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[SMR]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Recap]) $questionwarning = ' style="background-color: #ff0000;"';
	}
	if($question[FK_GameTypeID] == 2) {
		$answer = $question[Answer];
		
		$query = "SELECT IdsLUName, PrimaryLink FROM TblIdsLU WHERE IdsLUID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$question[Question]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[IdsLUName]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[PrimaryLink]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame2] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		
		$query = "SELECT PK_GameMasterSeg, FK_GameMasterPlayID, AnswerID, Answer, IsCorrect FROM TblGameMasterSeg WHERE FK_GameMasterPlayID = $question[PlayOrder]";
		foreach ($dbh->query($query) as $row) {
			if(strlen($row[Answer]) == 0) $questionwarning = ' style="background-color: #ff0000;"';
		}
	}
	if($question[FK_GameTypeID] == 3) {
		$answer = $question[Answer];
		
		$query = "SELECT IdsLUName, PrimaryLink FROM TblIdsLU WHERE IdsLUID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$question[Question]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[IdsLUName]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[PrimaryLink]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame2] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		
		$query = "SELECT PK_GameMasterSeg, FK_GameMasterPlayID, AnswerID, Answer, IsCorrect FROM TblGameMasterSeg WHERE FK_GameMasterPlayID = $question[PlayOrder]";
		foreach ($dbh->query($query) as $row) {
			if(strlen($row[Answer]) == 0) $questionwarning = ' style="background-color: #ff0000;"';
		}
	}
	if($question[FK_GameTypeID] == 4) {
		$answer = $question[Answer];
		
		$query = "SELECT WriteUp, MapLink, Actual, Depicted, SnapShotFrame FROM TblLocations WHERE LocationsID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$question[Question]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[WriteUp]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[MapLink]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Actual]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Depicted]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		
		$query = "SELECT PK_GameMasterSeg, FK_GameMasterPlayID, AnswerID, Answer, IsCorrect FROM TblGameMasterSeg WHERE FK_GameMasterPlayID = $question[PlayOrder]";
		foreach ($dbh->query($query) as $row) {
			if(strlen($row[Answer]) == 0) $questionwarning = ' style="background-color: #ff0000;"';
		}
	}
	if($question[FK_GameTypeID] == 5) {
		$query = "SELECT MusicID, Song, Album, Artist, SongYear, AlbumCover FROM TblMusic WHERE MusicID = ". $question[LinkID];
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$question[Question] = "In which of these scenes is ". $row[Song] ." played?";
		$answer = "SnapShot Frame: ". $question[Answer];
		
		if(!$row[Song]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Album]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Artist]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[SongYear]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $row[AlbumCover] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
	}
	if($question[FK_GameTypeID] == 6) {
		$answer = $question[Answer];
		
		$query = "SELECT WriteUp FROM TblWhy WHERE WhyID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$question[Question]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[WriteUp]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame2] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		
		$query = "SELECT PK_GameMasterSeg, FK_GameMasterPlayID, AnswerID, Answer, IsCorrect FROM TblGameMasterSeg WHERE FK_GameMasterPlayID = $question[PlayOrder]";
		foreach ($dbh->query($query) as $row) {
			if(strlen($row[Answer]) == 0) $questionwarning = ' style="background-color: #ff0000;"';
		}
	}
	if($question[FK_GameTypeID] == 7) {
		$query = "SELECT PerformerName, CharacterName FROM TblWhoLU WHERE WhoLUID = $question[Answer]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$answer = $row[PerformerName] .' as '. $row[CharacterName];
		$question[Question] = 'Who is '. $row[CharacterName];

		$query = "SELECT LineTop, LineBottom, SnapShotFrame FROM TblQuote WHERE QuoteID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
		$quote = trim($row[LineTop]) .' '. trim($row[LineBottom]);
		$question[Question] = 'Who said: "'. trim($quote) .'"';
		
		if(strlen($quote) == 0) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
	}
	if($question[FK_GameTypeID] == 8) {
		$query = "SELECT RecipeName, RecipeType, SnapShotFrame, Quote, Inspiration, Ingredients, Directions FROM TblRecipe WHERE RecipeID = ". $question[LinkID];
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
		if($row[RecipeType] == 1) $type = "Drink";
		if($row[RecipeType] == 2) $type = "Appetizer";
		if($row[RecipeType] == 3) $type = "Main Course";
		if($row[RecipeType] == 4) $type = "Side Dish";
		if($row[RecipeType] == 5) $type = "Dessert";
	
	
		$question[Question] = 'Which '. $type .' did our culinary team create for the movie?';
		$answer = $row[RecipeName];
		
		if(!$row[RecipeName]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Quote]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Inspiration]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Ingredients]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Directions]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
	}
	if($question[FK_GameTypeID] == 9) {
		$answer = $question[Answer];
		
		$query = "SELECT ShoppingItemName, BuyLinkActual, ImageFileNameActual, BuyLinkSubstitute, ImageFileNameSubstitute FROM TblShoppingLU WHERE ShoppingLUID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$question[Question]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[ShoppingItemName]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[BuyLinkActual] && !$row[BuyLinkSubstitute]) $questionwarning = ' style="background-color: #ff0000;"';
		if($row[BuyLinkActual]) {
			if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $row[ImageFileNameActual] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		}
		if($row[BuyLinkSubstitute]) {
			if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/'. $row[ImageFileNameSubstitute] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		}
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		
		$query = "SELECT PK_GameMasterSeg, FK_GameMasterPlayID, AnswerID, Answer, IsCorrect FROM TblGameMasterSeg WHERE FK_GameMasterPlayID = $question[PlayOrder]";
		foreach ($dbh->query($query) as $row) {
			if(strlen($row[Answer]) == 0) $questionwarning = ' style="background-color: #ff0000;"';
		}
	}
	if($question[FK_GameTypeID] == 10) {
		$answer = $question[Answer];
		
		$query = "SELECT WriteUp, SnapShotFrame FROM TblSuperFan WHERE SuperFanID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$question[Question]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[WriteUp]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		
		$query = "SELECT PK_GameMasterSeg, FK_GameMasterPlayID, AnswerID, Answer, IsCorrect FROM TblGameMasterSeg WHERE FK_GameMasterPlayID = $question[PlayOrder]";
		foreach ($dbh->query($query) as $row) {
			if(strlen($row[Answer]) == 0) $questionwarning = ' style="background-color: #ff0000;"';
		}
	}
	if($question[FK_GameTypeID] == 11) {
		$answer = $question[Answer];
		
		$query = "SELECT Fact, SnapShotFrame FROM TblTriviaLU WHERE TriviaID = $question[LinkID]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$question[Question]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!$row[Fact]) $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $question[SnapShotFrame2] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		if(!file_exists('../data-tv/'. $_GET[movieid] .'/AppleTVAssets/frame_'. $row[SnapShotFrame] .'.jpg'))  $questionwarning = ' style="background-color: #ff0000;"';
		
		$query = "SELECT PK_GameMasterSeg, FK_GameMasterPlayID, AnswerID, Answer, IsCorrect FROM TblGameMasterSeg WHERE FK_GameMasterPlayID = $question[PlayOrder]";
		foreach ($dbh->query($query) as $row) {
			if(strlen($row[Answer]) == 0) $questionwarning = ' style="background-color: #ff0000;"';
		}
	}
	if($question[FK_GameTypeID] == 12) {
		$query = "SELECT PerformerName, CharacterName FROM TblWhoLU WHERE WhoLUID = $question[Answer]";
		$stmt = $dbh->query($query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$answer = $row[PerformerName] .' as '. $row[CharacterName];
		$question[Question] = 'Who is '. $row[CharacterName];
	}
	
	
	$orderwarning = '';
	if((($question[FK_GameTypeID] == 1) || ($question[FK_GameTypeID] == 5) || ($question[FK_GameTypeID] == 7) || ($question[FK_GameTypeID] == 12)) && (($previousdept == 1) || ($previousdept == 5) || ($previousdept == 7) || ($previousdept == 12))) {
		$orderwarning = ' style="background-color: #ff0000;"';
	}
	$previousdept = $question[FK_GameTypeID];

	if($questionwarning) $allgood = false;
	
    echo '<tr><td'. $orderwarning .'>'. getOrderPulldown($question[PlayOrder], $question[PK_GameMasterLU]);
	echo '</td><td>'. getDepartamentName($question[FK_GameTypeID]) .'</td><td'. $questionwarning .'>'. $question[Question] .'</td><td>'. $answer .'</td><td><a href="act-moviemaster.php?movieid='. $_GET[movieid] .'&questionid='. $question[PK_GameMasterLU] .'">Edit</a></td></tr>';
}
			
if($allgood && (count($questions) == 30)) {
	$query = "UPDATE TblMovie SET MovieApiID = 1";
	$dbh->exec($query);
} else {
	$query = "UPDATE TblMovie SET MovieApiID = 0";
	$dbh->exec($query);
}
?>
		</tbody>
    </table>
        
    <div style="padding: 20px 0 0 0;"></div>
    </form>

    <div class="clearfix"></div>
    
    
	</div>    
</div>
</body>
</html>