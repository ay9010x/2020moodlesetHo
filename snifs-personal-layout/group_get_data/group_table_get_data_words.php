<?php
include '../db/config.php';
include '../db/customer_function.php';
start_session(180);

/***************************************************/
//傳值
$words = $_POST['words'];
$forum = $_POST['forum_id'];
/***************************************************/
// retrieve $cache
$cache_key = "";

if($words != null) {
    $cache_key = $cache_key . "_" .$words;
}


$cache_key = "cache_group_table_get_data_team" . $cache_key;

if (false === isset($_SESSION[$cache_key])) {
/***************************************************/
//點詞表格(一詞最多呈現10個人)
//$sql_table_words = "select words, group_name, user_account, group_name ,group_word_count from snifs_g_table_words where words = '$words' limit 10";
$sql_table_words = "select words, group_name, user_account, group_name ,group_word_count from snifs_g_table_words where words = '$words' AND forum_id = '$forum' limit 10";
$result_table_words = mysql_query($sql_table_words) or die ('Invalid query: '.mysql_error());
$num_table_words = mysql_num_rows($result_table_words);
for($i = 0; $i<$num_table_words; $i++)
{
    $row_table_words[$i] = mysql_fetch_row($result_table_words);
}
/***************************************************/
//data transmit
$data_set = array(
    row_table_words => url_encode($row_table_words),
);
    $data_set = json_encode($data_set);

    $_SESSION[$cache_key] = $data_set;

}   // if (isset($_SESSION[$cache_key]) === false) {
else {
    $data_set = $_SESSION[$cache_key];
}

echo urldecode($data_set);
/***************************************************/