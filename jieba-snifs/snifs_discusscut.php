<?php
$course_id = $_GET["course_id"];
$forum_id = $_GET["forum_id"];
$flag = '';
$jieba_lock_path = sys_get_temp_dir()."/jieba.lock";

if (is_file($jieba_lock_path)) {
	echo $jieba_lock_path;
	exit();
}

$jieb = fopen($jieba_lock_path, "w");

$enableEcho = true;

header("Content-Type:text/javascript; charset=utf-8");

include "../mod/forum/skunk_iframe/IPCS/db/function.php";//引入直接把sql查詢array化的function
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



//以下 IPCS用20200309和




function IPCS_POS($flag){

    include '/var/www/moodle/skunk_iframe/IPCS/db/function.php';

		$st = set_time();

    $POS_lock  = sys_get_temp_dir() . "/POS.lock";
    $POS = fopen($POS_lock, "w");

    //抓當前最新 討論區post_id
    $sql = "SELECT `post_id` FROM `all_posts` ORDER BY `post_id` DESC LIMIT 1";
		//"SELECT `post_id` FROM `all_posts` WHERE (`forum_id` =".$forum_id.")ORDER BY `post_id` DESC LIMIT 1"
    $result = sql_result($sql);
    $new_id = $result[0][0];
    //echo "new_id = $new_id<br>";
    //抓目前組合最新ID
    $sql = "SELECT `post_id` FROM `ipcs_pos_combination` ORDER BY `post_id` DESC LIMIT 1";
    $result = sql_result($sql);
    $old_id = $result[0][0];
    //echo "old_id = ".$old_id.".<br>";

    if($new_id!=$old_id){

        //初次進行計算，依照修課人數，人名、來源以及詞性進行組合
        if($old_id == ""){
            //取最新的貼文ID
            $sql = "SELECT post_id from all_posts WHERE forum_type = 'ipcs' ORDER BY post_id DESC LIMIT 1";
            $result = sql_result($sql);
            $max = $result[0][0];
            //echo "max = $max<br>";
            //取最舊的貼文ID
            $sql = "SELECT post_id from all_posts WHERE forum_type = 'ipcs' ORDER BY post_id ASC LIMIT 1";
            $result = sql_result($sql);
            $min = $result[0][0];
            //echo "min = $min<br>";
            process_POS($min,$max);
            echo "first POS done.";

        }else{
            //先前有計算過後，便只需要處理最新的貼文，以一則貼文為單位
            process_POS($old_id,$new_id);
            echo "POS done.";

        }
    }else{
        echo "not need to POS.";
    }

    $et = set_time();
    echo_time('set_POS_combination',$st,$et,$flag);
    save_spend_time('set_POS_combination.php',$st,$et,round($et-$st,4));
    unlink($POS_lock);

}


function process_POS($source_id_start,$source_id_end){

    //echo "process_POS<br>";
    //所有的形容詞與名詞
    $adj_list = array('a','ad','ag','an','b','z');
    $n_list = array('n','ng','nr','nrfg','nrt','ns','nt','nz');

    for ($i= ($source_id_start+1); $i <= $source_id_end; $i++) {//對於要處理的推文
				//依序sql抓結巴斷完詞去除停用字後的資料
        $sql = 'select id,tag,words,user_account,post_id,forum_id from ipcs_jiebacut_drop_stopwords where post_id ='.$i;
				//sql_result()是 mod\forum\skunk_iframe\IPCS\db\function.php 內的函數
				//sql_result()會自動連線資料庫=>下query=>轉成array=>切斷資料庫連線=>回傳array
        $result = sql_result($sql);//sql_result以array整理資料回傳
				for ($j = 0; $j<count($result); $j++ ){//對於斷詞後推文中的每個詞
					//echo 'index:'.$j.' 詞為:'.$result[$j][2].' 詞性:'.$result[$j][1].' 下一詞:'.$result[$j+1][2].' 詞性:'.$result[$j+1][1].' 下兩詞:'.$result[$j+2][2].' 詞性:'.$result[$j+2][1].' end</br>';

					if(in_array($result[$j][1],$n_list)){//若該詞為名詞
						if(($result[$j+1][0]=$result[$j][0]+1) and (in_array($result[$j+1][1],$adj_list))) {//若下個詞為形容詞 且 有在斷完詞去除停用字的資料表內
							//將詞性組合插入詞性組合資料表中
							$sql_insert_pos = "insert INTO ipcs_pos_combination (user_account, forum_id, post_id, n_words, adj_words) VALUES ('".$result[$j][3]."','".$result[$j][5]."','".$i."','".$result[$j][2]."','".$result[$j+1][2]."')";
							include '/var/www/html/mod/forum/skunk_iframe/IPCS/db/config.php';//include開資料庫
							$result = mysql_query($sql_insert_pos) or die (' Invalid query: '.mysql_error());//執行sql
							mysql_close($conn);//關資料庫
						}/*若下個詞非形容詞*/elseif (($result[$j+2][0]=$result[$j][0]+2) and (in_array($result[$j+2][1],$adj_list))) {//若下兩個詞為形容詞 且 有在斷完詞去除停用字的資料表內
							//將詞性組合插入詞性組合資料表中
							$sql_insert_pos = "insert INTO ipcs_pos_combination (user_account, forum_id, post_id, n_words, adj_words) VALUES ('".$result[$j][3]."','".$result[$j][5]."','".$i."','".$result[$j][2]."','".$result[$j+2][2]."')";
							include '/var/www/html/mod/forum/skunk_iframe/IPCS/db/config.php';//include開資料庫
							$result = mysql_query($sql_insert_pos) or die (' Invalid query: '.mysql_error());//執行sql
							mysql_close($conn);//關資料庫
						}
					}

				}


												}
}

function IPCS_diversity($time,$flag,$course_id,$forum_id){//多元度=相似差異度處理
    $al_s = set_time();

    $lock_path  = sys_get_temp_dir() . "/all.lock";
    $diversity_lock  = sys_get_temp_dir() . "/diversity.lock";
    $diversity = fopen($diversity_lock, "w");
    //先取得修課名單
		$sql = "select `user_account`,`user_name`,`group_name` FROM `course_groups_members_list` where (`course_id` = ".$course_id.")";

		//舊ipcs
    //$teacher = 'admin';
    //$sql = "SELECT username,firstname FROM `IPCS_enrol_user` WHERE username != '".$teacher."' order by username ASC";
    //echo "$sql<br>";


    $stuendt_list = sql_result($sql);

    $finger_print = array();

    $st = set_time();
    //依序取得每一位學生的資料
    foreach ($stuendt_list as $stuendt) {

        $one_s = set_time();
        $account = $stuendt[0];

        //取得新token
				//token指的是該學生常用詞前20，並統計其記數
        $sql = "SELECT `words`,`word_count` FROM `ipcs_personal_words_count` WHERE ((`course_id`='".$course_id."') AND (`forum_id`='".$forum_id."') AND (`user_account`='".$account."')) order by `word_count` DESC limit 20";
				//舊ipcs
				//$sql = "SELECT words,countof FROM IPCS_personal_data_count WHERE user_account = '".$account."' order by countof DESC limit 20";

				$new_tokens = sql_result($sql);

        $one_e2 = set_time();
        echo_time('get new/old sql',$one_s,$one_e2,$flag);
        //沒有舊的token資料

        //計算總hash值
        $s_hash = sum_hash($new_tokens);
        //echo "new s_hash<br>";


				$name_sql = "SELECT `user_name`, `group_name` FROM `course_groups_members_list` WHERE ((`user_account` = '".$account."')and(`course_id` = '".$course_id."'))";
        //$name_sql = "SELECT firstname,groupname From IPCS_enrol_user WHERE username = '".$account."'";
        $name_data = sql_result($name_sql);
        $st_name = $name_data[0][0];//字串化後的名稱
				$st_group = $name_data[0][1];//字串化後的組名
				//$st_group = substr($name_data[0][1],0,1);

        $finger_print[$account] = array(reduce_dimension($s_hash),$st_name,$st_group);//指紋

        $one_e = set_time();
        echo_time('single',$one_s,$one_e,$flag);
        //break;
    }

    $et = set_time();
    echo_time('save topN',$st,$et,$flag);
    //每人1秒，總共花費38秒

    foreach ($stuendt_list as $e) {

        //取得ego帳號
        $ego = $e[0];
        $ego_name = $e[1];
        //用來計算排序的smihash值
        $sim_value = array();

        //用來儲存的smihash陣列
        $sim_data = array();
        $s_v_n = 0;

        foreach ($stuendt_list as $a) {

            //取得alter帳號
            $alter = $a[0];

            if($ego==$alter){
                continue;
            }else{

                $simi = similarity($finger_print[$ego][0],$finger_print[$alter][0]);
                $sim_value[$s_v_n] = $simi;
                $sim_data[$s_v_n] = array($finger_print[$alter][1],$simi,$finger_print[$alter][2]);
                $s_v_n += 1;

            }
        }

        sort($sim_value);

        $sim_min = round($sim_value[0],2);
        $sim_max = round($sim_value[count($sim_value)-1],2);
        $sim_space = ($sim_max - $sim_min) / 3;
        $layer2 = $sim_min + $sim_space*1;
        $layer1 = $sim_min + $sim_space*2;

        for ($m=0; $m < count($sim_data); $m++) {

            //如果沒有資料，放最外圈
            if($sim_data[$m][1]==0){

                save_drawing_data($time,$ego,$ego_name,$sim_data[$m][0],1,$sim_data[$m][2]);

            //差異最小，最外圈
            }elseif($sim_data[$m][1]> 0 && $sim_data[$m][1]<$layer2){

                //echo "save_drawing_data<br>";
                save_drawing_data($time,$ego,$ego_name,$sim_data[$m][0],1,$sim_data[$m][2]);

            //差異中間，最中間
            }elseif ($sim_data[$m][1]>=$layer2 && $sim_data[$m][1]<$layer1) {

                save_drawing_data($time,$ego,$ego_name,$sim_data[$m][0],2,$sim_data[$m][2]);

            //差異最大，最內圈
            }elseif($sim_data[$m][1]>=$layer1){
                save_drawing_data($time,$ego,$ego_name,$sim_data[$m][0],3,$sim_data[$m][2]);
            }
            else{
                save_drawing_data($time,$ego,$ego_name,$sim_data[$m][0],1,$sim_data[$m][2]);
            }

        }

    }

    $et2 = set_time();
    echo_time('all learners diversity save',$et,$et2,$flag);

    $al_e = set_time();
    echo_time('diversity whole process',$al_s,$al_e,$flag);
    save_spend_time('set_all_diversity.php',$al_s,$al_e,round($al_e - $al_s,4));
    unlink($lock_path);
    unlink($diversity_lock);
    echo "diversity done.";

}

function save_drawing_data($time,$ego_account,$ego_name,$alter_name,$layer,$group){
		//echo 'save_drawing_data運作中<br>';
		include '/var/www/html/mod/forum/skunk_iframe/IPCS/db/config.php';
		//include './config.php';
    $sql = "INSERT INTO ipcs_drawing (drawing_time, ego_account, ego_name, alter_name, drawing_layer,drawing_group) VALUES ('".$time."','".$ego_account."','".$ego_name."','".$alter_name."','".$layer."','".$group."')";
		$result = mysql_query($sql) or die (' Invalid query: '.mysql_error());

    //關閉資料庫連線
    mysql_close($conn);

}

function calculate_hash($_token,$_weight){

    $hash = md5($_token);
    $hash_array = '';

    for ($i=0; $i < strlen($hash); $i++) {

        if(is_numeric($hash[$i])){

            if($hash[$i]>7){

                $hash_array = $hash_array.'1';
            }else{
                $hash_array = $hash_array.'0';
            }

        }else{

            $hash_array = $hash_array.'1';
        }
    }

    $hash_array_w = array();

    for ($i=0; $i <strlen($hash_array) ; $i++) {

        if($hash_array[$i]=='1'){
            $hash_array_w[$i] = $_weight;
        }else{
            $hash_array_w[$i] = -$_weight;
        }
    }
    return $hash_array_w;
}

function sum_hash($_all_data){//計算雜湊的函數

    //宣告總陣列的hash值，32個0
    $total_list = array();

    for ($i=0; $i <32 ; $i++) {
        $total_list[$i] = 0;
    }

    //把每個Token的陣列予以加總
    for ($i=0; $i < count($_all_data) ; $i++) {

        $data = $_all_data[$i];

        $pre_list = calculate_hash($data[0],$data[1]);

        for ($j=0; $j < count($total_list); $j++) {
            $total_list[$j] = $total_list[$j] + $pre_list[$j];
        }
    }

    return $total_list;
}

function reduce_dimension($total_list){

    //總陣列降維
    for ($i=0; $i < count($total_list); $i++) {

        if($total_list[$i]>0){
            $total_list[$i] = 1;
        }else{
            $total_list[$i] = 0;
        }
    }

    return $total_list;
}

function similarity($_sen1,$_sen2){

    $diff = 0;
    for ($i=0; $i < count($_sen1); $i++) {
        if($_sen1[$i]!=$_sen2[$i]){
            $diff += 1;
        }
    }

    $distance = $diff / count($_sen1);

    return round($distance,2);
}

//以上 IPCS用20200309和

mysql_close($conn);

$this_forum_type_sql = "SELECT `forum_type` from `forum_info` where (`forum_id`= $forum_id)";

$forum_type = sql_result($this_forum_type_sql);



if($forum_type[0]='ipcs'){
	IPCS_POS($flag);
	$time = time();
	IPCS_diversity($time,$flag,$course_id,$forum_id);
	unlink($jieba_lock_path);
	//echo 'doipcs';
	echo "true";

}else{

	unlink($jieba_lock_path);
	//echo 'noipcs';
	echo "true";
}
//echo "true";
?>
