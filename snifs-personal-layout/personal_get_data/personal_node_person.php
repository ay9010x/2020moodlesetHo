<?php
include '../db/config.php';
include '../db/customer_function.php';

start_session(180);

$cache_key = "cache_personal_node_person";
// retrieve $cache

if (false === isset($_SESSION[$cache_key])) {

/***************************************************/
//人節點
$sql_node_person = "select user_id, user_account, name, team, number, teamn from `snifs_p_node_person` group by user_id order by user_id";
$result_node_person = mysql_query($sql_node_person) or die ('Invalid query: '.mysql_error());
$num_node_person = mysql_num_rows($result_node_person);
for($i = 0; $i<$num_node_person; $i++)
{
    $row_node_person[$i] = mysql_fetch_row($result_node_person);
}
    mysql_close($conn);
/***************************************************/
//data transmit
$data_node_person = array(

    row_node_person => url_encode($row_node_person)
);
$data_node_person = json_encode($data_node_person);

    $_SESSION[$cache_key] = $data_node_person;

}   // if (isset($_SESSION[$cache_key]) === false) {
else {
    $data_node_person = $_SESSION[$cache_key];
}
echo urldecode($data_node_person);
/***************************************************/