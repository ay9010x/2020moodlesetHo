<?php
include '../db/config.php';
include '../db/customer_function.php';

start_session(180);

$cache_key = "cache_group_node_outwords";
// retrieve $cache

if (false === isset($_SESSION[$cache_key])) {
/***************************************************/
//圈外詞節點(一組最多10個)
$sql_node_outwords = "SELECT words, team, countof,user_account, row_numbers
FROM (
      SELECT  @row_numbers := IF(@var_team = team, @row_numbers + 1, 1) AS row_numbers, @var_team := team as team, user_account,  words, countof
      FROM
          (SELECT @row_numbers := 1) x,
          (SELECT countof, words, user_account, @var_team := team as team FROM snifs_g_node_outwords ORDER BY team, countof desc) y
    ) z
WHERE row_numbers <= 10;";
$result_node_outwords = mysql_query($sql_node_outwords) or die ('Invalid query: '.mysql_error());
$num_node_outwords = mysql_num_rows($result_node_outwords);

for($i = 0; $i<$num_node_outwords; $i++)
{
    $row_node_outwords[$i] = mysql_fetch_row($result_node_outwords);
}
mysql_close($conn);
/***************************************************/
//data transmit
$data_node_outwords = array(

    row_node_outwords => url_encode($row_node_outwords)

);
$data_node_outwords = json_encode($data_node_outwords);

    $_SESSION[$cache_key] = $data_node_outwords;

}   // if (isset($_SESSION[$cache_key]) === false) {
else {
    $data_node_outwords = $_SESSION[$cache_key];
}
echo urldecode($data_node_outwords);
/***************************************************/

