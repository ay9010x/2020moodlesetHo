<?php
include '../db/config.php';
include '../db/customer_function.php';
include '../php/my_functions.php';
//include './file_cache/filecache.php';

$d = $_POST['d'];
$forum = $_POST['forum'];//接討論串參數，用來找文本(詞彙節點)
$course = $_POST['course'];//接課程參數，用來找組別
//$course = 7;


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

/**
* @param string $path 缓存文件存放路径
* @param int $max_path 缓存的最大目录数
* @param int $max_file 缓存最大文件数
* @param int $gc_probality 执行set时遍历缓存以删除过期缓存数据操作的执行概率 百万分之 *
*/


//$cache = new FileCache($path = "/dev/shm");
$value_whole_group_data = null;
$whole_group_data = null;
$key_whole_group_data = "group_data";
$expired = 180;
// --------------------------


/*$value_whole_group_data = $cache->get($key_whole_group_data);

$key_lock = "key_group_lock";



if (null === $value_whole_group_data) {
    if (null === $cache->get($key_lock)) {
        $cache->set($key_lock, "true", $expired);
    }
    else {
        sleep(rand(20,30));
        $value_whole_group_data = $cache->get($key_whole_group_data);
        while (null === $value_whole_group_data && null !== $cache->get($key_lock)) {
            sleep(rand(10,20));
            $value_whole_group_data = $cache->get($key_whole_group_data);
        }
    }
}*/

//------------------------------------

// ----------------------------------
// group_node_person.php
function get_node_person($course) {
	/***************************************************/
    //組節點
		//靠杯喔名字是person實際上是找組別group喔幹 by廢物和20200225
	    $sql_node_person = "select group_name from group_info where course_id=".$course." order by group_name;";

    $result_node_person = mysql_query($sql_node_person) or die ('Invalid query sql_node_person: '.$sql_node_person);
    $num_node_person = mysql_num_rows($result_node_person);
    for($i = 0; $i<$num_node_person; $i++)
    {
        $row_node_person[$i] = mysql_fetch_row($result_node_person);
    }

    /***************************************************/
    //data transmit
    $data_node_person = url_encode($row_node_person);
    //$data_node_person = json_encode($data_node_person);
	return $data_node_person;
}

// ------------------------

function get_node_inwords($forum) {
	$sql_node_inwords = "select words, wordteam, Total_population,forum_id from  snifs_g_node_inwords where forum_id =".$forum." limit 15";
	$result_node_inwords = mysql_query($sql_node_inwords) or die ('Invalid query: '.mysql_error());
	$num_node_inwords = mysql_num_rows($result_node_inwords);
	for($i = 0; $i<$num_node_inwords; $i++) {
		$row_node_inwords[$i] = mysql_fetch_row($result_node_inwords);
	}
	$data_node_inwords = url_encode($row_node_inwords);
	return $data_node_inwords;
}

/**
 * 圈外詞節點(一組最多5個)
 */
function get_node_outwords($forum) {
	//圈外詞節點(一組最多5個)
	$sql_node_outwords = "SELECT words, group_name, word_count,user_account, row_numbers, forum_id
	FROM (
		  SELECT  @row_numbers := IF(@var_group_name = group_name, @row_numbers + 1, 1) AS row_numbers, @var_group_name := group_name as group_name, user_account,  words, word_count,forum_id
		  FROM
			  (SELECT @row_numbers := 1) x,
			  (SELECT forum_id, word_count, words, user_account, @var_group_name := group_name as group_name FROM snifs_g_node_outwords ORDER BY group_name, words desc) y
		) z
	WHERE row_numbers <= 5 AND forum_id=".$forum.";";
	$result_node_outwords = mysql_query($sql_node_outwords) or die ('Invalid query: '.mysql_error());
	$num_node_outwords = mysql_num_rows($result_node_outwords);

	for($i = 0; $i<$num_node_outwords; $i++) {
		$row_node_outwords[$i] = mysql_fetch_row($result_node_outwords);
	}
	$data_node_outwords = url_encode($row_node_outwords);
	return $data_node_outwords;
}

// ------------------------

/**
 * 圈內詞連線
 */
function get_link_inwords($forum) {
	$sql_link_inwords = "SELECT user_account, group_name, words, forum_id
	FROM snifs_g_link_inwords WHERE forum_id=".$forum.";";
	$result_link_inwords = mysql_query($sql_link_inwords) or die ('Invalid query: '.mysql_error());
	$num_link_inwords = mysql_num_rows($result_link_inwords);
	for($i = 0; $i<$num_link_inwords; $i++) {
		$row_link_inwords[$i] = mysql_fetch_row($result_link_inwords);
	}
	$data_link_inwords = url_encode($row_link_inwords);
	return $data_link_inwords;
}

// ------------------------

/**
 * 圈外詞連線(一組最多5個)
 */
function get_link_outwords($forum) {
	$sql_link_outwords = "SELECT word_count, group_name, words ,user_account, row_numbers,forum_id
	FROM (
		  SELECT  @row_numbers := IF(@var_team = group_name, @row_numbers + 1, 1) AS row_numbers, @var_team := group_name as group_name, user_account,  words, word_count, forum_id
		  FROM
			  (SELECT @row_numbers := 1) x,
			  (SELECT forum_id, word_count, words, user_account, @var_team := group_name as group_name FROM snifs_g_link_outwords ORDER BY group_name, words desc) y
		) z
	WHERE row_numbers <= 5 AND forum_id=".$forum.";";
	$result_link_outwords = mysql_query($sql_link_outwords) or die ('Invalid query: '.mysql_error());
	$num_link_outwords = mysql_num_rows($result_link_outwords);
	for($i = 0; $i<$num_link_outwords; $i++) {
		$row_link_outwords[$i] = mysql_fetch_row($result_link_outwords);
	}
	$data_link_outwords = url_encode($row_link_outwords);
	return $data_link_outwords;
}

function get_self_inwords($userteam, $forum) {
	$sql_self_inwords = "select group_name, words, forum_id from snifs_g_self_inwords where group_name = '" . $userteam . "' AND forum_id=".$forum.";";
	$result_self_inwords = mysql_query($sql_self_inwords) or die ('Invalid query: '.mysql_error());
	$num_self_inwords = mysql_num_rows($result_self_inwords);
	for($i = 0; $i<$num_self_inwords; $i++) {
		$row_self_inwords[$i]= mysql_fetch_row($result_self_inwords);
	}
	/***************************************************/
	//data transmit
	$data_self_inwords = url_encode($row_self_inwords);
	return $data_self_inwords;
}

// ----------------------------------------------------

//do cache
/*if(false === isset($value_whole_group_data)){
    $value_whole_group_data = array(
        node_person => get_node_person($course),
        node_inwords => get_node_inwords($forum),
        node_outwords => get_node_outwords($forum),
        link_inwords => get_link_inwords($forum),
        link_outwords => get_link_outwords($forum)
    );

    $cache->set($key_whole_group_data, $value_whole_group_data, $expired);
//end do cache
}


$key_team_data = $userteam.'_group_key';
$team_data = null;
$value_team_data = null;

$value_team_data = $cache->get($key_team_data);

    if(false === isset($value_team_data)){

    $value_team_data = get_self_inwords($userteam, $forum);

    $cache->set($key_team_data, $value_team_data, $expired);

    }
*/
$value_whole_group_data = array(
        node_person => get_node_person($course),
        node_inwords => get_node_inwords($forum),
        node_outwords => get_node_outwords($forum),
        link_inwords => get_link_inwords($forum),
        link_outwords => get_link_outwords($forum)
    );

$value_team_data = get_self_inwords($userteam, $forum);
$value_whole_group_data["self_inwords"] = $value_team_data;
//$value = array_merge($value_whole_class_data, $value_personal_data);
echo urldecode(json_encode($value_whole_group_data));

mysql_close($conn);
//$cache->delete($key_lock);
