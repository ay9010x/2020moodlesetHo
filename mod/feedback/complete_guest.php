<?php



require_once("../../config.php");

$url = new moodle_url('/mod/feedback/complete.php');
foreach ($_GET as $key => $value) {
    $url->param($key, $value);
}
redirect($url);
