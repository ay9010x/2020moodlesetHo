<?php

$jieba_lock_path = sys_get_temp_dir()."/jieba.lock";

if (is_file($jieba_lock_path)) {
	echo $jieba_lock_path;
	exit();
}

$jieb = fopen($jieba_lock_path, "w");

$enableEcho = true;

header("Content-Type:text/javascript; charset=utf-8");


require_once "./jieba/src/class/Jieba.php";
require_once "./jieba/src/class/Finalseg.php";
//斷詞+詞性
require_once "./jieba/src/class/Posseg.php";
require_once "./jieba/src/vendor/multi-array/MultiArray.php";
require_once "./jieba/src/vendor/multi-array/Factory/MultiArrayFactory.php";


use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
//斷詞+詞性
use Fukuball\Jieba\Posseg;
Jieba::init();
Finalseg::init();
//斷詞+詞性
Posseg::init();

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

//抓當前最新 討論區post_id
$sql_newid = "SELECT post_id AS source_id FROM all_posts ORDER BY source_id DESC LIMIT 1";
$result_newid = mysql_query($sql_newid);
$row_newid = mysql_fetch_row($result_newid);

$new_id = $row_newid[0];


//抓目前斷詞最新ID
$sql_old_anno_id = "SELECT post_id FROM jiebacut ORDER BY id DESC LIMIT 1";
$result_old_anno_id = mysql_query($sql_old_anno_id);
$old_id_row = mysql_fetch_row($result_old_anno_id);


if($old_id_row == ""){
    //抓討論區資料
    $sql_forum = "SELECT post_id, forum_id, discussion_id, user_id, group_id , created_time, forum_type, message FROM all_posts ORDER BY post_id ASC";
    $result_forum = mysql_query($sql_forum);
    $num_forum = mysql_num_rows($result_forum);

    for ($i = 0; $row_forum = mysql_fetch_row($result_forum); $i++){
		
		$post_id = "post_id"."$i"; 
		$forum_id = "forum_id"."$i";
		$discussion_id = "discussion_id"."$i";
        $user_id = "user_id"."$i"; 
		$group_id = "group_id"."$i";
		$created_time = "created_time"."$i";
		$forum_type = "forum_type"."$i";
        $toCut = "toCut"."$i";//message

		$$post_id = $row_forum[0];
        $$forum_id = $row_forum[1];
		$$discussion_id = $row_forum[2];
		$$user_id = $row_forum[3];
		$$group_id = $row_forum[4];
		$$created_time = $row_forum[5];
		$$forum_type = $row_forum[6];
        $$toCut = strip_tags($row_forum[7]);//去標籤
		
		//斷詞
        $seg_list = "seg_list"."$i";
        $$seg_list = Posseg ::cut($$toCut);
		foreach ($$seg_list as $cutComplete){
                $sql_insert = "INSERT INTO jiebacut (post_id, forum_id, discussion_id, user_id, group_id, created_time, forum_type, words, tag) VALUES ('".$$post_id."','".$$forum_id."','".$$discussion_id."','".$$user_id."','".$$group_id."','".$$created_time."','".$$forum_type."','".$cutComplete['word']."','".$cutComplete['tag']."')";
				
                $result_insert = mysql_query($sql_insert);
        }
			
		/*print_r($$seg_list);
		echo $i;*/
	}
}else{
    $old_id = $old_id_row[0];

    if($new_id != $old_id){

        //抓討論區新增的資料
        $sql_forum = "SELECT post_id, forum_id, discussion_id, user_id, group_id , created_time, forum_type, message FROM all_posts WHERE post_id > $old_id ORDER BY post_id ASC";
        $result_forum = mysql_query($sql_forum);
        $num_forum = mysql_num_rows($result_forum);

        for ($i = 0; $row_forum = mysql_fetch_row($result_forum); $i++){
            $post_id = "post_id"."$i"; 
			$forum_id = "forum_id"."$i";
			$discussion_id = "discussion_id"."$i";
			$user_id = "user_id"."$i"; 
			$group_id = "group_id"."$i";
			$created_time = "created_time"."$i";
			$forum_type = "forum_type"."$i";
			$toCut = "toCut"."$i";//message

			$$post_id = $row_forum[0];
			$$forum_id = $row_forum[1];
			$$discussion_id = $row_forum[2];
			$$user_id = $row_forum[3];
			$$group_id = $row_forum[4];
			$$created_time = $row_forum[5];
			$$forum_type = $row_forum[6];
			$$toCut = strip_tags($row_forum[7]);//去標籤
			
			//斷詞
			$seg_list = "seg_list"."$i";
			$$seg_list = Posseg ::cut($$toCut);
			
			foreach ($$seg_list as $cutComplete){
                $sql_insert = "INSERT INTO jiebacut (post_id, forum_id, discussion_id, user_id, group_id, created_time, forum_type, words, tag) VALUES ('".$$post_id."','".$$forum_id."','".$$discussion_id."','".$$user_id."','".$$group_id."','".$$created_time."','".$$forum_type."','".$cutComplete['word']."','".$cutComplete['tag']."')";
                $result_insert = mysql_query($sql_insert);
			}
			
		}   
    }
}
		
		
        


mysql_close($conn);


unlink($jieba_lock_path);
echo "true";
