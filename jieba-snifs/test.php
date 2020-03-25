<?php

//資料庫連接 (Postgresql _ KALS)
$conn_string = "host=localhost port=5432 dbname=kals user=kals password=password";
$conn = pg_connect($conn_string);

if (!$conn) {
	die(' 連線失敗，輸出錯誤訊息 : ' . mysql_error());
}


//之後要改的地方，抓登入id跟文章
/*****************************/
$login_id = '104155004';
$class = 'annotation_1_0';
/*****************************/


//抓使用者 email
$sql = "SELECT email FROM public.user WHERE name = '$login_id' AND domain_id = '194'";
$result = pg_query($conn, $sql);
$email = pg_fetch_result($result,0 ,0);

//自己的文字雲
$sql_me = "SELECT words, COUNT(frequency)*25 FROM jieba_by_user WHERE user_email = '$email' AND source = '$class' GROUP BY words";
$result_me = pg_query($conn, $sql_me);

//類似的功能還有pg_fetch_row / pg_fetch_assoc

$me_all = array();
while($me_words = pg_fetch_array($result_me)){
    $me_all[] = $me_words;
}
$me_list = $me_all;

//全班文字雲
$sql_others = "SELECT words, COUNT(frequency)*25 FROM jieba_by_user WHERE source = '$class' GROUP BY words";
$result_others = pg_query($conn, $sql_others);

$others_all = array();
while($others_words = pg_fetch_array($result_others)){
    $others_all[] = $others_words;
}

$others_list = $others_all;

/***************************************************************************************************************/

function wordcloud_json_encode ($list) {
	$output = array();
	
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
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        
<canvas id="wordcloud_self" class="word_cloud" width="400" height="400"></canvas>
<canvas id="wordcloud_others" class="word_cloud" width="400" height="400"></canvas>

<script type="text/javascript" src="wordcloud2.js"></script>
<script type="text/javascript">
WordCloud.minFontSize = "15px"
WordCloud(document.getElementById('wordcloud_self'), { list: <?php echo wordcloud_json_encode($me_list); ?> } );
WordCloud(document.getElementById('wordcloud_others'), { list: <?php echo wordcloud_json_encode($others_list); ?> } );
</script>

    </body>
</html>
