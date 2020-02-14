<?php




require_once("../../config.php");
require_once("lib.php");

$f          = required_param('f',PARAM_INT); $mark       = required_param('mark',PARAM_ALPHA); $d          = optional_param('d',0,PARAM_INT); $returnpage = optional_param('returnpage', 'index.php', PARAM_FILE);    
$url = new moodle_url('/mod/forum/markposts.php', array('f'=>$f, 'mark'=>$mark));
if ($d !== 0) {
    $url->param('d', $d);
}
if ($returnpage !== 'index.php') {
    $url->param('returnpage', $returnpage);
}
$PAGE->set_url($url);

if (! $forum = $DB->get_record("forum", array("id" => $f))) {
    print_error('invalidforumid', 'forum');
}

if (! $course = $DB->get_record("course", array("id" => $forum->course))) {
    print_error('invalidcourseid');
}

if (!$cm = get_coursemodule_from_instance("forum", $forum->id, $course->id)) {
    print_error('invalidcoursemodule');
}

$user = $USER;

require_login($course, false, $cm);
require_sesskey();

if ($returnpage == 'index.php') {
    $returnto = new moodle_url("/mod/forum/$returnpage", array('id' => $course->id));
} else {
    $returnto = new moodle_url("/mod/forum/$returnpage", array('f' => $forum->id));
}

if (isguestuser()) {       $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('noguesttracking', 'forum').'<br /><br />'.get_string('liketologin'), get_login_url(), $returnto);
    echo $OUTPUT->footer();
    exit;
}

$info = new stdClass();
$info->name  = fullname($user);
$info->forum = format_string($forum->name);

if ($mark == 'read') {
    if (!empty($d)) {
        if (! $discussion = $DB->get_record('forum_discussions', array('id'=> $d, 'forum'=> $forum->id))) {
            print_error('invaliddiscussionid', 'forum');
        }

        forum_tp_mark_discussion_read($user, $d);
    } else {
                $currentgroup = groups_get_activity_group($cm);
        if(!$currentgroup) {
                                    $currentgroup=false;
        }
        forum_tp_mark_forum_read($user, $forum->id, $currentgroup);
    }

}

redirect($returnto);

