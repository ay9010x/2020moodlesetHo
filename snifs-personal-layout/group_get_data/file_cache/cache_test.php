<?php
include '../../db/config.php';
include '../../db/customer_function.php';
include '../../php/my_functions.php';
include './filecache.php';

//header('Content-Type: text/html; charset=utf-8');
$username = "20401";

/**
* @param string $path 缓存文件存放路径
* @param int $max_path 缓存的最大目录数
* @param int $max_file 缓存最大文件数
* @param int $gc_probality 执行set时遍历缓存以删除过期缓存数据操作的执行概率 百万分之 *
*/

$cache = new FileCache($path = sys_get_temp_dir());
$value_whole_class_data = null;
$whole_class_data = null;
$key_whole_class_data = "personal_data";
$expired = 180;

//------------------------------------

function get_array_from_sql($sql) {
    $result = mysql_query($sql) or die ('Invalid query: '.mysql_error());
    $num_rows = mysql_num_rows($result);
	$row = array();
    for($i = 0; $i<$num_rows; $i++) {
        $row[$i] = mysql_fetch_row($result);
    }
    $output = url_encode($row);
	return $output;
}

/**
* 人節點
*/
function get_node_person() {
    $sql_node_person = "select user_id, user_account, name, team, number, teamn from `snifs_p_node_person` group by user_id order by user_id";
    return get_array_from_sql($sql_node_person);
}

// ------------------------

/**
* 圈內詞節點(最多30個 資料表snifs_p_node_inwords limit 30)
*/function get_node_inwords() {
	$sql_node_inwords = "select words, WordTeam, Total_population from snifs_p_node_inwords";
	return get_array_from_sql($sql_node_inwords);
}

/**
 * 圈外詞節點(一人最多2個)
 */
function get_node_outwords() {
	$sql_node_outwords = "SELECT words, team, countof,user_account, row_numbers
FROM (
      SELECT  @row_numbers := IF(@var_user_account = user_account, @row_numbers + 1, 1) AS row_numbers, @var_user_account := user_account as user_account, team, words,countof
      FROM
          (SELECT @row_numbers := 0) x,
          (SELECT countof, words, team, @var_user_account := user_account as user_account FROM snifs_p_node_outwords ORDER BY user_id, countof desc) y
    ) z
WHERE row_numbers <= 2 
ORDER BY user_account ASC ,words ASC;";
	return get_array_from_sql($sql_node_outwords);
}

// ------------------------

/**
 * 圈內詞連線
 */
function get_link_inwords() {
	$sql_link_inwords = "SELECT user_account, name, team, number, teamn, words
FROM snifs_p_link_inwords";
	return get_array_from_sql($sql_link_inwords);
}

// ------------------------

/**
 * 圈外詞連線(一人最多2個)
 */
function get_link_outwords() {
	$sql_link_outwords = "SELECT user_account, name, team, number, teamn, words
FROM (
      SELECT  @row_numbers := IF(@var_user_account = user_account, @row_numbers+ 1, 1) AS row_numbers, @var_user_account := user_account as user_account,  name, team, number, teamn, words
      FROM
          (SELECT @row_numbers := 0) x,
          (SELECT words, teamn, number,  team, name, @var_user_account := user_account as user_account FROM snifs_p_link_outwords  ORDER BY user_account) y
    ) z
WHERE row_numbers <= 2;";
	return get_array_from_sql($sql_link_outwords);
}

function get_self_inwords($username) {
    $sql_self_inwords = "select user_account, words from snifs_p_self_inwords where user_account = '" . $username . "';";
    return get_array_from_sql($sql_self_inwords);
}

// ----------------------------------------------------


$value_whole_class_data = $cache->get($key_whole_class_data);

//do cache
if(false === isset($value_whole_class_data)){
    $value_whole_class_data = array(
        'node_person' => get_node_person(),
        'node_inwords' => get_node_inwords(),
        'node_outwords' => get_node_outwords(),
        'link_inwords' => get_link_inwords(),
        'link_outwords' => get_link_outwords()
    );
//    $value_whole_class_data = urldecode(json_encode($whole_class_data));
    //$value_whole_class_data = $whole_class_data;
    $cache->set($key_whole_class_data, $value_whole_class_data, $expired);
//end do cache

}
//else{
//    $value_whole_class_data = $cache->get($key_whole_class_data);
//}

/********************************************************************************/

$key_personal_data = $username.'_personal_key';
$personal_data = null;
$value_personal_data = null;

$value_personal_data = $cache->get($key_personal_data);
    
    if(false === isset($value_personal_data)){
        
        
    //$personal_data = array(
    //    'self_inwords' => get_self_inwords($username)
    //);
    $value_personal_data = get_self_inwords($username);
        
//    $value_personal_data = $personal_data;
    $cache->set($key_personal_data, $value_personal_data, $expired);
        
    }

$value_whole_class_data["self_inwords"] = $value_personal_data;
//$value = array_merge($value_whole_class_data, $value_personal_data);
echo urldecode(json_encode($value_whole_class_data));