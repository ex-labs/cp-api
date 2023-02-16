<?php
include('/includes/config.php');

//ini_set('display_errors',1);
//error_reporting(E_ALL);

$mssql = new PDO("sqlsrv:Server=$server;Database=$database", $username, $password);
$mssqlmaps = new PDO("sqlsrv:Server=$server;Database=$mapperDB", $username, $password);

$query = "SELECT [PK_VidID], [VidSetParentID], [VidResolution], vid.VidTitle as VidTitle, rel.VidTitle as VidDirectory FROM [OperationsDB].[dbo].[tblVideos] vid LEFT JOIN [OperationsDB].[dbo].[tblVidReleaseInfo] rel ON vid.PK_VidID = rel.FK_VidID WHERE PK_VidID = ". $_GET[movieid];

$statement = $mssql->query($query);
$movie = $statement->fetch(PDO::FETCH_ASSOC);

if(substr($movie[VidResolution], 0, 4) == "1280") {
	$smallvid = true;
} else {
	$smallvid = false;
}

echo '<pre>';

//UPDATE AND EXIT IF SAVING
if($_POST[action] == 1) {
	$file = 'G:\\'. $_POST[maptype] .'Maps\\Tablet_Blank.s3db';
	$dircheck = 'G:\\'. $_POST[maptype] .'Maps\\'. $_GET[movieid];
	if(!file_exists($dircheck)) {
		mkdir($dircheck);
	}
	
	$dbfile = 'G:\\'. $_POST[maptype] .'Maps\\'. $_GET[movieid] .'\\'. $_GET[movieid] .'.s3db';
	copy($file, $dbfile);
	
	$dbname = 'sqlite:'. $dbfile;
	$dbh = new PDO($dbname);
	
	
	$errors = "";
	
	
	//GENERATE WHO FIRST, WILL NEED TO CKECK FOR DATA INTEGRITY HERE...
	//TblWhoLU
	$query = "SELECT PK_Actors, PerID, ChaID FROM TblActors LEFT JOIN TblWhoChar ON TblActors.PK_Actors = TblWhoChar.FK_ActorID AND TblActors.FK_VideoID = TblWhoChar.FK_VideoID LEFT JOIN TblWho ON TblWhoChar.FK_WhoSegmentID = TblWho.PK_ShoppingID AND TblWhoChar.FK_VideoID = TblWho.FK_VideoID WHERE TblActors.FK_VideoID = $_GET[movieid] AND BeginFrame > 0 GROUP BY PK_Actors, PerID, ChaID ORDER BY PK_Actors";
		
	$subquery = "INSERT INTO TblWhoLU (WhoLUID, PerID, ChaID, PerformerName, CharacterName, SnapShotFrame, BioLink) VALUES (?,?,?,?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		$msquery = "SELECT PK_VidCharacterID, a.FK_PerID, a.CharID,REPLACE(REPLACE(REPLACE('# ' + tblPerformers.PerTitle + ' ' + tblPerformers.PerFirstName + ' ' + tblPerformers.PerMidName + ' ' + tblPerformers.PerLastName + ' ' + isnull(tblPerformers.PerSuffix,'') + ' #','  ',' '),'# ',''),' #','') as 'PerformerName', tblPerformers.WebLink, a.VidCharName, a.VidCharFrameRefNum FROM [OperationsDB].[dbo].[tblVidCharacters] a
	  JOIN [OperationsDB].[dbo].[tblPerformers] on tblPerformers.PK_PerID = a.FK_PerID
	  WHERE a.PK_VidCharacterID = ". $row[PK_Actors];
		$msstm = $mssql->query($msquery);
		$whorow = $msstm->fetch(PDO::FETCH_ASSOC);
				
		$biolink = $whorow[WebLink];
		if (strpos($biolink, 'imdb.com') !== false) {
			$data = explode("/", $biolink);
			$biolink = $data[4];
		}
		//CEHCK FOR SAME FIRST/LAST NAME
		$verify = explode(" ",$whorow[PerformerName]);
		if((count($verify) == 2) && ($verify[0] == $verify[1])) $whorow[PerformerName] = $verify[0];
				
		$array = array("$row[PK_Actors]","$row[PerID]","$row[ChaID]","$whorow[PerformerName]","$whorow[VidCharName]","$whorow[VidCharFrameRefNum]","$biolink");
		$stm->execute($array);
	}
		
	//TblWhoSeg
	$query = "SELECT FK_ActorID, BeginFrame FROM TblWho LEFT JOIN TblWhoChar ON TblWho.PK_ShoppingID = TblWhoChar.FK_WhoSegmentID AND TblWho.FK_VideoID = TblWhoChar.FK_VideoID WHERE TblWho.FK_VideoID = $_GET[movieid] ORDER BY PK_WhoChar";
		
	$subquery = "INSERT INTO TblWhoSeg (FK_WhoLUID, Notify) VALUES (?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		$array = array("$row[FK_ActorID]","$row[BeginFrame]");
		$stm->execute($array);
	}
	
	
		
	//TblDilMoralReq
	$query = "SELECT [PK_MoralReq],[MoralReqName] FROM [OperationsDB].[dbo].[DilMoralReqs] ORDER BY PK_MoralReq";
	
	$subquery = "INSERT INTO TblDilMoralReq (DilMoralReqID, DilMoralReqName) VALUES (?, ?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssql->query($query) as $row) {
		$array = array("$row[PK_MoralReq]", "$row[MoralReqName]");
		$stm->execute($array);
	}
	
	//TblDilRelationship
	$query = "SELECT [PK_Relationship],[RelationshipName] FROM [OperationsDB].[dbo].[DilRelationships] ORDER BY PK_Relationship";
	
	$subquery = "INSERT INTO TblDilRelationship (DilRelationshipID, DilRelationshipName) VALUES (?, ?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssql->query($query) as $row) {
		$array = array("$row[PK_Relationship]", "$row[RelationshipName]");
		$stm->execute($array);
	}
	
	//TblDilemmas
	$query = "SELECT PK_Dilemmas, DilemmaName, SnapShotFrame, Relationship, FMR, SMR, WriteUp, RecapText, Notify FROM TblDilemmas WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_Dilemmas";
		
	$subquery = "INSERT INTO TblDilemmas (DilemmaID, DilemmaName, SnapShotFrame, Relationship, FMR, SMR, Recap, Question, Notify, FMRPercent, SMRPercent, QuestionYesPercent, QuestionNoPercent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		$array = array("$row[PK_Dilemmas]","$row[DilemmaName]","$row[SnapShotFrame]","$row[Relationship]","$row[FMR]","$row[SMR]","$row[RecapText]","$row[WriteUp]","$row[Notify]","50","50","50","50");
		$stm->execute($array);
	}
	
	
	//TblGameMusicTypeLU -- NEEDED FOR CP MAP CREATION
	$subquery = "INSERT INTO TblGameMusicTypeLU (GameMusicTypeID, GameMusicTypeName) VALUES (?,?)";
	$stm = $dbh->prepare($subquery);
	$array = array("1", "Music - Scene Question");
	$stm->execute($array);
	$array = array("2", "Music - Character Question");
	$stm->execute($array);
	$array = array("3", "Music - Text Question");
	$stm->execute($array);
	
	//TblGameTypeLU
	$subquery = "INSERT INTO TblGameTypeLU (GameTypeID, GameTypeName) VALUES (?,?)";
	$stm = $dbh->prepare($subquery);
	$array = array("1", "Dilemmas");
	$stm->execute($array);
	$array = array("2", "Vehicles");
	$stm->execute($array);
	$array = array("3", "Weapons");
	$stm->execute($array);
	$array = array("4", "Locations");
	$stm->execute($array);
	$array = array("5", "Music");
	$stm->execute($array);
	$array = array("6", "Plot Info");
	$stm->execute($array);
	$array = array("7", "Quotes");
	$stm->execute($array);
	$array = array("8", "Recipes");
	$stm->execute($array);
	$array = array("9", "Shopping");
	$stm->execute($array);
	$array = array("10", "SuperFan");
	$stm->execute($array);
	$array = array("11", "Trivia");
	$stm->execute($array);
	$array = array("12", "Who");
	$stm->execute($array);
	
	//TblIdsCategory
	$subquery = "INSERT INTO TblIdsCategory (IdsCategoryID, IdsCategoryName) VALUES (?,?)";
	$stm = $dbh->prepare($subquery);
	$array = array("1", "Vehicles");
	$stm->execute($array);
	$array = array("2", "Weapons");
	$stm->execute($array);
	
	//TblIdsLU
	$query = "SELECT PK_MusLUID,ItemName,SnapShotFrame,FK_CategoryID,BuyLinkWiki,BuyLinkVendor FROM TblIDSLU WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_MusLUID";
		
	$subquery = "INSERT INTO TblIdsLU (IdsLUID, IdsLUName, SnapShotFrame, FK_IdsCategoryID ,PrimaryLink, SecondaryLink) VALUES (?, ?, ?, ?, ?, ?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		if(($row[FK_CategoryID] == 1) || ($row[FK_CategoryID] == 2) || ($row[FK_CategoryID] == 3) || ($row[FK_CategoryID] == 7) || ($row[FK_CategoryID] == 10)) {
			$idstype = 1;
		} else {
			$idstype = 2;
		}
			
		$array = array("$row[PK_MusLUID]","$row[ItemName]","$row[SnapShotFrame]","$idstype","$row[BuyLinkWiki]","$row[BuyLinkVendor]");
		$stm->execute($array);
	}
		
	//TblIdsSeg
	$query = "SELECT FK_Mus,EndTime FROM TblIdsLU LEFT JOIN TblIdsSeg ON TblIdsLU.PK_MusLUID = TblIdsSeg.FK_Mus AND TblIdsLU.FK_VideoID = TblIdsSeg.FK_VideoID WHERE TblIdsLU.Included != 0 AND TblIdsSeg.Included != 0 AND TblIdsLU.FK_VideoID = $_GET[movieid] ORDER BY PK_MusSeg";
		
	$subquery = "INSERT INTO TblIdsSeg (FK_IdsLUID,Notify) VALUES (?, ?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		$array = array("$row[FK_Mus]","$row[EndTime]");
		$stm->execute($array);
	}

	
	//TblLocations
	$query = "SELECT PK_Where,Title,Notify,SnapShotFrame,WriteUp,LinkMap,LinkInterest,Depicted,Actual FROM TblWhere WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_Where";
		
	$subquery = "INSERT INTO TblLocations (LocationsID,Title,SnapShotFrame,WriteUp,MapLink,Latitude,Longitude,StreetLatitude,StreetLongitude,Zoom,CameraZoom,Heading,Pitch,MapImage,Type,InterestLink,Depicted,Actual,Notify) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		$mapurl = $row[LinkMap];
			
		$lat = 0;
		$lon = 0;
		$lat_str = 0;
		$lon_str = 0;
		$zoom = 19;
		$zoom_str = 2;
		$heading = 0;
		$pitch = 0;
		$maptype = "";
			
			
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
					} //https://www.google.com/maps/@28.0107703,-82.468822,3a,90y,341.47h,84.02t/data=!3m6!1e1!3m4!1s6qlSwrSk-pTPT-hhbtoDzw!2e0!7i13312!8i6656?hl=en
				}
			}
		}
			
		if(($lat == 0) || ($lon == 0)) {
			$errors .= "Locations ID: ". $row[PK_Where] ." error in url: ". $row[LinkMap] ."\n";
		}
		
		$array = array("$row[PK_Where]","$row[Title]","$row[SnapShotFrame]","$row[WriteUp]","$row[LinkMap]","$lat","$lon","$lat_str","$lon_str","$zoom","$zoom_str","$heading","$pitch","","$maptype","$row[LinkInterest]","$row[Depicted]","$row[Actual]","$row[Notify]");
		$stm->execute($array);
	}
	
	
	//TblMovie
	$query = "SELECT PK_VidID, VidTitle, VidYear, VidDirector FROM [OperationsDB].[dbo].[tblVideos] WHERE PK_VidID = ". $_GET[movieid];

	$statement = $mssql->query($query);
	$row = $statement->fetch(PDO::FETCH_ASSOC);
	$subquery = "INSERT INTO TblMovie (MovieID, MovieTitle, MovieYear, DirectorName) VALUES (?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	$vidTitle = str_replace(" [AVI]", "", $row[VidTitle]);
	$array = array("$row[PK_VidID]","$vidTitle","$row[VidYear]","$row[VidDirector]");
	$stm->execute($array);
	
	
	
	//TblMusic
	$query = "SELECT PK_MusLUID, SongID, SubSongID, Notify, SnapShotFrame, Substitute FROM TblMusLU WHERE Included != 0 AND Unknown = 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_MusLUID";
		
	$subquery = "INSERT INTO TblMusic (MusicID, Song, Artist, Album, AlbumCover, SongYear, FK_MusicCategoryID, SubSong, SubArtist, SubAlbum, SubAlbumCover, SubSongYear, FK_SubMusicCategoryID, Substitute, Notify, SnapShotFrame) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$stm = $dbh->prepare($subquery);
		
	foreach ($mssqlmaps->query($query) as $row) {
		$haveData = false;
		$msrow = array();
		$msrow2 = array();
		$albumArtwork = "";
		$subAlbumArtwork = "";
		if($row[SongID] > 0) {
			$haveData = true;
			$msquery = "SELECT [SongYear], [TitleName], [AlbumName], [PK_MusAlbum], [ArtistName], [FK_MusCategoryID]
						FROM [OperationsDB].[dbo].[tblMUS_SRelationshipsLU] relations
						LEFT JOIN [OperationsDB].[dbo].[tblMUS_STitleLU] titles
						ON relations.FK_SongTitleID = titles.TitleLUID
						LEFT JOIN [OperationsDB].[dbo].[tblMUS_SAlbumLU] albums
						ON relations.FK_AlbumID = albums.PK_MusAlbum
						LEFT JOIN [OperationsDB].[dbo].[tblMUS_SArtistLU] artists
						ON relations.FK_ArtistID = artists.ArtistLUID
						LEFT JOIN [OperationsDB].[dbo].[tblMUS_SCategoryLU] categories
						ON relations.FK_MusCategoryID = categories.CategoryLUID
						WHERE PK_MusRelationshipsLU = ". $row[SongID];
			$statement = $mssql->query($msquery);
			$msrow = $statement->fetch(PDO::FETCH_ASSOC);
			$albumArtwork = "ALB_". str_pad($msrow[PK_MusAlbum],5,"0",STR_PAD_LEFT);
		}
		if($row[SubSongID] > 0) {
			$haveData = true;
			$msquery = "SELECT [SongYear], [TitleName], [AlbumName], [PK_MusAlbum], [ArtistName], [FK_MusCategoryID]
						FROM [OperationsDB].[dbo].[tblMUS_SRelationshipsLU] relations
						LEFT JOIN [OperationsDB].[dbo].[tblMUS_STitleLU] titles
						ON relations.FK_SongTitleID = titles.TitleLUID
						LEFT JOIN [OperationsDB].[dbo].[tblMUS_SAlbumLU] albums
						ON relations.FK_AlbumID = albums.PK_MusAlbum
						LEFT JOIN [OperationsDB].[dbo].[tblMUS_SArtistLU] artists
						ON relations.FK_ArtistID = artists.ArtistLUID
						LEFT JOIN [OperationsDB].[dbo].[tblMUS_SCategoryLU] categories
						ON relations.FK_MusCategoryID = categories.CategoryLUID
						WHERE PK_MusRelationshipsLU = ". $row[SubSongID];
			$statement = $mssql->query($msquery);
			$msrow2 = $statement->fetch(PDO::FETCH_ASSOC);
			$subAlbumArtwork = "ALB_". str_pad($msrow2[PK_MusAlbum],5,"0",STR_PAD_LEFT);
		} 
		if($haveData) {
			$array = array("$row[PK_MusLUID]","$msrow[TitleName]","$msrow[ArtistName]","$msrow[AlbumName]","$albumArtwork","$msrow[SongYear]","$msrow[FK_MusCategoryID]","$msrow2[TitleName]","$msrow2[ArtistName]","$msrow2[AlbumName]","$subAlbumArtwork","$msrow2[SongYear]","$msrow2[FK_MusCategoryID]","$row[Substitute]","$row[Notify]","$row[SnapShotFrame]");
			$stm->execute($array);
		}
	}
	
	
	//TblMusicCategory
	$query = "SELECT CategoryLUID, CategoryName FROM [OperationsDB].[dbo].[tblMUS_SCategoryLU]";

	$subquery = "INSERT INTO TblMusicCategory (MusicCategoryID, MusicCategoryName) VALUES (?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssql->query($query) as $row) {
		$array = array("$row[CategoryLUID]", "$row[CategoryName]");
		$stm->execute($array);
	}
	
	//TblQuote
	$query = "SELECT PK_Quote, Notify, SnapShotFrame, fk_VidCharID, FontName, FontSize, TextTop, TextBottom FROM TblQuotes WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_Quote";
		
	$subquery = "INSERT INTO TblQuote (QuoteID, LineTop, LineBottom, Font, FontSize ,SnapShotFrame, FK_WhoLUID, Notify, QuoteRating) VALUES (?,?,?,?,?,?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		$lineTop = trim($row[TextTop]);
		$lineBtm = trim($row[TextBottom]);
		$font = "Coda";
		$array = array("$row[PK_Quote]","$lineTop","$lineBtm","$font","$row[FontSize]","$row[SnapShotFrame]","$row[fk_VidCharID]","$row[Notify]","3.5");
		$stm->execute($array);
		
		//CHECK FOR PRESENCE OF WHO LINK
		checkWhoLUEntries($row[fk_VidCharID], $mssql, $dbh);
	}
	
	
	//TblRecipe
	$query = "SELECT PK_Recipe, RecipeName, Notify, SnapShotFrame, RecipeType, PrepTime, CookTime, Servings, Quote, Inspiration, Ingredients, Directions, Notes FROM TblRecipes WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_Recipe";
		
	$subquery = "INSERT INTO TblRecipe (RecipeID, RecipeName, SnapShotFrame, RecipeType, Quote, Inspiration, PrepTime, CookTime, Servings, Ingredients, Directions, PhotoFile, Notify, RecipeRating) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$subquery2 = "INSERT INTO TblRecipeLinks (FK_RecipeID, Name, Link) VALUES (?,?,?)";
	$stm = $dbh->prepare($subquery);
	$stm2 = $dbh->prepare($subquery2);
	foreach ($mssqlmaps->query($query) as $row) {
		$photoFile = "REC". str_pad($_GET[movieid], 5, '0', STR_PAD_LEFT) ."_". str_pad($row[PK_Recipe], 3, '0', STR_PAD_LEFT) ."Ph.jpg";
		$array = array("$row[PK_Recipe]","$row[RecipeName]","$row[SnapShotFrame]","$row[RecipeType]","$row[Quote]","$row[Inspiration]","$row[PrepTime]","$row[CookTime]","$row[Servings]","$row[Ingredients]","$row[Directions]","$photoFile","$row[Notify]","3.5");
		$stm->execute($array);
		if(strlen($row[Notes]) > 0) {
			$links = explode("\n", $row[Notes]);
			foreach($links as $link) {
				$data = explode(" - ", $link);
				if((strlen($data[0]) > 0) && (strlen($data[1]) > 0)) {
					$array = array("$row[PK_Recipe]","$data[0]","$data[1]");
					$stm2->execute($array);
				}
			}
		}
	}
	
		
	//TblRecipeCategory
	$subquery = "INSERT INTO TblRecipeCategory (RecipeCategoryID, RecipeCategoryName) VALUES (?,?)";
	$stm = $dbh->prepare($subquery);
	$array = array("1", "Drink");
	$stm->execute($array);
	$array = array("2", "Appetizer");
	$stm->execute($array);
	$array = array("3", "Main Course");
	$stm->execute($array);
	$array = array("4", "Side Dish");
	$stm->execute($array);
	$array = array("5", "Dessert");
	$stm->execute($array);
	
	
	//TblShoopingMasterCategory
	$query = "SELECT PK_ShoppingMasterCategories_s_ID,ShoppingMasterCategoryID,ShoppingMasterCategoryName FROM [OperationsDB].[dbo].[tblShoppingMasterCategories_s]";

	$subquery = "INSERT INTO TblShoppingMasterCategory (ShoppingMasterCategoryID, ShoppingMasterCategoryName) VALUES (?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssql->query($query) as $row) {
		$array = array("$row[ShoppingMasterCategoryID]", "$row[ShoppingMasterCategoryName]");
		$stm->execute($array);
	}
	
	
	//TblShoopingCategory
	$query = "SELECT PK_ShopCatID,CatName,FK_ShoppingMasterCategoryID FROM [OperationsDB].[dbo].[tblShoppingCategories_s] WHERE Bad = 0";

	$subquery = "INSERT INTO TblShoppingCategory (FK_ShoppingMasterCategoryID, ShoppingCategoryID, ShoppingCategoryName) VALUES (?,?,?)";
	$stm = $dbh->prepare($subquery);
	$validcats = array();
	foreach ($mssql->query($query) as $row) {
		$categoryName = substr($row[CatName], 4);;
		$array = array("$row[FK_ShoppingMasterCategoryID]", "$row[PK_ShopCatID]", "$categoryName");
		$stm->execute($array);
		$validcats[] = $row[PK_ShopCatID];
	}
	
	
	//TblShoppingLU
	$query = "SELECT PK_ShoppingID, ItemName, SnapShotFrame, FK_CategoryID, StoreTitleActual, BuyLinkActual, ImageFileNameActual, StoreTitleSubstitute, BuyLinkSubstitute, ImageFileNameSubstitute, Sample, Unexpected, FK_CharacterID FROM TblShopping WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_ShoppingID";
		
	$subquery = "INSERT INTO TblShoppingLU (ShoppingLUID, ShoppingItemName, SnapShotFrame, FK_ShoppingCategoryID, StoreTitleActual, BuyLinkActual, ImageFileNameActual, StoreTitleSubstitute, BuyLinkSubstitute, ImageFileNameSubstitute, Reference, Unexpected, FK_WhoLUID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		if(in_array($row[FK_CategoryID], $validcats)) {
			$array = array("$row[PK_ShoppingID]","$row[ItemName]","$row[SnapShotFrame]","$row[FK_CategoryID]","$row[StoreTitleActual]","$row[BuyLinkActual]","$row[ImageFileNameActual]","$row[StoreTitleSubstitute]","$row[BuyLinkSubstitute]","$row[ImageFileNameSubstitute]","$row[Sample]","$row[Unexpected]","$row[FK_CharacterID]");
			$stm->execute($array);
			
			//CHECK FOR PRESENCE OF WHO LINK
			checkWhoLUEntries($row[FK_CharacterID], $mssql, $dbh);
		}
	}
		
	//TblShoppingSeg
	$query = "SELECT FK_Ads, TblAdsSeg.SnapshotFrame, EndTime, List, FK_CategoryID FROM TblShopping LEFT JOIN TblAdsSeg ON TblShopping.PK_ShoppingID = TblAdsSeg.FK_Ads AND TblShopping.FK_VideoID = TblAdsSeg.FK_VideoID WHERE TblShopping.Included != 0 AND TblAdsSeg.Included != 0 AND List != 0 AND TblShopping.FK_VideoID = $_GET[movieid] ORDER BY PK_AdsSeg";
		
	$subquery = "INSERT INTO TblShoppingSeg (FK_ShoppingLUID, SnapShotFrame, List, Notify) VALUES (?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		if(in_array($row[FK_CategoryID], $validcats)) {
			$array = array("$row[FK_Ads]","$row[SnapshotFrame]","1","$row[EndTime]");
			$stm->execute($array);
		}
	}
	
	
	
	
	
	//TblSuperFan
	$query = "SELECT PK_Dilemmas,DilemmaName,SnapShotFrame,WriteUp,SPFError,Fan,Notify FROM TblSuperFan WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_Dilemmas";
		
	$subquery = "INSERT INTO TblSuperFan (SuperFanID, SuperFanName, SnapShotFrame, WriteUp, Error, Fan, Notify) VALUES (?,?,?,?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		$fan = abs($row[Fan]);
		$err = abs($row[SPFError]);
		$array = array("$row[PK_Dilemmas]","$row[DilemmaName]","$row[SnapShotFrame]","$row[WriteUp]","$err","$fan","$row[Notify]");
		$stm->execute($array);
	}
	
	
	
	
	//TblTriviaLU
	$query = "SELECT COUNT(*) FROM TblTrivia WHERE Included != 0 AND FK_VideoID = $_GET[movieid]";
	$stmt = $mssqlmaps->query($query);
	$questionCount = $stmt->fetchColumn();
	
	$query = "SELECT PK_Trivia, TriviaTitle, List, Included, Notify, SnapShotFrame, WriteUp, Question, Poll FROM TblTrivia WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_Trivia";
		
	$subquery = "INSERT INTO TblTriviaLU (TriviaID, SnapShotFrame, Question, Fact, List, Poll, StandAlone, Notify, PlayOrder) VALUES (?,?,?,?,?,?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	$playorder = array();
	foreach ($mssqlmaps->query($query) as $row) {
		if(substr($row[WriteUp], 0, 2) == "@ ") {
			$standalone = 1;
			$row[WriteUp] = substr($row[WriteUp], 2, strlen($row[WriteUp]));
		} else {
			$standalone = 0;
		}
		$list = abs($row['List']);
		$poll = abs($row[Poll]);
		
		$random = mt_rand(1,$questionCount);
		while(in_array($random, $playorder)) {
			$random = mt_rand(1,$questionCount);
		}
		
		$playorder[] = $random;
		$row[Question] = trim($row[Question]);
		$row[WriteUp] = trim($row[WriteUp]);
		$array = array("$row[PK_Trivia]","$row[SnapShotFrame]","$row[Question]","$row[WriteUp]","$list","$poll","$standalone","$row[Notify]","$random");
		$stm->execute($array);
	}
		
		
	//TblTriviaSeg
	$query = "SELECT FK_Trivia, AnswerID, TA_text, IsCorrect, TriviaTitle, Included, Poll FROM TblTriAns LEFT JOIN TblTrivia ON TblTriAns.FK_Trivia = TblTrivia.PK_Trivia AND TblTriAns.FK_VideoID = TblTrivia.FK_VideoID WHERE LEN(LTRIM(RTRIM(CAST(TA_text AS CHAR)))) > 0 AND Included != 0 AND TblTrivia.FK_VideoID = $_GET[movieid] ORDER BY PK_TriAns";
		
	$subquery = "INSERT INTO TblTriviaSeg (FK_TriviaID, AnswerID, AnswerText, AnswerPercent, IsCorrect) VALUES (?,?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	$playorder = array();
	
	$fkTriviaID = 0;
	foreach ($mssqlmaps->query($query) as $row) {
		$isCorrect = abs($row[IsCorrect]);
		$row[TA_text] = trim($row[TA_text]);
		$array = array("$row[FK_Trivia]","$row[AnswerID]","$row[TA_text]","25","$isCorrect");
		$stm->execute($array);
		
		if($fkTriviaID != $row[FK_Trivia]) {
			//TRIVIA ERROR CHECKS
			$errquery = "SELECT COUNT(*) FROM TblTriAns WHERE FK_Trivia = $row[FK_Trivia] AND IsCorrect != 0 AND FK_VideoID = $_GET[movieid]";
			$errstmt = $mssqlmaps->query($errquery);
			$isCorrectCount = $errstmt->fetchColumn();
	
			if($row[Poll] != 0) {
				if($isCorrectCount > 0) {
					$errors .= "Trivia question #". $row[FK_Trivia] ." (". $row[TriviaTitle] .") is a poll, but has correct answer marked...";
				}
			} else {
				if($isCorrectCount == 0) {
					$errors .= "Trivia question #". $row[FK_Trivia] ." (". $row[TriviaTitle] .") does not have correct answer marked...";
				}
			}
			$fkTriviaID = $row[FK_Trivia];
		}
	}
	
	


	//TblWhy (Plot Info)
	$query = "SELECT PK_Dilemmas, DilemmaName, SnapShotFrame, WriteUp, Clue, Recap, Plot, Notify, Credit FROM TblPlotInfo WHERE Included != 0 AND FK_VideoID = $_GET[movieid] ORDER BY PK_Dilemmas";
		
	$subquery = "INSERT INTO TblWhy (WhyID, WhyName, SnapShotFrame, WriteUp, Clue, Credit, Recap, Plot, Notify) VALUES (?,?,?,?,?,?,?,?,?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssqlmaps->query($query) as $row) {
		$clue = abs($row[Clue]);
		$recap = abs($row[Recap]);
		$plot = abs($row[Plot]);
		$credit = abs($row[Credit]);
	
		$array = array("$row[PK_Dilemmas]","$row[DilemmaName]","$row[SnapShotFrame]","$row[WriteUp]","$clue","$credit","$recap","$plot","$row[Notify]");
		$stm->execute($array);
	}
	
	
	
	
	
	
	//TblGameMasterLU
	$query = "SELECT [FK_GameTypeID],[FK_WhoLUID],[NotifyLink],[SnapshotLink],[WhoLink],[SnapShotFrame],[SnapShotFrame2],[Question],[PlayOrder] FROM [OperationsDB].[dbo].[tblGameMasterLU] WHERE FK_VidID = $_GET[movieid] AND Included = 1 ORDER BY [FK_GameTypeID]";
  
	$subquery = "INSERT INTO TblGameMasterLU (FK_GameTypeID, FK_WhoLUID, LinkID, SnapShotFrame, SnapShotFrame2, Question, PlayOrder) VALUES (?, ?, ?, ?, ?, ?, ?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssql->query($query) as $row) {
		//WHOLE LINK NONSENSE.... NEED TO GET IDS FROM CORRESPONDING TABLES
		$linkID = 0;
		if($row[FK_GameTypeID] == 1) {
			$linkq = "SELECT DilemmaID FROM TblDilemmas WHERE Notify = $row[NotifyLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[DilemmaID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Notify Link...\n";
		}
		if($row[FK_GameTypeID] == 2) {
			$linkq = "SELECT IdsLUID FROM TblIdsLU WHERE SnapShotFrame = $row[SnapshotLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[IdsLUID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Snapshot Link...\n";
		}
		if($row[FK_GameTypeID] == 3) {
			$linkq = "SELECT IdsLUID FROM TblIdsLU WHERE SnapShotFrame = $row[SnapshotLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[IdsLUID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Snapshot Link...\n";
		}
		if($row[FK_GameTypeID] == 4) {
			$linkq = "SELECT LocationsID FROM TblLocations WHERE SnapShotFrame = $row[SnapshotLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[LocationsID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Snapshot Link...\n";
		}
		if($row[FK_GameTypeID] == 5) {
			$linkq = "SELECT MusicID FROM TblMusic WHERE Notify = $row[NotifyLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[MusicID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Notify Link...";
		}
		if($row[FK_GameTypeID] == 6) {
			$linkq = "SELECT WhyID FROM TblWhy WHERE SnapShotFrame = $row[SnapshotLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[WhyID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Snapshot Link...\n";
		}
		if($row[FK_GameTypeID] == 7) {
			$linkq = "SELECT QuoteID FROM TblQuote WHERE Notify = $row[NotifyLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[QuoteID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Notify Link...\n";
		}
		if($row[FK_GameTypeID] == 8) {
			$linkq = "SELECT RecipeID FROM TblRecipe WHERE SnapShotFrame = $row[SnapshotLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[RecipeID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Snapshot Link...\n";
		}
		if($row[FK_GameTypeID] == 9) {
			$linkq = "SELECT ShoppingLUID FROM TblShoppingLU WHERE SnapShotFrame = $row[SnapshotLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[ShoppingLUID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Snapshot Link...\n";
		}
		if($row[FK_GameTypeID] == 10) {
			$linkq = "SELECT SuperFanID FROM TblSuperFan WHERE Notify = $row[NotifyLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[SuperFanID];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Notify Link...\n";
		}
		if($row[FK_GameTypeID] == 11) {
			$linkq = "SELECT TriviaID, Question FROM TblTriviaLU WHERE Notify = $row[NotifyLink]";
			$statement = $dbh->query($linkq);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			$linkID = $linkrow[TriviaID];
			$row[Question] = $linkrow[Question];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Notify Link...\n";
		}
		if($row[FK_GameTypeID] == 12) {
			$linkID = $row[WhoLink];
			if(!($linkID > 0)) $errors .= "Game Master Question with PlayOrder ". $row[PlayOrder] ." doesn't have a valid Who Link...\n";
		}
		$array = array("$row[FK_GameTypeID]", "$row[FK_WhoLUID]", "$linkID", "$row[SnapShotFrame]", "$row[SnapShotFrame2]", "$row[Question]", "$row[PlayOrder]");
		$stm->execute($array);
		
		if($row[FK_WhoLUID] > 0) {
			//CHECK FOR PRESENCE OF WHO LINK
			checkWhoLUEntries($row[FK_WhoLUID], $mssql, $dbh);
		}
	}
	
	//TblGameMasterSeg
	$query = "SELECT [FK_GameTypeID],[PlayOrder],[Answer],[AnswerID],[IsCorrect] FROM [OperationsDB].[dbo].[tblGameMasterLU] lu LEFT JOIN [OperationsDB].[dbo].[tblGameMasterSeg] seg ON lu.PK_GameMasterLU = seg.FK_GameMasterLUID WHERE lu.FK_VidID = $_GET[movieid] AND Included = 1 ORDER BY [PlayOrder]";
	
	$subquery = "INSERT INTO TblGameMasterSeg (FK_GameMasterPlayID, AnswerID, Answer, IsCorrect) VALUES (?, ?, ?, ?)";
	$stm = $dbh->prepare($subquery);
	foreach ($mssql->query($query) as $row) {
		if($row[FK_GameTypeID] == 11) {
			$subsubquery1 = "SELECT LinkID FROM TblGameMasterLU WHERE PlayOrder = $row[PlayOrder]";
			$statement = $dbh->query($subsubquery1);
			$linkrow = $statement->fetch(PDO::FETCH_ASSOC);
			
			$subsubquery2 = "SELECT AnswerID, AnswerText, IsCorrect FROM TblTriviaSeg WHERE FK_TriviaID = $linkrow[LinkID]";
			foreach ($dbh->query($subsubquery2) as $linkrow) {
				$updatedAnswerID = $linkrow[AnswerID] + 1;
				$array = array("$row[PlayOrder]", "$updatedAnswerID", "$linkrow[AnswerText]", "$linkrow[IsCorrect]");
				$stm->execute($array);
				if(($row[FK_GameTypeID] == 1) || ($row[FK_GameTypeID] == 7) || ($row[FK_GameTypeID] == 12)) {
					 checkWhoLUEntries($row[Answer], $mssql, $dbh);
				}
			}
		} else {
			$array = array("$row[PlayOrder]", "$row[AnswerID]", "$row[Answer]", "$row[IsCorrect]");
			$stm->execute($array);
				if(($row[FK_GameTypeID] == 1) || ($row[FK_GameTypeID] == 7) || ($row[FK_GameTypeID] == 12)) {
				 checkWhoLUEntries($row[Answer], $mssql, $dbh);
			}
		}
	}
	
	//TblGameMusicLU -- NEEDED FOR CP MAP CREATION
	
	//TblGameMusicSeg -- NEEDED FOR CP MAP CREATION

}

//IMAGE RESOURCE GENERATION
if($_POST[action] == 2) {
	$frames = array();
	$who = array();
	$whoname = array();
	$recipes = array();
	$shopping = array();
	$shoppingname = array();
	$albums = array();
	
	$dbfile = 'G:\\'. $_POST[maptype] .'Maps\\'. $_GET[movieid] .'\\'. $_GET[movieid] .'.s3db';
	$dbname = 'sqlite:'. $dbfile;
	$dbh = new PDO($dbname);
	
	
	
	$query = "SELECT AlbumCover, SubAlbumCover FROM TblGameMasterLU LEFT JOIN TblMusic ON TblGameMasterLU.LinkID = TblMusic.MusicID WHERE FK_GameTypeID = 5";
	foreach ($dbh->query($query) as $row) {
		if(strpos($row[AlbumCover], "ALB") !== false) {
			if(!in_array($row[AlbumCover], $albums)) {
				$albums[] = $row[AlbumCover];
			}
		}
		if(strpos($row[SubAlbumCover], "ALB") !== false) {
			if(!in_array($row[SubAlbumCover], $albums)) {
				$albums[] = $row[SubAlbumCover];
			}
		}
	}
	
	$query = "SELECT Answer FROM TblGameMasterLU LEFT JOIN TblGameMasterSeg ON TblGameMasterLU.PlayOrder = TblGameMasterSeg.FK_GameMasterPlayID WHERE FK_GameTypeID = 8";
	foreach ($dbh->query($query) as $row) {
		if(!in_array($row[Answer], $recipes)) {
			$recipes[] = $row[Answer];
		}
	}
	
	$query = "SELECT ImageFileNameActual, ImageFileNameSubstitute, ShoppingLUID, ShoppingItemName FROM TblGameMasterLU LEFT JOIN TblShoppingLU ON TblGameMasterLU.LinkID = TblShoppingLU.ShoppingLUID WHERE FK_GameTypeID = 9";
	foreach ($dbh->query($query) as $row) {
		if(strlen($row[ImageFileNameActual]) > 0) {
			if(!in_array($row[ImageFileNameActual], $shopping)) {
				$shopping[] = $row[ImageFileNameActual];
				$shoppingname[] = $row[ShoppingItemName] ." (ID: ". $row[ShoppingLUID] .")";
			}
		}
		if(strlen($row[ImageFileNameSubstitute]) > 0) {
			if(!in_array($row[ImageFileNameSubstitute], $shopping)) {
				$shopping[] = $row[ImageFileNameSubstitute];
				$shoppingname[] = $row[ShoppingItemName] ." (ID: ". $row[ShoppingLUID] .")";
			}
		}
	}
	
	$query = "SELECT FK_WhoLUID, PerformerName, CharacterName FROM TblGameMasterLU LEFT JOIN TblWhoLU ON TblGameMasterLU.FK_WhoLUID = TblWhoLU.WhoLUID WHERE FK_WhoLUID > 0";
	foreach ($dbh->query($query) as $row) {
		if(!in_array($row[FK_WhoLUID], $who)) {
			$who[] = $row[FK_WhoLUID];
			$whoname[] = $row[PerformerName] ." as ". $row[CharacterName] ." (ID: ". $row[FK_WhoLUID] .")";
		}
	}
	$query = "SELECT Answer, PerformerName, CharacterName FROM TblGameMasterLU LEFT JOIN TblGameMasterSeg ON TblGameMasterLU.PlayOrder = TblGameMasterSeg.FK_GameMasterPlayID LEFT JOIN TblWhoLU ON TblGameMasterSeg.Answer = TblWhoLU.WhoLUID WHERE FK_GameTypeID = 1 OR FK_GameTypeID = 7 OR FK_GameTypeID = 12";
	foreach ($dbh->query($query) as $row) {
		if(!in_array($row[Answer], $who)) {
			$who[] = $row[Answer];
			$whoname[] = $row[PerformerName] ." as ". $row[CharacterName] ." (ID: ". $row[Answer] .")";
		}
	}
	
	
	$query = "SELECT Answer, PlayOrder FROM TblGameMasterLU LEFT JOIN TblGameMasterSeg ON TblGameMasterLU.PlayOrder = TblGameMasterSeg.FK_GameMasterPlayID WHERE FK_GameTypeID = 5";
	foreach ($dbh->query($query) as $row) {
		if($row[Answer] > 0) {
			if(!in_array($row[Answer], $frames)) {
				$frames[] = $row[Answer];
			}
		} else {
			$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH PLAY ORDER ID ". $row[PlayOrder] ."\n";
		}
	}

	
	//ADD ERRORCHECK
	$query = "SELECT SnapShotFrame, SnapShotFrame2, FK_GameTypeID, LinkID, PlayOrder FROM TblGameMasterLU";
	foreach ($dbh->query($query) as $row) {
		if($row[FK_GameTypeID] == 1) {
			$query = "SELECT SnapShotFrame FROM TblDilemmas WHERE DilemmaID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID ". $row[PlayOrder] ."\n";
			}
		}
		if(($row[FK_GameTypeID] == 2) || ($row[FK_GameTypeID] == 3)) {
			$query = "SELECT SnapShotFrame FROM TblIdsLU WHERE IdsLUID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
	
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
			if($row[SnapShotFrame] > 0) {
				if(!in_array($row[SnapShotFrame], $frames)) {
					$frames[] = $row[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
			if($row[SnapShotFrame2] > 0) {
				if(!in_array($row[SnapShotFrame2], $frames)) {
					$frames[] = $row[SnapShotFrame2];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
		if($row[FK_GameTypeID] == 4) {
			$query = "SELECT SnapShotFrame FROM TblLocations WHERE LocationsID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
		if($row[FK_GameTypeID] == 5) {
			$query = "SELECT SnapShotFrame FROM TblMusic WHERE MusicID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
		if($row[FK_GameTypeID] == 6) {
			$query = "SELECT SnapShotFrame FROM TblWhy WHERE WhyID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
			if($row[SnapShotFrame] > 0) {
				if(!in_array($row[SnapShotFrame], $frames)) {
					$frames[] = $row[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
			if($row[SnapShotFrame2] > 0) {
				if(!in_array($row[SnapShotFrame2], $frames)) {
					$frames[] = $row[SnapShotFrame2];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
		if($row[FK_GameTypeID] == 7) {
			$query = "SELECT SnapShotFrame FROM TblQuote WHERE QuoteID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
		if($row[FK_GameTypeID] == 8) {
			$query = "SELECT SnapShotFrame FROM TblRecipe WHERE RecipeID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
		if($row[FK_GameTypeID] == 9) {
			$query = "SELECT SnapShotFrame FROM TblShoppingLU WHERE ShoppingLUID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
			if($row[SnapShotFrame] > 0) {
				if(!in_array($row[SnapShotFrame], $frames)) {
					$frames[] = $row[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
			if($row[SnapShotFrame2] > 0) {
				if(!in_array($row[SnapShotFrame2], $frames)) {
					$frames[] = $row[SnapShotFrame2];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
		if($row[FK_GameTypeID] == 10) {
			$query = "SELECT SnapShotFrame FROM TblSuperFan WHERE SuperFanID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
		if($row[FK_GameTypeID] == 11) {
			$query = "SELECT SnapShotFrame FROM TblTriviaLU WHERE TriviaID = $row[LinkID]";
			$stmt = $dbh->query($query);
			$subrow = $stmt->fetch(PDO::FETCH_ASSOC);
			if($subrow[SnapShotFrame] > 0) {
				if(!in_array($subrow[SnapShotFrame], $frames)) {
					$frames[] = $subrow[SnapShotFrame];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
			if($row[SnapShotFrame2] > 0) {
				if(!in_array($row[SnapShotFrame2], $frames)) {
					$frames[] = $row[SnapShotFrame2];
				}
			} else {
				$errors .= "MISSING SNAPSHOT FOR MOVIEMASTER QUESTION WITH ORDER ID". $row[PlayOrder] ."\n";
			}
		}
	}
	
	
	//$result = shell_exec("net use q: \\\\r2d2\\y\\videos 1Q247M5z /user:appl_poptrivia@customplay.local /persistent:no 2>&1");
	
	//SAVE IMAGES AND CREATE ZIP
	//SPECIFIC TO MAP TYPE....
	if($_POST[maptype] == "PT") {
		//CLEAR ALL OLD DATA
		$imgdir = 'G:\\PTMaps\\'. $_GET[movieid];
		if(!file_exists($imgdir)) {
			mkdir($imgdir);
		}
		$imgdir = 'G:\\PTMaps\\'. $_GET[movieid] .'\\AppleTVAssets';
		if(!file_exists($imgdir)) {
			mkdir($imgdir);
		} else {
			$files = glob($imgdir .'/*');
			foreach($files as $file) { 
				if(is_file($file))
				unlink($file);
			}
		}
		$tmpdir = 'G:\\PTMaps\\tmp';
		$files = glob($tmpdir .'/*');
		foreach($files as $file) { 
			if(is_file($file))
			unlink($file);
		}
		
		$destdir = 'G:\\PTMaps\\'. $_GET[movieid] .'\\AppleTVAssets';
		
		foreach($recipes as $img) {
			$namebits = explode('_', $img);
			$vidid = str_replace("REC0", "", $namebits[0]);
			$query = "SELECT VidTitle FROM [OperationsDB].[dbo].[tblVidReleaseInfo] WHERE FK_VidID = ". $vidid;
			$stmt = $mssql->query($query);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$source = '\\\\R2D2\\Y\\PHOTOS\\REC\\'. $row[VidTitle] .'\\'. $img .'.jpg';
			if(!file_exists($source)) {
				$source = '\\\\R2D2\\Y\\PHOTOS\\REC\\'. $row[VidTitle] .'\\'. $img .'.JPG';
			}
			$dest = $destdir .'\\'. $img .'.jpg';
			
			$src_img = imagecreatefromjpeg($source);
			$width = imagesx($src_img) / 2.0;
			$height = imagesy($src_img) / 2.0;
			$dst_img = imagecreatetruecolor($width, $height);
			imagecopyresampled($dst_img,$src_img,0,0,0,0,$width,$height,imagesx($src_img),imagesy($src_img));
			imagejpeg($dst_img, $dest, 70);
					
			imagedestroy($src_img);
			imagedestroy($dst_img);
		}
		
		
		
		/*
		print_r($frames);
		print_r($who);
		print_r($whoname);
		print_r($recipes);
		print_r($shopping );
		print_r($shoppingname);
		print_r($albums);
		//*/
		
		
		
		//ALBUM ARTWORK
		$check = array();
		$sourcedir = '\\\\R2D2\\Y\\PHOTOS\\MUS';
		$files = scandir($sourcedir);
		foreach($files as $file) {
			$name = explode('.', $file);
			if(in_array($name[0], $albums)) {
				$check[] = $name[0];
				$source = $sourcedir .'\\'. $file;
				if((strtoupper($name[1]) == "JPG") || (strtoupper($name[1]) == "JPEG")) {
					$src_img = imagecreatefromjpeg($source);
				}
				if(strtoupper($name[1]) == "PNG") {
					$src_img = imagecreatefrompng($source);
				}
				if(strtoupper($name[1]) == "GIF") {
					$src_img = imagecreatefromgif($source);
				}
				if(strtoupper($name[1]) == "BMP") {
					$src_img = imagecreatefromwbmp($source);
				}
				
				if($src_img) {
					$dst_img = imagecreatetruecolor(200,200);
					imagecopyresampled($dst_img,$src_img,0,0,0,0,200,200,imagesx($src_img),imagesy($src_img));
					$dest = $destdir .'\\'. $name[0] .'.jpg';
					imagejpeg($dst_img, $dest, 70);
					imagedestroy($src_img);
					imagedestroy($dst_img);
				} else {
					$errors .= "Error generating MUSIC image ". $file ."\n";
				}
			}
		}
		foreach($albums as $item) {
			if(!(in_array($item, $check))) {
				$errors .= "Missing MUSIC image ". $item ."\n";
			}
		}
		
		
		
		
		//SHOPPING COPY AND RESIZE
		$check = array();
		$sourcedir = '\\\\R2D2\\Y\\PHOTOS\\SHO\\'. $movie[VidDirectory];
		$files = scandir($sourcedir);
		foreach($files as $file) {
			$name = explode('.', $file);
			if(in_array($name[0], $shopping)) {
				$check[] = $name[0];
				$source = $sourcedir .'/'. $file;
				if((strtoupper($name[1]) == "JPG") || (strtoupper($name[1]) == "JPEG")) {
					$src_img = imagecreatefromjpeg($source);
				}
				if(strtoupper($name[1]) == "PNG") {
					$src_img = imagecreatefrompng($source);
				}
				if(strtoupper($name[1]) == "GIF") {
					$src_img = imagecreatefromgif($source);
				}
				if(strtoupper($name[1]) == "BMP") {
					$src_img = imagecreatefromwbmp($source);
				}
				if($src_img) {
					if((imagesx($src_img) > 200) || (imagesy($src_img) > 200)) {
						$ratio = imagesx($src_img) / imagesy($src_img);
						if($ratio > 1) {
							$width = 200;
							$height = 200 / $ratio;
						} else {
							$width = 200 * $ratio;
							$height = 200;
						}
					} else {
						$width = imagesx($src_img);
						$height = imagesy($src_img);
					}
		
					$dst_img = imagecreatetruecolor($width, $height);
					imagecopyresampled($dst_img,$src_img,0,0,0,0,$width,$height,imagesx($src_img),imagesy($src_img));
					$dest = $destdir .'\\'. $name[0] .'.jpg';
					imagejpeg($dst_img, $dest, 70);
					
					imagedestroy($src_img);
					imagedestroy($dst_img);
				} else {
					$errors .= "Error generating SHOPPING image ". $file ." for ". $shoppingname[array_search($name[0], $shopping)] ."\n";
				}
			}
		}
		foreach($shopping as $item) {
			if(!(in_array($item, $check))) {
				$errors .= "Missing SHOPPING image ". $item ." for ". $shoppingname[array_search($item, $shopping)] ."\n";
			}
		}
		
		
		//WHO - STRAIGHT COPY
		$check = array();
		$sourcedir = '\\\\R2D2\\Y\\PHOTOS\\WHO\\'. $movie[VidDirectory];
		$files = scandir($sourcedir);
		foreach($files as $file) {
			$name = explode('.', $file);
			if(in_array(str_replace("WhoLUID_","",$name[0]), $who)) {
				$check[] = str_replace("WhoLUID_","",$name[0]);
				$source = $sourcedir .'\\'. $file;
				$dest = $destdir .'\\'. $name[0] .'.jpg';
				
				$src_img = imagecreatefromjpeg($source);
				$width = imagesx($src_img) / 1.5;
				$height = imagesy($src_img) / 1.5;
				$dst_img = imagecreatetruecolor($width, $height);
				imagecopyresampled($dst_img,$src_img,0,0,0,0,$width,$height,imagesx($src_img),imagesy($src_img));
				imagejpeg($dst_img, $dest, 70);
					
				imagedestroy($src_img);
				imagedestroy($dst_img);
			}
		}
		foreach($who as $item) {
			if(!(in_array($item, $check))) {
				$errors .= "Missing WHO image ". $item  ." for ". $whoname[array_search($item, $who)] ."\n";
			}
		}
	
		
		
		//FRAMES - AVISYNTH GENERATION
		$contents = "A1=AviSource(\"\\\\R2D2\\Y\\VIDEOS\\_AVI\\$movie[VidDirectory]\\$movie[VidDirectory].avi\").ConvertToRGB.Trim(0,243500)". PHP_EOL;
		$contents .= "A2=AviSource(\"\\\\R2D2\\Y\\VIDEOS\\_AVI\\$movie[VidDirectory]\\$movie[VidDirectory].avi\").ConvertToRGB.Trim(243500,0)". PHP_EOL;
		$contents .= "A= A1 ++ A2". PHP_EOL;
		$contents .= "OutputI=";
		foreach($frames as $frame) {
			$contents .= "A.Trim(". $frame .",". $frame .")++";
		}
		$contents = substr($contents, 0, -2);
		$contents .= PHP_EOL;
		$contents .="OutputI";
		
		$scriptfile = 'G:\\PTMaps\\'. $_GET[movieid] .'\\avisynth.avs';
		file_put_contents($scriptfile, $contents);
		
		
		if($smallvid) {
			$command = "C:\\inetpub\\wwwroot\\ffmpeg\\bin\\ffmpeg -i \"G:\\PTMaps\\$_GET[movieid]\\avisynth.avs\" -vf \"scale=iw*.63:-1\" -q:v 4 G:\\PTMaps\\tmp\%05d.jpg 2>&1";
		} else {
			$command = "C:\\inetpub\\wwwroot\\ffmpeg\\bin\\ffmpeg -i \"G:\\PTMaps\\$_GET[movieid]\\avisynth.avs\" -vf \"scale=iw*.42:-1\" -q:v 4 G:\\PTMaps\\tmp\%05d.jpg 2>&1";
		}
		
		//$command = "C:\\inetpub\\wwwroot\\ffmpeg\\bin\\ffmpeg -i \"G:\\PTMaps\\$_GET[movieid]\\avisynth.avs\" -vf \"scale=iw*.42:-1\" -q:v 4 G:\\PTMaps\\tmp\%05d.jpg 2>&1";
		shell_exec($command);
		
		
		
		$tmpdir = 'G:\\PTMaps\\tmp';
		$files = glob($tmpdir .'/*');
		$n = 0;
		foreach($files as $file) { 
			if(is_file($file)) {
				$dest = 'G:\\PTMaps\\'. $_GET[movieid] .'\\AppleTVAssets\\frame_'. $frames[$n] .'.jpg';
				rename($file, $dest);
				$n++;
			}
		}
		if($n != count($frames)) {
			$errors .= "Failed to generate all FRAME images. ". count($frames) ." expected, ". $n ." generated...\n";
		}
		
		
		$imgres = 'G:\\PTMaps\\'. $_GET[movieid] .'\\Boxart.jpg';
		if(!file_exists($imgres)) {
			$errors .= "Boxart image not found...\n";
		}
		$imgres = 'G:\\PTMaps\\'. $_GET[movieid] .'\\BG.jpg';
		if(!file_exists($imgres)) {
			$errors .= "Landscape Background image not found...\n";
		}
		$imgres = 'G:\\PTMaps\\'. $_GET[movieid] .'\\BG_Portrait.jpg';
		if(!file_exists($imgres)) {
			$errors .= "Portrait Background image not found...\n";
		}
		
		
		//ALL DONE, GENERATE ZIP FILE
		$path = 'G:\\PTMaps\\'. $_GET[movieid];
		$todelete = $path .'\\avisynth.avs';
		if(file_exists($todelete)) {
			unlink($todelete);
		}
		$todelete = $path .'\\Thumbs.db';
		if(file_exists($todelete)) {
			unlink($todelete);
		}
		$todelete = $path .'\\AppleTVAssets\\Thumbs.db';
		if(file_exists($todelete)) {
			unlink($todelete);
		}
		$todelete = $path .'\\'. $_GET[movieid] .'.zip';
		if(file_exists($todelete)) {
			unlink($todelete);
		}
		
		
		$tmpzipfile = 'G:\\PTMaps\\'. $_GET[movieid] .'.zip';
		if(file_exists($tmpzipfile)) {
			unlink($tmpzipfile);
		}
		
		if(strlen($errors) == 0) {
			HZip::zipDir($path, $tmpzipfile);
		
			$zipfile = 'G:\\PTMaps\\'. $_GET[movieid] .'\\'. $_GET[movieid] .'.zip';
			rename($tmpzipfile, $zipfile);
		}
	}
}


echo '</pre>';
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
	
});
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    <a href="/dashboard.php">Dashboard</a> / <a href="/dashboard-moviemaster.php?movieid=<?php echo $_GET[movieid];?>"><?php echo str_replace(" [AVI]", "", $movie[VidTitle]);?> MovieMaster</a> / Map Creation
    <div class="grad1 rounded module">

<?php if($_POST[action] == 1) { //DATABASE GENERATED, DO RESOURCE GENERATION ?>
	<h3>The Database has been generated</h3>
    <div style="padding: 20px 0 20px 0;">
    <?php if(strlen($errors) > 0) {?>
    Errors:
    <pre><?php echo $errors;?></pre>
    <?php }?>
    </div>
    <?php if(strlen($errors) == 0) {?>
	<form action="act-createmap.php?movieid=<?php echo $_GET['movieid'];?>" method="post">
    <input type="hidden" name="action" value="2" />
    <input type="hidden" name="maptype" value="<?php echo $_POST['maptype'];?>" />
    <div class="record"><input type="submit" id="submitbtn" value="Generate Image Resources!" class="rounded"></div>
    </form>
    <?php }?>
<?php } else if($_POST[action] == 2) { //IMAGES GENERATED, CHECK FOR SHIT ?>
	<h3>Images have been gathered</h3>
    <div style="padding: 20px 0 20px 0;">
    <?php if(strlen($errors) > 0) {?>
    Errors:
    <pre><?php echo $errors;?></pre>
    <?php }?>
    </div>
	<?php if(strlen($errors) == 0) {?>
	<form action="act-editmovie.php?movieid=<?php echo $_GET['movieid'];?>" method="post">
    <div class="record"><input type="submit" id="submitbtn" value="Go to Final Update Area!" class="rounded"></div>
    </form>
    <?php }?>

<?php } else {  //FIRST ENTRY, CHECK FOR PRESENCE OF ALL DATABASES ?> 
	<h3>Resource Check</h3>
<?php
$have_all_data = true;

$playorder = array();
$count = 0;
$query = "SELECT PlayOrder FROM [OperationsDB].[dbo].[tblGameMasterLU] WHERE Included = 1 AND FK_VidID = ". $_GET[movieid];
foreach ($mssql->query($query) as $row) {
	$count++;
	if(!in_array($row[PlayOrder], $playorder)) {
		$playorder[] = $row[PlayOrder];
	}
}

$checkcount = 30;
if($movie[VidSetParentID] > 0) {
	$checkcount = 10;
}


if(($count == $checkcount) && (count($playorder) == $checkcount)) {
	$db_mmaster = '<div style="color: #00aa00;">Check!</div>';
} else {
	$have_all_data = false;
	$msg = "";
	if($count != $checkcount) $msg = "Need ". $checkcount ." questions. ";
	else if(count($playorder) != $checkcount)  $msg .= "Question order not right.";
	$db_mmaster = '<div style="color: #ff0000;">'. $msg .'</div>';
}

?>
    <div class="record"><label>Data Integrity:</label><?php echo $db_mmaster;?></div>
    
    <?php if($have_all_data) {?>
    <form action="act-createmap.php?movieid=<?php echo $_GET['movieid'];?>" method="post">
    <input type="hidden" name="action" value="1" />
    <input type="hidden" name="maptype" value="<?php echo $_POST['maptype'];?>" />
    <div class="record"><label></label><input type="submit" id="submitbtn" value="Create Map!" class="rounded"></div>
    </form>
    <?php }?>

<?php }?>

	<div class="clearfix"></div>
	</div>    
</div>
</body>
</html>

<?php
function checkWhoLUEntries($whoid, $mssql, $dbh) {
	//CHECK FOR PRESENCE OF WHO LINK
	if($whoid > 0) {
		$query = "SELECT COUNT(*) FROM TblWhoLU WHERE WhoLUID = ". $whoid;
		$stm = $dbh->query($query);
		$whoPresent = $stm->fetchColumn();
		if(!($whoPresent > 0)) {
			$query = "SELECT PK_VidCharacterID, a.FK_PerID, a.CharID,REPLACE(REPLACE(REPLACE('# ' + tblPerformers.PerTitle + ' ' + tblPerformers.PerFirstName + ' ' + tblPerformers.PerMidName + ' ' + tblPerformers.PerLastName + ' ' + isnull(tblPerformers.PerSuffix,'') + ' #','  ',' '),'# ',''),' #','') as 'PerformerName', tblPerformers.WebLink, a.VidCharName, a.VidCharFrameRefNum FROM [OperationsDB].[dbo].[tblVidCharacters] a JOIN [OperationsDB].[dbo].[tblPerformers] on tblPerformers.PK_PerID = a.FK_PerID WHERE a.PK_VidCharacterID = ". $whoid;
			$stm = $mssql->query($query);
			$whorow = $stm->fetch(PDO::FETCH_ASSOC);
				
			$biolink = $whorow[WebLink];
			if (strpos($biolink, 'imdb.com') !== false) {
				$data = explode("/", $biolink);
				$biolink = $data[4];
			}
			//CEHCK FOR SAME FIRST/LAST NAME
			$verify = explode(" ",$whorow[PerformerName]);
			if((count($verify) == 2) && ($verify[0] == $verify[1])) $whorow[PerformerName] = $verify[0];
			
			$query = "INSERT INTO TblWhoLU (WhoLUID, PerID, ChaID, PerformerName, CharacterName, SnapShotFrame, BioLink) VALUES (?,?,?,?,?,?,?)";
			$stm = $dbh->prepare($query);
			$array = array("$whoid","$whorow[FK_PerID]","$whorow[CharID]","$whorow[PerformerName]","$whorow[VidCharName]","$whorow[VidCharFrameRefNum]","$biolink");
			$stm->execute($array);
		}
	}
}



class HZip { 
  /** 
   * Add files and sub-directories in a folder to zip file. 
   * @param string $folder 
   * @param ZipArchive $zipFile 
   * @param int $exclusiveLength Number of text to be exclusived from the file path. 
   */ 
  private static function folderToZip($folder, &$zipFile, $exclusiveLength) { 
    $handle = opendir($folder); 
    while (false !== $f = readdir($handle)) { 
      if ($f != '.' && $f != '..') { 
        $filePath = "$folder/$f"; 
        // Remove prefix from file path before add to zip. 
        $localPath = substr($filePath, $exclusiveLength); 
        if (is_file($filePath)) { 
		  if (strpos($filePath, 'Thumbs.db') === false) {
			 $zipFile->addFile($filePath, $localPath); 
		  }
        } elseif (is_dir($filePath)) { 
          // Add sub-directory. 
          $zipFile->addEmptyDir($localPath); 
          self::folderToZip($filePath, $zipFile, $exclusiveLength); 
        } 
      } 
    } 
    closedir($handle); 
  } 

  /** 
   * Zip a folder (include itself). 
   * Usage: 
   *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip'); 
   * 
   * @param string $sourcePath Path of directory to be zip. 
   * @param string $outZipPath Path of output zip file. 
   */ 
  public static function zipDir($sourcePath, $outZipPath) { 
    $pathInfo = pathInfo($sourcePath); 
    $parentPath = $pathInfo['dirname']; 
    $dirName = $pathInfo['basename']; 

    $z = new ZipArchive(); 
    $z->open($outZipPath, ZIPARCHIVE::CREATE); 
    $z->addEmptyDir($dirName); 
    self::folderToZip($sourcePath, $z, strlen("$parentPath/")); 
    $z->close(); 
  } 
}




?>