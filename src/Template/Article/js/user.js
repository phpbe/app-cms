

$(function(){
	$(".user-menu-bar li").click(function(){
		$(".user-menu-bar li").removeClass("active");
		$(this).addClass("active");
		
		$(".user-tab").hide();
		$(".user-tab-"+$(this).attr("data-content")).show();
	});
});