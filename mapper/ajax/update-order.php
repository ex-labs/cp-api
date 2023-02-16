<?php
include('../includes/config.php');

$dbfile = '../../data-tv/'. $_POST[movieid] .'/'. $_POST[movieid] .'.s3db';
$dbname = 'sqlite:'. $dbfile;
$dbh = new PDO($dbname);

$query = "SELECT PlayOrder FROM TblGameMasterLU WHERE PK_GameMasterLU = $_POST[questionid]";
$stmt = $dbh->query($query);
$old_order = $stmt->fetchColumn();

$query = "UPDATE TblGameMasterLU SET PlayOrder = 0 WHERE PK_GameMasterLU = $_POST[questionid]";
$dbh->query($query);
$query = "UPDATE TblGameMasterSeg SET FK_GameMasterPlayID = 0 WHERE FK_GameMasterPlayID = $old_order";
$dbh->query($query);


if($old_order < $_POST[neworder]) {
	for($i=$old_order; $i<=$_POST[neworder]; $i++) {
		if($i == $_POST[neworder]) {
			$query = "UPDATE TblGameMasterLU SET PlayOrder = $i WHERE PlayOrder = 0";
			$dbh->query($query);
			$query = "UPDATE TblGameMasterSeg SET FK_GameMasterPlayID = $i WHERE FK_GameMasterPlayID = 0";
			$dbh->query($query);
		} else {
			$query = "UPDATE TblGameMasterLU SET PlayOrder = $i WHERE PlayOrder = $i + 1";
			$dbh->query($query);
			$query = "UPDATE TblGameMasterSeg SET FK_GameMasterPlayID = $i WHERE FK_GameMasterPlayID = $i + 1";
			$dbh->query($query);
		}
	}
} else {
	for($i=$old_order; $i>=$_POST[neworder]; $i--) {
		if($i == $_POST[neworder]) {
			$query = "UPDATE TblGameMasterLU SET PlayOrder = $i WHERE PlayOrder = 0";
			$dbh->query($query);
			$query = "UPDATE TblGameMasterSeg SET FK_GameMasterPlayID = $i WHERE FK_GameMasterPlayID = 0";
			$dbh->query($query);
		} else {
			$query = "UPDATE TblGameMasterLU SET PlayOrder = $i WHERE PlayOrder = $i - 1";
			$dbh->query($query);
			$query = "UPDATE TblGameMasterSeg SET FK_GameMasterPlayID = $i WHERE FK_GameMasterPlayID = $i - 1";
			$dbh->query($query);
		}
	}
}

echo "success";
?>