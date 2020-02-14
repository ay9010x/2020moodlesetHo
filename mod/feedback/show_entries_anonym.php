<?php



require_once("../../config.php");

$url = new moodle_url('/mod/feedback/show_entries.php');
foreach ($_GET as $key => $value) {
    $url->param($key, $value);
}
redirect($url);
