<?php




require_once('../../config.php');
require_once('lib.php');
require_once('export_form.php');

$d = required_param('d', PARAM_INT);
$exportuser = optional_param('exportuser', false, PARAM_BOOL); $exporttime = optional_param('exporttime', false, PARAM_BOOL); $exportapproval = optional_param('exportapproval', false, PARAM_BOOL); 
$PAGE->set_url('/mod/data/export.php', array('d'=>$d));

if (! $data = $DB->get_record('data', array('id'=>$d))) {
    print_error('wrongdataid', 'data');
}

if (! $cm = get_coursemodule_from_instance('data', $data->id, $data->course)) {
    print_error('invalidcoursemodule');
}

if(! $course = $DB->get_record('course', array('id'=>$cm->course))) {
    print_error('invalidcourseid');
}

$data->course     = $cm->course;
$data->cmidnumber = $cm->idnumber;
$data->instance   = $cm->instance;

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability(DATA_CAP_EXPORT, $context);

$fieldrecords = $DB->get_records('data_fields', array('dataid'=>$data->id), 'id');

if(empty($fieldrecords)) {
    if (has_capability('mod/data:managetemplates', $context)) {
        redirect($CFG->wwwroot.'/mod/data/field.php?d='.$data->id);
    } else {
        print_error('nofieldindatabase', 'data');
    }
}

$fields = array();
foreach ($fieldrecords as $fieldrecord) {
    $fields[]= data_get_field($fieldrecord, $data);
}


$mform = new mod_data_export_form('export.php?d='.$data->id, $fields, $cm, $data);

if($mform->is_cancelled()) {
    redirect('view.php?d='.$data->id);
} elseif (!$formdata = (array) $mform->get_data()) {
        $PAGE->set_title($data->name);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($data->name), 2);
    echo $OUTPUT->box(format_module_intro('data', $data, $cm->id), 'generalbox', 'intro');

    $url = new moodle_url('/mod/data/export.php', array('d' => $d));
    groups_print_activity_menu($cm, $url);

        $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);
    $currenttab = 'export';
    include('tabs.php');
    $mform->display();
    echo $OUTPUT->footer();
    die;
}

$selectedfields = array();
foreach ($formdata as $key => $value) {
        if (strpos($key, 'field_')===0 && !empty($value)) {
        $selectedfields[] = substr($key, 6);
    }
}

$currentgroup = groups_get_activity_group($cm);

$exportdata = data_get_exportdata($data->id, $fields, $selectedfields, $currentgroup, $context,
                                  $exportuser, $exporttime, $exportapproval);
$count = count($exportdata);
switch ($formdata['exporttype']) {
    case 'csv':
        data_export_csv($exportdata, $formdata['delimiter_name'], $data->name, $count);
        break;
    case 'xls':
        data_export_xls($exportdata, $data->name, $count);
        break;
    case 'ods':
        data_export_ods($exportdata, $data->name, $count);
        break;
}

die();
