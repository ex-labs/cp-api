<?php

$dbh = mysql_connect ("mysql51-063.wc1.ord1.stabletransit.com", "971786_db", "Cust0mP1ay#)") or die ('Cannot connect db: '. mysql_error());
mysql_select_db ("971786_db") or die('Cannot select db: ' . mysql_error());

$cpplayers = array();

$query = "SELECT uid, first_name, last_name, token FROM pt_multiusers WHERE player_cp = 1";
$result = mysql_query($query) or die ('error: '. mysql_error());
while($row = mysql_fetch_array($result)) {
	$data = array();
	$data[uid] = (string)$row[uid];
	$data[name] = trim(stripslashes($row[first_name])) ." ". stripslashes(substr(trim($row[last_name]), 0, 1)) .".";
	$data[token] = $row[token];
	$cpplayers[] = $data;
}

$query = "SELECT gid, bet, playerOne, uid, first_name, last_name, token FROM pt_games LEFT JOIN pt_multiusers ON pt_games.playerOne = pt_multiusers.uid WHERE challenge = 0 AND status = 0 AND player_cp = 0";
$result = mysql_query($query) or die ('error: '. mysql_error());
while($row = mysql_fetch_array($result)) {
	$random = mt_rand(0,count($cpplayers) - 1);
	$lucky = $cpplayers[$random];
	$now = time();
	
	$query = "UPDATE pt_games SET playerTwo = $lucky[uid], status = 1, timer = $now WHERE gid = $row[gid]";
	mysql_query($query) or die ('error: '. mysql_error());
	
	//MESSAGE TO REAL PLAYER
	$message = "Your game has started!";

	if(strpos($row[token], "https://") !== false) {
		$auth = authorizePushWindows();
		sendMessageWindows($auth,$row[token],$message);
	} else if(strlen($row[token]) == 64) {
		sendMessageApple($row[token], $message);
	} else {
		sendMessageAndroid($row[token], $message);
	}
	
	
	//MESSAGE TO CP PLAYER
	$message = "This is your captain speaking... Please fasten your seatbelt and play a game!";
	
	if(strpos($lucky[token], "https://") !== false) {
		$auth = authorizePushWindows();
		sendMessageWindows($auth,$lucky[token],$message);
	} else if(strlen($lucky[token]) == 64) {
		sendMessageApple($lucky[token], $message);
	} else {
		sendMessageAndroid($lucky[token], $message);
	}
	
}
    
mysql_close($dbh);
	
	
	
	
	
	
	
	
function sendMessageApple($token, $message) {
    $apnsHost = 'gateway.push.apple.com';
    $apnsPort = 2195;
    $apnsCert = 'popcorn-prod-cert2.pem';
  
    $payload['aps'] = array('alert' => $message , 'sound' => 'pushnotify.wav', 'badge' => 1);
    $payload = json_encode($payload);
    
    $streamContext = stream_context_create();
    stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
    $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
    
    $apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $token)) . chr(0) . chr(strlen($payload)) . $payload;
    fwrite($apns, $apnsMessage);
	
    fclose($apns);
}
    
function sendMessageAndroid($token, $message) {
    $apiKey = 'AIzaSyAoIDQCigW-Dd5e1qptKaSQ9s97j0Fb-IA';
    $registrationIDs = array( $token );
    $data = array( 'message' => urldecode($message) );
    
    $url = 'https://android.googleapis.com/gcm/send';
    $fields = array(
                    'registration_ids'  => $registrationIDs,
                    'data'              => $data,
                    );
    
    $headers = array(
                     'Authorization: key=' . $apiKey,
                     'Content-Type: application/json'
                     );
    
    // Open connection
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
    $result = curl_exec($ch);
    curl_close($ch);
}
	
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
		CURLOPT_HTTPHEADER     	  =>    $token_headers,
		CURLOPT_VERBOSE        =>    true,
		CURLOPT_STDERR         =>    $f
	);	

	$ch = curl_init();
	curl_setopt_array($ch,$tokenOptions);
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
	
	$pushOptions=array(
	    CURLOPT_POST           =>    true,
		CURLOPT_RETURNTRANSFER =>    true,
		CURLOPT_SSL_VERIFYPEER =>    false,
		CURLOPT_HTTPHEADER     =>    $headers,
		CURLOPT_POSTFIELDS     =>    "$toast",
		CURLOPT_SSL_VERIFYHOST =>    false
	);
	$ch = curl_init($userToken);
	curl_setopt_array($ch,$pushOptions);
	$response = curl_exec($ch);
	$info = curl_getinfo( $ch );
	curl_close($ch);
	return $info;
}	
?>