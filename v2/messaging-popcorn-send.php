<?php
$dbh = mysql_connect ("mysql51-063.wc1.ord1.stabletransit.com", "971786_db", "Cust0mP1ay#)") or die ('Cannot connect db: '. mysql_error());
mysql_select_db ("971786_db") or die('Cannot select db: ' . mysql_error());


if($_POST[device] == "apple") {
	$query = "SELECT DISTINCT token, platform FROM pt_devices WHERE token != '' AND platform = 1";
	$result = mysql_query($query);
	
	while($row = mysql_fetch_array($result)) {
		sendAppleMessages($row[token], $_POST[message]);
	}
}

if($_POST[device] == "android") {
	$query = "SELECT DISTINCT token, platform FROM pt_devices WHERE token != '' AND platform = 2";
	$result = mysql_query($query);
	
	$count = 0;
	$batch = 1;
	$tokens = array();
	while($row = mysql_fetch_array($result)) {
		$tokens[] = $row[token];
		$count++;
		
		if($count == 500) {
			sendAndroidMessages($tokens, $_POST[message]);
			$tokens = array();
			$count = 0;
			echo " batch ". $batch .".";
			$batch++;
		}
	}
	if(count($tokens) > 0) {
		sendAndroidMessages($tokens, $_POST[message]);
	}
}
mysql_close($dbh);







function sendAppleMessages($token, $message) {
	$apnsHost = 'gateway.push.apple.com';
    $apnsPort = 2195;
    $apnsCert = 'popcorn-prod-cert2.pem';
	   
    $payload['aps'] = array('alert' => $message , 'sound' => 'pushnotify.wav', 'badge' => 0);
    $payload = json_encode($payload);
    
    $streamContext = stream_context_create();
    stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
    $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
    
	$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $token)) . chr(0) . chr(strlen($payload)) . $payload;
    fwrite($apns, $apnsMessage);
	
    fclose($apns);
}

function sendAndroidMessages($tokens, $message) {
    $data = array( 'message' => urldecode($message) );
    
    $url = 'https://android.googleapis.com/gcm/send';
    $fields = array(
                    'registration_ids'  => $tokens,
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

?>