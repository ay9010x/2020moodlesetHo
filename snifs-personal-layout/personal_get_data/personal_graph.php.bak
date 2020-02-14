<?php
include '../db/config.php';
include '../db/customer_function.php';
include '../php/my_functions.php';

start_session(180);
$cache_key = "cache_personal_gragh";

if (false === isset($_SESSION[$cache_key])) {

if (isset($_SESSION["discuss_user_name"])) {	
	$username = $_SESSION["discuss_user_name"];
}
else {
	$username = get_username_from_cookie();
	$_SESSION["discuss_user_name"] = $username;
}


// ----------------------------------
// group_node_person.php

function get_array_from_sql($sql) {
    $result = mysql_query($sql) or die ('Invalid query: '.mysql_error());
    $num_rows = mysql_num_rows($result);
	$row = array();
    for($i = 0; $i<$num_rows; $i++) {
        $row[$i] = mysql_fetch_row($result);
    }
    $output = url_encode($row);
	return $output;
}

/**
 * 人節點
 */
function get_node_person() {
    $sql_node_person = "select user_id, user_account, name, team, number, teamn from `snifs_p_node_person` group by user_id order by user_id";
    return get_array_from_sql($sql_node_person);
}

// ------------------------

/**
 * 圈內詞節點(最多30個 資料表snifs_p_node_inwords limit 30)
 */
function get_node_inwords() {
	$sql_node_inwords = "select words, WordTeam, Total_population from snifs_p_node_inwords";
	return get_array_from_sql($sql_node_inwords);
}

/**
 * 圈外詞節點(一人最多2個)
 */
function get_node_outwords() {
	$sql_node_outwords = "SELECT words, team, countof,user_account, row_numbers
FROM (
      SELECT  @row_numbers := IF(@var_user_account = user_account, @row_numbers + 1, 1) AS row_numbers, @var_user_account := user_account as user_account, team, words,countof
      FROM
          (SELECT @row_numbers := 0) x,
          (SELECT countof, words, team, @var_user_account := user_account as user_account FROM snifs_p_node_outwords ORDER BY user_id, countof desc) y
    ) z
WHERE row_numbers <= 2 
ORDER BY user_account ASC ,words ASC;";
	return get_array_from_sql($sql_node_outwords);
}

// ------------------------

/**
 * 圈內詞連線
 */
function get_link_inwords() {
	$sql_link_inwords = "SELECT user_account, name, team, number, teamn, words
FROM snifs_p_link_inwords";
	return get_array_from_sql($sql_link_inwords);
}

// ------------------------

/**
 * 圈外詞連線(一人最多2個)
 */
function get_link_outwords() {
	$sql_link_outwords = "SELECT user_account, name, team, number, teamn, words
FROM (
      SELECT  @row_numbers := IF(@var_user_account = user_account, @row_numbers+ 1, 1) AS row_numbers, @var_user_account := user_account as user_account,  name, team, number, teamn, words
      FROM
          (SELECT @row_numbers := 0) x,
          (SELECT words, teamn, number,  team, name, @var_user_account := user_account as user_account FROM snifs_p_link_outwords  ORDER BY user_account) y
    ) z
WHERE row_numbers <= 2;";
	return get_array_from_sql($sql_link_outwords);
}

function get_self_inwords($username) {
	$sql_self_inwords = "select user_account, words from snifs_p_self_inwords where user_account = '" . $username . "';";
	return get_array_from_sql($sql_self_inwords);
}

// ------------------------

$output_json = array(
	node_person => get_node_person(),
	node_inwords => get_node_inwords(),
	node_outwords => get_node_outwords(),
	link_inwords => get_link_inwords(),
	link_outwords => get_link_outwords(),
	self_inwords => get_self_inwords($username)
);
$output_json = urldecode(json_encode($output_json));
$_SESSION[$cache_key] = $output_json;

} // if (false === isset($_SESSION[$cache_key])) {
else {
    $output_json = $_SESSION[$cache_key];
}

echo $output_json;
mysql_close($conn);