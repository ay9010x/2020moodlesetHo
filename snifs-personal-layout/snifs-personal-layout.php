<!DOCTYPE html>
<?php

	require_once('../config.php');
    require_once('../mod/forum/lib.php');
    require_once($CFG->libdir.'/completionlib.php');
	require("php/my_functions.php");
	
	//資料庫連接 (MySQL _ Moodle)
	$dbhost = 'localhost:3306';
	$dbuser = 'root';
	$dbpass = 'la2391';
	$dbname = 'moodle';

	$conn = mysql_connect($dbhost, $dbuser, $dbpass) ; 
	mysql_query("SET NAMES 'UTF8'"); 
	mysql_select_db($dbname); 

	if (!$conn) {
		die(' 連線失敗，輸出錯誤訊息 : ' . mysql_error());
	}
	
$d = $_GET['d'];
$forum = $_GET['forum'];
$course = $_GET['course'];

?>


<html>
	<head>
		<meta charset="UTF-8">
			<!-- <script src="/iframe/jquery-3.3.1.min.js" type="text/javascript"></script> -->
			<script src="/snifs-personal-layout/assets/js/jQuery3.3.1.min.js"
					type="text/javascript"></script>

			<script src="semantic-ui/semantic.min.js"></script>
			<script src="../google-analytics/config/exp-snifs-2018.js" type="text/javascript"></script>
			<script src="lib/go.js" type="text/javascript"></script>
			<!-- <script src="lib/dragscroll.js" type="text/javascript"></script> -->
			<link rel="stylesheet" type="text/css" href="css/popup_container.css">
			<link rel="stylesheet" type="text/css" href="snifs-personal-layout.css">
				<link rel="stylesheet" type="text/css" href="semantic-ui/semantic.min.css">


					<title>SNIFS Graph</title>
				</head>

				<body>
<!--抓使用者帳號-->
<script type="text/javascript">
<?php
require('../config.php');
if (isset($USER) && isset($USER->username)) {
	$userid = $USER->username;
	?>
	userid = <?php echo json_encode($userid); ?>;
	<?php
}
if (isset($_SESSION["discuss_group_name"])) {
	?>
	userteam = "<?php echo $_SESSION["discuss_group_name"]; ?>";
	<?php
}

?>
layout = "<?php echo $_GET["layout"]; ?>";
dd=<?php echo $_GET["d"]; ?>;
forum=<?php echo $_GET["forum"]; ?>;
course=<?php echo $_GET["course"]; ?>;
</script>
	
	<!--判定抓個人或小組資料-->
					<script type="text/javascript">
						
					</script>

					<div class="loading"></div>
					<div class="brand-mask"></div>
					<div id ="myDiagramDivContainer">
						<div id ="myDiagramDiv"></div>
					</div>
					

					<div id ="inputEventsMsg">
						<table class="ui unstackable blue table" id="keyword_table" border="1" cellpadding="2" style="border-collapse: collapse;"></table>
					</div>

					<script src="snifs-personal-layout.js" type="text/javascript"></script>
					<script src="js/scroll_center.js" type="text/javascript"></script>
					
					<script src="js/auto_exec_jieba.js" type="text/javascript"></script>
<?php
if (isset($_GET["scoll_to_center"]) && $_GET["scoll_to_center"] === "true") {
	?>
	<script>
	scroll_to_center();
	</script>
	<?php
}
?>


<div class="popup-container" id="search_result">
	<div class="article-content">
		<span class="btnClose" onclick="$(this).parents('.popup-container:first').hide();$('.fullscreen-mask').hide();">&times;</span>
		<iframe name="search_discuss111" class="frameBox"></iframe>
	</div>
</div>

<div class="fullscreen-mask"></div>
				</body>
			</html>
			