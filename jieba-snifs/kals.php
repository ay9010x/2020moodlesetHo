<?php
$enableEcho = false;

header("Content-Type:text/html; charset=utf-8");

//斷詞設定檔
ini_set('memory_set', '256M');

require_once "./jieba/src/class/Jieba.php";
require_once "./jieba/src/class/Finalseg.php";
require_once "./jieba/src/vendor/multi-array/MultiArray.php";
require_once "./jieba/src/vendor/multi-array/Factory/MultiArrayFactory.php";

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init();
Finalseg::init();

//資料庫連接 (Postgresql _ KALS)
$conn_string = "host=localhost port=5432 dbname=kals user=kals password=password";
$conn = pg_connect($conn_string);

if (!$conn) {
	die(' 連線失敗，輸出錯誤訊息 : ' . mysql_error());
}

//抓文章標註最新ID
$sql_new_anno_id = "SELECT annotation_id FROM annotation_kals_freewriting ORDER BY annotation_id DESC LIMIT 1";
$result_new_anno_id = pg_query($conn, $sql_new_anno_id);
$new_anno_id = pg_fetch_result($result_new_anno_id,0 ,0);

//抓目前斷詞最新ID
$sql_old_anno_id = "SELECT source_id FROM jiebacut WHERE source LIKE 'annotation%' ORDER BY source_id DESC LIMIT 1";
$result_old_anno_id = pg_query($conn, $sql_old_anno_id);
$old_id = pg_fetch_row($result_old_anno_id);

//初次使用
if($old_id == ""){
	if ($enableEcho === true) {
		echo "初次使用";
	}
    //抓全部資料筆數
    $sql_num = "SELECT COUNT(annotation) FROM annotation_kals_freewriting";
    $result_num = pg_query($conn, $sql_num);
    $num = pg_fetch_result($result_num, 0, 0);
    
    //逐筆斷詞
    for ($i = 0; $i < $num; $i++){
        $sql = "SELECT user_id, email, annotation_id, annotation, uri FROM annotation_kals_freewriting ORDER BY user_id ASC, annotation_id DESC LIMIT 1 OFFSET $i";
        $result = pg_query($conn, $sql);
        $user_id = pg_fetch_result($result,0 ,0);
        $user_mail = pg_fetch_result($result,0 ,1);
        $anno_id = pg_fetch_result($result,0 ,2);
        $annoToCut = pg_fetch_result($result,0 ,3);
        $anno_uri = pg_fetch_result($result,0 ,4);
        
        //User ID
        $uid = "uid"."$i";
        $$uid = $user_id;
        
        //User Mail
        $mail = "mail"."$i";
        $$mail = $user_mail;
        
        //標註id = source_id
        $annoID = "annoID"."$i";
        $$annoID = $anno_id;
        
        //uri = source
        $uri = "uri"."$i";
        //記人篇kals文章編號 2187 2192 2193    //記物篇kals文章編號 2195 2196 2197    //記景篇kals文章編號 2199 2200 2201
        switch ($anno_uri){
            case "2167":
                $$uri = "annotation_1_0";
                break;
            case "2187":
                $$uri = "annotation_1_1";
                break;
            case "2192":
                $$uri = "annotation_1_2";
                break;
            case "2193":
                $$uri = "annotation_1_3";
                break;
            case "2177":
                $$uri = "annotation_2_0";
                break;
            case "2195":
                $$uri = "annotation_2_1";
                break;
            case "2196":
                $$uri = "annotation_2_2";
                break;
            case "2197":
                $$uri = "annotation_2_3";
                break;
            case "2178":
                $$uri = "annotation_3_0";
                break;
            case "2199":
                $$uri = "annotation_3_1";
                break;
            case "2200":
                $$uri = "annotation_3_2";
                break;
            case "2201":
                $$uri = "annotation_3_3";
                break;
        }
        
        //要斷詞的標註內容
        $toCut = "toCut"."$i";
        $$toCut = preg_replace('/\s/i','',$annoToCut);
        
        //開始斷詞
        $seg_list = "seg_list"."$i";
        $$seg_list = Jieba::cut($$toCut);
        
        foreach ($$seg_list as $cutComplete){
            $sql_insert = "INSERT INTO jiebacut (user_email, source, source_id, words) VALUES ('".$$mail."','".$$uri."',".$$annoID.",'".$cutComplete."')";
            $result_insert = pg_query($conn, $sql_insert);
        }
    }
    //非初次使用
}else{
    $old_anno_id = $old_id[0];
    //兩邊資料一樣
    if($new_anno_id == $old_anno_id){ 
		if ($enableEcho === true) {
			echo "沒有新資料";
		}
        
    //有新資料
    }else if($new_anno_id > $old_anno_id){ 
		if ($enableEcho === true) {
			echo "資料已更新";
		}
        //抓資料筆數
        $sql_num = "SELECT COUNT(annotation) FROM annotation_kals_freewriting WHERE annotation_id > $old_anno_id";
        $result_num = pg_query($conn, $sql_num);
        $num = pg_fetch_result($result_num, 0, 0);
        
        //抓新資料逐筆斷詞
        for ($i = 0; $i < $num; $i++){
            $sql = "SELECT user_id, email, annotation_id, annotation, uri FROM annotation_kals_freewriting WHERE annotation_id > $old_anno_id ORDER BY user_id ASC, annotation_id DESC LIMIT 1 OFFSET $i";
            $result = pg_query($conn, $sql);
            $user_id = pg_fetch_result($result,0 ,0);
            $user_mail = pg_fetch_result($result,0 ,1);
            $anno_id = pg_fetch_result($result,0 ,2);
            $annoToCut = pg_fetch_result($result,0 ,3);
            $anno_uri = pg_fetch_result($result,0 ,4);
            
            //User ID
            $uid = "uid"."$i";
            $$uid = $user_id;
            
            //User Mail
            $mail = "mail"."$i";
            $$mail = $user_mail;
            
            //標註id
            $annoID = "annoID"."$i";
            $$annoID = $anno_id;
            
            //uri
            $uri = "uri"."$i";
            switch ($anno_uri){
                case "2167":
                    $$uri = "annotation_1_0";
                    break;
                case "2187":
                    $$uri = "annotation_1_1";
                    break;
                case "2192":
                    $$uri = "annotation_1_2";
                    break;
                case "2193":
                    $$uri = "annotation_1_3";
                    break;
                case "2177":
                    $$uri = "annotation_2_0";
                    break;
                case "2195":
                    $$uri = "annotation_2_1";
                    break;
                case "2196":
                    $$uri = "annotation_2_2";
                    break;
                case "2197":
                    $$uri = "annotation_2_3";
                    break;
                case "2178":
                    $$uri = "annotation_3_0";
                    break;
                case "2199":
                    $$uri = "annotation_3_1";
                    break;
                case "2200":
                    $$uri = "annotation_3_2";
                    break;
                case "2201":
                    $$uri = "annotation_3_3";
                    break;
            }
            
            //要斷詞的標註內容
            $toCut = "toCut"."$i";
            $$toCut = preg_replace('/\s/i','',$annoToCut);
            
            //開始斷詞
            $seg_list = "seg_list"."$i";
            $$seg_list = Jieba::cut($$toCut);
            
            foreach ($$seg_list as $cutComplete){
                $sql_insert = "INSERT INTO jiebacut (user_email, source, source_id, words) VALUES ('".$$mail."','".$$uri."',".$$annoID.",'".$cutComplete."')";
                $result_insert = pg_query($conn, $sql_insert);
            }
        }
    }
}

pg_close($conn);