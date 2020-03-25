<?php



require_once("../../config.php");
require_once("lib.php");
require_once('import_form.php');

$id = required_param('id', PARAM_INT);
$choosefile = optional_param('choosefile', false, PARAM_PATH);
$action = optional_param('action', false, PARAM_ALPHA);

$url = new moodle_url('/mod/feedback/import.php', array('id'=>$id));
if ($choosefile !== false) {
    $url->param('choosefile', $choosefile);
}
if ($action !== false) {
    $url->param('action', $action);
}
$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('feedback', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $feedback = $DB->get_record("feedback", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

$context = context_module::instance($cm->id);

require_login($course, true, $cm);

require_capability('mod/feedback:edititems', $context);

$mform = new feedback_import_form();
$newformdata = array('id'=>$id,
                    'deleteolditems'=>'1',
                    'action'=>'choosefile',
                    'confirmadd'=>'1',
                    'do_show'=>'templates');
$mform->set_data($newformdata);
$formdata = $mform->get_data();

if ($mform->is_cancelled()) {
    redirect('edit.php?id='.$id.'&do_show=templates');
}

if ($choosefile) {
    $xmlcontent = $mform->get_file_content('choosefile');

    if (!$xmldata = feedback_load_xml_data($xmlcontent)) {
        print_error('cannotloadxml', 'feedback', 'edit.php?id='.$id);
    }

    $importerror = feedback_import_loaded_data($xmldata, $feedback->id);
    if ($importerror->stat == true) {
        $url = 'edit.php?id='.$id.'&do_show=templates';
        redirect($url, get_string('import_successfully', 'feedback'), 3);
        exit;
    }
}


$strfeedbacks = get_string("modulenameplural", "feedback");
$strfeedback  = get_string("modulename", "feedback");

$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedback->name));
$current_tab = 'templates';
require('tabs.php');

echo $OUTPUT->heading(get_string('import_questions', 'feedback'), 3);

if (isset($importerror->msg) AND is_array($importerror->msg)) {
    echo $OUTPUT->box_start('generalbox errorboxcontent boxaligncenter');
    foreach ($importerror->msg as $msg) {
        echo $msg.'<br />';
    }
    echo $OUTPUT->box_end();
}

$mform->display();

echo $OUTPUT->footer();

function feedback_load_xml_data($xmlcontent) {
    global $CFG;
    require_once($CFG->dirroot.'/lib/xmlize.php');

    if (!$xmlcontent = feedback_check_xml_utf8($xmlcontent)) {
        return false;
    }

    $data = xmlize($xmlcontent, 1, 'UTF-8');

    if (intval($data['FEEDBACK']['@']['VERSION']) != 200701) {
        return false;
    }
    $data = $data['FEEDBACK']['#']['ITEMS'][0]['#']['ITEM'];
    return $data;
}

function feedback_import_loaded_data(&$data, $feedbackid) {
    global $CFG, $DB;

    feedback_load_feedback_items();

    $deleteolditems = optional_param('deleteolditems', 0, PARAM_INT);

    $error = new stdClass();
    $error->stat = true;
    $error->msg = array();

    if (!is_array($data)) {
        $error->msg[] = get_string('data_is_not_an_array', 'feedback');
        $error->stat = false;
        return $error;
    }

    if ($deleteolditems) {
        feedback_delete_all_items($feedbackid);
        $position = 0;
    } else {
                $position = $DB->count_records('feedback_item', array('feedback'=>$feedbackid));
    }

            $dependitemsmap = array();
    $itembackup = array();
    foreach ($data as $item) {
        $position++;
                $typ = $item['@']['TYPE'];

                switch($typ) {
            case 'radio':
                $typ = 'multichoice';
                $oldtyp = 'radio';
                break;
            case 'dropdown':
                $typ = 'multichoice';
                $oldtyp = 'dropdown';
                break;
            case 'check':
                $typ = 'multichoice';
                $oldtyp = 'check';
                break;
            case 'radiorated':
                $typ = 'multichoicerated';
                $oldtyp = 'radiorated';
                break;
            case 'dropdownrated':
                $typ = 'multichoicerated';
                $oldtyp = 'dropdownrated';
                break;
            default:
                $oldtyp = $typ;
        }

        $itemclass = 'feedback_item_'.$typ;
        if ($typ != 'pagebreak' AND !class_exists($itemclass)) {
            $error->stat = false;
            $error->msg[] = 'type ('.$typ.') not found';
            continue;
        }
        $itemobj = new $itemclass();

        $newitem = new stdClass();
        $newitem->feedback = $feedbackid;
        $newitem->template = 0;
        $newitem->typ = $typ;
        $newitem->name = trim($item['#']['ITEMTEXT'][0]['#']);
        $newitem->label = trim($item['#']['ITEMLABEL'][0]['#']);
        if ($typ === 'captcha' || $typ === 'label') {
            $newitem->label = '';
            $newitem->name = '';
        }
        $newitem->options = trim($item['#']['OPTIONS'][0]['#']);
        $newitem->presentation = trim($item['#']['PRESENTATION'][0]['#']);
                switch($oldtyp) {
            case 'radio':
                $newitem->presentation = 'r>>>>>'.$newitem->presentation;
                break;
            case 'dropdown':
                $newitem->presentation = 'd>>>>>'.$newitem->presentation;
                break;
            case 'check':
                $newitem->presentation = 'c>>>>>'.$newitem->presentation;
                break;
            case 'radiorated':
                $newitem->presentation = 'r>>>>>'.$newitem->presentation;
                break;
            case 'dropdownrated':
                $newitem->presentation = 'd>>>>>'.$newitem->presentation;
                break;
        }

        if (isset($item['#']['DEPENDITEM'][0]['#'])) {
            $newitem->dependitem = intval($item['#']['DEPENDITEM'][0]['#']);
        } else {
            $newitem->dependitem = 0;
        }
        if (isset($item['#']['DEPENDVALUE'][0]['#'])) {
            $newitem->dependvalue = trim($item['#']['DEPENDVALUE'][0]['#']);
        } else {
            $newitem->dependvalue = '';
        }
        $olditemid = intval($item['#']['ITEMID'][0]['#']);

        if ($typ != 'pagebreak') {
            $newitem->hasvalue = $itemobj->get_hasvalue();
        } else {
            $newitem->hasvalue = 0;
        }
        $newitem->required = intval($item['@']['REQUIRED']);
        $newitem->position = $position;
        $newid = $DB->insert_record('feedback_item', $newitem);

        $itembackup[$olditemid] = $newid;
        if ($newitem->dependitem) {
            $dependitemsmap[$newid] = $newitem->dependitem;
        }

    }
        foreach ($dependitemsmap as $key => $dependitem) {
        $newitem = $DB->get_record('feedback_item', array('id'=>$key));
        $newitem->dependitem = $itembackup[$newitem->dependitem];
        $DB->update_record('feedback_item', $newitem);
    }

    return $error;
}

function feedback_check_xml_utf8($text) {
        $searchpattern = '/^\<\?xml.+(encoding=\"([a-z0-9-]*)\").+\?\>/is';

    if (!preg_match($searchpattern, $text, $match)) {
        return false;     }

                if (isset($match[0]) AND !isset($match[1])) {         return $text;
    }

        if (isset($match[0]) AND isset($match[1]) AND isset($match[2])) {
        $enc = $match[2];
        return core_text::convert($text, $enc);
    }
}
