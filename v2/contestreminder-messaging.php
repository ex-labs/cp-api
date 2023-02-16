<?php
error_reporting(E_ERROR | E_PARSE);

$host = "64.91.249.141";
$db = "custompl_971786_db";
$user = "custompl_db";
$passwd = "Cust0mP1ay#)";

$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time() + 3600;


$query = "SELECT token, platform FROM pt_contestwinner LEFT JOIN pt_devices ON pt_contestwinner.uid = pt_devices.uid WHERE contest = 4 AND pt_contestwinner.timestamp = 0 AND length(token) > 0";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$message = "Claim your prize from our last contest and check out the new contest!";
	$auth = authorizePushWindows();
	
	if($row[platform] == 1) {
		sendAppleMessages($row[token], $message);
	}
	if($row[platform] == 2) {
		sendMessageAndroid($row[token], $message);
	}
	if($row[platform] == 3) {
		sendMessageWindows($auth,$row[token],$message);
	}
}



function sendAppleMessages($tokens, $message) {
	$apnsHost = 'gateway.push.apple.com';
    $apnsPort = 2195;
    $apnsCert = '/home/customplay/public_html/api.customplay.com/v2/popcorn-prod-cert2.pem';
	   
    $payload['aps'] = array('alert' => $message , 'sound' => 'pushnotify.wav', 'badge' => 0);
    $payload = json_encode($payload);
    
    $streamContext = stream_context_create();
    stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
    $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $streamContext);
	if (!$apns) {
		echo date("m/d/Y h:00:sA", time()) ." --- Apple: Failed to connect: $err $errstr\n";
	}
	stream_set_blocking($apns, 0);
	
	foreach($tokens as $token) {
		$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $token)) . chr(0) . chr(strlen($payload)) . $payload;
		fwrite($apns, $apnsMessage);
	}
	
    fclose($apns);
}

function sendMessageAndroid($tokens, $message) {
    $apiKey = 'AIzaSyAoIDQCigW-Dd5e1qptKaSQ9s97j0Fb-IA';
	
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
	//echo $result;
    curl_close($ch);
}


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
		CURLOPT_HTTPHEADER     	  =>    $token_headers
	);	

	$ch = curl_init();
	curl_setopt_array($ch,$tokenOptions);
	$response = curl_exec($ch);
	$json = json_decode($response,true);
	//echo date("m/d/Y h:00:sA", time()) ." --- Windows Auth: ". $response ."\n"; 
	
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
	
	//echo date("m/d/Y h:00:sA", time()) ." --- Windows Send: ". $response ." ----- ". $info ."\n"; 
	
	curl_close($ch);
	return $info;
}

?>