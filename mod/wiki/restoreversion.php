<?php




require_once('../../config.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

$pageid = required_param('pageid', PARAM_INT);
$versionid = required_param('versionid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

if (!$page = wiki_get_page($pageid)) {
    print_error('incorrectpageid', 'wiki');
}

if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
    print_error('incorrectsubwikiid', 'wiki');
}

if (!$wiki = wiki_get_wiki($subwiki->wikiid)) {
    print_error('incorrectwikiid', 'wiki');
}

if (!$cm = get_coursemodule_from_instance('wiki', $wiki->id)) {
    print_error('invalidcoursemodule');
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

if (!wiki_user_can_view($subwiki)) {
    print_error('cannotviewpage', 'wiki');
}

if ($confirm) {
    if (!confirm_sesskey()) {
        print_error(get_string('invalidsesskey', 'wiki'));
    }
    $wikipage = new page_wiki_confirmrestore($wiki, $subwiki, $cm);
    $wikipage->set_page($page);
    $wikipage->set_versionid($versionid);

} else {

    $wikipage = new page_wiki_restoreversion($wiki, $subwiki, $cm);
    $wikipage->set_page($page);
    $wikipage->set_versionid($versionid);

}

$wikipage->print_header();
$wikipage->print_content();

$wikipage->print_footer();
