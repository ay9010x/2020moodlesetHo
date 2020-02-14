<?php
header('Content-Type: text/html; charset=utf-8');//include database_config
error_reporting(0);
$dbhost = 'localhost:3306';
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
