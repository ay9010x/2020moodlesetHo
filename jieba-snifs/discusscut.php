<?php
$enableEcho = true;


header("Content-Type:text/html; charset=utf-8");
ini_set('memory_set', '256M');

require_once "./jieba/src/class/Jieba.php";
require_once "./jieba/src/class/Finalseg.php";
require_once "./jieba/src/vendor/multi-array/MultiArray.php";
require_once "./jieba/src/vendor/multi-array/Factory/MultiArrayFactory.php";

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init();
Finalseg::init();


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
$sql_newid = "SELECT post_id AS source_id FROM discussion_snifs_all ORDER BY source_id DESC LIMIT 1";
$result_newid = mysql_query($sql_newid);
$row_newid = mysql_fetch_row($result_newid);

$new_id = $row_newid[0];

/*****************************************************************************************************************/
//資料庫連接 (Postgresql)
//$conn_string = "host=localhost port=5432 dbname=kals user=kals password=password";
//$conn_pg = pg_connect($conn_string);

//抓目前斷詞最新ID
$sql_old_anno_id = "SELECT source_id FROM jiebacut WHERE source LIKE 'disscussion%' ORDER BY source_id DESC LIMIT 1";
$result_old_anno_id = mysql_query($sql_old_anno_id);
$old_id_row = mysql_fetch_row($result_old_anno_id);

if($old_id_row == ""){
    //抓討論區資料
    $sql_hsuforum = "SELECT post_id AS source_id, account AS user_account, discussion_id AS source, message AS tocut FROM discussion_snifs_all ORDER BY source_id ASC";
    $result_hsuforum = mysql_query($sql_hsuforum); 
    $num_hsuforum = mysql_num_rows($result_hsuforum);

    for ($i = 0; $i < $num_hsuforum; $i++){
        $user_account = "user_account"."$i";
        $source = "source"."$i";
        $source_id = "source_id"."$i";
        $toCut = "toCut"."$i";
      
        while($row_hsuforum = mysql_fetch_row($result_hsuforum)){
            $$user_account = $row_hsuforum[1];
            echo $$user_account;
            switch ($row_hsuforum[2]){
                case "2":
                    $$source = "disscussion_D";
                    break;
                case "3":
                    $$source = "disscussion_A";
                    break;
                case "4":
                    $$source = "disscussion_B";
                    break;
                case "5":
                    $$source = "disscussion_C";
                    break;
                
            }
            $$source_id = $row_hsuforum[0];
            $$toCut = preg_replace('/\s/i','',$row_hsuforum[3]);
	
            //開始斷詞
            $seg_list = "seg_list"."$i";
            $$seg_list = Jieba::cut($$toCut);
			
            foreach ($$seg_list as $cutComplete){
                $sql_insert = "INSERT INTO jiebacut (user_account, source, source_id, words) VALUES ('".$$user_account."','".$$source."',".$$source_id.",'".$cutComplete."')";
                $result_insert = mysql_query($sql_insert);
            }
        }
    }
    echo "初次利用";
}else{
    $old_id = $old_id_row[0];

    if($new_id == $old_id){ 

		if ($enableEcho === true) {
        echo "沒有新資料";
		}
	}
	else{
        //抓新資料筆數
        $sql_hsuforum = "SELECT COUNT(post_id) FROM discussion_snifs_all WHERE post_id > $old_id ORDER BY post_id DESC";
        $result_hsuforum = mysql_query($sql_hsuforum); 
        $num_hsuforum = mysql_num_rows($result_hsuforum);
        
        //抓討論區資料
        $sql_hsuforum = "SELECT post_id AS source_id, account AS user_account, discussion_id AS source, message AS tocut FROM discussion_snifs_all WHERE post_id > $old_id ORDER BY source_id ASC";
        $result_hsuforum = mysql_query($sql_hsuforum);
        $num_hsuforum = mysql_num_rows($result_hsuforum);
        
        for ($i = 0; $i < $num_hsuforum; $i++){
            $user_account = "user_account"."$i";
            $source = "source"."$i";
            $source_id = "source_id"."$i";
            $toCut = "toCut"."$i";
            
            while($row_hsuforum = mysql_fetch_row($result_hsuforum)){
                $$user_account = $row_hsuforum[1];
                echo $$user_account;
                switch ($row_hsuforum[2]){
                    case "2":
                        $$source = "disscussion_D";
                        break;
                    case "3":
                        $$source = "disscussion_A";
                        break;
                    case "4":
                        $$source = "disscussion_B";
                        break;
                    case "5":
                        $$source = "disscussion_C";
                        break;
                   
                }
                $$source_id = $row_hsuforum[0];
                $$toCut = preg_replace('/\s/i','',$row_hsuforum[3]);
               
                //開始斷詞
                $seg_list = "seg_list"."$i";
                $$seg_list = Jieba::cut($$toCut);
             
                foreach ($$seg_list as $cutComplete){
                    $sql_insert = "INSERT INTO jiebacut (user_account, source, source_id, words) VALUES ('".$$user_account."','".$$source."',".$$source_id.",'".$cutComplete."')";
                    $result_insert = mysql_query($sql_insert);
                }
            }
        }
        if ($enableEcho === true) {
			echo "資料已更新";
		}
    }
}


/*****************************************************************************************************************/
mysql_close($conn);
//pg_close($conn_pg);
?>


	
