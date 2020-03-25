<?php




defined('MOODLE_INTERNAL') || die();


function wiki_add_instance($wiki) {
    global $DB;

    $wiki->timemodified = time();
        if (empty($wiki->forceformat)) {
        $wiki->forceformat = 0;
    }
    return $DB->insert_record('wiki', $wiki);
}


function wiki_update_instance($wiki) {
    global $DB;

    $wiki->timemodified = time();
    $wiki->id = $wiki->instance;
    if (empty($wiki->forceformat)) {
        $wiki->forceformat = 0;
    }

    
    return $DB->update_record('wiki', $wiki);
}


function wiki_delete_instance($id) {
    global $DB;

    if (!$wiki = $DB->get_record('wiki', array('id' => $id))) {
        return false;
    }

    $result = true;

        $subwikis = $DB->get_records('wiki_subwikis', array('wikiid' => $wiki->id));

    foreach ($subwikis as $subwiki) {
                if (!$DB->delete_records('wiki_links', array('subwikiid' => $subwiki->id), IGNORE_MISSING)) {
            $result = false;
        }

                if ($pages = $DB->get_records('wiki_pages', array('subwikiid' => $subwiki->id))) {
            foreach ($pages as $page) {
                                if (!$DB->delete_records('wiki_locks', array('pageid' => $page->id), IGNORE_MISSING)) {
                    $result = false;
                }

                                if (!$DB->delete_records('wiki_versions', array('pageid' => $page->id), IGNORE_MISSING)) {
                    $result = false;
                }
            }

                        if (!$DB->delete_records('wiki_pages', array('subwikiid' => $subwiki->id), IGNORE_MISSING)) {
                $result = false;
            }
        }

                if (!$DB->delete_records('wiki_synonyms', array('subwikiid' => $subwiki->id), IGNORE_MISSING)) {
            $result = false;
        }

                if (!$DB->delete_records('wiki_subwikis', array('id' => $subwiki->id), IGNORE_MISSING)) {
            $result = false;
        }
    }

        if (!$DB->delete_records('wiki', array('id' => $wiki->id))) {
        $result = false;
    }

    return $result;
}


function wiki_reset_userdata($data) {
    global $CFG,$DB;
    require_once($CFG->dirroot . '/mod/wiki/pagelib.php');
    require_once($CFG->dirroot . "/mod/wiki/locallib.php");

    $componentstr = get_string('modulenameplural', 'wiki');
    $status = array();

        if (!$wikis = $DB->get_records('wiki', array('course' => $data->courseid))) {
        return false;
    }
    if (empty($data->reset_wiki_comments) && empty($data->reset_wiki_tags) && empty($data->reset_wiki_pages)) {
        return $status;
    }

    foreach ($wikis as $wiki) {
        if (!$cm = get_coursemodule_from_instance('wiki', $wiki->id, $data->courseid)) {
            continue;
        }
        $context = context_module::instance($cm->id);

                if (!empty($data->reset_wiki_pages) || !empty($data->reset_wiki_tags)) {

                        $subwikis = wiki_get_subwikis($wiki->id);

            foreach ($subwikis as $subwiki) {
                                if ($pages = wiki_get_page_list($subwiki->id)) {
                                        if (empty($data->reset_wiki_pages)) {
                                                foreach ($pages as $page) {
                            core_tag_tag::remove_all_item_tags('mod_wiki', 'wiki_pages', $page->id);
                        }
                    } else {
                                                wiki_delete_pages($context, $pages, $subwiki->id);
                    }
                }
                if (!empty($data->reset_wiki_pages)) {
                                        $DB->delete_records('wiki_subwikis', array('id' => $subwiki->id), IGNORE_MISSING);

                                        $fs = get_file_storage();
                    $fs->delete_area_files($context->id, 'mod_wiki', 'attachments');
                }
            }

            if (!empty($data->reset_wiki_pages)) {
                $status[] = array('component' => $componentstr, 'item' => get_string('deleteallpages', 'wiki'),
                    'error' => false);
            }
            if (!empty($data->reset_wiki_tags)) {
                $status[] = array('component' => $componentstr, 'item' => get_string('tagsdeleted', 'wiki'), 'error' => false);
            }
        }

                if (!empty($data->reset_wiki_comments) || !empty($data->reset_wiki_pages)) {
            $DB->delete_records_select('comments', "contextid = ? AND commentarea='wiki_page'", array($context->id));
            if (!empty($data->reset_wiki_comments)) {
                $status[] = array('component' => $componentstr, 'item' => get_string('deleteallcomments'), 'error' => false);
            }
        }
    }
    return $status;
}


function wiki_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'wikiheader', get_string('modulenameplural', 'wiki'));
    $mform->addElement('advcheckbox', 'reset_wiki_pages', get_string('deleteallpages', 'wiki'));
    $mform->addElement('advcheckbox', 'reset_wiki_tags', get_string('removeallwikitags', 'wiki'));
    $mform->addElement('advcheckbox', 'reset_wiki_comments', get_string('deleteallcomments'));
}


function wiki_supports($feature) {
    switch ($feature) {
    case FEATURE_GROUPS:
        return true;
    case FEATURE_GROUPINGS:
        return true;
    case FEATURE_MOD_INTRO:
        return true;
    case FEATURE_COMPLETION_TRACKS_VIEWS:
        return true;
    case FEATURE_GRADE_HAS_GRADE:
        return false;
    case FEATURE_GRADE_OUTCOMES:
        return false;
    case FEATURE_RATE:
        return false;
    case FEATURE_BACKUP_MOODLE2:
        return true;
    case FEATURE_SHOW_DESCRIPTION:
        return true;

    default:
        return null;
    }
}


function wiki_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG, $DB, $OUTPUT;

    $sql = "SELECT p.id, p.timemodified, p.subwikiid, sw.wikiid, w.wikimode, sw.userid, sw.groupid
            FROM {wiki_pages} p
                JOIN {wiki_subwikis} sw ON sw.id = p.subwikiid
                JOIN {wiki} w ON w.id = sw.wikiid
            WHERE p.timemodified > ? AND w.course = ?
            ORDER BY p.timemodified ASC";
    if (!$pages = $DB->get_records_sql($sql, array($timestart, $course->id))) {
        return false;
    }
    require_once($CFG->dirroot . "/mod/wiki/locallib.php");

    $wikis = array();

    $modinfo = get_fast_modinfo($course);

    $subwikivisible = array();
    foreach ($pages as $page) {
        if (!isset($subwikivisible[$page->subwikiid])) {
            $subwiki = (object)array('id' => $page->subwikiid, 'wikiid' => $page->wikiid,
                'groupid' => $page->groupid, 'userid' => $page->userid);
            $wiki = (object)array('id' => $page->wikiid, 'course' => $course->id, 'wikimode' => $page->wikimode);
            $subwikivisible[$page->subwikiid] = wiki_user_can_view($subwiki, $wiki);
        }
        if ($subwikivisible[$page->subwikiid]) {
            $wikis[] = $page;
        }
    }
    unset($subwikivisible);
    unset($pages);

    if (!$wikis) {
        return false;
    }
    echo $OUTPUT->heading(get_string("updatedwikipages", 'wiki') . ':', 3);
    foreach ($wikis as $wiki) {
        $cm = $modinfo->instances['wiki'][$wiki->wikiid];
        $link = $CFG->wwwroot . '/mod/wiki/view.php?pageid=' . $wiki->id;
        print_recent_activity_note($wiki->timemodified, $wiki, $cm->name, $link, false, $viewfullnames);
    }

    return true; }

function wiki_cron() {
    global $CFG;

    return true;
}


function wiki_grades($wikiid) {
    return null;
}


function wiki_scale_used($wikiid, $scaleid) {
    $return = false;

                    
    return $return;
}


function wiki_scale_used_anywhere($scaleid) {
    global $DB;

                    
    return false;
}


function wiki_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    require_once($CFG->dirroot . "/mod/wiki/locallib.php");

    if ($filearea == 'attachments') {
        $swid = (int) array_shift($args);

        if (!$subwiki = wiki_get_subwiki($swid)) {
            return false;
        }

        require_capability('mod/wiki:viewpage', $context);

        $relativepath = implode('/', $args);

        $fullpath = "/$context->id/mod_wiki/attachments/$swid/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, null, 0, $options);
    }
}

function wiki_search_form($cm, $search = '', $subwiki = null) {
    global $CFG, $OUTPUT;

    $output = '<div class="wikisearch">';
    $output .= '<form method="post" action="' . $CFG->wwwroot . '/mod/wiki/search.php" style="display:inline">';
    $output .= '<fieldset class="invisiblefieldset">';
    $output .= '<legend class="accesshide">'. get_string('searchwikis', 'wiki') .'</legend>';
    $output .= '<label class="accesshide" for="searchwiki">' . get_string("searchterms", "wiki") . '</label>';
    $output .= '<input id="searchwiki" name="searchstring" type="text" size="18" value="' . s($search, true) . '" alt="search" />';
    $output .= '<input name="courseid" type="hidden" value="' . $cm->course . '" />';
    $output .= '<input name="cmid" type="hidden" value="' . $cm->id . '" />';
    if (!empty($subwiki->id)) {
        $output .= '<input name="subwikiid" type="hidden" value="' . $subwiki->id . '" />';
    }
    $output .= '<input name="searchwikicontent" type="hidden" value="1" />';
    $output .= '<input value="' . get_string('searchwikis', 'wiki') . '" type="submit" />';
    $output .= '</fieldset>';
    $output .= '</form>';
    $output .= '</div>';

    return $output;
}
function wiki_extend_navigation(navigation_node $navref, $course, $module, $cm) {
    global $CFG, $PAGE, $USER;

    require_once($CFG->dirroot . '/mod/wiki/locallib.php');

    $context = context_module::instance($cm->id);
    $url = $PAGE->url;
    $userid = 0;
    if ($module->wikimode == 'individual') {
        $userid = $USER->id;
    }

    if (!$wiki = wiki_get_wiki($cm->instance)) {
        return false;
    }

    if (!$gid = groups_get_activity_group($cm)) {
        $gid = 0;
    }
    if (!$subwiki = wiki_get_subwiki_by_group($cm->instance, $gid, $userid)) {
        return null;
    } else {
        $swid = $subwiki->id;
    }

    $pageid = $url->param('pageid');
    $cmid = $url->param('id');
    if (empty($pageid) && !empty($cmid)) {
                $page = wiki_get_page_by_title($swid, $wiki->firstpagetitle);
        $pageid = $page->id;
    }

    if (wiki_can_create_pages($context)) {
        $link = new moodle_url('/mod/wiki/create.php', array('action' => 'new', 'swid' => $swid));
        $node = $navref->add(get_string('newpage', 'wiki'), $link, navigation_node::TYPE_SETTING);
    }

    if (is_numeric($pageid)) {

        if (has_capability('mod/wiki:viewpage', $context)) {
            $link = new moodle_url('/mod/wiki/view.php', array('pageid' => $pageid));
            $node = $navref->add(get_string('view', 'wiki'), $link, navigation_node::TYPE_SETTING);
        }

        if (wiki_user_can_edit($subwiki)) {
            $link = new moodle_url('/mod/wiki/edit.php', array('pageid' => $pageid));
            $node = $navref->add(get_string('edit', 'wiki'), $link, navigation_node::TYPE_SETTING);
        }

        if (has_capability('mod/wiki:viewcomment', $context)) {
            $link = new moodle_url('/mod/wiki/comments.php', array('pageid' => $pageid));
            $node = $navref->add(get_string('comments', 'wiki'), $link, navigation_node::TYPE_SETTING);
        }

        if (has_capability('mod/wiki:viewpage', $context)) {
            $link = new moodle_url('/mod/wiki/history.php', array('pageid' => $pageid));
            $node = $navref->add(get_string('history', 'wiki'), $link, navigation_node::TYPE_SETTING);
        }

        if (has_capability('mod/wiki:viewpage', $context)) {
            $link = new moodle_url('/mod/wiki/map.php', array('pageid' => $pageid));
            $node = $navref->add(get_string('map', 'wiki'), $link, navigation_node::TYPE_SETTING);
        }

        if (has_capability('mod/wiki:viewpage', $context)) {
            $link = new moodle_url('/mod/wiki/files.php', array('pageid' => $pageid));
            $node = $navref->add(get_string('files', 'wiki'), $link, navigation_node::TYPE_SETTING);
        }

        if (has_capability('mod/wiki:managewiki', $context)) {
            $link = new moodle_url('/mod/wiki/admin.php', array('pageid' => $pageid));
            $node = $navref->add(get_string('admin', 'wiki'), $link, navigation_node::TYPE_SETTING);
        }
    }
}

function wiki_get_extra_capabilities() {
    return array('moodle/comment:view', 'moodle/comment:post', 'moodle/comment:delete');
}


function wiki_comment_permissions($comment_param) {
    return array('post'=>true, 'view'=>true);
}


function wiki_comment_validate($comment_param) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/wiki/locallib.php');
        if ($comment_param->commentarea != 'wiki_page') {
        throw new comment_exception('invalidcommentarea');
    }
        if (!$record = $DB->get_record('wiki_pages', array('id'=>$comment_param->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    if (!$subwiki = wiki_get_subwiki($record->subwikiid)) {
        throw new comment_exception('invalidsubwikiid');
    }
    if (!$wiki = wiki_get_wiki_from_pageid($comment_param->itemid)) {
        throw new comment_exception('invalidid', 'data');
    }
    if (!$course = $DB->get_record('course', array('id'=>$wiki->course))) {
        throw new comment_exception('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('wiki', $wiki->id, $course->id)) {
        throw new comment_exception('invalidcoursemodule');
    }
    $context = context_module::instance($cm->id);
        if ($subwiki->groupid) {
        $groupmode = groups_get_activity_groupmode($cm, $course);
        if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
            if (!groups_is_member($subwiki->groupid)) {
                throw new comment_exception('notmemberofgroup');
            }
        }
    }
        if ($context->id != $comment_param->context->id) {
        throw new comment_exception('invalidcontext');
    }
        if (!empty($comment_param->commentid)) {
        if ($comment = $DB->get_record('comments', array('id'=>$comment_param->commentid))) {
            if ($comment->commentarea != 'wiki_page') {
                throw new comment_exception('invalidcommentarea');
            }
            if ($comment->contextid != $context->id) {
                throw new comment_exception('invalidcontext');
            }
            if ($comment->itemid != $comment_param->itemid) {
                throw new comment_exception('invalidcommentitemid');
            }
        } else {
            throw new comment_exception('invalidcommentid');
        }
    }
    return true;
}


function wiki_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array(
        'mod-wiki-*'=>get_string('page-mod-wiki-x', 'wiki'),
        'mod-wiki-view'=>get_string('page-mod-wiki-view', 'wiki'),
        'mod-wiki-comments'=>get_string('page-mod-wiki-comments', 'wiki'),
        'mod-wiki-history'=>get_string('page-mod-wiki-history', 'wiki'),
        'mod-wiki-map'=>get_string('page-mod-wiki-map', 'wiki')
    );
    return $module_pagetype;
}


function wiki_view($wiki, $course, $cm, $context) {
        $params = array(
        'context' => $context,
        'objectid' => $wiki->id
    );
    $event = \mod_wiki\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('wiki', $wiki);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}


function wiki_page_view($wiki, $page, $course, $cm, $context, $uid = null, $other = null, $subwiki = null) {

        $params = array(
        'context' => $context,
        'objectid' => $page->id
    );
    if ($uid != null) {
        $params['relateduserid'] = $uid;
    }
    if ($other != null) {
        $params['other'] = $other;
    }

    $event = \mod_wiki\event\page_viewed::create($params);

    $event->add_record_snapshot('wiki_pages', $page);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('wiki', $wiki);
    if ($subwiki != null) {
        $event->add_record_snapshot('wiki_subwikis', $subwiki);
    }
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
