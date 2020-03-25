<?php




require_once('../../config.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

$pageid = required_param('pageid', PARAM_INT); $delete = optional_param('delete', 0, PARAM_INT); $option = optional_param('option', 1, PARAM_INT); $listall = optional_param('listall', 0, PARAM_INT); $toversion = optional_param('toversion', 0, PARAM_INT); $fromversion = optional_param('fromversion', 0, PARAM_INT); 
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

$context = context_module::instance($cm->id);
require_capability('mod/wiki:managewiki', $context);

if (!empty($delete) && confirm_sesskey()) {
    if ($pageid != $delete) {
                $deletepage = wiki_get_page($delete);
        if (!$deletepage || $deletepage->subwikiid != $page->subwikiid) {
            print_error('incorrectsubwikiid', 'wiki');
        }
    }
    wiki_delete_pages($context, $delete, $page->subwikiid);
            if ($pageid == $delete) {
        $params = array('swid' => $page->subwikiid, 'title' => $page->title);
        $url = new moodle_url('/mod/wiki/create.php', $params);
        redirect($url);
    }
}

if (!empty($toversion) && !empty($fromversion) && confirm_sesskey()) {
        $versioncount = wiki_count_wiki_page_versions($pageid);
    $versioncount -= 1;     $totalversionstodelete = $toversion - $fromversion;
    $totalversionstodelete += 1; 
    if (($totalversionstodelete >= $versioncount) || ($versioncount <= 1)) {
        print_error('incorrectdeleteversions', 'wiki');
    } else {
        $versions = array();
        for ($i = $fromversion; $i <= $toversion; $i++) {
                        if (wiki_get_wiki_page_version($pageid, $i)) {
                array_push($versions, $i);
            }
        }
        $purgeversions[$pageid] = $versions;
        wiki_delete_page_versions($purgeversions, $context);
    }
}

$wikipage = new page_wiki_admin($wiki, $subwiki, $cm);

$wikipage->set_page($page);
$wikipage->print_header();
$wikipage->set_view($option, empty($listall)?true:false);
$wikipage->print_content();

$wikipage->print_footer();
