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
    
    $query = "SELECT DISTINCT token, platform FROM pt_devices WHERE token != '' AND platform = 1";
	$result = mysql_query($query) or die ('error: '. mysql_error());
	$tokensapple_count = mysql_num_rows($result);
	
	$query = "SELECT DISTINCT token, platform FROM pt_devices WHERE token != '' AND platform = 2";
	$result = mysql_query($query) or die ('error: '. mysql_error());
	$tokensdroid_count = mysql_num_rows($result);
	
	$query = "SELECT DISTINCT token, platform FROM pt_devices WHERE token != '' AND platform = 3";
	$result = mysql_query($query) or die ('error: '. mysql_error());
	$tokenswindows_count = mysql_num_rows($result);
    
    mysql_close($dbh);
?>
$(document).ready(function(){
    $('#send-iphone').click(function() {
		$('#update-iphone').text(" sending... ");
        var url = "messaging-popcorn-send.php";
        var params = {
            device: "apple",
            message: $('#message').val()
        };
        $.ajax({
            type: 'POST',
            url: url,
            data: params,
            success: function(res) {
               $('#update-iphone').text(" sent! " + res);
               $('#debug').val(res);
            },
            error: function() {
               alert('Ooops... Something went wrong!');
            }
        });
    });
	
	$('#send-android').click(function() {
		$('#update-android').text(" sending... ");
        var url = "messaging-popcorn-send.php";
        var params = {
            device: "android",
            message: $('#message').val()
        };
        $.ajax({
            type: 'POST',
            url: url,
            data: params,
            success: function(res) {
               $('#update-android').text(" sent! " + res);
               $('#debug').val(res);
            },
            error: function() {
               alert('Ooops... Something went wrong!');
            }
        });
    });
	
	$('#send-windows').click(function() {
		$('#update-windows').text(" sending... ");
        var url = "messaging-popcorn-send.php";
        var params = {
            device: "windows",
            message: $('#message').val()
        };
        $.ajax({
            type: 'POST',
            url: url,
            data: params,
            success: function(res) {
               $('#update-windows').text(" sent! " + res);
               $('#debug').val(res);
            },
            error: function() {
               alert('Ooops... Something went wrong!');
            }
        });
    });

});
</script>
</head>

<body>

<div class="wrapper">
    
    <div style="width: 1000px; margin: 0 auto; padding: 50px;">

        <h2>Push Message Text</h2>
        <div style="padding: 0 0 30px 0;"><input type="text" id="message" style="font-size: 14px; color: #666; padding: 5px; width: 500px;" /></div>
        <h3 id="send-iphone">Send Message to all <?php echo $tokensapple_count;?> Apple Devices <span id="update-iphone" style="padding 0 0 0 20px; font-style: italic;"></span></h3>
        <h3 id="send-android">Send Message to all <?php echo $tokensdroid_count;?> Android Devices <span id="update-android" style="padding 0 0 0 20px; font-style: italic;"></span></h3>
        <h3 id="send-windows">Send Message to all <?php echo $tokenswindows_count;?> Windows Devices <span id="update-windows" style="padding 0 0 0 20px; font-style: italic;"></span></h3>

        <div id="debug" style="padding: 100px 0 100px 0;"></div>


    </div>

    <div class="clear"></div>

</div>
</body>
</html>