<?php




require_once('../../config.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

$id = optional_param('id', 0, PARAM_INT); 
$pageid = optional_param('pageid', 0, PARAM_INT); 
$wid = optional_param('wid', 0, PARAM_INT); $title = optional_param('title', '', PARAM_TEXT); $currentgroup = optional_param('group', 0, PARAM_INT); $userid = optional_param('uid', 0, PARAM_INT); $groupanduser = optional_param('groupanduser', 0, PARAM_TEXT);

$edit = optional_param('edit', -1, PARAM_BOOL);

$action = optional_param('action', '', PARAM_ALPHA);
$swid = optional_param('swid', 0, PARAM_INT); 

if ($id) {
        if (!$cm = get_coursemodule_from_id('wiki', $id)) {
        print_error('invalidcoursemodule');
    }

        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    require_login($course, true, $cm);

        if (!$wiki = wiki_get_wiki($cm->instance)) {
        print_error('incorrectwikiid', 'wiki');
    }
    $PAGE->set_cm($cm);

                
        $currentgroup = groups_get_activity_group($cm);

        if ($wiki->wikimode == 'individual') {
        $userid = $USER->id;
    } else {
        $userid = 0;
    }

        if (!$subwiki = wiki_get_subwiki_by_group($wiki->id, $currentgroup, $userid)) {
        $params = array('wid' => $wiki->id, 'group' => $currentgroup, 'uid' => $userid, 'title' => $wiki->firstpagetitle);
        $url = new moodle_url('/mod/wiki/create.php', $params);
        redirect($url);
    }

        if (!$page = wiki_get_first_page($subwiki->id, $wiki)) {
        $params = array('swid'=>$subwiki->id, 'title'=>$wiki->firstpagetitle);
        $url = new moodle_url('/mod/wiki/create.php', $params);
        redirect($url);
    }

    
} elseif ($pageid) {

        if (!$page = wiki_get_page($pageid)) {
        print_error('incorrectpageid', 'wiki');
    }

        if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
        print_error('incorrectsubwikiid', 'wiki');
    }

        if (!$wiki = wiki_get_wiki($subwiki->wikiid)) {
        print_error('incorrectwikiid', 'wiki');
    }

        if (!$cm = get_coursemodule_from_instance("wiki", $subwiki->wikiid)) {
        print_error('invalidcoursemodule');
    }

    $currentgroup = $subwiki->groupid;

        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    require_login($course, true, $cm);
    
} elseif ($wid && $title) {

        if (!$wiki = wiki_get_wiki($wid)) {
        print_error('incorrectwikiid', 'wiki');
    }

        if (!$cm = get_coursemodule_from_instance("wiki", $wiki->id)) {
        print_error('invalidcoursemodule');
    }

        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    require_login($course, true, $cm);

    $groupmode = groups_get_activity_groupmode($cm);

    if ($wiki->wikimode == 'individual' && ($groupmode == SEPARATEGROUPS || $groupmode == VISIBLEGROUPS)) {
        list($gid, $uid) = explode('-', $groupanduser);
    } else if ($wiki->wikimode == 'individual') {
        $gid = 0;
        $uid = $userid;
    } else if ($groupmode == NOGROUPS) {
        $gid = 0;
        $uid = 0;
    } else {
        $gid = $currentgroup;
        $uid = 0;
    }

        if (!$subwiki = wiki_get_subwiki_by_group($wiki->id, $gid, $uid)) {
        $context = context_module::instance($cm->id);

        $modeanduser = $wiki->wikimode == 'individual' && $uid != $USER->id;
        $modeandgroupmember = $wiki->wikimode == 'collaborative' && !groups_is_member($gid);

        $manage = has_capability('mod/wiki:managewiki', $context);
        $edit = has_capability('mod/wiki:editpage', $context);
        $manageandedit = $manage && $edit;

        if ($groupmode == VISIBLEGROUPS and ($modeanduser || $modeandgroupmember) and !$manageandedit) {
            print_error('nocontent','wiki');
        }

        $params = array('wid' => $wiki->id, 'group' => $gid, 'uid' => $uid, 'title' => $title);
        $url = new moodle_url('/mod/wiki/create.php', $params);
        redirect($url);
    }

        if (!$page = wiki_get_page_by_title($subwiki->id, $title)) {
        $params = array('wid' => $wiki->id, 'group' => $gid, 'uid' => $uid, 'title' => $wiki->firstpagetitle);
                if (!wiki_get_page_by_title($subwiki->id, $wiki->firstpagetitle)) {
            $url = new moodle_url('/mod/wiki/create.php', $params);
        } else {
            $url = new moodle_url('/mod/wiki/view.php', $params);
        }
        redirect($url);
    }

                                                                                                                                                                                            } else {
    print_error('invalidparameters', 'wiki');
}

if (!wiki_user_can_view($subwiki, $wiki)) {
    print_error('cannotviewpage', 'wiki');
}

if (($edit != - 1) and $PAGE->user_allowed_editing()) {
    $USER->editing = $edit;
}

$wikipage = new page_wiki_view($wiki, $subwiki, $cm);

$wikipage->set_gid($currentgroup);
$wikipage->set_page($page);

$context = context_module::instance($cm->id);
if ($pageid) {
    wiki_page_view($wiki, $page, $course, $cm, $context, null, null, $subwiki);
} else if ($id) {
    wiki_view($wiki, $course, $cm, $context);
} else if ($wid && $title) {
    $other = array(
        'title' => $title,
        'wid' => $wid,
        'group' => $gid,
        'groupanduser' => $groupanduser
    );
    wiki_page_view($wiki, $page, $course, $cm, $context, $uid, $other, $subwiki);
}

$wikipage->print_header();
$wikipage->print_content();

$wikipage->print_footer();
