$(function(){
	var vv = $(".block_course_menu").width();
	$(".block_course_menu").css({left:'-'+vv+'px'});
	
	var ww = 0;
	
	var url = $("#coursemenudiv").attr("imglink")+"/blocks/course_menu/css/";
	 
	$(".block_action.notitle").append("<img id='arr-01' src='"+url+"aarrow-1.png' height=100 width=100>");
	$(".block_action.notitle").append("<img id='arr-02' src='"+url+"aarrow-2.png' height=100 width=100>");
	$(".block_action.notitle #arr-02").hide();
			
	$(".block_action.notitle").click(function(){
		if (ww == 0)
		{
			$(".block_course_menu").animate({ left:"+="+vv }, 600 ,'swing');
			ww = 1;
			$(".block_action.notitle #arr-01").hide();
			$(".block_action.notitle #arr-02").show();
		} else {
			$(".block_course_menu").animate( { left:"-="+vv }, 600 ,'swing');
			ww = 0;
			$(".block_action.notitle #arr-02").hide();
			$(".block_action.notitle #arr-01").show();			
	  }
	});
});