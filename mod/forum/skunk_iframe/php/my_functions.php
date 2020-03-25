<?php
function check_user_logined() {
	require( '../config.php' );

	if (FALSE === isloggedin()) {
		setcookie("login_redirect", $CFG->wwwroot . "/skunk_iframe", time() + (86400 * 30), "/");
		header("Location: /login/index.php");
	}
	
}

function get_username_from_cookie() {
	$username = NULL;
	if (isset($_COOKIE["moodle_user"])) {
		$username = $_COOKIE["moodle_user"];
		if (strpos($username, "@") !== FALSE) {
			$username = substr($username, 0,  strpos($username, "@"));
		}
	}
	return $username;
}

function get_user_team($user) {
	include '../skunk_iframe/IPCS/db/config.php';
	include '../skunk_iframe/IPCS/db/customer_function.php';
	/*
	$sql_self_team = "select team from IPCS_user_group where courseid = 75 and user_account LIKE '$user%';";
	*/
	$sql_self_team = "select groupname from IPCS_enrol_user where courseid = 75 and username LIKE '$user%';";
	//$sql_self_team = "select team from snifs_p_node_person where user_account = '$user';";
	$result_self_team = mysql_query($sql_self_team) or die ('Invalid query: '.mysql_error());
	$num_self_team = mysql_num_rows($result_self_team);
	$user_team = mysql_fetch_row($result_self_team);
	$user_team = str_replace("組","",$user_team[0]);
	return $user_team;
}