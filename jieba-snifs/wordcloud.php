<?php
// 設定檔
$include_test_article = true;
$display_sql = false;

$minSizeLimit = 20;
$enableReload = true;

// ---------------------------------

$course = 'annotation_1_';	// $_GET["course"]


if (isset($_GET["course"])) {
	$course = $_GET["course"];
}

$cookieNeedle = "load_".$course;
if (substr($course, 0, 4) === "anno") {
	// 簡易快取功能
	if (!isset($_COOKIE[$cookieNeedle])) {
		include("kals.php");
		setcookie($cookieNeedle, "true", time() + (60*30), "/");
	}
	
} else if (substr($course, 0, 4) === "diss") {
	if (substr($_GET["course"], -1) === "4") {
		include("discuss.php");
	}
	else if (!isset($_COOKIE[$cookieNeedle])) {
		include("discuss.php");
		setcookie($cookieNeedle, "true", time() + (60*30), "/");
	}
}


// ---------------------------------

//可以不斷重新載入標註kals.php與討論區討論、自由寫discuss.php
//include("discuss.php");
//header("Refresh:0; url=wordcloud.php");


// ---------------------------------------------------------

//資料庫連接 (Postgresql _ KALS)
$conn_string = "host=localhost port=5432 dbname=kals user=kals password=password";
$conn = pg_connect($conn_string);

if (!$conn) {
	die(' 連線失敗，輸出錯誤訊息 : ' . postgresql_error());
}

//之後要改的地方，抓登入id跟文章
/*****************************/
/*
$login_id = '104155004'; 	
*/
//require_once("/var/www/moodle/config.php");
$user_email = NULL;
if (isset($_COOKIE["moodle_user"])) {
	$user_email = urldecode( $_COOKIE["moodle_user"] );
}
//$login_id = substr($_COOKIE["key"], strpos($_COOKIE["key"],"1"), strpos($_COOKIE["key"],".")); //32, -15*/
//echo $login_id;
//exit;

/*****************************/

$where = "source = '" . $course . "'" ;
if (substr($course, -2) !== "_4") {
	$where = array();
	$i = 1;
	if ($include_test_article === true) {
		$i = 0;
	}
	for ($i; $i < 4; $i++) {
		$where[] = "source = '" . $course . $i . "'";
	}
	$where = implode(" or ", $where);
	$where = "(" . $where . ")";	
}

/*
echo $_GET["d"]; 
*/


//抓使用者 email
//$sql = "SELECT email FROM public.user WHERE name = '" . $user_email . "' AND domain_id = '194'";
//抓使用者email 課程是閱讀標註 annotation_1_1/_1_2/_1_3
/*$sql = "select * from jieba_by_user where user_email= '105155002@nccu.edu.tw'";*/
//$result = pg_query($conn, $sql);
//$email = pg_fetch_result($result,0 ,0);
//尚未設計"及時抓取資料的動作"去繪製文字雲

$minSizeLimit = 20;
$maxSizeLimit = 40;

// -----------------------------------------------
// , COUNT(frequency) AS count 


//自己的文字雲
$sql_me = "
SELECT words, rank() over (order by freq ASC) as count
FROM
(
SELECT words
, COUNT(frequency) as freq
FROM jieba_by_user 
WHERE user_email = '" . $user_email ."' AND " . $where . " AND char_length(words) > 3
GROUP BY words
ORDER By freq DESC
LIMIT 50
) as a";


if ($display_sql === true) {
	echo "<div>" . $sql_me . "</div>";
}

$result_me = pg_query($conn, $sql_me);

$me_list = array();
$minSize = null;
$maxSize = null;
while($me_words = pg_fetch_array($result_me)){
    $me_list[] = $me_words;
	$size = $me_words[1];
	if ($minSize === null) {
		$minSize = $size;
		$maxSize = $size;
	}
	if ($size < $minSize) {
		$minSize = $size;
	}
	if ($size > $maxSize) {
		$maxSize = $size;
	}
	
}

// a 3  30
// b 2  20
// c 1  10

// a 300 30
// b 200 20
// c 100 10

//$minSize 	//1
//$minSizeLimit //10
$buff = 1;
if ($minSize > 0) {
	$buff = $minSizeLimit / $minSize;
}


for ($i = 0; $i < count($me_list); $i++) {
	//$me_list[$i][1] = $me_list[$i][1] * $buff;
	if (($maxSize - $minSize) > 0) {
		$me_list[$i][1] = ( ($me_list[$i][1] - $minSize) / ($maxSize - $minSize) )*$minSizeLimit + $minSizeLimit;
	}
	else {
		//echo $buff . "-" . $minSize . "_" . $me_list[$i][1] . "^" . ($me_list[$i][1] * $buff) .  " ";
		
		$me_list[$i][1] = $me_list[$i][1] * $buff;
	}
}

// -----------------------------------------------

/*
$me_list = array();
$minSize = null;
while($me_words = pg_fetch_array($result_me)){
    $me_list[] = $me_words;
	$size = $me_words[1];
	if ($minSize === null) {
		$minSize = $size;
	}
	if ($size < $minSize) {
		$minSize = $size;
	}
}
*/
// , COUNT(frequency) AS count 
//全班文字雲
$sql_others = "
SELECT words, rank() over (order by freq ASC) as count
FROM
(
SELECT words
, COUNT(frequency) as freq
FROM jieba_by_user 
WHERE " . $where . " AND user_email <> '" . $user_email ."' AND char_length(words) > 3
GROUP BY words
ORDER By freq DESC
LIMIT 50
) as a";
//echo $sql_others;
$result_others = pg_query($conn, $sql_others);


if ($display_sql === true) {
	echo "<div>" . $sql_others . "</div>";
}


$others_list = array();
$minSize = null ;
$maxSize = null;
while($others_words = pg_fetch_array($result_others)){
    $others_list[] = $others_words;
	$size = $others_words[1] * 1;
	if ($minSize === null) {
		$minSize = $size;
		$maxSize = $size;
	}
	if ($size < $minSize) {
		$minSize = $size;
	}
	if ($size > $maxSize) {
		$maxSize = $size;
	}
}

//$buff = $minSizeLimit / $minSize;
$buff = 1;
if ($minSize > 0) {
	// 40 20
	// 10 7100
	
	$buff = $minSizeLimit / $minSize;
}

for ($i = 0; $i < count($others_list); $i++) {
	//$others_list[$i][1] = $others_list[$i][1] * $buff;
	if (($maxSize - $minSize) > 0) {
		$others_list[$i][1] = ( ($others_list[$i][1] - $minSize) / ($maxSize - $minSize) )*$minSizeLimit + $minSizeLimit;
	}
	else {
		$others_list[$i][1] = $others_list[$i][1] * $buff;
	}
 }

/***************************************************************************************************************/

function wordcloud_json_encode ($list) {
	$output = array();
	
	//$list[0][1] = $list[0][1]+ 20; 
	
	foreach ($list AS $l) {
		$output[] = '["' . $l[0] . '", ' . $l[1] . ']';
	}
	
	$output = "[" . implode(",", $output) . "]";
	
	return $output;
}

pg_close($conn);

?>
<!DOCTYPE html>
<html>
　<head>
　<title>Wordcloud</title> 
　</head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style type="text/css">
body {
	background-color: white;
}

div.group {
	text-align: center;
}

div.group:nth-of-type(1) {
	padding-bottom: 0.2rem;
	border-bottom: 1px solid gray;
	margin-bottom: 0.2rem;
}

div.group .label {
	font-weight: bold;
	font-size: 1rem;
	margin: 0;
	background-color: yellow;
    width: 50%;
    margin: 0 auto;
    border-radius: 10px;
    padding: 2px;
}
</style>
		
		</head>
    <body>


	
<div class="group">
	<h1 class="label">個人的文字雲</h1>
	<canvas id="wordcloud_self" class="word_cloud" width="310" height="210"></canvas>
</div>	

<div class="group">
	<h1 class="label">全班的文字雲</h1>	
	<canvas id="wordcloud_others" class="word_cloud" width="310" height="210"></canvas>
</div>

<script type="text/javascript" src="wordcloud2.js"></script>
<script type="text/javascript">
// 
WordCloud.minFontSize = "15px";

var _options = [];

_options.push({
	"list": <?php echo wordcloud_json_encode($me_list); ?>
});

_options.push({
	"list": <?php echo wordcloud_json_encode($others_list); ?>
});

for (var _i = 0; _i < _options.length; _i++) {
	_options[_i].rotateRatio = 0;
}

WordCloud(document.getElementById('wordcloud_self'), _options[0] );
WordCloud(document.getElementById('wordcloud_others'), _options[1] );

// ------------------------
// 重新讀取

// 等待5秒之後
	// 重新載入網頁 location.reload();

setTimeout(function () {
	//alert("準備重新載入");
	<?php
	if ($enableReload === true && substr($_GET["course"], -1) === "4") {
		?>
		window.location.reload();
		<?php
	}
	?>
	
},5000);
</script>

</script>
    </body>
</html>
