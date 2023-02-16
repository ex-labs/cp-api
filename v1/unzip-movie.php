<?php
if($_GET[movieid]) {
	$datapath = "../data-tv/";
	$zipfile = $datapath . $_GET[movieid] ."/". $_GET[movieid] .".zip";
		
	$zip = new ZipArchive;
	$res = $zip->open($zipfile);
	$zip->extractTo($datapath);
  	$zip->close();
		
	echo 'done';
}
?>