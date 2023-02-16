<?php
$now = time();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow">
<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<title>Data Management Thingy</title>
<link rel="stylesheet" href="css/pe.css" type="text/css" />
</head>
<body>
<div class="wrapper">
	<form action="dashboard.php" method="post">
    	<div class="loginbox grad1 rounded">
        <h1 class="mainlogo">CustomPlay Dashboard</h1>
        	<h2>PopcornTrivia App Statistics</h2>
            <h3><a href="stats-newusers.php">New User Acquisition</a></h3>
            <h3><a href="stats-singleplayer.php">Single Player Stats</a></h3>
            <h3><a href="stats-multiplayer.php">Multi-Player Stats</a></h3>
            <h3><a href="stats-multiplayer-categories.php">Multi-Player Category Stats</a></h3>
            <h3><a href="stats-activeusers.php">Active User Stats</a></h3>
            <h3><a href="stats-ingame.php">Ingame Click Stats</a></h3>
            <h3><a href="stats-movies.php">Movie Stats</a></h3>
            <h3><a href="stats-sponsor.php">Sponsor/Vote Stats</a></h3>
			<h3><a href="stats-costs.php">Cost Stats</a></h3>
            <h3><a href="stats-sales.php">Sales Stats</a></h3>
            <p></p>
            <h3><a href="categories.php">Category Manager</a></h3>
            <p></p>
            <h3><a href="messaging.php">Push Message Tool</a></h3>
            <p></p>
            <h3><a href="vote-excluded.php">Excluded Movies from Voting</a></h3>
            <p></p>
            <h3><a href="superfan.php">SuperFan Resources</a></h3>
            <p></p>
            <p></p>
            <p></p>
            <h4>Currect Timestamp: <?php echo $now;?></h4>
            <h4>Current Date & Time: <?php echo date("m/d/Y h:i:sA", $now);?></h4>
         	<p></p>
        </div>
	</form>
</div>

</body>
</html>