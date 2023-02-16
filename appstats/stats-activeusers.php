<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);
$now = time();

$datearray = 'var datearray = new Array(';
$oneDay = 3600 * 24;
$start = $now;

for($i=0; $i<140; $i++) {
	$thisDay = strtotime(date("F j, Y", $start)) - 3600 * 24;
	$datearray .= '"'. $thisDay .'", ';
	$start = $start - $oneDay;
}
$datearray = substr($datearray, 0, -2);
$datearray .= ');';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow">
<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<title>Data Management Thingy</title>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="css/pe.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/tables.css" type="text/css" media="all" />
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(function() {
    $("#datepicker").datepicker();
});

$(document).ready(function() {
	<?php echo $datearray;?>
	
	for(var i=0; i<datearray.length; i++) {
    	var day = datearray[i];
		var url = "ajax/calc-activeusers.php";
        var params = {
			request: "activity",
            day: day
        };
        $.ajax({
            type: 'POST',
            url: url,
            data: params,
            success: function(res) {
				var vals = res.split("-");
				var newid = vals[4] + "-new";
				var dayid = vals[4] + "-day";
				var weekid = vals[4] + "-week";
				var monthid = vals[4] + "-month";
				$('#' + newid).text(vals[0]);
				$('#' + dayid).text(vals[1]);
				$('#' + weekid).text(vals[2]);
				$('#' + monthid).text(vals[3]);
                //alert('Something happened! Yay! .... ' + res);
            },
            error: function() {
               alert('Ooops... Something went wrong!');
            }
        });
	}
});
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <div class="grad1 rounded module">
    	<div style="padding: 0 0 20px 0;"><strong><a href="/appstats/index.php">Home</a> / Activity Stats</strong></div>

        <div style="padding: 0 0 5px 0; text-align: right;">Last 75 days</div>
		<table class="tablesorter" border="0" cellpadding="0" cellspacing="0" style="width: 720px;">
            <thead>
            <tr>
                <th width="120">Date</th>
                <th width="150">Daily New</th>
                <th width="150">Daily Active</th>
                <th width="150">Weekly Active</th>
                <th width="150">Monthly Active</th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php
$start = $now;
for($i=0; $i<140; $i++) {
	$thisDay = strtotime(date("F j, Y", $start)) - 3600 * 24;
		
	if(date("D",$start) == "Mon") {
		$rowbg = "#288d00";
	} else {
		$rowbg = "#000000";
	}
	echo '<tr><td><span style="color:'. $rowbg .'; font-weight:bold;">'. date("m/d/Y", $thisDay) .'</span></td><td><span id="'. $thisDay .'-new"></span></td><td><span id="'. $thisDay .'-day"></span></td><td><span id="'. $thisDay .'-week"></span></td><td><span id="'. $thisDay .'-month"></span></td></tr>';
	
	$start = $start - $oneDay;
}
?>


		</tbody>
    	</table>
	</div>    
</div>
</body>
</html>