<?php



defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir.'/eventslib.php');
require_once($CFG->libdir.'/formslib.php');

define('FEEDBACK_ANONYMOUS_YES', 1);
define('FEEDBACK_ANONYMOUS_NO', 2);
define('FEEDBACK_MIN_ANONYMOUS_COUNT_IN_GROUP', 2);
define('FEEDBACK_DECIMAL', '.');
define('FEEDBACK_THOUSAND', ',');
define('FEEDBACK_RESETFORM_RESET', 'feedback_reset_data_');
define('FEEDBACK_RESETFORM_DROP', 'feedback_drop_feedback_');
define('FEEDBACK_MAX_PIX_LENGTH', '400'); define('FEEDBACK_DEFAULT_PAGE_COUNT', 20);


function feedback_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function feedback_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


function feedback_add_instance($feedback) {
    global $DB;

    $feedback->timemodified = time();
    $feedback->id = '';

    if (empty($feedback->site_after_submit)) {
        $feedback->site_after_submit = '';
    }

        $feedbackid = $DB->insert_record("feedback", $feedback);

    $feedback->id = $feedbackid;

    feedback_set_events($feedback);

    if (!isset($feedback->coursemodule)) {
        $cm = get_coursemodule_from_id('feedback', $feedback->id);
        $feedback->coursemodule = $cm->id;
    }
    $context = context_module::instance($feedback->coursemodule);

    $editoroptions = feedback_get_editor_options();

        if ($draftitemid = $feedback->page_after_submit_editor['itemid']) {
        $feedback->page_after_submit = file_save_draft_area_files($draftitemid, $context->id,
                                                    'mod_feedback', 'page_after_submit',
                                                    0, $editoroptions,
                                                    $feedback->page_after_submit_editor['text']);

        $feedback->page_after_submitformat = $feedback->page_after_submit_editor['format'];
    }
    $DB->update_record('feedback', $feedback);

    return $feedbackid;
}


function feedback_update_instance($feedback) {
    global $DB;

    $feedback->timemodified = time();
    $feedback->id = $feedback->instance;

    if (empty($feedback->site_after_submit)) {
        $feedback->site_after_submit = '';
    }

        $DB->update_record("feedback", $feedback);

        feedback_set_events($feedback);

    $context = context_module::instance($feedback->coursemodule);

    $editoroptions = feedback_get_editor_options();

        if ($draftitemid = $feedback->page_after_submit_editor['itemid']) {
        $feedback->page_after_submit = file_save_draft_area_files($draftitemid, $context->id,
                                                    'mod_feedback', 'page_after_submit',
                                                    0, $editoroptions,
                                                    $feedback->page_after_submit_editor['text']);

        $feedback->page_after_submitformat = $feedback->page_after_submit_editor['format'];
    }
    $DB->update_record('feedback', $feedback);

    return true;
}


function feedback_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($filearea === 'item' or $filearea === 'template') {
        $itemid = (int)array_shift($args);
                if (!$item = $DB->get_record('feedback_item', array('id'=>$itemid))) {
            return false;
        }
        $feedbackid = $item->feedback;
        $templateid = $item->template;
    }

    if ($filearea === 'page_after_submit' or $filearea === 'item') {
        if (! $feedback = $DB->get_record("feedback", array("id"=>$cm->instance))) {
            return false;
        }

        $feedbackid = $feedback->id;

                $canload = false;
                if (has_capability('mod/feedback:complete', $context)) {
            $canload = true;
        }

                if (has_capability('mod/feedback:view', $context)) {
            $canload = true;
        }

                        if (isset($CFG->feedback_allowfullanonymous)
                    AND $CFG->feedback_allowfullanonymous
                    AND $course->id == SITEID
                    AND $feedback->anonymous == FEEDBACK_ANONYMOUS_YES ) {
            $canload = true;
        }

        if (!$canload) {
            return false;
        }
    } else if ($filearea === 'template') {         if (!$template = $DB->get_record('feedback_template', array('id'=>$templateid))) {
            return false;
        }

                if (!$template->ispublic) {
            if (!has_capability('mod/feedback:edititems', $context)) {
                return false;
            }
        } else {             if (!isloggedin()) {
                return false;
            }
        }
    } else {
        return false;
    }

    if ($context->contextlevel == CONTEXT_MODULE) {
        if ($filearea !== 'item' and $filearea !== 'page_after_submit') {
            return false;
        }
    }

    if ($context->contextlevel == CONTEXT_COURSE || $context->contextlevel == CONTEXT_SYSTEM) {
        if ($filearea !== 'template') {
            return false;
        }
    }

    $relativepath = implode('/', $args);
    if ($filearea === 'page_after_submit') {
        $fullpath = "/{$context->id}/mod_feedback/$filearea/$relativepath";
    } else {
        $fullpath = "/{$context->id}/mod_feedback/$filearea/{$item->id}/$relativepath";
    }

    $fs = get_file_storage();

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

        send_stored_file($file, 0, 0, true, $options); 
    return false;
}


function feedback_delete_instance($id) {
    global $DB;

        $feedbackitems = $DB->get_records('feedback_item', array('feedback'=>$id));

        if (is_array($feedbackitems)) {
        foreach ($feedbackitems as $feedbackitem) {
            $DB->delete_records("feedback_value", array("item"=>$feedbackitem->id));
            $DB->delete_records("feedback_valuetmp", array("item"=>$feedbackitem->id));
        }
        if ($delitems = $DB->get_records("feedback_item", array("feedback"=>$id))) {
            foreach ($delitems as $delitem) {
                feedback_delete_item($delitem->id, false);
            }
        }
    }

        $DB->delete_records("feedback_completed", array("feedback"=>$id));

        $DB->delete_records("feedback_completedtmp", array("feedback"=>$id));

        $DB->delete_records('event', array('modulename'=>'feedback', 'instance'=>$id));
    return $DB->delete_records("feedback", array("id"=>$id));
}


function feedback_delete_course($course) {
    global $DB;

        return $DB->delete_records('feedback_template', array('course'=>$course->id));
}


function feedback_user_outline($course, $user, $mod, $feedback) {
    return null;
}


function feedback_get_recent_mod_activity(&$activities, &$index,
                                          $timemodified, $courseid,
                                          $cmid, $userid="", $groupid="") {

    global $CFG, $COURSE, $USER, $DB;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id'=>$courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];

    $sqlargs = array();

    $userfields = user_picture::fields('u', null, 'useridagain');
    $sql = " SELECT fk . * , fc . * , $userfields
                FROM {feedback_completed} fc
                    JOIN {feedback} fk ON fk.id = fc.feedback
                    JOIN {user} u ON u.id = fc.userid ";

    if ($groupid) {
        $sql .= " JOIN {groups_members} gm ON  gm.userid=u.id ";
    }

    $sql .= " WHERE fc.timemodified > ?
                AND fk.id = ?
                AND fc.anonymous_response = ?";
    $sqlargs[] = $timemodified;
    $sqlargs[] = $cm->instance;
    $sqlargs[] = FEEDBACK_ANONYMOUS_NO;

    if ($userid) {
        $sql .= " AND u.id = ? ";
        $sqlargs[] = $userid;
    }

    if ($groupid) {
        $sql .= " AND gm.groupid = ? ";
        $sqlargs[] = $groupid;
    }

    if (!$feedbackitems = $DB->get_records_sql($sql, $sqlargs)) {
        return;
    }

    $cm_context = context_module::instance($cm->id);

    if (!has_capability('mod/feedback:view', $cm_context)) {
        return;
    }

    $accessallgroups = has_capability('moodle/site:accessallgroups', $cm_context);
    $viewfullnames   = has_capability('moodle/site:viewfullnames', $cm_context);
    $groupmode       = groups_get_activity_groupmode($cm, $course);

    $aname = format_string($cm->name, true);
    foreach ($feedbackitems as $feedbackitem) {
        if ($feedbackitem->userid != $USER->id) {

            if ($groupmode == SEPARATEGROUPS and !$accessallgroups) {
                $usersgroups = groups_get_all_groups($course->id,
                                                     $feedbackitem->userid,
                                                     $cm->groupingid);
                if (!is_array($usersgroups)) {
                    continue;
                }
                $usersgroups = array_keys($usersgroups);
                $intersect = array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid));
                if (empty($intersect)) {
                    continue;
                }
            }
        }

        $tmpactivity = new stdClass();

        $tmpactivity->type      = 'feedback';
        $tmpactivity->cmid      = $cm->id;
        $tmpactivity->name      = $aname;
        $tmpactivity->sectionnum= $cm->sectionnum;
        $tmpactivity->timestamp = $feedbackitem->timemodified;

        $tmpactivity->content = new stdClass();
        $tmpactivity->content->feedbackid = $feedbackitem->id;
        $tmpactivity->content->feedbackuserid = $feedbackitem->userid;

        $tmpactivity->user = user_picture::unalias($feedbackitem, null, 'useridagain');
        $tmpactivity->user->fullname = fullname($feedbackitem, $viewfullnames);

        $activities[$index++] = $tmpactivity;
    }

    return;
}


function feedback_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
    global $CFG, $OUTPUT;

    echo '<table border="0" cellpadding="3" cellspacing="0" class="forum-recent">';

    echo "<tr><td class=\"userpicture\" valign=\"top\">";
    echo $OUTPUT->user_picture($activity->user, array('courseid'=>$courseid));
    echo "</td><td>";

    if ($detail) {
        $modname = $modnames[$activity->type];
        echo '<div class="title">';
        echo "<img src=\"" . $OUTPUT->pix_url('icon', $activity->type) . "\" ".
             "class=\"icon\" alt=\"$modname\" />";
        echo "<a href=\"$CFG->wwwroot/mod/feedback/view.php?id={$activity->cmid}\">{$activity->name}</a>";
        echo '</div>';
    }

    echo '<div class="title">';
    echo '</div>';

    echo '<div class="user">';
    echo "<a href=\"$CFG->wwwroot/user/view.php?id={$activity->user->id}&amp;course=$courseid\">"
         ."{$activity->user->fullname}</a> - ".userdate($activity->timestamp);
    echo '</div>';

    echo "</td></tr></table>";

    return;
}


function feedback_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

        $feedback = $DB->get_record('feedback', array('id'=>$cm->instance), '*', MUST_EXIST);

        if ($feedback->completionsubmit) {
        $params = array('userid'=>$userid, 'feedback'=>$feedback->id);
        return $DB->record_exists('feedback_completed', $params);
    } else {
                return $type;
    }
}



function feedback_user_complete($course, $user, $mod, $feedback) {
    return true;
}


function feedback_cron () {
    return true;
}


function feedback_scale_used ($feedbackid, $scaleid) {
    return false;
}


function feedback_scale_used_anywhere($scaleid) {
    return false;
}


function feedback_get_view_actions() {
    return array('view', 'view all');
}


function feedback_get_post_actions() {
    return array('submit');
}


function feedback_reset_userdata($data) {
    global $CFG, $DB;

    $resetfeedbacks = array();
    $dropfeedbacks = array();
    $status = array();
    $componentstr = get_string('modulenameplural', 'feedback');

        foreach ($data as $key => $value) {
        switch(true) {
            case substr($key, 0, strlen(FEEDBACK_RESETFORM_RESET)) == FEEDBACK_RESETFORM_RESET:
                if ($value == 1) {
                    $templist = explode('_', $key);
                    if (isset($templist[3])) {
                        $resetfeedbacks[] = intval($templist[3]);
                    }
                }
            break;
            case substr($key, 0, strlen(FEEDBACK_RESETFORM_DROP)) == FEEDBACK_RESETFORM_DROP:
                if ($value == 1) {
                    $templist = explode('_', $key);
                    if (isset($templist[3])) {
                        $dropfeedbacks[] = intval($templist[3]);
                    }
                }
            break;
        }
    }

        foreach ($resetfeedbacks as $id) {
        $feedback = $DB->get_record('feedback', array('id'=>$id));
        feedback_delete_all_completeds($feedback);
        $status[] = array('component'=>$componentstr.':'.$feedback->name,
                        'item'=>get_string('resetting_data', 'feedback'),
                        'error'=>false);
    }

        if ($data->timeshift) {
        $shifterror = !shift_course_mod_dates('feedback', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => $shifterror);
    }

    return $status;
}


function feedback_reset_course_form_definition(&$mform) {
    global $COURSE, $DB;

    $mform->addElement('header', 'feedbackheader', get_string('modulenameplural', 'feedback'));

    if (!$feedbacks = $DB->get_records('feedback', array('course'=>$COURSE->id), 'name')) {
        return;
    }

    $mform->addElement('static', 'hint', get_string('resetting_data', 'feedback'));
    foreach ($feedbacks as $feedback) {
        $mform->addElement('checkbox', FEEDBACK_RESETFORM_RESET.$feedback->id, $feedback->name);
    }
}


function feedback_reset_course_form_defaults($course) {
    global $DB;

    $return = array();
    if (!$feedbacks = $DB->get_records('feedback', array('course'=>$course->id), 'name')) {
        return;
    }
    foreach ($feedbacks as $feedback) {
        $return[FEEDBACK_RESETFORM_RESET.$feedback->id] = true;
    }
    return $return;
}


function feedback_reset_course_form($course) {
    global $DB, $OUTPUT;

    echo get_string('resetting_feedbacks', 'feedback'); echo ':<br />';
    if (!$feedbacks = $DB->get_records('feedback', array('course'=>$course->id), 'name')) {
        return;
    }

    foreach ($feedbacks as $feedback) {
        echo '<p>';
        echo get_string('name', 'feedback').': '.$feedback->name.'<br />';
        echo html_writer::checkbox(FEEDBACK_RESETFORM_RESET.$feedback->id,
                                1, true,
                                get_string('resetting_data', 'feedback'));
        echo '<br />';
        echo html_writer::checkbox(FEEDBACK_RESETFORM_DROP.$feedback->id,
                                1, false,
                                get_string('drop_feedback', 'feedback'));
        echo '</p>';
    }
}


function feedback_get_editor_options() {
    return array('maxfiles' => EDITOR_UNLIMITED_FILES,
                'trusttext'=>true);
}


function feedback_set_events($feedback) {
    global $DB, $CFG;

        require_once($CFG->dirroot.'/calendar/lib.php');

        if (!isset($feedback->coursemodule)) {
        $cm = get_coursemodule_from_instance('feedback', $feedback->id, $feedback->course);
        $feedback->coursemodule = $cm->id;
    }

        $eventid = $DB->get_field('event', 'id',
            array('modulename' => 'feedback', 'instance' => $feedback->id, 'eventtype' => 'open'));

    if (isset($feedback->timeopen) && $feedback->timeopen > 0) {
        $event = new stdClass();
        $event->name         = get_string('calendarstart', 'feedback', $feedback->name);
        $event->description  = format_module_intro('feedback', $feedback, $feedback->coursemodule);
        $event->timestart    = $feedback->timeopen;
        $event->visible      = instance_is_visible('feedback', $feedback);
        $event->timeduration = 0;
        if ($eventid) {
                        $event->id = $eventid;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
                        $event->courseid     = $feedback->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'feedback';
            $event->instance     = $feedback->id;
            $event->eventtype    = 'open';
            calendar_event::create($event);
        }
    } else if ($eventid) {
                $calendarevent = calendar_event::load($eventid);
        $calendarevent->delete();
    }

        $eventid = $DB->get_field('event', 'id',
            array('modulename' => 'feedback', 'instance' => $feedback->id, 'eventtype' => 'close'));

    if (isset($feedback->timeclose) && $feedback->timeclose > 0) {
        $event = new stdClass();
        $event->name         = get_string('calendarend', 'feedback', $feedback->name);
        $event->description  = format_module_intro('feedback', $feedback, $feedback->coursemodule);
        $event->timestart    = $feedback->timeclose;
        $event->visible      = instance_is_visible('feedback', $feedback);
        $event->timeduration = 0;
        if ($eventid) {
                        $event->id = $eventid;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
                        $event->courseid     = $feedback->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'feedback';
            $event->instance     = $feedback->id;
            $event->eventtype    = 'close';
            calendar_event::create($event);
        }
    } else if ($eventid) {
                $calendarevent = calendar_event::load($eventid);
        $calendarevent->delete();
    }
}


function feedback_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid) {
        if (! $feedbacks = $DB->get_records("feedback", array("course" => $courseid))) {
            return true;
        }
    } else {
        if (! $feedbacks = $DB->get_records("feedback")) {
            return true;
        }
    }

    foreach ($feedbacks as $feedback) {
        feedback_set_events($feedback);
    }
    return true;
}


function feedback_delete_course_module($id) {
    global $DB;

    if (!$cm = $DB->get_record('course_modules', array('id'=>$id))) {
        return true;
    }
    return $DB->delete_records('course_modules', array('id'=>$cm->id));
}





function feedback_get_context($cmid) {
    debugging('Function feedback_get_context() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    static $context;

    if (isset($context)) {
        return $context;
    }

    $context = context_module::instance($cmid);
    return $context;
}


function feedback_check_is_switchrole() {
    global $USER;
    if (isset($USER->switchrole) AND
            is_array($USER->switchrole) AND
            count($USER->switchrole) > 0) {

        return true;
    }
    return false;
}


function feedback_get_incomplete_users(cm_info $cm,
                                       $group = false,
                                       $sort = '',
                                       $startpage = false,
                                       $pagecount = false) {

    global $DB;

    $context = context_module::instance($cm->id);

        $cap = 'mod/feedback:complete';
    $fields = 'u.id, u.username';
    if (!$allusers = get_users_by_capability($context,
                                            $cap,
                                            $fields,
                                            $sort,
                                            '',
                                            '',
                                            $group,
                                            '',
                                            true)) {
        return false;
    }
        $info = new \core_availability\info_module($cm);
    $allusers = $info->filter_user_list($allusers);

    $allusers = array_keys($allusers);

        $params = array('feedback'=>$cm->instance);
    if (!$completedusers = $DB->get_records_menu('feedback_completed', $params, '', 'userid,id')) {
        return $allusers;
    }
    $completedusers = array_keys($completedusers);

        $allusers = array_diff($allusers, $completedusers);

        if ($startpage !== false AND $pagecount !== false) {
        $allusers = array_slice($allusers, $startpage, $pagecount);
    }

    return $allusers;
}


function feedback_count_incomplete_users($cm, $group = false) {
    if ($allusers = feedback_get_incomplete_users($cm, $group)) {
        return count($allusers);
    }
    return 0;
}


function feedback_count_complete_users($cm, $group = false) {
    global $DB;

    $params = array(FEEDBACK_ANONYMOUS_NO, $cm->instance);

    $fromgroup = '';
    $wheregroup = '';
    if ($group) {
        $fromgroup = ', {groups_members} g';
        $wheregroup = ' AND g.groupid = ? AND g.userid = c.userid';
        $params[] = $group;
    }

    $sql = 'SELECT COUNT(u.id) FROM {user} u, {feedback_completed} c'.$fromgroup.'
              WHERE anonymous_response = ? AND u.id = c.userid AND c.feedback = ?
              '.$wheregroup;

    return $DB->count_records_sql($sql, $params);

}


function feedback_get_complete_users($cm,
                                     $group = false,
                                     $where = '',
                                     array $params = null,
                                     $sort = '',
                                     $startpage = false,
                                     $pagecount = false) {

    global $DB;

    $context = context_module::instance($cm->id);

    $params = (array)$params;

    $params['anon'] = FEEDBACK_ANONYMOUS_NO;
    $params['instance'] = $cm->instance;

    $fromgroup = '';
    $wheregroup = '';
    if ($group) {
        $fromgroup = ', {groups_members} g';
        $wheregroup = ' AND g.groupid = :group AND g.userid = c.userid';
        $params['group'] = $group;
    }

    if ($sort) {
        $sortsql = ' ORDER BY '.$sort;
    } else {
        $sortsql = '';
    }

    $ufields = user_picture::fields('u');
    $sql = 'SELECT DISTINCT '.$ufields.', c.timemodified as completed_timemodified
            FROM {user} u, {feedback_completed} c '.$fromgroup.'
            WHERE '.$where.' anonymous_response = :anon
                AND u.id = c.userid
                AND c.feedback = :instance
              '.$wheregroup.$sortsql;

    if ($startpage === false OR $pagecount === false) {
        $startpage = false;
        $pagecount = false;
    }
    return $DB->get_records_sql($sql, $params, $startpage, $pagecount);
}


function feedback_get_viewreports_users($cmid, $groups = false) {

    $context = context_module::instance($cmid);

                return get_users_by_capability($context,
                            'mod/feedback:viewreports',
                            '',
                            'lastname',
                            '',
                            '',
                            $groups,
                            '',
                            false);
}


function feedback_get_receivemail_users($cmid, $groups = false) {

    $context = context_module::instance($cmid);

                return get_users_by_capability($context,
                            'mod/feedback:receivemail',
                            '',
                            'lastname',
                            '',
                            '',
                            $groups,
                            '',
                            false);
}



function feedback_create_template($courseid, $name, $ispublic = 0) {
    global $DB;

    $templ = new stdClass();
    $templ->course   = ($ispublic ? 0 : $courseid);
    $templ->name     = $name;
    $templ->ispublic = $ispublic;

    $templid = $DB->insert_record('feedback_template', $templ);
    return $DB->get_record('feedback_template', array('id'=>$templid));
}


function feedback_save_as_template($feedback, $name, $ispublic = 0) {
    global $DB;
    $fs = get_file_storage();

    if (!$feedbackitems = $DB->get_records('feedback_item', array('feedback'=>$feedback->id))) {
        return false;
    }

    if (!$newtempl = feedback_create_template($feedback->course, $name, $ispublic)) {
        return false;
    }

                if ($ispublic) {
        $s_context = context_system::instance();
    } else {
        $s_context = context_course::instance($newtempl->course);
    }
    $cm = get_coursemodule_from_instance('feedback', $feedback->id);
    $f_context = context_module::instance($cm->id);

                $dependitemsmap = array();
    $itembackup = array();
    foreach ($feedbackitems as $item) {

        $t_item = clone($item);

        unset($t_item->id);
        $t_item->feedback = 0;
        $t_item->template     = $newtempl->id;
        $t_item->id = $DB->insert_record('feedback_item', $t_item);
                $itemfiles = $fs->get_area_files($f_context->id,
                                    'mod_feedback',
                                    'item',
                                    $item->id,
                                    "id",
                                    false);
        if ($itemfiles) {
            foreach ($itemfiles as $ifile) {
                $file_record = new stdClass();
                $file_record->contextid = $s_context->id;
                $file_record->component = 'mod_feedback';
                $file_record->filearea = 'template';
                $file_record->itemid = $t_item->id;
                $fs->create_file_from_storedfile($file_record, $ifile);
            }
        }

        $itembackup[$item->id] = $t_item->id;
        if ($t_item->dependitem) {
            $dependitemsmap[$t_item->id] = $t_item->dependitem;
        }

    }

        foreach ($dependitemsmap as $key => $dependitem) {
        $newitem = $DB->get_record('feedback_item', array('id'=>$key));
        $newitem->dependitem = $itembackup[$newitem->dependitem];
        $DB->update_record('feedback_item', $newitem);
    }

    return true;
}


function feedback_delete_template($template) {
    global $DB;

        if ($t_items = $DB->get_records("feedback_item", array("template"=>$template->id))) {
        foreach ($t_items as $t_item) {
            feedback_delete_item($t_item->id, false, $template);
        }
    }
    $DB->delete_records("feedback_template", array("id"=>$template->id));
}


function feedback_items_from_template($feedback, $templateid, $deleteold = false) {
    global $DB, $CFG;

    require_once($CFG->libdir.'/completionlib.php');

    $fs = get_file_storage();

    if (!$template = $DB->get_record('feedback_template', array('id'=>$templateid))) {
        return false;
    }
        if (!$templitems = $DB->get_records('feedback_item', array('template'=>$templateid))) {
        return false;
    }

            if ($template->ispublic) {
        $s_context = context_system::instance();
    } else {
        $s_context = context_course::instance($feedback->course);
    }
    $course = $DB->get_record('course', array('id'=>$feedback->course));
    $cm = get_coursemodule_from_instance('feedback', $feedback->id);
    $f_context = context_module::instance($cm->id);

            if ($deleteold) {
        if ($feedbackitems = $DB->get_records('feedback_item', array('feedback'=>$feedback->id))) {
                        foreach ($feedbackitems as $item) {
                feedback_delete_item($item->id, false);
            }

            $params = array('feedback'=>$feedback->id);
            if ($completeds = $DB->get_records('feedback_completed', $params)) {
                $completion = new completion_info($course);
                foreach ($completeds as $completed) {
                                        if ($completion->is_enabled($cm) && $feedback->completionsubmit) {
                        $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
                    }
                    $DB->delete_records('feedback_completed', array('id'=>$completed->id));
                }
            }
            $DB->delete_records('feedback_completedtmp', array('feedback'=>$feedback->id));
        }
        $positionoffset = 0;
    } else {
                        $positionoffset = $DB->count_records('feedback_item', array('feedback'=>$feedback->id));
    }

                $dependitemsmap = array();
    $itembackup = array();
    foreach ($templitems as $t_item) {
        $item = clone($t_item);
        unset($item->id);
        $item->feedback = $feedback->id;
        $item->template = 0;
        $item->position = $item->position + $positionoffset;

        $item->id = $DB->insert_record('feedback_item', $item);

                $templatefiles = $fs->get_area_files($s_context->id,
                                        'mod_feedback',
                                        'template',
                                        $t_item->id,
                                        "id",
                                        false);
        if ($templatefiles) {
            foreach ($templatefiles as $tfile) {
                $file_record = new stdClass();
                $file_record->contextid = $f_context->id;
                $file_record->component = 'mod_feedback';
                $file_record->filearea = 'item';
                $file_record->itemid = $item->id;
                $fs->create_file_from_storedfile($file_record, $tfile);
            }
        }

        $itembackup[$t_item->id] = $item->id;
        if ($item->dependitem) {
            $dependitemsmap[$item->id] = $item->dependitem;
        }
    }

        foreach ($dependitemsmap as $key => $dependitem) {
        $newitem = $DB->get_record('feedback_item', array('id'=>$key));
        $newitem->dependitem = $itembackup[$newitem->dependitem];
        $DB->update_record('feedback_item', $newitem);
    }
}


function feedback_get_template_list($course, $onlyownorpublic = '') {
    global $DB, $CFG;

    switch($onlyownorpublic) {
        case '':
            $templates = $DB->get_records_select('feedback_template',
                                                 'course = ? OR ispublic = 1',
                                                 array($course->id),
                                                 'name');
            break;
        case 'own':
            $templates = $DB->get_records('feedback_template',
                                          array('course'=>$course->id),
                                          'name');
            break;
        case 'public':
            $templates = $DB->get_records('feedback_template', array('ispublic'=>1), 'name');
            break;
    }
    return $templates;
}



function feedback_get_item_class($typ) {
    global $CFG;

        $itemclass = 'feedback_item_'.$typ;
        if (!class_exists($itemclass)) {
        require_once($CFG->dirroot.'/mod/feedback/item/'.$typ.'/lib.php');
    }
    return new $itemclass();
}


function feedback_load_feedback_items($dir = 'mod/feedback/item') {
    global $CFG;
    $names = get_list_of_plugins($dir);
    $ret_names = array();

    foreach ($names as $name) {
        require_once($CFG->dirroot.'/'.$dir.'/'.$name.'/lib.php');
        if (class_exists('feedback_item_'.$name)) {
            $ret_names[] = $name;
        }
    }
    return $ret_names;
}


function feedback_load_feedback_items_options() {
    global $CFG;

    $feedback_options = array("pagebreak" => get_string('add_pagebreak', 'feedback'));

    if (!$feedback_names = feedback_load_feedback_items('mod/feedback/item')) {
        return array();
    }

    foreach ($feedback_names as $fn) {
        $feedback_options[$fn] = get_string($fn, 'feedback');
    }
    asort($feedback_options);
    return $feedback_options;
}


function feedback_get_depend_candidates_for_item($feedback, $item) {
    global $DB;
        $where = "feedback = ? AND typ != 'pagebreak' AND hasvalue = 1";
    $params = array($feedback->id);
    if (isset($item->id) AND $item->id) {
        $where .= ' AND id != ?';
        $params[] = $item->id;
    }
    $dependitems = array(0 => get_string('choose'));
    $feedbackitems = $DB->get_records_select_menu('feedback_item',
                                                  $where,
                                                  $params,
                                                  'position',
                                                  'id, label');

    if (!$feedbackitems) {
        return $dependitems;
    }
        foreach ($feedbackitems as $key => $val) {
        if (trim(strval($val)) !== '') {
            $dependitems[$key] = format_string($val);
        }
    }
    return $dependitems;
}


function feedback_create_item($data) {
    debugging('Function feedback_create_item() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    global $DB;

    $item = new stdClass();
    $item->feedback = $data->feedbackid;

    $item->template=0;
    if (isset($data->templateid)) {
            $item->template = intval($data->templateid);
    }

    $itemname = trim($data->itemname);
    $item->name = ($itemname ? $data->itemname : get_string('no_itemname', 'feedback'));

    if (!empty($data->itemlabel)) {
        $item->label = trim($data->itemlabel);
    } else {
        $item->label = get_string('no_itemlabel', 'feedback');
    }

    $itemobj = feedback_get_item_class($data->typ);
    $item->presentation = ''; 
    $item->hasvalue = $itemobj->get_hasvalue();

    $item->typ = $data->typ;
    $item->position = $data->position;

    $item->required=0;
    if (!empty($data->required)) {
        $item->required = $data->required;
    }

    $item->id = $DB->insert_record('feedback_item', $item);

        $data->id = $item->id;
    $data->feedback = $item->feedback;
    $data->name = $item->name;
    $data->label = $item->label;
    $data->required = $item->required;
    return $itemobj->postupdate($data);
}


function feedback_update_item($item) {
    global $DB;
    return $DB->update_record("feedback_item", $item);
}


function feedback_delete_item($itemid, $renumber = true, $template = false) {
    global $DB;

    $item = $DB->get_record('feedback_item', array('id'=>$itemid));

        $fs = get_file_storage();

    if ($template) {
        if ($template->ispublic) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($template->course);
        }
        $templatefiles = $fs->get_area_files($context->id,
                                    'mod_feedback',
                                    'template',
                                    $item->id,
                                    "id",
                                    false);

        if ($templatefiles) {
            $fs->delete_area_files($context->id, 'mod_feedback', 'template', $item->id);
        }
    } else {
        if (!$cm = get_coursemodule_from_instance('feedback', $item->feedback)) {
            return false;
        }
        $context = context_module::instance($cm->id);

        $itemfiles = $fs->get_area_files($context->id,
                                    'mod_feedback',
                                    'item',
                                    $item->id,
                                    "id", false);

        if ($itemfiles) {
            $fs->delete_area_files($context->id, 'mod_feedback', 'item', $item->id);
        }
    }

    $DB->delete_records("feedback_value", array("item"=>$itemid));
    $DB->delete_records("feedback_valuetmp", array("item"=>$itemid));

        $DB->set_field('feedback_item', 'dependvalue', '', array('dependitem'=>$itemid));
    $DB->set_field('feedback_item', 'dependitem', 0, array('dependitem'=>$itemid));

    $DB->delete_records("feedback_item", array("id"=>$itemid));
    if ($renumber) {
        feedback_renumber_items($item->feedback);
    }
}


function feedback_delete_all_items($feedbackid) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    if (!$feedback = $DB->get_record('feedback', array('id'=>$feedbackid))) {
        return false;
    }

    if (!$cm = get_coursemodule_from_instance('feedback', $feedback->id)) {
        return false;
    }

    if (!$course = $DB->get_record('course', array('id'=>$feedback->course))) {
        return false;
    }

    if (!$items = $DB->get_records('feedback_item', array('feedback'=>$feedbackid))) {
        return;
    }
    foreach ($items as $item) {
        feedback_delete_item($item->id, false);
    }
    if ($completeds = $DB->get_records('feedback_completed', array('feedback'=>$feedback->id))) {
        $completion = new completion_info($course);
        foreach ($completeds as $completed) {
                        if ($completion->is_enabled($cm) && $feedback->completionsubmit) {
                $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
            }
            $DB->delete_records('feedback_completed', array('id'=>$completed->id));
        }
    }

    $DB->delete_records('feedback_completedtmp', array('feedback'=>$feedbackid));

}


function feedback_switch_item_required($item) {
    global $DB, $CFG;

    $itemobj = feedback_get_item_class($item->typ);

    if ($itemobj->can_switch_require()) {
        $new_require_val = (int)!(bool)$item->required;
        $params = array('id'=>$item->id);
        $DB->set_field('feedback_item', 'required', $new_require_val, $params);
    }
    return true;
}


function feedback_renumber_items($feedbackid) {
    global $DB;

    $items = $DB->get_records('feedback_item', array('feedback'=>$feedbackid), 'position');
    $pos = 1;
    if ($items) {
        foreach ($items as $item) {
            $DB->set_field('feedback_item', 'position', $pos, array('id'=>$item->id));
            $pos++;
        }
    }
}


function feedback_moveup_item($item) {
    global $DB;

    if ($item->position == 1) {
        return true;
    }

    $params = array('feedback'=>$item->feedback);
    if (!$items = $DB->get_records('feedback_item', $params, 'position')) {
        return false;
    }

    $itembefore = null;
    foreach ($items as $i) {
        if ($i->id == $item->id) {
            if (is_null($itembefore)) {
                return true;
            }
            $itembefore->position = $item->position;
            $item->position--;
            feedback_update_item($itembefore);
            feedback_update_item($item);
            feedback_renumber_items($item->feedback);
            return true;
        }
        $itembefore = $i;
    }
    return false;
}


function feedback_movedown_item($item) {
    global $DB;

    $params = array('feedback'=>$item->feedback);
    if (!$items = $DB->get_records('feedback_item', $params, 'position')) {
        return false;
    }

    $movedownitem = null;
    foreach ($items as $i) {
        if (!is_null($movedownitem) AND $movedownitem->id == $item->id) {
            $movedownitem->position = $i->position;
            $i->position--;
            feedback_update_item($movedownitem);
            feedback_update_item($i);
            feedback_renumber_items($item->feedback);
            return true;
        }
        $movedownitem = $i;
    }
    return false;
}


function feedback_move_item($moveitem, $pos) {
    global $DB;

    $params = array('feedback'=>$moveitem->feedback);
    if (!$allitems = $DB->get_records('feedback_item', $params, 'position')) {
        return false;
    }
    if (is_array($allitems)) {
        $index = 1;
        foreach ($allitems as $item) {
            if ($index == $pos) {
                $index++;
            }
            if ($item->id == $moveitem->id) {
                $moveitem->position = $pos;
                feedback_update_item($moveitem);
                continue;
            }
            $item->position = $index;
            feedback_update_item($item);
            $index++;
        }
        return true;
    }
    return false;
}


function feedback_print_item_preview($item) {
    debugging('Function feedback_print_item_preview() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
}


function feedback_print_item_complete($item, $value = false, $highlightrequire = false) {
    debugging('Function feedback_print_item_complete() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
}


function feedback_print_item_show_value($item, $value = false) {
    debugging('Function feedback_print_item_show_value() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
}


function feedback_set_tmp_values($feedbackcompleted) {
    global $DB;
    debugging('Function feedback_set_tmp_values() is deprecated and since it is '
            . 'no longer used in mod_feedback', DEBUG_DEVELOPER);

        $tmpcpl = new stdClass();
    foreach ($feedbackcompleted as $key => $value) {
        $tmpcpl->{$key} = $value;
    }
    unset($tmpcpl->id);
    $tmpcpl->timemodified = time();
    $tmpcpl->id = $DB->insert_record('feedback_completedtmp', $tmpcpl);
        if (!$values = $DB->get_records('feedback_value', array('completed'=>$feedbackcompleted->id))) {
        return;
    }
    foreach ($values as $value) {
        unset($value->id);
        $value->completed = $tmpcpl->id;
        $DB->insert_record('feedback_valuetmp', $value);
    }
    return $tmpcpl;
}


function feedback_save_tmp_values($feedbackcompletedtmp, $feedbackcompleted) {
    global $DB;

    $tmpcplid = $feedbackcompletedtmp->id;
    if ($feedbackcompleted) {
                $DB->delete_records('feedback_value', array('completed'=>$feedbackcompleted->id));
                $feedbackcompleted->timemodified = time();
        $DB->update_record('feedback_completed', $feedbackcompleted);
    } else {
        $feedbackcompleted = clone($feedbackcompletedtmp);
        $feedbackcompleted->id = '';
        $feedbackcompleted->timemodified = time();
        $feedbackcompleted->id = $DB->insert_record('feedback_completed', $feedbackcompleted);
    }

    $allitems = $DB->get_records('feedback_item', array('feedback' => $feedbackcompleted->feedback));

            $params = array('completed'=>$feedbackcompletedtmp->id);
    $values = $DB->get_records('feedback_valuetmp', $params);
    foreach ($values as $value) {
                $item = $DB->get_record('feedback_item', array('id'=>$value->item));
        if ($item->dependitem > 0 && isset($allitems[$item->dependitem])) {
            $check = feedback_compare_item_value($tmpcplid,
                                        $allitems[$item->dependitem],
                                        $item->dependvalue,
                                        true);
        } else {
            $check = true;
        }
        if ($check) {
            unset($value->id);
            $value->completed = $feedbackcompleted->id;
            $DB->insert_record('feedback_value', $value);
        }
    }
        $DB->delete_records('feedback_valuetmp', array('completed'=>$tmpcplid));
    $DB->delete_records('feedback_completedtmp', array('id'=>$tmpcplid));

        $cm = get_coursemodule_from_instance('feedback', $feedbackcompleted->feedback);
    $event = \mod_feedback\event\response_submitted::create_from_record($feedbackcompleted, $cm);
    $event->trigger();
    return $feedbackcompleted->id;

}


function feedback_delete_completedtmp($tmpcplid) {
    global $DB;

    debugging('Function feedback_delete_completedtmp() is deprecated because it is no longer used',
            DEBUG_DEVELOPER);

    $DB->delete_records('feedback_valuetmp', array('completed'=>$tmpcplid));
    $DB->delete_records('feedback_completedtmp', array('id'=>$tmpcplid));
}



function feedback_create_pagebreak($feedbackid) {
    global $DB;

        $lastposition = $DB->count_records('feedback_item', array('feedback'=>$feedbackid));
    if ($lastposition == feedback_get_last_break_position($feedbackid)) {
        return false;
    }

    $item = new stdClass();
    $item->feedback = $feedbackid;

    $item->template=0;

    $item->name = '';

    $item->presentation = '';
    $item->hasvalue = 0;

    $item->typ = 'pagebreak';
    $item->position = $lastposition + 1;

    $item->required=0;

    return $DB->insert_record('feedback_item', $item);
}


function feedback_get_all_break_positions($feedbackid) {
    global $DB;

    $params = array('typ'=>'pagebreak', 'feedback'=>$feedbackid);
    $allbreaks = $DB->get_records_menu('feedback_item', $params, 'position', 'id, position');
    if (!$allbreaks) {
        return false;
    }
    return array_values($allbreaks);
}


function feedback_get_last_break_position($feedbackid) {
    if (!$allbreaks = feedback_get_all_break_positions($feedbackid)) {
        return false;
    }
    return $allbreaks[count($allbreaks) - 1];
}


function feedback_get_page_to_continue($feedbackid, $courseid = false, $guestid = false) {
    global $CFG, $USER, $DB;

    debugging('Function feedback_get_page_to_continue() is deprecated and since it is '
            . 'no longer used in mod_feedback', DEBUG_DEVELOPER);

    
    if (!$allbreaks = feedback_get_all_break_positions($feedbackid)) {
        return false;
    }

    $params = array();
    if ($courseid) {
        $courseselect = "AND fv.course_id = :courseid";
        $params['courseid'] = $courseid;
    } else {
        $courseselect = '';
    }

    if ($guestid) {
        $userselect = "AND fc.guestid = :guestid";
        $usergroup = "GROUP BY fc.guestid";
        $params['guestid'] = $guestid;
    } else {
        $userselect = "AND fc.userid = :userid";
        $usergroup = "GROUP BY fc.userid";
        $params['userid'] = $USER->id;
    }

    $sql =  "SELECT MAX(fi.position)
               FROM {feedback_completedtmp} fc, {feedback_valuetmp} fv, {feedback_item} fi
              WHERE fc.id = fv.completed
                    $userselect
                    AND fc.feedback = :feedbackid
                    $courseselect
                    AND fi.id = fv.item
         $usergroup";
    $params['feedbackid'] = $feedbackid;

    $lastpos = $DB->get_field_sql($sql, $params);

        foreach ($allbreaks as $pagenr => $br) {
        if ($lastpos < $br) {
            return $pagenr;
        }
    }
    return count($allbreaks);
}



function feedback_clean_input_value($item, $value) {
    debugging('Function feedback_clean_input_value() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
}


function feedback_save_values($usrid, $tmp = false) {
    global $DB;

    debugging('Function feedback_save_values() was deprecated because it did not have '.
            'enough arguments, was not suitable for non-temporary table and was taking '.
            'data directly from input', DEBUG_DEVELOPER);

    $completedid = optional_param('completedid', 0, PARAM_INT);
    $tmpstr = $tmp ? 'tmp' : '';
    $time = time();
    $timemodified = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));

    if ($usrid == 0) {
        return feedback_create_values($usrid, $timemodified, $tmp);
    }
    $completed = $DB->get_record('feedback_completed'.$tmpstr, array('id'=>$completedid));
    if (!$completed) {
        return feedback_create_values($usrid, $timemodified, $tmp);
    } else {
        $completed->timemodified = $timemodified;
        return feedback_update_values($completed, $tmp);
    }
}


function feedback_save_guest_values($guestid) {
    global $DB;

    debugging('Function feedback_save_guest_values() was deprecated because it did not have '.
            'enough arguments, was not suitable for non-temporary table and was taking '.
            'data directly from input', DEBUG_DEVELOPER);

    $completedid = optional_param('completedid', false, PARAM_INT);

    $timemodified = time();
    if (!$completed = $DB->get_record('feedback_completedtmp', array('id'=>$completedid))) {
        return feedback_create_values(0, $timemodified, true, $guestid);
    } else {
        $completed->timemodified = $timemodified;
        return feedback_update_values($completed, true);
    }
}


function feedback_get_item_value($completedid, $itemid, $tmp = false) {
    global $DB;

    $tmpstr = $tmp ? 'tmp' : '';
    $params = array('completed'=>$completedid, 'item'=>$itemid);
    return $DB->get_field('feedback_value'.$tmpstr, 'value', $params);
}


function feedback_compare_item_value($completedid, $item, $dependvalue, $tmp = false) {
    global $DB;

    if (is_int($item)) {
        $item = $DB->get_record('feedback_item', array('id' => $item));
    }

    $dbvalue = feedback_get_item_value($completedid, $item->id, $tmp);

    $itemobj = feedback_get_item_class($item->typ);
    return $itemobj->compare_value($item, $dbvalue, $dependvalue); }


function feedback_check_values($firstitem, $lastitem) {
    debugging('Function feedback_check_values() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
    return true;
}


function feedback_create_values($usrid, $timemodified, $tmp = false, $guestid = false) {
    global $DB;

    debugging('Function feedback_create_values() was deprecated because it did not have '.
            'enough arguments, was not suitable for non-temporary table and was taking '.
            'data directly from input', DEBUG_DEVELOPER);

    $tmpstr = $tmp ? 'tmp' : '';
        $completed = new stdClass();
    $completed->feedback           = $feedbackid;
    $completed->userid             = $usrid;
    $completed->guestid            = $guestid;
    $completed->timemodified       = $timemodified;
    $completed->anonymous_response = $anonymous_response;

    $completedid = $DB->insert_record('feedback_completed'.$tmpstr, $completed);

    $completed = $DB->get_record('feedback_completed'.$tmpstr, array('id'=>$completedid));

        
        if (!$allitems = $DB->get_records('feedback_item', array('feedback'=>$completed->feedback))) {
        return false;
    }
    foreach ($allitems as $item) {
        if (!$item->hasvalue) {
            continue;
        }
                $itemobj = feedback_get_item_class($item->typ);

        $keyname = $item->typ.'_'.$item->id;

        if ($item->typ === 'multichoice') {
            $itemvalue = optional_param_array($keyname, null, PARAM_INT);
        } else {
            $itemvalue = optional_param($keyname, null, PARAM_NOTAGS);
        }

        if (is_null($itemvalue)) {
            continue;
        }

        $value = new stdClass();
        $value->item = $item->id;
        $value->completed = $completed->id;
        $value->course_id = $courseid;

                        $value->value = $itemobj->create_value($itemvalue);
        $DB->insert_record('feedback_value'.$tmpstr, $value);
    }
    return $completed->id;
}


function feedback_update_values($completed, $tmp = false) {
    global $DB;

    debugging('Function feedback_update_values() was deprecated because it did not have '.
            'enough arguments, was not suitable for non-temporary table and was taking '.
            'data directly from input', DEBUG_DEVELOPER);

    $courseid = optional_param('courseid', false, PARAM_INT);
    $tmpstr = $tmp ? 'tmp' : '';

    $DB->update_record('feedback_completed'.$tmpstr, $completed);
        $values = $DB->get_records('feedback_value'.$tmpstr, array('completed'=>$completed->id));

        if (!$allitems = $DB->get_records('feedback_item', array('feedback'=>$completed->feedback))) {
        return false;
    }
    foreach ($allitems as $item) {
        if (!$item->hasvalue) {
            continue;
        }
                $itemobj = feedback_get_item_class($item->typ);

        $keyname = $item->typ.'_'.$item->id;

        if ($item->typ === 'multichoice') {
            $itemvalue = optional_param_array($keyname, null, PARAM_INT);
        } else {
            $itemvalue = optional_param($keyname, null, PARAM_NOTAGS);
        }

                if (is_null($itemvalue)) {
            continue;
        }

        $newvalue = new stdClass();
        $newvalue->item = $item->id;
        $newvalue->completed = $completed->id;
        $newvalue->course_id = $courseid;

                        $newvalue->value = $itemobj->create_value($itemvalue);

                $exist = false;
        foreach ($values as $value) {
            if ($value->item == $newvalue->item) {
                $newvalue->id = $value->id;
                $exist = true;
                break;
            }
        }
        if ($exist) {
            $DB->update_record('feedback_value'.$tmpstr, $newvalue);
        } else {
            $DB->insert_record('feedback_value'.$tmpstr, $newvalue);
        }
    }

    return $completed->id;
}


function feedback_get_group_values($item,
                                   $groupid = false,
                                   $courseid = false,
                                   $ignore_empty = false) {

    global $CFG, $DB;

        if (intval($groupid) > 0) {
        $params = array();
        if ($ignore_empty) {
            $value = $DB->sql_compare_text('fbv.value');
            $ignore_empty_select = "AND $value != :emptyvalue AND $value != :zerovalue";
            $params += array('emptyvalue' => '', 'zerovalue' => '0');
        } else {
            $ignore_empty_select = "";
        }

        $query = 'SELECT fbv .  *
                    FROM {feedback_value} fbv, {feedback_completed} fbc, {groups_members} gm
                   WHERE fbv.item = :itemid
                         AND fbv.completed = fbc.id
                         AND fbc.userid = gm.userid
                         '.$ignore_empty_select.'
                         AND gm.groupid = :groupid
                ORDER BY fbc.timemodified';
        $params += array('itemid' => $item->id, 'groupid' => $groupid);
        $values = $DB->get_records_sql($query, $params);

    } else {
        $params = array();
        if ($ignore_empty) {
            $value = $DB->sql_compare_text('value');
            $ignore_empty_select = "AND $value != :emptyvalue AND $value != :zerovalue";
            $params += array('emptyvalue' => '', 'zerovalue' => '0');
        } else {
            $ignore_empty_select = "";
        }

        if ($courseid) {
            $select = "item = :itemid AND course_id = :courseid ".$ignore_empty_select;
            $params += array('itemid' => $item->id, 'courseid' => $courseid);
            $values = $DB->get_records_select('feedback_value', $select, $params);
        } else {
            $select = "item = :itemid ".$ignore_empty_select;
            $params += array('itemid' => $item->id);
            $values = $DB->get_records_select('feedback_value', $select, $params);
        }
    }
    $params = array('id'=>$item->feedback);
    if ($DB->get_field('feedback', 'anonymous', $params) == FEEDBACK_ANONYMOUS_YES) {
        if (is_array($values)) {
            shuffle($values);
        }
    }
    return $values;
}


function feedback_is_already_submitted($feedbackid, $courseid = false) {
    global $USER, $DB;

    if (!isloggedin() || isguestuser()) {
        return false;
    }

    $params = array('userid' => $USER->id, 'feedback' => $feedbackid);
    if ($courseid) {
        $params['courseid'] = $courseid;
    }
    return $DB->record_exists('feedback_completed', $params);
}


function feedback_get_current_completed($feedbackid,
                                        $tmp = false,
                                        $courseid = false,
                                        $guestid = false) {

    debugging('Function feedback_get_current_completed() is deprecated. Please use either '.
            'feedback_get_current_completed_tmp() or feedback_get_last_completed()',
            DEBUG_DEVELOPER);

    global $USER, $CFG, $DB;

    $tmpstr = $tmp ? 'tmp' : '';

    if (!$courseid) {
        if ($guestid) {
            $params = array('feedback'=>$feedbackid, 'guestid'=>$guestid);
            return $DB->get_record('feedback_completed'.$tmpstr, $params);
        } else {
            $params = array('feedback'=>$feedbackid, 'userid'=>$USER->id);
            return $DB->get_record('feedback_completed'.$tmpstr, $params);
        }
    }

    $params = array();

    if ($guestid) {
        $userselect = "AND fc.guestid = :guestid";
        $params['guestid'] = $guestid;
    } else {
        $userselect = "AND fc.userid = :userid";
        $params['userid'] = $USER->id;
    }
            $sql =  "SELECT DISTINCT fc.*
               FROM {feedback_value{$tmpstr}} fv, {feedback_completed{$tmpstr}} fc
              WHERE fv.course_id = :courseid
                    AND fv.completed = fc.id
                    $userselect
                    AND fc.feedback = :feedbackid";
    $params['courseid']   = intval($courseid);
    $params['feedbackid'] = $feedbackid;

    if (!$sqlresult = $DB->get_records_sql($sql, $params)) {
        return false;
    }
    foreach ($sqlresult as $r) {
        return $DB->get_record('feedback_completed'.$tmpstr, array('id'=>$r->id));
    }
}


function feedback_get_completeds_group($feedback, $groupid = false, $courseid = false) {
    global $CFG, $DB;

    if (intval($groupid) > 0) {
        $query = "SELECT fbc.*
                    FROM {feedback_completed} fbc, {groups_members} gm
                   WHERE fbc.feedback = ?
                         AND gm.groupid = ?
                         AND fbc.userid = gm.userid";
        if ($values = $DB->get_records_sql($query, array($feedback->id, $groupid))) {
            return $values;
        } else {
            return false;
        }
    } else {
        if ($courseid) {
            $query = "SELECT DISTINCT fbc.*
                        FROM {feedback_completed} fbc, {feedback_value} fbv
                        WHERE fbc.id = fbv.completed
                            AND fbc.feedback = ?
                            AND fbv.course_id = ?
                        ORDER BY random_response";
            if ($values = $DB->get_records_sql($query, array($feedback->id, $courseid))) {
                return $values;
            } else {
                return false;
            }
        } else {
            if ($values = $DB->get_records('feedback_completed', array('feedback'=>$feedback->id))) {
                return $values;
            } else {
                return false;
            }
        }
    }
}


function feedback_get_completeds_group_count($feedback, $groupid = false, $courseid = false) {
    global $CFG, $DB;

    if ($courseid > 0 AND !$groupid <= 0) {
        $sql = "SELECT id, COUNT(item) AS ci
                  FROM {feedback_value}
                 WHERE course_id  = ?
              GROUP BY item ORDER BY ci DESC";
        if ($foundrecs = $DB->get_records_sql($sql, array($courseid))) {
            $foundrecs = array_values($foundrecs);
            return $foundrecs[0]->ci;
        }
        return false;
    }
    if ($values = feedback_get_completeds_group($feedback, $groupid)) {
        return count($values);
    } else {
        return false;
    }
}


function feedback_delete_all_completeds($feedback, $cm = null, $course = null) {
    global $DB;

    if (is_int($feedback)) {
        $feedback = $DB->get_record('feedback', array('id' => $feedback));
    }

    if (!$completeds = $DB->get_records('feedback_completed', array('feedback' => $feedback->id))) {
        return;
    }

    if (!$course && !($course = $DB->get_record('course', array('id' => $feedback->course)))) {
        return false;
    }

    if (!$cm && !($cm = get_coursemodule_from_instance('feedback', $feedback->id))) {
        return false;
    }

    foreach ($completeds as $completed) {
        feedback_delete_completed($completed, $feedback, $cm, $course);
    }
}


function feedback_delete_completed($completed, $feedback = null, $cm = null, $course = null) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    if (!isset($completed->id)) {
        if (!$completed = $DB->get_record('feedback_completed', array('id' => $completed))) {
            return false;
        }
    }

    if (!$feedback && !($feedback = $DB->get_record('feedback', array('id' => $completed->feedback)))) {
        return false;
    }

    if (!$course && !($course = $DB->get_record('course', array('id' => $feedback->course)))) {
        return false;
    }

    if (!$cm && !($cm = get_coursemodule_from_instance('feedback', $feedback->id))) {
        return false;
    }

        $DB->delete_records('feedback_value', array('completed' => $completed->id));

        $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && $feedback->completionsubmit) {
        $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
    }
        $return = $DB->delete_records('feedback_completed', array('id' => $completed->id));

        $event = \mod_feedback\event\response_deleted::create_from_record($completed, $cm, $feedback);
    $event->trigger();

    return $return;
}



function feedback_is_course_in_sitecourse_map($feedbackid, $courseid) {
    debugging('Function feedback_is_course_in_sitecourse_map() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    global $DB;
    $params = array('feedbackid'=>$feedbackid, 'courseid'=>$courseid);
    return $DB->count_records('feedback_sitecourse_map', $params);
}


function feedback_is_feedback_in_sitecourse_map($feedbackid) {
    debugging('Function feedback_is_feedback_in_sitecourse_map() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    global $DB;
    return $DB->record_exists('feedback_sitecourse_map', array('feedbackid'=>$feedbackid));
}


function feedback_get_feedbacks_from_sitecourse_map($courseid) {
    global $DB;

        $sql = "SELECT f.id AS id,
                   cm.id AS cmid,
                   f.name AS name,
                   f.timeopen AS timeopen,
                   f.timeclose AS timeclose
            FROM {feedback} f, {course_modules} cm, {feedback_sitecourse_map} sm, {modules} m
            WHERE f.id = cm.instance
                   AND f.course = '".SITEID."'
                   AND m.id = cm.module
                   AND m.name = 'feedback'
                   AND sm.courseid = ?
                   AND sm.feedbackid = f.id";

    if (!$feedbacks1 = $DB->get_records_sql($sql, array($courseid))) {
        $feedbacks1 = array();
    }

        $feedbacks2 = array();
    $sql = "SELECT f.id AS id,
                   cm.id AS cmid,
                   f.name AS name,
                   f.timeopen AS timeopen,
                   f.timeclose AS timeclose
            FROM {feedback} f, {course_modules} cm, {modules} m
            WHERE f.id = cm.instance
                   AND f.course = '".SITEID."'
                   AND m.id = cm.module
                   AND m.name = 'feedback'";
    if (!$allfeedbacks = $DB->get_records_sql($sql)) {
        $allfeedbacks = array();
    }
    foreach ($allfeedbacks as $a) {
        if (!$DB->record_exists('feedback_sitecourse_map', array('feedbackid'=>$a->id))) {
            $feedbacks2[] = $a;
        }
    }

    $feedbacks = array_merge($feedbacks1, $feedbacks2);
    $modinfo = get_fast_modinfo(SITEID);
    return array_filter($feedbacks, function($f) use ($modinfo) {
        return ($cm = $modinfo->get_cm($f->cmid)) && $cm->uservisible;
    });

}


function feedback_get_courses_from_sitecourse_map($feedbackid) {
    global $DB;

    $sql = "SELECT c.id, c.fullname, c.shortname
              FROM {feedback_sitecourse_map} f, {course} c
             WHERE c.id = f.courseid
                   AND f.feedbackid = ?
          ORDER BY c.fullname";

    return $DB->get_records_sql($sql, array($feedbackid));

}


function feedback_update_sitecourse_map($feedback, $courses) {
    global $DB;
    if (empty($courses)) {
        $courses = array();
    }
    $currentmapping = $DB->get_fieldset_select('feedback_sitecourse_map', 'courseid', 'feedbackid=?', array($feedback->id));
    foreach (array_diff($courses, $currentmapping) as $courseid) {
        $DB->insert_record('feedback_sitecourse_map', array('feedbackid' => $feedback->id, 'courseid' => $courseid));
    }
    foreach (array_diff($currentmapping, $courses) as $courseid) {
        $DB->delete_records('feedback_sitecourse_map', array('feedbackid' => $feedback->id, 'courseid' => $courseid));
    }
    }


function feedback_clean_up_sitecourse_map() {
    global $DB;
    debugging('Function feedback_clean_up_sitecourse_map() is deprecated because it was not used.',
            DEBUG_DEVELOPER);

    $maps = $DB->get_records('feedback_sitecourse_map');
    foreach ($maps as $map) {
        if (!$DB->get_record('course', array('id'=>$map->courseid))) {
            $params = array('courseid'=>$map->courseid, 'feedbackid'=>$map->feedbackid);
            $DB->delete_records('feedback_sitecourse_map', $params);
            continue;
        }
        if (!$DB->get_record('feedback', array('id'=>$map->feedbackid))) {
            $params = array('courseid'=>$map->courseid, 'feedbackid'=>$map->feedbackid);
            $DB->delete_records('feedback_sitecourse_map', $params);
            continue;
        }

    }
}



function feedback_print_numeric_option_list($startval, $endval, $selectval = '', $interval = 1) {
    debugging('Function feedback_print_numeric_option_list() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    for ($i = $startval; $i <= $endval; $i += $interval) {
        if ($selectval == ($i)) {
            $selected = 'selected="selected"';
        } else {
            $selected = '';
        }
        echo '<option '.$selected.'>'.$i.'</option>';
    }
}


function feedback_send_email($cm, $feedback, $course, $user) {
    global $CFG, $DB;

    if ($feedback->email_notification == 0) {          return;
    }

    if (is_int($user)) {
        $user = $DB->get_record('user', array('id' => $user));
    }

    if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
        $groupmode =  $cm->groupmode;
    } else {
        $groupmode = $course->groupmode;
    }

    if ($groupmode == SEPARATEGROUPS) {
        $groups = $DB->get_records_sql_menu("SELECT g.name, g.id
                                               FROM {groups} g, {groups_members} m
                                              WHERE g.courseid = ?
                                                    AND g.id = m.groupid
                                                    AND m.userid = ?
                                           ORDER BY name ASC", array($course->id, $user->id));
        $groups = array_values($groups);

        $teachers = feedback_get_receivemail_users($cm->id, $groups);
    } else {
        $teachers = feedback_get_receivemail_users($cm->id);
    }

    if ($teachers) {

        $strfeedbacks = get_string('modulenameplural', 'feedback');
        $strfeedback  = get_string('modulename', 'feedback');

        if ($feedback->anonymous == FEEDBACK_ANONYMOUS_NO) {
            $printusername = fullname($user);
        } else {
            $printusername = get_string('anonymous_user', 'feedback');
        }

        foreach ($teachers as $teacher) {
            $info = new stdClass();
            $info->username = $printusername;
            $info->feedback = format_string($feedback->name, true);
            $info->url = $CFG->wwwroot.'/mod/feedback/show_entries.php?'.
                            'id='.$cm->id.'&'.
                            'userid=' . $user->id;

            $a = array('username' => $info->username, 'feedbackname' => $feedback->name);

            $postsubject = get_string('feedbackcompleted', 'feedback', $a);
            $posttext = feedback_send_email_text($info, $course);

            if ($teacher->mailformat == 1) {
                $posthtml = feedback_send_email_html($info, $course, $cm);
            } else {
                $posthtml = '';
            }

            if ($feedback->anonymous == FEEDBACK_ANONYMOUS_NO) {
                $eventdata = new stdClass();
                $eventdata->name             = 'submission';
                $eventdata->component        = 'mod_feedback';
                $eventdata->userfrom         = $user;
                $eventdata->userto           = $teacher;
                $eventdata->subject          = $postsubject;
                $eventdata->fullmessage      = $posttext;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml  = $posthtml;
                $eventdata->smallmessage     = '';
                message_send($eventdata);
            } else {
                $eventdata = new stdClass();
                $eventdata->name             = 'submission';
                $eventdata->component        = 'mod_feedback';
                $eventdata->userfrom         = $teacher;
                $eventdata->userto           = $teacher;
                $eventdata->subject          = $postsubject;
                $eventdata->fullmessage      = $posttext;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml  = $posthtml;
                $eventdata->smallmessage     = '';
                message_send($eventdata);
            }
        }
    }
}


function feedback_send_email_anonym($cm, $feedback, $course) {
    global $CFG;

    if ($feedback->email_notification == 0) {         return;
    }

    $teachers = feedback_get_receivemail_users($cm->id);

    if ($teachers) {

        $strfeedbacks = get_string('modulenameplural', 'feedback');
        $strfeedback  = get_string('modulename', 'feedback');
        $printusername = get_string('anonymous_user', 'feedback');

        foreach ($teachers as $teacher) {
            $info = new stdClass();
            $info->username = $printusername;
            $info->feedback = format_string($feedback->name, true);
            $info->url = $CFG->wwwroot.'/mod/feedback/show_entries.php?id=' . $cm->id;

            $a = array('username' => $info->username, 'feedbackname' => $feedback->name);

            $postsubject = get_string('feedbackcompleted', 'feedback', $a);
            $posttext = feedback_send_email_text($info, $course);

            if ($teacher->mailformat == 1) {
                $posthtml = feedback_send_email_html($info, $course, $cm);
            } else {
                $posthtml = '';
            }

            $eventdata = new stdClass();
            $eventdata->name             = 'submission';
            $eventdata->component        = 'mod_feedback';
            $eventdata->userfrom         = $teacher;
            $eventdata->userto           = $teacher;
            $eventdata->subject          = $postsubject;
            $eventdata->fullmessage      = $posttext;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml  = $posthtml;
            $eventdata->smallmessage     = '';
            message_send($eventdata);
        }
    }
}


function feedback_send_email_text($info, $course) {
    $coursecontext = context_course::instance($course->id);
    $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
    $posttext  = $courseshortname.' -> '.get_string('modulenameplural', 'feedback').' -> '.
                    $info->feedback."\n";
    $posttext .= '---------------------------------------------------------------------'."\n";
    $posttext .= get_string("emailteachermail", "feedback", $info)."\n";
    $posttext .= '---------------------------------------------------------------------'."\n";
    return $posttext;
}



function feedback_send_email_html($info, $course, $cm) {
    global $CFG;
    $coursecontext = context_course::instance($course->id);
    $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
    $course_url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
    $feedback_all_url = $CFG->wwwroot.'/mod/feedback/index.php?id='.$course->id;
    $feedback_url = $CFG->wwwroot.'/mod/feedback/view.php?id='.$cm->id;

    $posthtml = '<p><font face="sans-serif">'.
            '<a href="'.$course_url.'">'.$courseshortname.'</a> ->'.
            '<a href="'.$feedback_all_url.'">'.get_string('modulenameplural', 'feedback').'</a> ->'.
            '<a href="'.$feedback_url.'">'.$info->feedback.'</a></font></p>';
    $posthtml .= '<hr /><font face="sans-serif">';
    $posthtml .= '<p>'.get_string('emailteachermailhtml', 'feedback', $info).'</p>';
    $posthtml .= '</font><hr />';
    return $posthtml;
}


function feedback_encode_target_url($url) {
    if (strpos($url, '?')) {
        list($part1, $part2) = explode('?', $url, 2);         return $part1 . '?' . htmlentities($part2);
    } else {
        return $url;
    }
}


function feedback_extend_settings_navigation(settings_navigation $settings,
                                             navigation_node $feedbacknode) {

    global $PAGE;

    if (!$context = context_module::instance($PAGE->cm->id, IGNORE_MISSING)) {
        print_error('badcontext');
    }

    if (has_capability('mod/feedback:edititems', $context)) {
        $questionnode = $feedbacknode->add(get_string('questions', 'feedback'));

        $questionnode->add(get_string('edit_items', 'feedback'),
                    new moodle_url('/mod/feedback/edit.php',
                                    array('id' => $PAGE->cm->id,
                                          'do_show' => 'edit')));

        $questionnode->add(get_string('export_questions', 'feedback'),
                    new moodle_url('/mod/feedback/export.php',
                                    array('id' => $PAGE->cm->id,
                                          'action' => 'exportfile')));

        $questionnode->add(get_string('import_questions', 'feedback'),
                    new moodle_url('/mod/feedback/import.php',
                                    array('id' => $PAGE->cm->id)));

        $questionnode->add(get_string('templates', 'feedback'),
                    new moodle_url('/mod/feedback/edit.php',
                                    array('id' => $PAGE->cm->id,
                                          'do_show' => 'templates')));
    }

    if (has_capability('mod/feedback:mapcourse', $context) && $PAGE->course->id == SITEID) {
        $feedbacknode->add(get_string('mappedcourses', 'feedback'),
                    new moodle_url('/mod/feedback/mapcourse.php',
                                    array('id' => $PAGE->cm->id)));
    }

    if (has_capability('mod/feedback:viewreports', $context)) {
        $feedback = $PAGE->activityrecord;
        if ($feedback->course == SITEID) {
            $feedbacknode->add(get_string('analysis', 'feedback'),
                    new moodle_url('/mod/feedback/analysis_course.php',
                                    array('id' => $PAGE->cm->id)));
        } else {
            $feedbacknode->add(get_string('analysis', 'feedback'),
                    new moodle_url('/mod/feedback/analysis.php',
                                    array('id' => $PAGE->cm->id)));
        }

        $feedbacknode->add(get_string('show_entries', 'feedback'),
                    new moodle_url('/mod/feedback/show_entries.php',
                                    array('id' => $PAGE->cm->id)));

        if ($feedback->anonymous == FEEDBACK_ANONYMOUS_NO AND $feedback->course != SITEID) {
            $feedbacknode->add(get_string('show_nonrespondents', 'feedback'),
                        new moodle_url('/mod/feedback/show_nonrespondents.php',
                                        array('id' => $PAGE->cm->id)));
        }
    }
}

function feedback_init_feedback_session() {
        global $SESSION;
    if (!empty($SESSION)) {
        if (!isset($SESSION->feedback) OR !is_object($SESSION->feedback)) {
            $SESSION->feedback = new stdClass();
        }
    }
}


function feedback_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-feedback-*'=>get_string('page-mod-feedback-x', 'feedback'));
    return $module_pagetype;
}


function feedback_ajax_saveitemorder($itemlist, $feedback) {
    global $DB;

    $result = true;
    $position = 0;
    foreach ($itemlist as $itemid) {
        $position++;
        $result = $result && $DB->set_field('feedback_item',
                                            'position',
                                            $position,
                                            array('id'=>$itemid, 'feedback'=>$feedback->id));
    }
    return $result;
}


function feedback_can_view_analysis($feedback, $context, $courseid = false) {
    if (has_capability('mod/feedback:viewreports', $context)) {
        return true;
    }

    if (intval($feedback->publish_stats) != 1 ||
            !has_capability('mod/feedback:viewanalysepage', $context)) {
        return false;
    }

    if (!isloggedin() || isguestuser()) {
                return $feedback->course == SITEID;
    }

    return feedback_is_already_submitted($feedback->id, $courseid);
}
