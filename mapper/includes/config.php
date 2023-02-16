<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE);

$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);





function getDepartamentName($departamentID) {
	switch($departamentID) {
		case 1:
			$questonType = "Dilemma";
			break;
			
		case 2:
			$questonType = "Vehicles";
			break;
			
		case 3:
			$questonType = "Weapons";
			break;
			
		case 4:
			$questonType = "Locations";
			break;
			
		case 5:
			$questonType = "Music";
			break;
			
		case 6:
			$questonType = "Plot Info";
			break;
			
		case 7:
			$questonType = "Quotes";
			break;
			
		case 8:
			$questonType = "Recipes";
			break;
			
		case 9:
			$questonType = "Shopping";
			break;
		
		case 10:
			$questonType = "SuperFan";
			break;
			
		case 11:
			$questonType = "Trivia";
			break;
			
		case 12:
			$questonType = "Who";
			break;
		default:
			break;
	}
	return $questonType;
}

function getOrderPulldown($selectedID, $questionID) {
	$string = '<select class="playorder" id="'. $questionID .'" name="PlayOrder['. $questionID .']">';
	if($selectedID == 1) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="1"'. $selected .'>1</option>';
	if($selectedID == 2) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="2"'. $selected .'>2</option>';
	if($selectedID == 3) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="3"'. $selected .'>3</option>';
	if($selectedID == 4) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="4"'. $selected .'>4</option>';
	if($selectedID == 5) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="5"'. $selected .'>5</option>';
	if($selectedID == 6) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="6"'. $selected .'>6</option>';
	if($selectedID == 7) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="7"'. $selected .'>7</option>';
	if($selectedID == 8) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="8"'. $selected .'>8</option>';
	if($selectedID == 9) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="9"'. $selected .'>9</option>';
	if($selectedID == 10) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="10"'. $selected .'>10</option>';
	if($selectedID == 11) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="11"'. $selected .'>11</option>';
	if($selectedID == 12) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="12"'. $selected .'>12</option>';
	if($selectedID == 13) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="13"'. $selected .'>13</option>';
	if($selectedID == 14) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="14"'. $selected .'>14</option>';
	if($selectedID == 15) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="15"'. $selected .'>15</option>';
	if($selectedID == 16) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="16"'. $selected .'>16</option>';
	if($selectedID == 17) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="17"'. $selected .'>17</option>';
	if($selectedID == 18) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="18"'. $selected .'>18</option>';
	if($selectedID == 19) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="19"'. $selected .'>19</option>';
	if($selectedID == 20) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="20"'. $selected .'>20</option>';
	if($selectedID == 21) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="21"'. $selected .'>21</option>';
	if($selectedID == 22) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="22"'. $selected .'>22</option>';
	if($selectedID == 23) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="23"'. $selected .'>23</option>';
	if($selectedID == 24) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="24"'. $selected .'>24</option>';
	if($selectedID == 25) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="25"'. $selected .'>25</option>';
	if($selectedID == 26) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="26"'. $selected .'>26</option>';
	if($selectedID == 27) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="27"'. $selected .'>27</option>';
	if($selectedID == 28) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="28"'. $selected .'>28</option>';
	if($selectedID == 29) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="29"'. $selected .'>29</option>';
	if($selectedID == 30) $selected = ' selected="selected"'; else $selected = "";
	$string .= '<option value="30"'. $selected .'>30</option>';
	
	return $string;
}



function getCharacterInfo($mssql, $movieid, $selectedid) {
	$query = "SELECT PK_VidCharacterID, a.FK_PerID, a.CharID,REPLACE(REPLACE(REPLACE('# ' + tblPerformers.PerTitle + ' ' + tblPerformers.PerFirstName + ' ' + tblPerformers.PerMidName + ' ' + tblPerformers.PerLastName + ' ' + isnull(tblPerformers.PerSuffix,'') + ' #','  ',' '),'# ',''),' #','') as 'PerformerName',
a.VidCharName, a.VidCharFrameRefNum from tblVidCharacters a
  join tblperformers on tblperformers.PK_PerID = a.FK_PerID
  join tblVideos on tblvideos.PK_VidID = a.FK_VidID
  where tblVideos.PK_VidID = ". $movieid ." AND PK_VidCharacterID = ". $selectedid;
	$statement = $mssql->query($query);
	$row = $statement->fetch(PDO::FETCH_ASSOC);
	
	$array[PerformerName] = $row[PerformerName];
	$array[VidCharName] = $row[VidCharName];
	
	return $array;
}

function generateRecipePulldowns($mssql, $movieid, $selectedid) {
	
	//$result = shell_exec("net use P: \\\\r2d2\\y\\PHOTOS 1Q247M5z /user:appl_poptrivia@customplay.local /persistent:no 2>&1");
	
	if($movieid != 0) {
		$query = "SELECT [PK_VidID] ,vid.VidTitle as VidTitle, rel.VidTitle as VidDirectory FROM [OperationsDB].[dbo].[tblVideos] vid LEFT JOIN [OperationsDB].[dbo].[tblVidReleaseInfo] rel ON vid.PK_VidID = rel.FK_VidID WHERE PK_VidID = ". $movieid;
  		$statement = $mssql->query($query);
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		
		$options = "";
		if($row[VidDirectory]) {
			$recdir = '\\\\R2D2\\Y\\PHOTOS\\REC\\'. $row[VidDirectory];
			if(file_exists($recdir)) {
				$files = scandir($recdir);
				foreach($files as $file) {
					if(strpos($file, "REC") !== false) {
						$name = str_replace(".jpg", "", $file);
						$name = str_replace(".JPG", "", $name);
						$options .= '<option value="'. $name .'" imagepath="\\REC\\'. $row[VidDirectory] .'\\'. $file .'"';
						if($name == $selectedid) $options .= ' selected="selected"';
						$options .= '>'. str_replace("[AVI]", "", $row[VidTitle]) .' -- '. $name .'</option>';
					}
				}
			}
		}
		echo $options;
	} else {
		$query = "SELECT [PK_VidID] ,vid.VidTitle as VidTitle, rel.VidTitle as VidDirectory FROM [OperationsDB].[dbo].[tblVideos] vid LEFT JOIN [OperationsDB].[dbo].[tblVidReleaseInfo] rel ON vid.PK_VidID = rel.FK_VidID WHERE PK_VidID > 1159 ORDER BY VidTitle";
		$options = "";
  		foreach ($mssql->query($query) as $row) {
			$recdir = '\\\\R2D2\\Y\\PHOTOS\\REC\\'. $row[VidDirectory];
			if($row[VidDirectory]) {
				if(file_exists($recdir)) {
					$files = scandir($recdir);
					foreach($files as $file) {
							if(strpos($file, "REC") !== false) {
							$name = str_replace(".jpg", "", $file);
							$name = str_replace(".JPG", "", $name);
							$options .= '<option value="'. $name .'" imagepath="\\REC\\'. $row[VidDirectory] .'\\'. $file .'"';
							if($name == $selectedid) $options .= ' selected="selected"';
							$options .= '>'. str_replace("[AVI]", "", $row[VidTitle]) .' -- '. $name .'</option>';
						}
					}
				}
			}
		}
		echo $options;
	}
}

function sanitizeTextString($string) {
	$chars = " !	#$¢%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~¡£¥¦§¨©«¬­®¯°±²³´µ·¸¹»¼½¾¿ÆÇÐ×ØÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿıŁłŒœšžƒˆˇ˘˙˚˛˜˝π–—‘’‚“”„†‡•…‰‹›⁄€™Ω∏∑−√∫≠ŠōőÖůćÉńÄā";
	$newstr = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	echo $newstr;
}


?>