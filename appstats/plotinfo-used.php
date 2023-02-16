<?php
$myfile = fopen("used-plotinfo.txt", "r") or die("Unable to open file!");
$used = fgets($myfile);
fclose($myfile);


$used .= $_POST[needle];

$myfile = fopen("used-plotinfo.txt", "w") or die("Unable to open file!");
fwrite($myfile, $used);
fclose($myfile);
?>