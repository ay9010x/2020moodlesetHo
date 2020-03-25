<?php




require_once('../../config.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

$pageid = required_param('pageid', PARAM_INT); $option = optional_param('option', 0, PARAM_INT); 
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

$wikipage = new page_wiki_map($wiki, $subwiki, $cm);

$context = context_module::instance($cm->id);
$event = \mod_wiki\event\page_map_viewed::create(
        array(
            'context' => $context,
            'objectid' => $pageid,
            'other' => array(
                'option' => $option
                )
            ));
$event->add_record_snapshot('wiki_pages', $page);
$event->add_record_snapshot('wiki', $wiki);
$event->add_record_snapshot('wiki_subwikis', $subwiki);
$event->trigger();

$wikipage->set_view($option);
$wikipage->set_page($page);
$wikipage->print_header();
$wikipage->print_content();

$wikipage->print_footer();
