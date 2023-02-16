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
$(document).ready(function() {
    $.tablesorter.defaults.sortList = [[1,0]]; 
    $("table").tablesorter();
});
$(function() {
    $("#datepicker").datepicker();
});
</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <div class="grad1 rounded module">
    <h2>New User Acquisition</h2>
    <input type="text" id="datepicker">
    <table class="tablesorter" border="0" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th width="70">Video ID</th>
                <th>Title</th>
                <th width="100">CP Active</th>
                <th width="100">PT Active</th>
                <th width="200">Errors</th>
                <th width="150"></th>
            </tr>
        </thead>
        <tbody class="zebra">
<?php



?>
		</tbody>
    </table>
	</div>    
</div>
</body>
</html>