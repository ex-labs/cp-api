<?php
//client has to refresh url that push notifications go to. Put in area between $dbh = blah... and mysql_close($dbh)
if ($_POST['request'] == "addWindowsToken"){
	$token = $_POST['client_url'];
	$uid = $_POST['uid'];
	$query = "UPDATE pt_multiusers SET token = '$token' WHERE uid = $uid";
	$result=mysql_query($query) or die (json_encode(array('status'=>"not saved" ,'error'=>mysql_error()))); 
	echo(json_encode(array("status"=>"saved")));
}

//returns token needed for pushing notifications through Windows
function authorizePushWindows(){
	$apiKey = urlencode('Th3rMfOSumhq6Fq6uzDagUm');	
	$appId = urlencode('ms-app://s-1-15-2-981807083-30848534-1972440605-4155331106-985848221-2782754458-2623629205');
	$url = 'https://login.live.com/accesstoken.srf';
	$token_headers = array(
		'Content-Type'=>'application/x-www-form-urlencoded'
	);
	$token_body='grant_type=client_credentials&client_id=ms-app://s-1-15-2-981807083-30848534-1972440605-4155331106-985848221-2782754458-2623629205&client_secret=Th3rMfOSumhq6Fq6uzDagUm&scope=notify.windows.com';
	$tokenOptions = array(
		CURLOPT_POST              =>    true,
		CURLOPT_URL	              =>    $url,
		CURLOPT_RETURNTRANSFER    =>    true,
		CURLOPT_POSTFIELDS		  =>    $token_body,
		CURLOPT_HTTPHEADER     	  =>    $token_headers 
	);	

	$ch = curl_init();
	curl_setopt_array($ch,$tokenOptions);
	$response = curl_exec($ch);
	$json = json_decode($response,true);
	curl_close($ch);
	
	return $json["access_token"];
}

function sendMessageWindows($authToken,$userToken,$message){
	//for examples of all the crazy stuff you can do with windows push notifications
	//https://msdn.microsoft.com/windows/uwp/controls-and-patterns/tiles-and-notifications-adaptive-interactive-toasts
	$toast ='<toast><visual lang="en-US"><binding template="ToastGeneric"><text id="1">'.$message.'</text></binding></visual></toast>';

	$length =strlen($toast);
	$headers = array('Content-Type: text/xml', "Content-Length: " . strlen($toast), "X-WNS-Type: wns/toast", "Authorization: Bearer $authToken",'X-WNS-RequestForStatus: true');
	
	//$f = fopen("windows-push-request.txt",'w');
	$pushOptions=array(
	    CURLOPT_POST           =>    true,
		CURLOPT_RETURNTRANSFER =>    true,
		CURLOPT_SSL_VERIFYPEER =>    false,
		CURLOPT_HTTPHEADER     =>    $headers,
		CURLOPT_POSTFIELDS     =>    "$toast",
		CURLOPT_SSL_VERIFYHOST =>    false,
		CURLOPT_VERBOSE        =>    true,
		// CURLOPT_STDERR         =>    $f
	);
	$ch = curl_init($userToken);
	curl_setopt_array($ch,$pushOptions);
	$response = curl_exec($ch);
	$info = curl_getinfo( $ch );
	curl_close($ch);
	//fclose($f);
	return $info;
}



?>