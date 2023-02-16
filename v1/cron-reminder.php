<?php
$dbh = mysql_connect ("mysql51-063.wc1.ord1.stabletransit.com", "971786_db", "Cust0mP1ay#)") or die ('Cannot connect db: '. mysql_error());
mysql_select_db ("971786_db") or die('Cannot select db: ' . mysql_error());
    
$now = time();
$timeto = $now - 3600 * 48;
$timefrom = $now - 3600 * 49;

//Look at results
$query = "SELECT gid, scoreOne, scoreTwo, status, status_updated, a.timestamp, timer, b.uid as p1uid, b.token as p1token, b.first_name as p1name, c.uid as p2uid, c.token as p2token, c.first_name as p2name FROM pt_games a LEFT JOIN pt_multiusers b ON a.playerOne = b.uid LEFT JOIN pt_multiusers c ON a.playerTwo = c.uid WHERE playerTwo > 0 AND (status = 3 OR status = 4) AND timer > $timefrom AND timer < $timeto";
$result = mysql_query($query) or die ('error: '. mysql_error());
while($row = mysql_fetch_array($result)) {
	if(($row[scoreOne] > 0) && (strlen($row[p1token]) > 0) && ($row[status_updated] != $row[p1uid])) {
		$message = "See your results of game with ". stripslashes($row[p2name]) .".";
		$token = $row[p1token];
		sendMessage($token, $message, $row[gid]);
	}
	if(($row[scoreTwo] > 0) && (strlen($row[p2token]) > 0) && ($row[status_updated] != $row[p2uid])) {
		$message = "See your results of game with ". stripslashes($row[p1name]) .".";
		$token = $row[p2token];
		sendMessage($token, $message, $row[gid]);
	}
}



$timeto = $now - 3600 * 24;
$timefrom = $now - 3600 * 25;

//Game is about to expire
$query = "SELECT scoreOne, scoreTwo, status, a.timestamp, timer, b.token as p1token, b.first_name as p1name, c.token as p2token, c.first_name as p2name FROM pt_games a LEFT JOIN pt_multiusers b ON a.playerOne = b.uid LEFT JOIN pt_multiusers c ON a.playerTwo = c.uid WHERE (scoreOne = 0 OR scoreTwo = 0) AND playerTwo > 0 AND (status = 1 OR status = 2) AND timer > $timefrom AND timer < $timeto";
$result = mysql_query($query) or die ('error: '. mysql_error());
while($row = mysql_fetch_array($result)) {
	if(($row[scoreOne] == 0) && (strlen($row[p1token]) > 0)) {
		$message = "Your game with ". stripslashes($row[p2name]) ." is about to expire.";
		$token = $row[p1token];
		sendMessage($token, $message, $row[gid]);
	}
	if(($row[scoreTwo] == 0) && (strlen($row[p2token]) > 0)) {
		$message = "Your game with ". stripslashes($row[p1name]) ." is about to expire.";
		$token = $row[p2token];
		sendMessage($token, $message, $row[gid]);
	}
}


	
mysql_close($dbh);
	
	
	
	
	
	
function sendMessage($token, $message, $gid) {
	if(strpos($token, "https://") !== false) {
		$auth = authorizePushWindows();
		sendMessageWindows($auth, $token, $message);
	} else if(strlen($row[token]) == 64) {
		sendMessageApple($token, $message);
	} else {
		sendMessageAndroid($token, $message);
	}
}
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