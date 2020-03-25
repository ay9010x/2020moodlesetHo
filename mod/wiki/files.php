<?php



require_once('../../config.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');

$pageid       = required_param('pageid', PARAM_INT); $wid          = optional_param('wid', 0, PARAM_INT); $currentgroup = optional_param('group', 0, PARAM_INT); $userid       = optional_param('uid', 0, PARAM_INT); $groupanduser = optional_param('groupanduser', null, PARAM_TEXT);

if (!$page = wiki_get_page($pageid)) {
    print_error('incorrectpageid', 'wiki');
}

if ($groupanduser) {
    list($currentgroup, $userid) = explode('-', $groupanduser);
    $currentgroup = clean_param($currentgroup, PARAM_INT);
    $userid       = clean_param($userid, PARAM_INT);
}

if ($wid) {
        if (!$wiki = wiki_get_wiki($wid)) {
        print_error('incorrectwikiid', 'wiki');
    }
    if (!$subwiki = wiki_get_subwiki_by_group($wiki->id, $currentgroup, $userid)) {
                $subwikiid = wiki_add_subwiki($wiki->id, $currentgroup, $userid);
        $subwiki = wiki_get_subwiki($subwikiid);
    }
} else {
        if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
        print_error('incorrectsubwikiid', 'wiki');
    }

        if (!$wiki = wiki_get_wiki($subwiki->wikiid)) {
        print_error('incorrectwikiid', 'wiki');
    }
}

if (!$cm = get_coursemodule_from_instance("wiki", $subwiki->wikiid)) {
    print_error('invalidcoursemodule');
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$context = context_module::instance($cm->id);


$PAGE->set_url('/mod/wiki/files.php', array('pageid'=>$pageid));
require_login($course, true, $cm);

if (!wiki_user_can_view($subwiki, $wiki)) {
    print_error('cannotviewfiles', 'wiki');
}

$PAGE->set_title(get_string('wikifiles', 'wiki'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(format_string(get_string('wikifiles', 'wiki')));
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($wiki->name));
echo $OUTPUT->box(format_module_intro('wiki', $wiki, $PAGE->cm->id), 'generalbox', 'intro');

$renderer = $PAGE->get_renderer('mod_wiki');

$tabitems = array('view' => 'view', 'edit' => 'edit', 'comments' => 'comments', 'history' => 'history', 'map' => 'map', 'files' => 'files', 'admin' => 'admin');

$options = array('activetab'=>'files');
echo $renderer->tabs($page, $tabitems, $options);


echo $OUTPUT->box_start('generalbox');
echo $renderer->wiki_print_subwiki_selector($PAGE->activityrecord, $subwiki, $page, 'files');
echo $renderer->wiki_files_tree($context, $subwiki);
echo $OUTPUT->box_end();

if (has_capability('mod/wiki:managefiles', $context)) {
    echo $OUTPUT->single_button(new moodle_url('/mod/wiki/filesedit.php', array('subwiki'=>$subwiki->id, 'pageid'=>$pageid)), get_string('editfiles', 'wiki'), 'get');
}
echo $OUTPUT->footer();
