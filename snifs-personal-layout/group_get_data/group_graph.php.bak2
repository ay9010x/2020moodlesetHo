<?php
include '../db/config.php';
include '../db/customer_function.php';
include '../php/my_functions.php';

start_session(180);

$cache_key = "cache_group_gragh";
if (false === isset($_SESSION[$cache_key])) {

if (isset($_SESSION["discuss_group_name"])) {
	$userteam = $_SESSION["discuss_group_name"];
}
else {
	$userteam = get_user_team(get_username_from_cookie());
	$_SESSION["discuss_group_name"] = $userteam;
}

// ----------------------------------
// group_node_person.php
function get_node_person() {
	/***************************************************/
    //組節點
    $sql_node_person = "select team from `snifs_g_node_team` order by team";
    $result_node_person = mysql_query($sql_node_person) or die ('Invalid query: '.mysql_error());
    $num_node_person = mysql_num_rows($result_node_person);
    for($i = 0; $i<$num_node_person; $i++)
    {
        $row_node_person[$i] = mysql_fetch_row($result_node_person);
    }

    /***************************************************/
    //data transmit
    $data_node_person = url_encode($row_node_person);
    //$data_node_person = json_encode($data_node_person);
	return $data_node_person;
}

// ------------------------

function get_node_inwords() {
	$sql_node_inwords = "select words, WordTeam, Total_population from snifs_g_node_inwords limit 30";
	$result_node_inwords = mysql_query($sql_node_inwords) or die ('Invalid query: '.mysql_error());
	$num_node_inwords = mysql_num_rows($result_node_inwords);
	for($i = 0; $i<$num_node_inwords; $i++) {
		$row_node_inwords[$i] = mysql_fetch_row($result_node_inwords);
	}
	$data_node_inwords = url_encode($row_node_inwords);
	return $data_node_inwords;
}

/**
 * 圈外詞節點(一組最多5個)
 */
function get_node_outwords() {
	//圈外詞節點(一組最多5個)
	$sql_node_outwords = "SELECT words, team, countof,user_account, row_numbers
	FROM (
		  SELECT  @row_numbers := IF(@var_team = team, @row_numbers + 1, 1) AS row_numbers, @var_team := team as team, user_account,  words, countof
		  FROM
			  (SELECT @row_numbers := 1) x,
			  (SELECT countof, words, user_account, @var_team := team as team FROM snifs_g_node_outwords ORDER BY team, countof desc) y
		) z
	WHERE row_numbers <= 5;";
	$result_node_outwords = mysql_query($sql_node_outwords) or die ('Invalid query: '.mysql_error());
	$num_node_outwords = mysql_num_rows($result_node_outwords);

	for($i = 0; $i<$num_node_outwords; $i++) {
		$row_node_outwords[$i] = mysql_fetch_row($result_node_outwords);
	}
	$data_node_outwords = url_encode($row_node_outwords);
	return $data_node_outwords;
}

// ------------------------

/**
 * 圈內詞連線
 */
function get_link_inwords() {
	$sql_link_inwords = "SELECT user_account, team, words
	FROM snifs_g_link_inwords";
	$result_link_inwords = mysql_query($sql_link_inwords) or die ('Invalid query: '.mysql_error());
	$num_link_inwords = mysql_num_rows($result_link_inwords);
	for($i = 0; $i<$num_link_inwords; $i++) {
		$row_link_inwords[$i] = mysql_fetch_row($result_link_inwords);
	}
	$data_link_inwords = url_encode($row_link_inwords);
	return $data_link_inwords;
}

// ------------------------

/**
 * 圈外詞連線(一組最多5個)
 */
function get_link_outwords() {
	$sql_link_outwords = "SELECT countof, team, words ,user_account, row_numbers
	FROM (
		  SELECT  @row_numbers := IF(@var_team = team, @row_numbers + 1, 1) AS row_numbers, @var_team := team as team, user_account,  words, countof
		  FROM
			  (SELECT @row_numbers := 1) x,
			  (SELECT countof, words, user_account, @var_team := team as team FROM snifs_g_link_outwords ORDER BY team, countof desc) y
		) z
	WHERE row_numbers <= 5;";
	$result_link_outwords = mysql_query($sql_link_outwords) or die ('Invalid query: '.mysql_error());
	$num_link_outwords = mysql_num_rows($result_link_outwords);
	for($i = 0; $i<$num_link_outwords; $i++) {
		$row_link_outwords[$i] = mysql_fetch_row($result_link_outwords);
	}
	$data_link_outwords = url_encode($row_link_outwords);
	return $data_link_outwords;
}

function get_self_inwords($userteam) {
	$sql_self_inwords = "select team, words from snifs_g_self_inwords where team = '" . $userteam . "';";
	$result_self_inwords = mysql_query($sql_self_inwords) or die ('Invalid query: '.mysql_error());
	$num_self_inwords = mysql_num_rows($result_self_inwords);
	for($i = 0; $i<$num_self_inwords; $i++) {
		$row_self_inwords[$i]= mysql_fetch_row($result_self_inwords);
	}
	/***************************************************/
	//data transmit
	$data_self_inwords = url_encode($row_self_inwords);
	return $data_self_inwords;
}

// ------------------------

$output_json = array(
	node_person => get_node_person(),
	node_inwords => get_node_inwords(),
	node_outwords => get_node_outwords(),
	link_inwords => get_link_inwords(),
	link_outwords => get_link_outwords(),
	self_inwords => get_self_inwords($userteam)
);
$output_json = urldecode(json_encode($output_json));
$_SESSION[$cache_key] = $output_json;

} // if (false === isset($_SESSION[$cache_key])) {
else {
    $output_json = $_SESSION[$cache_key];
}

echo $output_json;
mysql_close($conn);
