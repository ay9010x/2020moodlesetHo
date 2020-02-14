<?php
include '../db/config.php';
include '../db/customer_function.php';

start_session(180);



/***************************************************/
//傳值
$person = $_POST['person'];

$cache_key = "";
// retrieve $cache
if($person != null)
{
     $cache_key = $cache_key . "_" .$person;
}


if (false === isset($_SESSION[$cache_key])) {
/***************************************************/
//點人表格(一人最多呈現10個詞)
$sql_table_person = "select teamn, name, words, countof from snifs_p_table_person where cast(teamn as char character set utf8)  = '$person' limit 10";
$result_table_person = mysql_query($sql_table_person) or die ('Invalid query: '.mysql_error());
$num_table_person = mysql_num_rows($result_table_person);
for($i = 0; $i<$num_table_person; $i++)
{
    $row_table_person[$i] = mysql_fetch_row($result_table_person);
}
    mysql_close($conn);
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
