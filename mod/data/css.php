<?php




define('NO_MOODLE_COOKIES', true); 
require_once('../../config.php');

$d = optional_param('d', 0, PARAM_INT);   $lifetime  = 600;                                   
$PAGE->set_url('/mod/data/css.php', array('d'=>$d));

if ($data = $DB->get_record('data', array('id'=>$d))) {
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
    header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $lifetime) . ' GMT');
    header('Cache-control: max_age = '. $lifetime);
    header('Pragma: ');
    header('Content-type: text/css; charset=utf-8');  
    echo $data->csstemplate;
}