<?php
$myfile = fopen("used-superfans.txt", "r") or die("Unable to open file!");
$used = fgets($myfile);
fclose($myfile);


$used .= $_POST[needle];

$myfile = fopen("used-superfans.txt", "w") or die("Unable to open file!");
fwrite($myfile, $used);
fclose($myfile);
?>