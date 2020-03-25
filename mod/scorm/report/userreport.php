<?php



require_once("../../../config.php");
require_once($CFG->dirroot.'/mod/scorm/locallib.php');

$id = required_param('id', PARAM_INT); $userid = required_param('user', PARAM_INT); $attempt = optional_param('attempt', 1, PARAM_INT); 
$url = new moodle_url('/mod/scorm/report/userreport.php', array('id' => $id,
                                                                'user' => $userid,
                                                                'attempt' => $attempt));
$tracksurl = new moodle_url('/mod/scorm/report/userreporttracks.php', array('id' => $id,
                                                                            'user' => $userid,
                                                                            'attempt' => $attempt));
$cm = get_coursemodule_from_id('scorm', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$scorm = $DB->get_record('scorm', array('id' => $cm->instance), '*', MUST_EXIST);
$user = $DB->get_record('user', array('id' => $userid), user_picture::fields(), MUST_EXIST);
$attemptids = scorm_get_all_attempts($scorm->id, $userid);

$PAGE->set_url($url);

require_login($course, false, $cm);
$contextmodule = context_module::instance($cm->id);
require_capability('mod/scorm:viewreport', $contextmodule);

if (!groups_user_groups_visible($course, $userid, $cm)) {
    throw new moodle_exception('nopermissiontoshow');
}

$event = \mod_scorm\event\user_report_viewed::create(array(
    'context' => $contextmodule,
    'relateduserid' => $userid,
    'other' => array('attemptid' => $attempt, 'instanceid' => $scorm->id)
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('scorm', $scorm);
$event->trigger();

$strreport = get_string('report', 'scorm');
$strattempt = get_string('attempt', 'scorm');

$PAGE->set_title("$course->shortname: ".format_string($scorm->name));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strreport, new moodle_url('/mod/scorm/report.php', array('id' => $cm->id)));
$PAGE->navbar->add(fullname($user). " - $strattempt $attempt");

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($scorm->name));
$currenttab = 'scoes';
require($CFG->dirroot . '/mod/scorm/report/userreporttabs.php');

$output = $PAGE->get_renderer('mod_scorm');
echo $output->view_user_heading($user, $course, $PAGE->url, $attempt, $attemptids);

if ($scoes = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id), 'sortorder, id')) {
        $table = new html_table();
    $table->head = array(
            get_string('title', 'scorm'),
            get_string('status', 'scorm'),
            get_string('time', 'scorm'),
            get_string('score', 'scorm'),
            '');
    $table->align = array('left', 'center', 'center', 'right', 'left');
    $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap');
    $table->width = '80%';
    $table->size = array('*', '*', '*', '*', '*');
    foreach ($scoes as $sco) {
        if ($sco->launch != '') {
            $row = array();
            $score = '&nbsp;';
            if ($trackdata = scorm_get_tracks($sco->id, $userid, $attempt)) {
                if ($trackdata->score_raw != '') {
                    $score = $trackdata->score_raw;
                }
                if ($trackdata->status == '') {
                    if (!empty($trackdata->progress)) {
                        $trackdata->status = $trackdata->progress;
                    } else {
                        $trackdata->status = 'notattempted';
                    }
                }
                $tracksurl->param('scoid', $sco->id);
                $detailslink = html_writer::link($tracksurl, get_string('details', 'scorm'));
            } else {
                $trackdata = new stdClass();
                $trackdata->status = 'notattempted';
                $trackdata->total_time = '&nbsp;';
                $detailslink = '&nbsp;';
            }
            $strstatus = get_string($trackdata->status, 'scorm');
            $row[] = '<img src="'.$OUTPUT->pix_url($trackdata->status, 'scorm').'" alt="'.$strstatus.'" title="'.
            $strstatus.'" />&nbsp;'.format_string($sco->title);
            $row[] = get_string($trackdata->status, 'scorm');
            $row[] = scorm_format_duration($trackdata->total_time);
            $row[] = $score;
            $row[] = $detailslink;
        } else {
            $row = array(format_string($sco->title), '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
        }
        $table->data[] = $row;
    }
    echo html_writer::table($table);
}


echo $OUTPUT->footer();
