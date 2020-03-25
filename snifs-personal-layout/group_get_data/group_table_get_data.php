<?php
include '../db/config.php';
include '../db/customer_function.php';
start_session(180);

/***************************************************/
//傳值
$self = $_POST['user'];
$self_team = $_POST['user_team'];
//$team = "A";
/***************************************************/
// retrieve $cache
$cache_key = "cache_group_table_get_data_highlight";

if (false === isset($_SESSION[$cache_key])) {

/***************************************************/
//利用user帳號去搜尋組別
$sql_self_team = "select team from snifs_p_node_person where user_account = '$self';";
$result_self_team = mysql_query($sql_self_team) or die ('Invalid query: '.mysql_error());
$num_self_team = mysql_num_rows($result_self_team);
for($i = 0; $i<$num_self_team; $i++)
{
    $row_self_team[$i]= mysql_fetch_row($result_self_team);
}
/***************************************************/
//利用user組別去搜尋該組的圈內詞
$sql_self_inwords = "select team, words from snifs_g_self_inwords where team = '$self_team';";
$result_self_inwords = mysql_query($sql_self_inwords) or die ('Invalid query: '.mysql_error());
$num_self_inwords = mysql_num_rows($result_self_inwords);
for($i = 0; $i<$num_self_inwords; $i++)
{
    $row_self_inwords[$i]= mysql_fetch_row($result_self_inwords);
}
/***************************************************/
//data transmit
$data_set = array(
    row_self_team => url_encode($row_self_team),
    row_self_inwords => url_encode($row_self_inwords)
);
    $data_set = json_encode($data_set);

    $_SESSION[$cache_key] = $data_set;

}   // if (isset($_SESSION[$cache_key]) === false) {
else {
    $data_set = $_SESSION[$cache_key];
}

echo urldecode($data_set);
/***************************************************/