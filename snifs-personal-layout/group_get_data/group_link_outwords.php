<?php
include '../db/config.php';
include '../db/customer_function.php';

start_session(180);

$cache_key = "cache_group_link_outwords";
// retrieve $cache

if (false === isset($_SESSION[$cache_key])) {
    
/***************************************************/
//圈外詞連線(一組最多10個)
$sql_link_outwords = "SELECT countof, team, words ,user_account, row_numbers
FROM (
      SELECT  @row_numbers := IF(@var_team = team, @row_numbers + 1, 1) AS row_numbers, @var_team := team as team, user_account,  words, countof
      FROM
          (SELECT @row_numbers := 1) x,
          (SELECT countof, words, user_account, @var_team := team as team FROM snifs_g_link_outwords ORDER BY team, countof desc) y
    ) z
WHERE row_numbers <= 10;";
$result_link_outwords = mysql_query($sql_link_outwords) or die ('Invalid query: '.mysql_error());
$num_link_outwords = mysql_num_rows($result_link_outwords);
for($i = 0; $i<$num_link_outwords; $i++)
{
    $row_link_outwords[$i] = mysql_fetch_row($result_link_outwords);
}
    mysql_close($conn);
/***************************************************/
//data transmit
$data_link_outwords = array(

    row_link_outwords => url_encode($row_link_outwords)
);
    $data_link_outwords = json_encode($data_link_outwords);

    $_SESSION[$cache_key] = $data_link_outwords;
    
}
else {
    $data_link_outwords = $_SESSION[$cache_key];
}
echo urldecode($data_link_outwords);
/***************************************************/
