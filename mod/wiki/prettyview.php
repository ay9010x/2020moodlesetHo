<?php



require_once('../../config.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

$pageid = required_param('pageid', PARAM_INT); 
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

$wikipage = new page_wiki_prettyview($wiki, $subwiki, $cm);

$wikipage->set_page($page);

$context = context_module::instance($cm->id);

$other = array('prettyview' => true);
wiki_page_view($wiki, $page, $course, $cm, $context, null, $other, $subwiki);

$wikipage->print_header();
$wikipage->print_content();
$wikipage->print_footer();
