<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Messaging Page</title>
<link href="styles.css" rel="stylesheet" type="text/css" media="screen" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script type="text/javascript">
<?php
    $dbh = mysql_connect ("mysql51-063.wc1.ord1.stabletransit.com", "971786_db", "Cust0mP1ay#)") or die ('Cannot connect db: '. mysql_error());
    mysql_select_db ("971786_db") or die('Cannot select db: ' . mysql_error());
    
    $query = "SELECT token FROM pt_multiusers WHERE token != ''";
    $result = mysql_query($query) or die ('error: '. mysql_error());
    
    $tokensapple = 'var tokens = new Array(';
	$tokensdroid = 'var tokens = new Array(';
    while($row = mysql_fetch_array($result)) {
		if(strlen($row[token]) == 64) {
			$tokensapple .= '"'. $row[token] .'", ';
		} else {
			$tokensdroid .= '"'. $row[token] .'", ';
		}
    }
    $tokensapple = substr($tokensapple, 0, -2);
    $tokensapple .= ');';
	$tokensdroid = substr($tokensdroid, 0, -2);
    $tokensdroid .= ');';
    
    mysql_close($dbh);
?>
$(document).ready(function(){
    $('#send-iphone').click(function() {
        <?php echo $tokensapple;?>
        for(var i=0; i<tokens.length; i++) {
            var token = tokens[i];
            var url = "messaging-popcorn-send.php";
            var params = {
                device: "apple",
                token: token,
                message: $('#message').val()
            };
            $.ajax({
                type: 'POST',
                url: url,
                data: params,
                success: function(res) {
                   //alert('Something happened! Yay! .... ' + res);
                   $('#update-iphone').text(" sending... " + i);
                   $('#debug').val(res);
                },
                error: function() {
                   alert('Ooops... Something went wrong!');
                }
            });
        }
    });
                  
    $('#send-android').click(function() {
        <?php echo $tokensdroid;?>
        for(var i=0; i<tokens.length; i++) {
            var token = tokens[i];
            var url = "messaging-popcorn-send.php";
            var params = {
                device: "android",
                token: token,
                message: $('#message').val()
            };
            $.ajax({
                type: 'POST',
                url: url,
                data: params,
                success: function(res) {
                    $('#update-android').text(" sending... " + i);
                    $('#debug').val(res);
                },
                error: function() {
                   alert('Ooops... Something went wrong!');
                }
            });
        }
    });

});
</script>
</head>

<body>
<div class="colors">
    <div class="color-part color-1"></div>
    <div class="color-part color-2"></div>
    <div class="color-part color-3"></div>
    <div class="color-part color-4"></div>
    <div class="color-part color-5"></div>
    <div class="clear"></div>
</div>

<div class="clear"></div>

<div class="wrapper">
    <div class="site-header">
        <div class="site-header-content">
            <div class="site-header-left"><span><img src="http://www.customplay.com/images/page_logo.png" width="257" height="59" alt="CustomPlay - art within art" /></span></div>

            <div class="site-header-right site-header-right-m">
                <p>Coming Soon</p>
                <span><img src="http://www.customplay.com/images/btn_googleplay.png" width="139" height="43" alt="CustomPlay at Google play" /></span>
                <span><img src="http://www.customplay.com/images/btn_windows.png" width="139" height="43" alt="CustomPlay at the Windows Store" /></span>
                <div class="clear"></div>
            </div>

            <div class="site-header-right">
                <p>Available Now</p>
                <a href="https://itunes.apple.com/us/app/customplay/id1042734643?ls=1&mt=8"><span><img src="http://www.customplay.com/images/btn_appstore.png" width="139" height="43" alt="CustomPlay at the App Store" /></span></a>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
    </div>

    <div class="nav">
        <div class="nav-container">
            <ul>
                <li class="nav01"><a href="http://www.customplay.com/">Home</a></li>
                <li class="nav02"><a href="http://www.customplay.com/contact.php">Contact</a></li>
                <li class="nav03"><a href="http://www.customplay.com/about.php">About Us</a></li>
                <li class="nav04"><a href="#" class="slvj-link-lightbox 1" data-videoid="-yqi36MYSC4" data-videosite="youtube"><span>Watch Demo</span></a></li>
            </ul>
            <div class="clear"></div>
        </div>
    </div>


    <div style="width: 1000px; margin: 0 auto; padding: 50px;">

        <h2>Push Message Text</h2>
        <div style="padding: 0 0 30px 0;"><input type="text" id="message" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <h3 id="send-iphone">Send Message to all Apple Devices <span id="update-iphone" style="padding 0 0 0 20px; font-style: italic;"></span></h3>
        <h3 id="send-android">Send Message to all Android Devices <span id="update-android" style="padding 0 0 0 20px; font-style: italic;"></span></h3>

        <div id="debug" style="padding: 100px 0 100px 0;"></div>


    </div>

    <div class="clear"></div>




    <div class="site-footer footer-home">
        <div class="site-footer-left">
            <a href="http://www.facebook.com/CustomPlayLLC" target="_blank"><span><img src="http://www.customplay.com/images/icon_facebook.png" width="27" height="27" alt="CustomPlay on Facebook" /></span></a>
            <a href="http://www.twitter.com/CustomPlayApp" target="_blank"><span><img src="http://www.customplay.com/images/icon_twitter.png" width="27" height="27" alt="CustomPlay on Twitter" /></span></a>
            <a href="mailto:info@customplay.com"><span><img src="http://www.customplay.com/images/icon_email.png" width="27" height="27" alt="Email CustomPlay" /></span></a>
            <div class="clear"></div>
        </div>

        <div class="site-footer-center">
            <a href="http://www.customplay.com/terms-of-use.php">Terms Of Use</a> <span>|</span> <a href="http://www.customplay.com/privacy-policy.php">Privacy Policy</a> <span>|</span> <a href="applications-eula.php">Applications EULA</a>
        </div>

        <div class="site-footer-right">&copy; 2016 CustomPlay, LLC</div>
        <div class="clear"></div>
    </div>

</div>
</body>
</html>