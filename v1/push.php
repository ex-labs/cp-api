<?php
    if($_POST[act] == 1) {
        sendMessageApple($_POST[token]);
        $message = "Message sent...";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon">


<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>

<div style="width: 400px; margin: 0 auto; padding: 100px 0 0 0;">
    <form action="push.php" method="post">
    <input type="hidden" name="act" value="1">
    <input type="text" name="token" style="width: 200px; padding: 3px;"> <input type="submit" value="Send Push">>
    </form>
    <div style="padding: 30px 0 0 0;"><?php echo $message;?></div>
</div>
</body>
</html>

<?php
    function sendMessageApple($token) {
        $apnsHost = 'gateway.sandbox.push.apple.com';
        //$apnsHost = 'gateway.push.apple.com';
        $apnsPort = 2195;
        $apnsCert = 'cert-dev.pem';
        
        $message = "Don't be alarmed... This is just a test!";
        
        $payload['aps'] = array('alert' => $message , 'sound' => 'pushnotify.wav', 'badge' => 0);
        $payload = json_encode($payload);
        
        
        $streamContext = stream_context_create();
        stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
        $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
        
        $apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $token)) . chr(0) . chr(strlen($payload)) . $payload;
        fwrite($apns, $apnsMessage);
        
        fclose($apns);
    }
?>