<?php
include '../db/config.php';
include '../db/customer_function.php';


start_session(180);

$cache_key = "cache_group_link_inwords";
// retrieve $cache

if (false === isset($_SESSION[$cache_key])) {

/***************************************************/
//圈內詞連線
$sql_link_inwords = "SELECT user_account, team, words
FROM snifs_g_link_inwords";
$result_link_inwords = mysql_query($sql_link_inwords) or die ('Invalid query: '.mysql_error());
$num_link_inwords = mysql_num_rows($result_link_inwords);
for($i = 0; $i<$num_link_inwords; $i++)
{
    $row_link_inwords[$i] = mysql_fetch_row($result_link_inwords);
}
    mysql_close($conn);
/***************************************************/
//data transmit
$data_link_inwords = array(

    row_link_inwords => url_encode($row_link_inwords)

);
    $data_link_inwords = json_encode($data_link_inwords);

    $_SESSION[$cache_key] = $data_link_inwords;

}   // if (isset($_SESSION[$cache_key]) === false) {
else {
    $data_link_inwords = $_SESSION[$cache_key];
}


echo urldecode($data_link_inwords);
/***************************************************/
