<?php




require_once('../../config.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

$search = optional_param('searchstring', null, PARAM_TEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$searchcontent = optional_param('searchwikicontent', 0, PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$subwikiid = optional_param('subwikiid', 0, PARAM_INT);
$userid = optional_param('uid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
if (!$cm = get_coursemodule_from_id('wiki', $cmid)) {
    print_error('invalidcoursemodule');
}

require_login($course, true, $cm);

if (!$wiki = wiki_get_wiki($cm->instance)) {
    print_error('incorrectwikiid', 'wiki');
}

if ($subwikiid) {
        $subwiki = wiki_get_subwiki($subwikiid);
    if (!$subwiki || $subwiki->wikiid != $wiki->id) {
        print_error('incorrectsubwikiid', 'wiki');
    }
} else {
        $gid = groups_get_activity_group($cm);

        if ($wiki->wikimode == 'individual') {
        $userid = $userid ? $userid : $USER->id;
    } else {
        $userid = 0;
    }
    if (!$subwiki = wiki_get_subwiki_by_group($cm->instance, $gid, $userid)) {
                $params = array('wid' => $wiki->id, 'group' => $gid, 'uid' => $userid, 'title' => $wiki->firstpagetitle);
        $url = new moodle_url('/mod/wiki/view.php', $params);
        redirect($url);
    }
}

if ($subwiki && !wiki_user_can_view($subwiki, $wiki)) {
    print_error('cannotviewpage', 'wiki');
}

$wikipage = new page_wiki_search($wiki, $subwiki, $cm);

$wikipage->set_search_string($search, $searchcontent);

$wikipage->set_title(get_string('search'));

$wikipage->print_header();

$wikipage->print_content();

$wikipage->print_footer();
