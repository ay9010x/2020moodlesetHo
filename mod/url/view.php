<?php




require('../../config.php');
require_once("$CFG->dirroot/mod/url/lib.php");
require_once("$CFG->dirroot/mod/url/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        $u        = optional_param('u', 0, PARAM_INT);         $redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {      $url = $DB->get_record('url', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('url', $url->id, $url->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('url', $id, 0, false, MUST_EXIST);
    $url = $DB->get_record('url', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/url:view', $context);

url_view($url, $course, $cm, $context);

$PAGE->set_url('/mod/url/view.php', array('id' => $cm->id));

$exturl = trim($url->externalurl);
if (empty($exturl) or $exturl === 'http://') {
    url_print_header($url, $cm, $course);
    url_print_heading($url, $cm, $course);
    url_print_intro($url, $cm, $course);
    notice(get_string('invalidstoredurl', 'url'), new moodle_url('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($exturl);

$displaytype = url_get_final_display_type($url);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
            if (strpos(get_local_referer(false), 'modedit.php') === false) {
        $redirect = true;
    }
}

if ($redirect) {
            $fullurl = str_replace('&amp;', '&', url_get_full_url($url, $cm, $course));

    if (!course_get_format($course)->has_view_page()) {
                        $editurl = null;
        if (has_capability('moodle/course:manageactivities', $context)) {
            $editurl = new moodle_url('/course/modedit.php', array('update' => $cm->id));
            $edittext = get_string('editthisactivity');
        } else if (has_capability('moodle/course:update', $context->get_course_context())) {
            $editurl = new moodle_url('/course/edit.php', array('id' => $course->id));
            $edittext = get_string('editcoursesettings');
        }
        if ($editurl) {
            redirect($fullurl, html_writer::link($editurl, $edittext)."<br/>".
                    get_string('pageshouldredirect'), 10);
        }
    }
    redirect($fullurl);
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        url_display_embed($url, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        url_display_frame($url, $cm, $course);
        break;
    default:
        url_print_workaround($url, $cm, $course);
        break;
}
