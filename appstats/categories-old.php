<?php
$host = "mariadb-134.wc1.phx1.stabletransit.com";
$db = "2008150_custompl_db";
$user = "2008150_poptriv";
$passwd = "Cust0mPl@y!";


$mysql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $passwd);

$categories = array();
$catheaders = "";
$query = "SELECT * FROM challenge_categories WHERE 1 ORDER BY categoryid";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$query = "SELECT id FROM movies JOIN challenge_categories_mov ON movies.id = challenge_categories_mov.mid WHERE categoryid = $row[categoryid]";
	$stmt2 = $mysql->query($query);
	$count = $stmt2->rowCount();
	
	$catheaders .= '<th width="33"><div class="rotate">'. $row[abreviation] .' (<span class="count_'. $row[categoryid] .'" id="count_'. $row[categoryid] .'">'. $count .'</span>)</div></th>';
	$categories[] = $row[categoryid];
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow">
<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<title>Category Management Thingy</title>
<link rel="stylesheet" href="css/pe.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/tables.css" type="text/css" media="all" />
<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script type="text/javascript" src="js/tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $.tablesorter.defaults.sortList = [[1,0]]; 
    $("table").tablesorter();
	
	$('.update').click(function(event) {
		var myid = event.target.id;
		var categories = new Array();
		for(var i=0; i<23; i++) {
			var varname = "#checkbox" + myid + "_" + i;
			if($(varname).is(':checked')) {
				categories[i] = "on";
			} else {
				categories[i] = "off";
			}
		}
	
		var url = "ajax/update-categories.php";
		var params = {
			movieid: myid,
			streetdate: $("#streetdate" + myid).val(),
			categories: categories
		};
		$.ajax({
			type: 'POST',
			url: url,
			data: params,
			success: function(res) {
				alert(res);
			},
			error: function() { alert('Ooops... Something went wrong!'); }			
		});
	});
	
	$('.updateone').change(function(event) {
		var myid = event.target.id;
		var parts = myid.split(":");
		var movie = parts[0];
		var category = parts[1];
		var state = "off";
		var change = -1;
	
		if($(this).is(':checked')) {
			state = "on";
			change = 1;
		}
	
		var url = "ajax/update-category.php";
		var params = {
			movieid: movie,
			category: category,
			state: state
		};
		$.ajax({
			type: 'POST',
			url: url,
			data: params,
			success: function(res) {
				var varname = "#count_" + category;
				var varnamecl = ".count_" + category;
				var oldval = $(varname).text();
				var newval = parseInt(oldval) + change;
				/*alert(varname + " _ " + oldval + " _ " + newval);*/
				$(varnamecl).text("" + newval);
			},
			error: function() { alert('Ooops... Something went wrong!'); }			
		});
	});
});
function UpdateTableHeaders() {
   $(".persist-area").each(function() {
       var el             = $(this),
           offset         = el.offset(),
           scrollTop      = $(window).scrollTop(),
           floatingHeader = $(".floatingHeader", this)
       
       if ((scrollTop > offset.top) && (scrollTop < offset.top + el.height())) {
           floatingHeader.css({
            "visibility": "visible"
           });
       } else {
           floatingHeader.css({
            "visibility": "hidden"
           });      
       };
   });
}

// DOM Ready      
$(function() {

   var clonedHeaderRow;

   $(".persist-area").each(function() {
       clonedHeaderRow = $(".persist-header", this);
       clonedHeaderRow
         .before(clonedHeaderRow.clone())
         .css("width", clonedHeaderRow.width())
         .addClass("floatingHeader");
         
   });
   
   $(window)
    .scroll(UpdateTableHeaders)
    .trigger("scroll");
   
});
</script>
<style>
.floatingHeader {
	position: fixed;
	top: 0;
	visibility: hidden;
}
.rotate {
	width: 100%;
	text-align: center;
}
/*
.rotate {
	overflow: hidden;
    white-space: nowrap;
	-moz-transform: rotate(-90.0deg);
	-o-transform: rotate(-90.0deg);
	-webkit-transform: rotate(-90.0deg);
	filter: progid: DXImageTransform.Microsoft.BasicImage(rotation=0.083);
	-ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=0.083)";
	transform: rotate(-90.0deg);
	margin: 0 0 0 0px;
}*/
</style>
</head>
<body>
<div class="wrapper" style="width: 1800px;">
	<div class="header">
    	<h1 class="mainlogo">CustomPlay Dashboard</h1>
    </div>
    
    <div class="grad1 rounded module">
    
    <h3>Category Management Thingy</h3>
    <article class="persist-area">
    <table style="table-layout:fixed;" class="tablesorter" border="0" cellpadding="0" cellspacing="0">
        <thead class="persist-header">
            <tr>
                <th width="60">VidID</th>
                <th width="200">Title</th>
                <th width="60">Year</th>
                <?php echo $catheaders;?>
            </tr>
        </thead>
        <tbody class="zebra">
<?php

$query = "SELECT * FROM movies WHERE parentid = 0 ORDER BY title";
$stmt = $mysql->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$popcornactive = "NO";
	$dateparts = explode("-", $row[releasedate]);
	if(($row[popcornactive] == 1) || ($row[popcornbonus] > 0)) $popcornactive = "Active";
    echo '<tr><td>'. $row[id] .'</td><td style="white-space:nowrap; overflow:hidden;">'. $row[title] .'</td><td>'. $dateparts[0] .'</td>';
	$active = array();
	$subquery = "SELECT * FROM challenge_categories_mov WHERE mid = $row[id]";
	$stmt2 = $mysql->query($subquery);
	while($r = $stmt2->fetch(PDO::FETCH_ASSOC)) $active[] = $r[categoryid];
	
	foreach($categories as $category) {
		if(in_array($category, $active)) {
			echo '<td><span class="hidden">0</span><input class="updateone" type="checkbox" id="'. $row[id] .':'. $category .'" checked /></td>';
		} else {
			echo '<td><span class="hidden">1</span><input class="updateone" type="checkbox" id="'. $row[id] .':'. $category .'" /></td>';
		}
		
		/*
		if(in_array($category, $active)) {
			echo '<td><span class="hidden">0</span><input class="update" type="checkbox" id="checkbox'. $row[id] .'_'. $category .'" checked /></td>';
		} else {
			echo '<td><span class="hidden">1</span><input class="update" type="checkbox" id="checkbox'. $row[id] .'_'. $category .'" /></td>';
		}
		*/
	}
	
	echo '</tr>';
}
?>
		</tbody>
    </table>
    </article>
	</div>    
</div>
</body>
</html>