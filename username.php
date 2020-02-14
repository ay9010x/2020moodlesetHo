<?php
header('Content-Type: application/javascript; charset=utf-8');
require('config.php');
$userid = $USER->username;
echo $userid;
