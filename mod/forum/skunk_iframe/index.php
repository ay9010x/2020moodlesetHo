<?php
require("php/my_functions.php");
check_user_logined();
$username = get_username_from_cookie();
if ($username === NULL) {
	$username = "您尚未登入";
}
else {
	$group = get_user_team($username);
	session_start();
	$_SESSION["discuss_user_name"] = $username;
	$_SESSION["discuss_group_name"] = $group;
	$_COOKIE["discuss_user_name"] = $username;
	$_COOKIE["discuss_group_name"] = $group;
	$name = $username.$group;
	$username = $username . " (" . $group . "組) 同學您好1111";
	echo "$username<br>";
}

?>


<!DOCTYPE html>
<html>

<head>
    <title>
        政大附中班級討論區
    </title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<link rel="shortcut icon" href="/favicon.ico"/>
	<link rel="bookmark" href="/favicon.ico"/>

    <!-- CSS Here -->
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" type="text/css" href="IPCS/css/semantic.min.css">

	<script src="lib/jquery-3.3.1.min.js" type="text/javascript"></script>
    <!--只跑一次的JS-->
    <!--GA-->
	<script src="/google-analytics/config/exp-ipcs-2019.js" type="text/javascript"></script>



</head>

<body>

<!--登入的使用者-->
<script type="text/javascript">

var ego_id = <?php echo json_encode($username); ?>;
var mode = <?php echo json_encode($_GET["mode"]); ?>;

//console.log(ego_id)幹你的2;

</script>

<!--最上方的選單-->
<div class="ui pointing stackable inverted menu" id="top_menu" style="border-bottom: 0px;">

	<!--system logo-->
	<div class="item">
		<h1 class="ui header" style="color:#FFFFFF">IPCS</h1>　即時觀點比較系統
	</div>

  <!--slide bar-->
  <a class="item" onclick="operate_modal('#reading_article_1','show');">
    <i class="big book icon"></i>
    閱讀教材A
  </a>
    <a class="item" onclick="operate_modal('#reading_article_2','show');">
    <i class="big book icon"></i>
    閱讀教材B
  </a>
    <a class="item" onclick="operate_modal('#reading_article_3','show');">
    <i class="big book icon"></i>
    閱讀教材C
  </a>
  <a class="item" onclick="operate_modal('#report_guide','show');">
    <i class="big edit outline icon"></i>
    報告格式
  </a>
   <a class="item" onclick="window.open('https://www.surveycake.com/s/4Ddn9');">
    <i class="big cloud upload icon"></i>
    繳交報告
  </a>
   <a class="item"  onclick="reload(mode);">
    <i class="big newspaper outline icon"></i>
    最新貼文
  </a>
  <!--2019.5.9 先取消
  <a class="item"  onclick="operate_modal('#watch','show');">
    <i class="big eye icon"></i>
    觀摩別組
  </a>
  -->


	<!--右上角的外部資源查找-->
	<div class="right menu" style="width: 250px;">
		<div class="item" id="external_search">
			<script>
			  (function() {
			    var cx = '001448559930653686977:qqcxxcb4tq0';
			    var gcse = document.createElement('script');
			    gcse.type = 'text/javascript';
			    gcse.async = true;
			    gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
			    var s = document.getElementsByTagName('script')[0];
			    s.parentNode.insertBefore(gcse, s);
			  })();

			</script>
			<gcse:search></gcse:search>
		</div>
	</div>
</div>



<!--主頁面-->
<div class="ui grid" id="mian_grid">

		<!--控制組或實驗組-->
	<?php
		if (!(isset($_GET["mode"]) && $_GET["mode"] === "ctl")) {
	?>

		<!--實驗組 左半部同心圓-->
		<div class="eight wide column">
			<div class="ui container" id="ipcs_tab">
				<iframe name="ipcs" class="frameBox" id="ipcs" src="IPCS/IPCS_drawing.php?name=<?=$name?>" scrolling="yes"></iframe>
			</div>
		</div>

		<!--實驗組 右半部討論區-->
		<div class="eight wide column"  id="moodle_page">

			<?php
				if($group=='A'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=21" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>

			<?php
				}elseif($group=='B'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=22" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>

			<?php
				}elseif($group=='C'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=23" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>

			<?php
				}elseif($group=='D'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=24" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}elseif($group=='E'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=25" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}elseif($group=='F'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=26" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}elseif($group=='G'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=27" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}else{
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/view.php?id=572" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}
		}else{
	?>
		<!--控制組 右半部討論區-->
		<div class="sixteen wide column"  id="moodle_page">
			<?php
				if($group=='A'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=21" style="width: 100vw;height: 90vh;" scrolling="yes"></iframe>

			<?php
				}elseif($group=='B'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=22" style="width: 100vw;height: 90vh;" scrolling="yes"></iframe>

			<?php
				}elseif($group=='C'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=23" style="width: 100vw;height: 90vh;" scrolling="yes"></iframe>

			<?php
				}elseif($group=='D'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=24" style="width: 100vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}elseif($group=='E'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=25" style="width: 100vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}elseif($group=='F'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=26" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}elseif($group=='G'){
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d=27" style="width: 49vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}else{
			?>
				<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/view.php?id=572" style="width: 100vw;height: 90vh;" scrolling="yes"></iframe>
			<?php
				}
		}
	?>

	</div>
</div>


<!--閱讀文章-->
<div class="ui modal" id="reading_article_1">
  <i class="close icon"></i>
 	 <?php echo file_get_contents("reading_article/reading_article_1.html") ?>
  <div class="actions">
    <div class="ui button" onclick="operate_modal('#reading_article_1','hide');">OK</div>
  </div>
</div>

<div class="ui modal" id="reading_article_2">
  <i class="close icon"></i>
 	 <?php echo file_get_contents("reading_article/reading_article_2.html") ?>
  <div class="actions">
    <div class="ui button" onclick="operate_modal('#reading_article_2','hide');">OK</div>
  </div>
</div>

<div class="ui modal" id="reading_article_3">
  <i class="close icon"></i>
 	 <?php echo file_get_contents("reading_article/reading_article_3.html") ?>
  <div class="actions">
    <div class="ui button" onclick="operate_modal('#reading_article_3','hide');">OK</div>
  </div>
</div>

<!--報告格式-->
<div class="ui modal" id="report_guide">
  <i class="close icon"></i>
	<?php echo file_get_contents("report_guide.html") ?>
  <div class="actions">
    <div class="ui button" onclick="operate_modal('#report_guide','hide');">OK</div>
  </div>
</div>

<!--注意事項-->
<div class="ui mini modal" id="watch">
  <i class="close icon"></i>
  <div class="header" align="center">
    請選擇您要觀摩的組別
  </div>

	<div class="ui container" style="padding: 3vh 3vw;">
			<button class="circular ui big icon button" onclick="watch_other_group(mode,21,'A');">A
			</button>
		 	<button class="circular ui big icon button" onclick="watch_other_group(mode,22,'B');">B
			</button>
		 	<button class="circular ui big icon button" onclick="watch_other_group(mode,23,'C');">C
			</button>
		 	<button class="circular ui big icon button" onclick="watch_other_group(mode,24,'D');">D
			</button>
		 	<button class="circular ui big icon button" onclick="watch_other_group(mode,25,'E');">E
			</button>
			 <button class="circular ui big icon button" onclick="watch_other_group(mode,26,'F');">F
			</button>
			<button class="circular ui big icon button" onclick="watch_other_group(mode,27,'G');">G
			</button>

	</div>

  <div class="actions">
    <div class="ui button" onclick="operate_modal('#watch','hide');">返回</div>
  </div>

 </div>



<!-- Script Here -->
<script src="IPCS/lib/semantic.min.js" type="text/javascript"></script>

</body>
</html>

<script type="text/javascript">

	$(window).on('load', function(){

		//防止使用者意外離開視窗
		$(window).bind('beforeunload', function() { return 'Are you sure ?';} );

		if(mode!='ctl'){

			//自動計時
			var c = -1;
			var t;
			var timer_is_on = 0;
			startCount();
		}else{
			console.log('nothing');
		}


	function timedCount() {

	  c = c + 1;
	  t = setTimeout(timedCount, 1000);
	  //console.log(c);
	  //到固定的時間就重新整理

	  if(c==10){
	  	c=0;
	  	//三個好像都得全班的
	  	set_jieba_diversity_pos();

	  }
	}

	function startCount() {
	  if (!timer_is_on) {
	    timer_is_on = 1;
	    timedCount();
	  }
	}

	function stopCount() {
	  clearTimeout(t);
	  timer_is_on = 0;
	}

	function set_jieba_diversity_pos() {

		console.log('start to set_jieba_diversity_pos.')

		var time = new Date().getTime() / 1000;
		var dic = '/skunk_iframe/IPCS/db/set_jieba_diversity_pos.php';
	   	var toPHP = '?time=' + time;

	   	jQuery.post(dic + toPHP ,function(result){
	   		console.log(result);
	   	})
	}

	});

	function reload(_mode){

		if(_mode!='ctl'){
			$('#ipcs').attr('src', $('#ipcs').attr('src'));
		}

		$('#discuss').attr('src', $('#discuss').attr('src'));

		//等iframe重新整理完後 才執行以下動作
		$('#discuss').on('load', function(){

			//var scrollHeight = $('#discuss').contents().scrollTop();
			var scrollHeight = $('#discuss').prop("scrollHeight");
			$('#discuss').contents().scrollTop(scrollHeight*100);

		})

		var _ga_content = ego_id + ": " + get_timestamp_d() + ": " + "Clicked on reload_btn to reload page" ;
		console.log(_ga_content);
		ga("send", 'event','single_click', _ga_content);
	}

		//operating for all of pop pages
	function operate_modal(_page,_operate) {

		$(_page)
		.modal({
			dimmerSettings: {

				onShow:function(){
					//GA 開啟IPCS上面的主分頁
					var _ga_content = ego_id + ": " + get_timestamp_d() + ": " + "Clicked on " + _page + " to show" ;
					console.log(_ga_content);
					ga("send", 'event','single_click', _ga_content);
				},
				onHide:function(){
					//GA 關閉IPCS上面的主分頁
					var _ga_content = ego_id + ": " + get_timestamp_d() + ": " + "Clicked on " + _page + " to hide" ;
					console.log(_ga_content);
					ga("send", 'event','single_click', _ga_content);
				}
			}
		  })
		.modal(_operate)
		;
	}





	function watch_other_group(mode,d,gruop){

		if(mode=='ctl'){
			_size = 100 ;
		}else{
			_size = 49 ;
		}

		var _iframe = '<iframe id="discuss" name="discuss" class="frameBox" src="/mod/hsuforum/discuss.php?d='+ d + '" style="width: ' + _size + 'vw; height: 90vh;" scrolling="yes"></iframe>';

		jQuery('#moodle_page').html(_iframe);
		operate_modal('#watch','hide');

		//GA 選擇其他組別進行觀摩
		var _ga_content = ego_id + ": " + get_timestamp_d() + ": " + "Clicked on " + gruop + " group to watch." ;
		console.log(_ga_content);
		ga("send", 'event','single_click', _ga_content);
	}

		//GA相關的函式庫
	function get_timestamp_d() {
	    var d = new Date;
	    d = [
	        d.getFullYear(),
	        (d.getMonth()+1).padLeft(),
	        d.getDate().padLeft(),
	        d.getHours().padLeft(),
	        d.getMinutes().padLeft(),
	        d.getSeconds().padLeft()
	        ].join('');
	    return d;
	}

</script>
