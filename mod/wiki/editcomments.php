<?php



require_once('../../config.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

$pageid = required_param('pageid', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$commentid = optional_param('commentid', 0, PARAM_INT);

if (!$page = wiki_get_page($pageid)) {
    print_error('incorrectpageid', 'wiki');
}
if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
    print_error('incorrectsubwikiid', 'wiki');
}
if (!$cm = get_coursemodule_from_instance("wiki", $subwiki->wikiid)) {
    print_error('invalidcoursemodule');
}
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
if (!$wiki = wiki_get_wiki($subwiki->wikiid)) {
    print_error('incorrectwikiid', 'wiki');
}
require_login($course, true, $cm);

if (!wiki_user_can_view($subwiki, $wiki)) {
    print_error('cannotviewpage', 'wiki');
}

$editcomments = new page_wiki_editcomment($wiki, $subwiki, $cm);
$comment = new stdClass();
if ($action == 'edit') {
    if (!$comment = $DB->get_record('comments', array('id' => $commentid))) {
        print_error('invalidcomment');
    }
}

$editcomments->set_page($page);
$editcomments->set_action($action, $comment);

$editcomments->print_header();
$editcomments->print_content();
$editcomments->print_footer();
