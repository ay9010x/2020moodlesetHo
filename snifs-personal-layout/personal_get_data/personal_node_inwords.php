<?php
include '../db/config.php';
include '../db/customer_function.php';

start_session(180);

$cache_key = "cache_personal_node_inwords";
// retrieve $cache

if (false === isset($_SESSION[$cache_key])) {
/***************************************************/
//圈內詞節點(最多30個 資料表snifs_p_node_inwords limit 30)
$sql_node_inwords = "select words, WordTeam, Total_population from snifs_p_node_inwords";
$result_node_inwords = mysql_query($sql_node_inwords) or die ('Invalid query: '.mysql_error());
$num_node_inwords = mysql_num_rows($result_node_inwords);
for($i = 0; $i<$num_node_inwords; $i++)
{
    $row_node_inwords[$i] = mysql_fetch_row($result_node_inwords);
}
    mysql_close($conn);
/***************************************************/
//data transmit
$data_node_inwords = array(

    row_node_inwords => url_encode($row_node_inwords)

);
$data_node_inwords = json_encode($data_node_inwords);

    $_SESSION[$cache_key] = $data_node_inwords;

}   // if (isset($_SESSION[$cache_key]) === false) {
else {
    $data_node_inwords = $_SESSION[$cache_key];
}

echo urldecode($data_node_inwords);
/***************************************************/
    