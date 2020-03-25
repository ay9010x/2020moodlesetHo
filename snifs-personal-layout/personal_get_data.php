<?php
/*
error_reporting(0);
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = 'la2391';
$dbname = 'moodle';
$conn = mysql_connect($dbhost, $dbuser, $dbpass);
if(!$conn){
    die('Could not connect: ' . mysql_error());
}
//echo 'Connected successfully';
mysql_query("Set names 'utf8'");
mysql_select_db($dbname);
/***************************************************/
//人節點
// $sql_node_person = "select user_id, user_account, name, team, number, teamn from `snifs_p_node_person` group by user_id order by user_id";
// $result_node_person = mysql_query($sql_node_person) or die ('Invalid query: '.mysql_error());
// $num_node_person = mysql_num_rows($result_node_person);
// for($i = 0; $i<$num_node_person; $i++)
// {
//     $row_node_person[$i] = mysql_fetch_row($result_node_person);
// };
// /***************************************************/
//data transmit
// $data_node_person = array(
//
//     row_node_person => $row_node_person
// );
//
// echo json_encode($data_node_person);
/***************************************************/
//圈內詞節點(最多20個 snifs_p_node_inwords limit 20)
$sql_node_inwords = "select words, WordTeam, Total from snifs_p_node_inwords";
$result_node_inwords = mysql_query($sql_node_inwords) or die ('Invalid query: '.mysql_error());
$num_node_inwords = mysql_num_rows($result_node_inwords);
for($i = 0; $i<$num_node_inwords; $i++)
{
    $row_node_inwords[$i] = mysql_fetch_row($result_node_inwords);
};
/***************************************************/
//data transmit
// $data_node_inwords = array(
//
//     row_node_inwords => $row_node_inwords
//
// );
//
// echo json_encode($data_node_inwords);
/***************************************************/
//圈外詞節點(一人最多10個)
$sql_node_outwords = "SELECT words, team, countof,user_account, row_numbers
FROM (
      SELECT  @row_numbers := IF(@var_user_account = user_account, @row_numbers + 1, 1) AS row_numbers, @var_user_account := user_account as user_account, team, words,countof
      FROM
          (SELECT @row_numbers := 1) x,
          (SELECT countof, words, team, @var_user_account := user_account as user_account FROM snifs_p_node_outwords ORDER BY user_account, countof desc) y
    ) z
WHERE row_numbers <= 10;";
$result_node_outwords = mysql_query($sql_node_outwords) or die ('Invalid query: '.mysql_error());
$num_node_outwords = mysql_num_rows($result_node_outwords);
for($i = 0; $i<$num_node_outwords; $i++)
{
    $row_node_outwords[$i] = mysql_fetch_row($result_node_outwords);
};
/***************************************************/
//data transmit
// $data_node_outwords = array(
//
//     row_node_outwords => $row_node_outwords,
//
// );
//
// echo json_encode($data_node_outwords);
/***************************************************/
//圈內詞連線(最多20個 snifs_p_node_inwords limit 20)
$sql_link_inwords = "SELECT user_account, name, team, number, teamn, words
FROM snifs_p_link_inwords";
$result_link_inwords = mysql_query($sql_link_inwords) or die ('Invalid query: '.mysql_error());
$num_link_inwords = mysql_num_rows($result_link_inwords);
for($i = 0; $i<$num_link_inwords; $i++)
{
    $row_link_inwords[$i] = mysql_fetch_row($result_link_inwords);
};
/***************************************************/
//data transmit
// $data_link_inwords = array(
//
//     row_link_inwords => $row_link_inwords,
//
// );
//
// echo json_encode($data_link_inwords);
/***************************************************/
//圈外詞連線(一人最多10個)
$sql_link_outwords = "SELECT user_account, name, team, number, teamn, words
FROM (
      SELECT  @row_numbers := IF(@var_user_account = user_account, @row_numbers+ 1, 1) AS row_numbers, @var_user_account := user_account as user_account,  name, team, number, teamn, words
      FROM
          (SELECT @row_numbers := 1) x,
          (SELECT words, teamn, number,  team, name, @var_user_account := user_account as user_account FROM snifs_p_link_outwords  ORDER BY user_account) y
    ) z
WHERE row_numbers <= 10;";
$result_link_outwords = mysql_query($sql_link_outwords) or die ('Invalid query: '.mysql_error());
$num_link_outwords = mysql_num_rows($result_link_outwords);
for($i = 0; $i<$num_link_outwords; $i++)
{
    $row_link_outwords[$i] = mysql_fetch_row($result_link_outwords);
};
/***************************************************/
//data transmit
// $data_link_outwords = array(
//
//     row_link_outwords => $row_link_outwords,
// );
//
// echo json_encode($data_link_outwords);
/***************************************************/
//data transmit
$data_set = array(

    // row_node_person => $row_node_person,
    row_node_inwords => $row_node_inwords,
    row_node_outwords => $row_node_outwords,
    row_link_inwords => $row_link_inwords,
    row_link_outwords => $row_link_outwords,
);

echo json_encode($data_set);
/***************************************************/

mysql_close($conn);

?>
