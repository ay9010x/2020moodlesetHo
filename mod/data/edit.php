<?php




require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/rsslib.php");
require_once("$CFG->libdir/form/filemanager.php");

$id    = optional_param('id', 0, PARAM_INT);    $d     = optional_param('d', 0, PARAM_INT);    $rid   = optional_param('rid', 0, PARAM_INT);    $cancel   = optional_param('cancel', '', PARAM_RAW);    $mode ='addtemplate';    


$url = new moodle_url('/mod/data/edit.php');
if ($rid !== 0) {
    $record = $DB->get_record('data_records', array(
            'id' => $rid,
            'dataid' => $d,
        ), '*', MUST_EXIST);
    $url->param('rid', $rid);
}
if ($cancel !== '') {
    $url->param('cancel', $cancel);
}

if ($id) {
    $url->param('id', $id);
    $PAGE->set_url($url);
    if (! $cm = get_coursemodule_from_id('data', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
        print_error('coursemisconf');
    }
    if (! $data = $DB->get_record('data', array('id'=>$cm->instance))) {
        print_error('invalidcoursemodule');
    }

} else {
    $url->param('d', $d);
    $PAGE->set_url($url);
    if (! $data = $DB->get_record('data', array('id'=>$d))) {
        print_error('invalidid', 'data');
    }
    if (! $course = $DB->get_record('course', array('id'=>$data->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance('data', $data->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($course, false, $cm);

if (isguestuser()) {
    redirect('view.php?d='.$data->id);
}

$context = context_module::instance($cm->id);

if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    $strdatabases = get_string("modulenameplural", "data");

    $PAGE->set_title($data->name);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    notice(get_string("activityiscurrentlyhidden"));
}

if (has_capability('mod/data:managetemplates', $context)) {
    if (!$DB->record_exists('data_fields', array('dataid'=>$data->id))) {              redirect($CFG->wwwroot.'/mod/data/field.php?d='.$data->id);      }
}

if ($rid) {
        require_sesskey();
}

$currentgroup = groups_get_activity_group($cm);
$groupmode = groups_get_activity_groupmode($cm);

if (!has_capability('mod/data:manageentries', $context)) {
    if ($rid) {
                if (!data_user_can_manage_entry($record, $data, $context)) {
            print_error('noaccess','data');
        }
    } else if (!data_user_can_add_entry($data, $currentgroup, $groupmode, $context)) {
                print_error('noaccess','data');
    }
}

if ($cancel) {
    redirect('view.php?d='.$data->id);
}


if (!empty($CFG->enablerssfeeds) && !empty($CFG->data_enablerssfeeds) && $data->rssarticles > 0) {
    $courseshortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
    $rsstitle = $courseshortname . ': ' . format_string($data->name);
    rss_add_http_header($context, 'mod_data', $data, $rsstitle);
}
if ($data->csstemplate) {
    $PAGE->requires->css('/mod/data/css.php?d='.$data->id);
}
if ($data->jstemplate) {
    $PAGE->requires->js('/mod/data/js.php?d='.$data->id, true);
}

$possiblefields = $DB->get_records('data_fields', array('dataid'=>$data->id), 'id');

foreach ($possiblefields as $field) {
    if ($field->type == 'file' || $field->type == 'picture') {
        require_once($CFG->dirroot.'/repository/lib.php');
        break;
    }
}

$strdata = get_string('modulenameplural','data');

if ($rid) {
    $PAGE->navbar->add(get_string('editentry', 'data'));
}

$PAGE->set_title($data->name);
$PAGE->set_heading($course->fullname);


$generalnotifications = array();
$fieldnotifications = array();

if ($datarecord = data_submitted() and confirm_sesskey()) {
    if ($rid) {
        
                $fields = $DB->get_records('data_fields', array('dataid' => $datarecord->d));

                $processeddata = data_process_submission($data, $fields, $datarecord);

                $generalnotifications = array_merge($generalnotifications, $processeddata->generalnotifications);
        $fieldnotifications = array_merge($fieldnotifications, $processeddata->fieldnotifications);

        if ($processeddata->validated) {
            
            
                        if (!has_capability('mod/data:approve', $context)) {
                $record->approved = 0;
            }

                        $record->timemodified = time();
            $DB->update_record('data_records', $record);

                        foreach ($processeddata->fields as $fieldname => $field) {
                $field->update_content($rid, $datarecord->$fieldname, $fieldname);
            }

                        $event = \mod_data\event\record_updated::create(array(
                'objectid' => $rid,
                'context' => $context,
                'courseid' => $course->id,
                'other' => array(
                    'dataid' => $data->id
                )
            ));
            $event->add_record_snapshot('data', $data);
            $event->trigger();

            $viewurl = new moodle_url('/mod/data/view.php', array(
                'd' => $data->id,
                'rid' => $rid,
            ));
            redirect($viewurl);
        }

    } else {
        
                $fields = $DB->get_records('data_fields', array('dataid' => $datarecord->d));

                $processeddata = data_process_submission($data, $fields, $datarecord);

                $generalnotifications = array_merge($generalnotifications, $processeddata->generalnotifications);
        $fieldnotifications = array_merge($fieldnotifications, $processeddata->fieldnotifications);

                if ($processeddata->validated && $recordid = data_add_record($data, $currentgroup)) {

                        $records = array();
            foreach ($fields as $field) {
                $content = new stdClass();
                $content->recordid = $recordid;
                $content->fieldid = $field->id;
                $records[] = $content;
            }

                        $DB->insert_records('data_content', $records);

                        foreach ($processeddata->fields as $fieldname => $field) {
                $field->update_content($recordid, $datarecord->$fieldname, $fieldname);
            }

                        $event = \mod_data\event\record_created::create(array(
                'objectid' => $rid,
                'context' => $context,
                'courseid' => $course->id,
                'other' => array(
                    'dataid' => $data->id
                )
            ));
            $event->add_record_snapshot('data', $data);
            $event->trigger();

            if (!empty($datarecord->saveandview)) {
                $viewurl = new moodle_url('/mod/data/view.php', array(
                    'd' => $data->id,
                    'rid' => $recordid,
                ));
                redirect($viewurl);
            } else if (!empty($datarecord->saveandadd)) {
                                $datarecord = null;
            }
        }
    }
}



echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($data->name), 2);
echo $OUTPUT->box(format_module_intro('data', $data, $cm->id), 'generalbox', 'intro');
groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/data/edit.php?d='.$data->id);


$currenttab = 'add';
if ($rid) {
    $editentry = true;  }
include('tabs.php');



$patterns = array();    $replacement = array();    
echo '<form enctype="multipart/form-data" action="edit.php" method="post">';
echo '<div>';
echo '<input name="d" value="'.$data->id.'" type="hidden" />';
echo '<input name="rid" value="'.$rid.'" type="hidden" />';
echo '<input name="sesskey" value="'.sesskey().'" type="hidden" />';
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');

if (!$rid){
    echo $OUTPUT->heading(get_string('newentry','data'), 3);
}


if ($data->addtemplate){
    $possiblefields = $DB->get_records('data_fields', array('dataid'=>$data->id), 'id');
    $patterns = array();
    $replacements = array();

        foreach ($possiblefields as $eachfield){
        $field = data_get_field($eachfield, $data);

                if (strpos($data->addtemplate, "[[".$field->field->name."]]") !== false) {
                        $patterns[] = "[[".$field->field->name."]]";
            $errors = '';
            if (!empty($fieldnotifications[$field->field->name])) {
                foreach ($fieldnotifications[$field->field->name] as $notification) {
                    $errors .= $OUTPUT->notification($notification);
                }
            }
            $replacements[] = $errors . $field->display_add_field($rid, $datarecord);
        }

                $patterns[] = "[[".$field->field->name."#id]]";
        $replacements[] = 'field_'.$field->field->id;
    }
    $newtext = str_ireplace($patterns, $replacements, $data->{$mode});

} else {        echo data_generate_default_template($data, 'addtemplate', $rid, true, false);
    $newtext = '';
}

foreach ($generalnotifications as $notification) {
    echo $OUTPUT->notification($notification);
}
echo $newtext;

echo '<div class="mdl-align"><input type="submit" name="saveandview" value="'.get_string('saveandview','data').'" />';
if ($rid) {
    echo '&nbsp;<input type="submit" name="cancel" value="'.get_string('cancel').'" onclick="javascript:history.go(-1)" />';
} else {
    if ((!$data->maxentries) || has_capability('mod/data:manageentries', $context) || (data_numentries($data) < ($data->maxentries - 1))) {
        echo '&nbsp;<input type="submit" name="saveandadd" value="' . get_string('saveandadd', 'data') . '" />';
    }
}
echo '</div>';
echo $OUTPUT->box_end();
echo '</div></form>';



if (!$fields = $DB->get_records('data_fields', array('dataid'=>$data->id))) {
    print_error('nofieldindatabase', 'data');
}
foreach ($fields as $eachfield) {
    $field = data_get_field($eachfield, $data);
    $field->print_after_form();
}

echo $OUTPUT->footer();
