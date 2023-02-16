$(function(){
	$(".filter").click(function () {
		$(".filterStats").slideDown("fast");
		$(".escFilter").fadeIn("slow");
		return false;
	});
	
	$(".escFilter").click(function () {
		$(".filterStats").slideUp("fast");
		$(".escFilter").fadeOut("fast");
		return false;
	});
	
	$("#all_none").click(function () {
		if($("#all_none").is(":checked")) {
			$(".photo_list").attr('checked', true);
		} else {
			$(".photo_list").attr('checked', false);
		}
	});
	
});
  
  