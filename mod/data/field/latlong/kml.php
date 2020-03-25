<?php



require_once('../../../../config.php');
require_once('../../lib.php');


$d       = required_param('d', PARAM_INT);   $fieldid = required_param('fieldid', PARAM_INT);   $rid     = optional_param('rid', 0, PARAM_INT);    
$url = new moodle_url('/mod/data/field/latlong/kml.php', array('d'=>$d, 'fieldid'=>$fieldid));
if ($rid !== 0) {
    $url->param('rid', $rid);
}
$PAGE->set_url($url);

if ($rid) {
    if (! $record = $DB->get_record('data_records', array('id'=>$rid))) {
        print_error('invalidrecord', 'data');
    }
    if (! $data = $DB->get_record('data', array('id'=>$record->dataid))) {
        print_error('invalidid', 'data');
    }
    if (! $course = $DB->get_record('course', array('id'=>$data->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance('data', $data->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    if (! $field = $DB->get_record('data_fields', array('id'=>$fieldid))) {
        print_error('invalidfieldid', 'data');
    }
    if (! $field->type == 'latlong') {         print_error('invalidfieldtype', 'data');
    }
    if (! $content = $DB->get_record('data_content', array('fieldid'=>$fieldid, 'recordid'=>$rid))) {
        print_error('nofieldcontent', 'data');
    }
} else {       if (! $data = $DB->get_record('data', array('id'=>$d))) {
        print_error('invalidid', 'data');
    }
    if (! $course = $DB->get_record('course', array('id'=>$data->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance('data', $data->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    if (! $field = $DB->get_record('data_fields', array('id'=>$fieldid))) {
        print_error('invalidfieldid', 'data');
    }
    if (! $field->type == 'latlong') {         print_error('invalidfieldtype', 'data');
    }
    $record = NULL;
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    $PAGE->set_title($data->name);
    echo $OUTPUT->header();
    notice(get_string("activityiscurrentlyhidden"));
}

if (has_capability('mod/data:managetemplates', $context)) {
    if (!$DB->record_exists('data_fields', array('dataid'=>$data->id))) {              redirect($CFG->wwwroot.'/mod/data/field.php?d='.$data->id);      }
}




header('Content-type: application/vnd.google-earth.kml+xml kml');
header('Content-Disposition: attachment; filename="moodleearth-'.$d.'-'.$rid.'-'.$fieldid.'.kml"');


echo data_latlong_kml_top();

if($rid) {     $pm = new stdClass();
    $pm->name = data_latlong_kml_get_item_name($content, $field);
    $pm->description = "&lt;a href='$CFG->wwwroot/mod/data/view.php?d=$d&amp;rid=$rid'&gt;Item #$rid&lt;/a&gt; in Moodle data activity";
    $pm->long = $content->content1;
    $pm->lat = $content->content;
    echo data_latlong_kml_placemark($pm);
} else {   
    $contents = $DB->get_records('data_content', array('fieldid'=>$fieldid));

    echo '<Document>';

    foreach($contents as $content) {
        $pm->name = data_latlong_kml_get_item_name($content, $field);
        $pm->description = "&lt;a href='$CFG->wwwroot/mod/data/view.php?d=$d&amp;rid=$content->recordid'&gt;Item #$content->recordid&lt;/a&gt; in Moodle data activity";
        $pm->long = $content->content1;
        $pm->lat = $content->content;
        echo data_latlong_kml_placemark($pm);
    }

    echo '</Document>';

}

echo data_latlong_kml_bottom();




function data_latlong_kml_top() {
    return '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.0">

';
}

function data_latlong_kml_placemark($pm) {
    return '<Placemark>
  <description>'.$pm->description.'</description>
  <name>'.$pm->name.'</name>
  <LookAt>
    <longitude>'.$pm->long.'</longitude>
    <latitude>'.$pm->lat.'</latitude>
    <range>30500.8880792294568</range>
    <tilt>46.72425699662645</tilt>
    <heading>0.0</heading>
  </LookAt>
  <visibility>0</visibility>
  <Point>
    <extrude>1</extrude>
    <altitudeMode>relativeToGround</altitudeMode>
    <coordinates>'.$pm->long.','.$pm->lat.',50</coordinates>
  </Point>
</Placemark>
';
}

function data_latlong_kml_bottom() {
    return '</kml>';
}

function data_latlong_kml_get_item_name($content, $field) {
    global $DB;

    
    $name = '';

    if($field->param2 > 0) {
        $name = htmlspecialchars($DB->get_field('data_content', 'content', array('fieldid'=>$field->param2, 'recordid'=>$content->recordid)));
    }elseif($field->param2 == -2) {
        $name = $content->content . ', ' . $content->content1;
    }
    if($name=='') {         $name = get_string('entry', 'data') . " #$content->recordid";
    }


    return $name;
}
