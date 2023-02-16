<?php


if($_POST[token] && ($_POST[dev] == "apple")) {
	
	//$apnsHost = 'gateway.sandbox.push.apple.com';
    $apnsHost = 'gateway.push.apple.com';
    $apnsPort = 2195;
    $apnsCert = 'popcorn-prod-cert2.pem';
	
	$payload['aps'] = array('alert' => $_POST[message] , 'sound' => 'pushnotify.wav', 'badge' => 1);
    $payload = json_encode($payload);
	
	print_r($payload);
    
    $streamContext = stream_context_create();
    stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
    $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
	if(!$apns) {
		echo $errorString ." -----";
	} else {
		echo "NO ERROR";
	}
    
    $apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $_POST[token])) . chr(0) . chr(strlen($payload)) . $payload;
    fwrite($apns, $apnsMessage);
	    
    fclose($apns);
}

if($_POST[token] && ($_POST[dev] == "android")) {
    $apiKey = 'AIzaSyAoIDQCigW-Dd5e1qptKaSQ9s97j0Fb-IA';
    $registrationIDs = array( $_POST[token] );
    $data = array( 'message' => urldecode($_POST[message]) );
        
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
	
	echo 'RESULTS: '. $result;
}
if($_POST[token] && ($_POST[dev] == "windows")) {
	$auth = authorizePushWindows();
	sendMessageWindows($auth,$_POST[token],$message);
}

?>
    <div style="width: 1000px; margin: 0 auto; padding: 50px;">
        <form action="messaging-test.php" method="post">
        <input type="hidden" name="dev" value="apple">
        <h4>Push Message Text</h4>
        <div style="padding: 0 0 10px 0;"><input type="text" name="message" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <h4>Push Message Token</h4>
        <div style="padding: 0 0 10px 0;"><input type="text" name="token" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <div style="padding: 20px 0 0 0;"><input type="submit" value="Send Message to Apple Device" /></div>
        </form>

        <div id="debug" style="padding: 100px 0 100px 0;"><?php echo $debug;?></div>


    </div>
    
    <div style="width: 1000px; margin: 0 auto; padding: 50px;">
        <form action="messaging-test.php" method="post">
        <input type="hidden" name="dev" value="android">
        <h4>Push Message Text</h4>
        <div style="padding: 0 0 10px 0;"><input type="text" name="message" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <h4>Push Message Token</h4>
        <div style="padding: 0 0 10px 0;"><input type="text" name="token" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <div style="padding: 20px 0 0 0;"><input type="submit" value="Send Message to Android Device" /></div>
        </form>

        <div id="debug" style="padding: 100px 0 100px 0;"><?php echo $debug;?></div>


    </div>
    
    <div style="width: 1000px; margin: 0 auto; padding: 50px;">
        <form action="messaging-test.php" method="post">
        <input type="hidden" name="dev" value="windows">
        <h4>Push Message Text</h4>
        <div style="padding: 0 0 10px 0;"><input type="text" name="message" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <h4>Push Message Token</h4>
        <div style="padding: 0 0 10px 0;"><input type="text" name="token" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <div style="padding: 20px 0 0 0;"><input type="submit" value="Send Message to Windows Device" /></div>
        </form>

        <div id="debug" style="padding: 100px 0 100px 0;"><?php echo $debug;?></div>


    </div>
<?
function authorizePushWindows(){
	$apiKey = urlencode('Th3rMfOSumhq6Fq6uzDagUm');	
	$appId = urlencode('ms-app://s-1-15-2-981807083-30848534-1972440605-4155331106-985848221-2782754458-2623629205');
	$url = 'https://login.live.com/accesstoken.srf';
	$token_headers = array(
		'Content-Type'=>'application/x-www-form-urlencoded'
	);
	$token_body='grant_type=client_credentials&client_id='. $appId .'&client_secret=Th3rMfOSumhq6Fq6uzDagUm&scope=notify.windows.com';
	
	$tokenOptions = array(
		CURLOPT_POST              =>    true,
		CURLOPT_URL	              =>    $url,
		CURLOPT_RETURNTRANSFER    =>    true,
		CURLOPT_POSTFIELDS		  =>    $token_body,
		CURLOPT_HTTPHEADER     	  =>    $token_headers,
		CURLOPT_VERBOSE        =>    true
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