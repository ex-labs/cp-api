<?php
    echo 'MY IP: '. $_SERVER['SERVER_ADDR'];
$token = "c3vWwHEqQTQ:APA91bGnJ13nI5s_sqizcShg1Om7Beug-pDtQGkqGYeoE15H5jkmma2cWdsFCF1rC0fF4gRNK7dI4_DiLeGntPsmAk05sCZu_3XIeUuWjvFV9oBMmzp0ie_c_-eN9cy_DKLFceId5WsD";
$message = "What's up, bitches???";
sendMessageDroid($token, $message);
    
    
function sendMessageDroid($token, $message) {
    //$apiKey = 'AIzaSyAyJk5PpLmIw-09Sv0UP_-yYKkEJ7O0Yhs';
    $apiKey = 'AIzaSyAoIDQCigW-Dd5e1qptKaSQ9s97j0Fb-IA';
    $registrationIDs = array( $token );
    $data = array( 	'message' => urldecode($message),
					'pn' => urldecode("23456")
					);
        
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
    echo 'Sending '. $message .' to '. $token .' and the result is: '. $result;
    echo "done";
}
?>