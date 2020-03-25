<?php
include '../db/config.php';
include '../db/customer_function.php';
start_session(180);

/***************************************************/
//傳值
$team = $_POST['team'];
$forum = $_POST['forum_id'];
/***************************************************/
// retrieve $cache
$cache_key = "";
if($team != null) {
    $cache_key = $cache_key . "_" .$team;
}

$cache_key = "cache_group_table_get_data_team" . $cache_key;

if (false === isset($_SESSION[$cache_key])) {
/***************************************************/
//點人表格(一人最多呈現10個詞)
//$sql_table_person = "select group_name, words, group_word_count from snifs_g_table_team where cast(group_name as char character set utf8)  = '$team' limit 10";
$sql_table_person = "select group_name, words, group_word_count from snifs_g_table_team where cast(group_name as char character set utf8)  = '$team' AND forum_id = '$forum' limit 10";
$result_table_person = mysql_query($sql_table_person) or die ('Invalid query: '.mysql_error());
$num_table_person = mysql_num_rows($result_table_person);
for($i = 0; $i<$num_table_person; $i++)
{
    $row_table_person[$i] = mysql_fetch_row($result_table_person);
}
/***************************************************/
//data transmit
$data_set = array(
    row_table_person => url_encode($row_table_person)
);
    $data_set = json_encode($data_set);

    $_SESSION[$cache_key] = $data_set;

}   // if (isset($_SESSION[$cache_key]) === false) {
else {
    $data_set = $_SESSION[$cache_key];
}

echo urldecode($data_set);
/***************************************************/