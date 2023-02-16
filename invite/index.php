<?php
$date_of_expiry = time() + 60 * 60 * 24 * 30;
$gameid = $_GET[game];
setcookie( "gameid", $gameid, $date_of_expiry, "/", "popcorntrivia.com" );

$iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
$ipad = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
$windows = strpos($_SERVER['HTTP_USER_AGENT'],"Windows");
$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
?>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
<?php
if(($iphone || $ipod || $ipad) && !$windows) { //iOS
	$version = preg_replace("/(.+)(iPhone|iPad|iPod)(.+)OS[\s|\_](\d)\_?(\d)?[\_]?(\d)?.+/i", "$4.$5", $_SERVER['HTTP_USER_AGENT']);
	if($version >= 9.2) {
		echo "window.location = 'https://itunes.apple.com/us/app/popcorntrivia/id1083666964?ls=1&mt=8&at=1000l66r';";
	} else {
		echo "var customurl = 'popcorntrivia://". $gameid ."';";
		echo "var storeurl = 'https://itunes.apple.com/us/app/popcorntrivia/id1083666964?ls=1&mt=8&at=1000l66r';";
		echo "window.location = customurl;";
		echo "setTimeout(\"window.location = storeurl;\", 1000);";
	}
} else if($android && !$windows) { //Android
	echo "var customurl = 'popcorntrivia://". $gameid ."';";
	echo "var storeurl = 'https://play.google.com/store/apps/details?id=com.customplay.popcorntrivia&referrer=". $gameid ."';";
	echo "window.location = customurl;";
	echo "setTimeout(\"window.location = storeurl;\", 1000);";
} else { //Must be Windows....
	echo "window.location = 'http://www.popcorntrivia.com';";
}
?>
});
</script>
</head>
<body>
</body>
</html>
