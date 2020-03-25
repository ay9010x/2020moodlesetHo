<?php




require_once('../../config.php');
require_once($CFG->dirroot . "/mod/wiki/pagelib.php");
require_once($CFG->dirroot . "/mod/wiki/locallib.php");
require_once($CFG->dirroot . '/mod/wiki/comments_form.php');

$pageid = required_param('pageid', PARAM_TEXT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$id = optional_param('id', 0, PARAM_INT);
$commentid = optional_param('commentid', 0, PARAM_INT);
$newcontent = optional_param('newcontent', '', PARAM_CLEANHTML);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

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

if ($action == 'add' || $action == 'edit') {
        if (!confirm_sesskey()) {
        print_error(get_string('invalidsesskey', 'wiki'));
    }
    $comm = new page_wiki_handlecomments($wiki, $subwiki, $cm);
    $comm->set_page($page);
} else {
    if(!$confirm) {
        $comm = new page_wiki_deletecomment($wiki, $subwiki, $cm);
        $comm->set_page($page);
        $comm->set_url();
    } else {
        $comm = new page_wiki_handlecomments($wiki, $subwiki, $cm);
        $comm->set_page($page);
        if (!confirm_sesskey()) {
            print_error(get_string('invalidsesskey', 'wiki'));
        }
    }
}

if ($action == 'delete') {
    $comm->set_action($action, $commentid, 0);
} else {
    if (empty($newcontent)) {
        $form = new mod_wiki_comments_form();
        $newcomment = $form->get_data();
        $content = $newcomment->entrycomment_editor['text'];
    } else {
        $content = $newcontent;
    }

    if ($action == 'edit') {
        $comm->set_action($action, $id, $content);
    } else {
        $action = 'add';
        $comm->set_action($action, 0, $content);
    }
}

$comm->print_header();
$comm->print_content();
$comm->print_footer();
