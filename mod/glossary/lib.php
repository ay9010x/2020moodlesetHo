<?php



require_once($CFG->libdir . '/completionlib.php');

define("GLOSSARY_SHOW_ALL_CATEGORIES", 0);
define("GLOSSARY_SHOW_NOT_CATEGORISED", -1);

define("GLOSSARY_NO_VIEW", -1);
define("GLOSSARY_STANDARD_VIEW", 0);
define("GLOSSARY_CATEGORY_VIEW", 1);
define("GLOSSARY_DATE_VIEW", 2);
define("GLOSSARY_AUTHOR_VIEW", 3);
define("GLOSSARY_ADDENTRY_VIEW", 4);
define("GLOSSARY_IMPORT_VIEW", 5);
define("GLOSSARY_EXPORT_VIEW", 6);
define("GLOSSARY_APPROVAL_VIEW", 7);

define('GLOSSARY_STANDARD', 'standard');
define('GLOSSARY_AUTHOR', 'author');
define('GLOSSARY_CATEGORY', 'category');
define('GLOSSARY_DATE', 'date');

define('GLOSSARY_CONTINUOUS', 'continuous');
define('GLOSSARY_DICTIONARY', 'dictionary');
define('GLOSSARY_FULLWITHOUTAUTHOR', 'fullwithoutauthor');


function glossary_add_instance($glossary) {
    global $DB;

    if (empty($glossary->ratingtime) or empty($glossary->assessed)) {
        $glossary->assesstimestart  = 0;
        $glossary->assesstimefinish = 0;
    }

    if (empty($glossary->globalglossary) ) {
        $glossary->globalglossary = 0;
    }

    if (!has_capability('mod/glossary:manageentries', context_system::instance())) {
        $glossary->globalglossary = 0;
    }

    $glossary->timecreated  = time();
    $glossary->timemodified = $glossary->timecreated;

        $formats = get_list_of_plugins('mod/glossary/formats','TEMPLATE');
    if (!in_array($glossary->displayformat, $formats)) {
        print_error('unknowformat', '', '', $glossary->displayformat);
    }

    $returnid = $DB->insert_record("glossary", $glossary);
    $glossary->id = $returnid;
    glossary_grade_item_update($glossary);

    return $returnid;
}


function glossary_update_instance($glossary) {
    global $CFG, $DB;

    if (empty($glossary->globalglossary)) {
        $glossary->globalglossary = 0;
    }

    if (!has_capability('mod/glossary:manageentries', context_system::instance())) {
                unset($glossary->globalglossary);
    }

    $glossary->timemodified = time();
    $glossary->id           = $glossary->instance;

    if (empty($glossary->ratingtime) or empty($glossary->assessed)) {
        $glossary->assesstimestart  = 0;
        $glossary->assesstimefinish = 0;
    }

        $formats = get_list_of_plugins('mod/glossary/formats','TEMPLATE');
    if (!in_array($glossary->displayformat, $formats)) {
        print_error('unknowformat', '', '', $glossary->displayformat);
    }

    $DB->update_record("glossary", $glossary);
    if ($glossary->defaultapproval) {
        $DB->execute("UPDATE {glossary_entries} SET approved = 1 where approved <> 1 and glossaryid = ?", array($glossary->id));
    }
    glossary_grade_item_update($glossary);

    return true;
}


function glossary_delete_instance($id) {
    global $DB, $CFG;

    if (!$glossary = $DB->get_record('glossary', array('id'=>$id))) {
        return false;
    }

    if (!$cm = get_coursemodule_from_instance('glossary', $id)) {
        return false;
    }

    if (!$context = context_module::instance($cm->id, IGNORE_MISSING)) {
        return false;
    }

    $fs = get_file_storage();

    if ($glossary->mainglossary) {
                $sql = "SELECT ge.id, ge.sourceglossaryid, cm.id AS sourcecmid
                  FROM {glossary_entries} ge
                  JOIN {modules} m ON m.name = 'glossary'
                  JOIN {course_modules} cm ON (cm.module = m.id AND cm.instance = ge.sourceglossaryid)
                 WHERE ge.glossaryid = ? AND ge.sourceglossaryid > 0";

        if ($exported = $DB->get_records_sql($sql, array($id))) {
            foreach ($exported as $entry) {
                $entry->glossaryid = $entry->sourceglossaryid;
                $entry->sourceglossaryid = 0;
                $newcontext = context_module::instance($entry->sourcecmid);
                if ($oldfiles = $fs->get_area_files($context->id, 'mod_glossary', 'attachment', $entry->id)) {
                    foreach ($oldfiles as $oldfile) {
                        $file_record = new stdClass();
                        $file_record->contextid = $newcontext->id;
                        $fs->create_file_from_storedfile($file_record, $oldfile);
                    }
                    $fs->delete_area_files($context->id, 'mod_glossary', 'attachment', $entry->id);
                    $entry->attachment = '1';
                } else {
                    $entry->attachment = '0';
                }
                $DB->update_record('glossary_entries', $entry);
            }
        }
    } else {
                $sql = "UPDATE {glossary_entries}
                   SET sourceglossaryid = 0
                 WHERE sourceglossaryid = ?";
        $DB->execute($sql, array($id));
    }

        $entry_select = "SELECT id FROM {glossary_entries} WHERE glossaryid = ?";
    $DB->delete_records_select('comments', "contextid=? AND commentarea=? AND itemid IN ($entry_select)", array($id, 'glossary_entry', $context->id));
    $DB->delete_records_select('glossary_alias',    "entryid IN ($entry_select)", array($id));

    $category_select = "SELECT id FROM {glossary_categories} WHERE glossaryid = ?";
    $DB->delete_records_select('glossary_entries_categories', "categoryid IN ($category_select)", array($id));
    $DB->delete_records('glossary_categories', array('glossaryid'=>$id));
    $DB->delete_records('glossary_entries', array('glossaryid'=>$id));

        $fs->delete_area_files($context->id);

    glossary_grade_item_delete($glossary);

    $DB->delete_records('glossary', array('id'=>$id));

        \mod_glossary\local\concept_cache::reset_glossary($glossary);

    return true;
}


function glossary_user_outline($course, $user, $mod, $glossary) {
    global $CFG;

    require_once("$CFG->libdir/gradelib.php");
    $grades = grade_get_grades($course->id, 'mod', 'glossary', $glossary->id, $user->id);
    if (empty($grades->items[0]->grades)) {
        $grade = false;
    } else {
        $grade = reset($grades->items[0]->grades);
    }

    if ($entries = glossary_get_user_entries($glossary->id, $user->id)) {
        $result = new stdClass();
        $result->info = count($entries) . ' ' . get_string("entries", "glossary");

        $lastentry = array_pop($entries);
        $result->time = $lastentry->timemodified;

        if ($grade) {
            $result->info .= ', ' . get_string('grade') . ': ' . $grade->str_long_grade;
        }
        return $result;
    } else if ($grade) {
        $result = new stdClass();
        $result->info = get_string('grade') . ': ' . $grade->str_long_grade;

                                if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
            $result->time = $grade->dategraded;
        } else {
            $result->time = $grade->datesubmitted;
        }

        return $result;
    }
    return NULL;
}


function glossary_get_user_entries($glossaryid, $userid) {
    global $DB;

    return $DB->get_records_sql("SELECT e.*, u.firstname, u.lastname, u.email, u.picture
                                   FROM {glossary} g, {glossary_entries} e, {user} u
                             WHERE g.id = ?
                               AND e.glossaryid = g.id
                               AND e.userid = ?
                               AND e.userid = u.id
                          ORDER BY e.timemodified ASC", array($glossaryid, $userid));
}


function glossary_user_complete($course, $user, $mod, $glossary) {
    global $CFG, $OUTPUT;
    require_once("$CFG->libdir/gradelib.php");

    $grades = grade_get_grades($course->id, 'mod', 'glossary', $glossary->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        echo $OUTPUT->container(get_string('grade').': '.$grade->str_long_grade);
        if ($grade->str_feedback) {
            echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
        }
    }

    if ($entries = glossary_get_user_entries($glossary->id, $user->id)) {
        echo '<table width="95%" border="0"><tr><td>';
        foreach ($entries as $entry) {
            $cm = get_coursemodule_from_instance("glossary", $glossary->id, $course->id);
            glossary_print_entry($course, $cm, $glossary, $entry,"","",0);
            echo '<p>';
        }
        echo '</td></tr></table>';
    }
}


function glossary_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
    global $COURSE, $USER, $DB;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
    }

    $modinfo = get_fast_modinfo($course);
    $cm = $modinfo->cms[$cmid];
    $context = context_module::instance($cm->id);

    if (!$cm->uservisible) {
        return;
    }

    $viewfullnames = has_capability('moodle/site:viewfullnames', $context);
        

    $params['timestart'] = $timestart;

    if ($userid) {
        $userselect = "AND u.id = :userid";
        $params['userid'] = $userid;
    } else {
        $userselect = '';
    }

    if ($groupid) {
        $groupselect = 'AND gm.groupid = :groupid';
        $groupjoin   = 'JOIN {groups_members} gm ON  gm.userid=u.id';
        $params['groupid'] = $groupid;
    } else {
        $groupselect = '';
        $groupjoin   = '';
    }

    $approvedselect = "";
    if (!has_capability('mod/glossary:approve', $context)) {
        $approvedselect = " AND ge.approved = 1 ";
    }

    $params['timestart'] = $timestart;
    $params['glossaryid'] = $cm->instance;

    $ufields = user_picture::fields('u', null, 'userid');
    $entries = $DB->get_records_sql("
              SELECT ge.id AS entryid, ge.glossaryid, ge.concept, ge.definition, ge.approved,
                     ge.timemodified, $ufields
                FROM {glossary_entries} ge
                JOIN {user} u ON u.id = ge.userid
                     $groupjoin
               WHERE ge.timemodified > :timestart
                 AND ge.glossaryid = :glossaryid
                     $approvedselect
                     $userselect
                     $groupselect
            ORDER BY ge.timemodified ASC", $params);

    if (!$entries) {
        return;
    }

    foreach ($entries as $entry) {
                

        $tmpactivity                       = new stdClass();
        $tmpactivity->user                 = user_picture::unalias($entry, null, 'userid');
        $tmpactivity->user->fullname       = fullname($tmpactivity->user, $viewfullnames);
        $tmpactivity->type                 = 'glossary';
        $tmpactivity->cmid                 = $cm->id;
        $tmpactivity->glossaryid           = $entry->glossaryid;
        $tmpactivity->name                 = format_string($cm->name, true);
        $tmpactivity->sectionnum           = $cm->sectionnum;
        $tmpactivity->timestamp            = $entry->timemodified;
        $tmpactivity->content              = new stdClass();
        $tmpactivity->content->entryid     = $entry->entryid;
        $tmpactivity->content->concept     = $entry->concept;
        $tmpactivity->content->definition  = $entry->definition;
        $tmpactivity->content->approved    = $entry->approved;

        $activities[$index++] = $tmpactivity;
    }

    return true;
}


function glossary_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $OUTPUT;

    echo html_writer::start_tag('div', array('class'=>'glossary-activity clearfix'));
    if (!empty($activity->user)) {
        echo html_writer::tag('div', $OUTPUT->user_picture($activity->user, array('courseid'=>$courseid)),
            array('class' => 'glossary-activity-picture'));
    }

    echo html_writer::start_tag('div', array('class'=>'glossary-activity-content'));
    echo html_writer::start_tag('div', array('class'=>'glossary-activity-entry'));

    if (isset($activity->content->approved) && !$activity->content->approved) {
        $urlparams = array('g' => $activity->glossaryid, 'mode' => 'approval', 'hook' => $activity->content->concept);
        $class = array('class' => 'dimmed_text');
    } else {
        $urlparams = array('g' => $activity->glossaryid, 'mode' => 'entry', 'hook' => $activity->content->entryid);
        $class = array();
    }
    echo html_writer::link(new moodle_url('/mod/glossary/view.php', $urlparams),
            strip_tags($activity->content->concept), $class);
    echo html_writer::end_tag('div');

    $url = new moodle_url('/user/view.php', array('course'=>$courseid, 'id'=>$activity->user->id));
    $name = $activity->user->fullname;
    $link = html_writer::link($url, $name, $class);

    echo html_writer::start_tag('div', array('class'=>'user'));
    echo $link .' - '. userdate($activity->timestamp);
    echo html_writer::end_tag('div');

    echo html_writer::end_tag('div');

    echo html_writer::end_tag('div');
    return;
}

function glossary_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG, $USER, $DB, $OUTPUT, $PAGE;

        if (!defined('GLOSSARY_RECENT_ACTIVITY_LIMIT')) {
        define('GLOSSARY_RECENT_ACTIVITY_LIMIT', 50);
    }
    $modinfo = get_fast_modinfo($course);
    $ids = array();

    foreach ($modinfo->cms as $cm) {
        if ($cm->modname != 'glossary') {
            continue;
        }
        if (!$cm->uservisible) {
            continue;
        }
        $ids[$cm->instance] = $cm->id;
    }

    if (!$ids) {
        return false;
    }

        $approvals = array();
    foreach ($ids as $glinstanceid => $glcmid) {
        $context = context_module::instance($glcmid);
        if (has_capability('mod/glossary:view', $context)) {
                        if (has_capability('mod/glossary:approve', $context)) {
                $approvals[] = ' ge.glossaryid = :glsid'.$glinstanceid.' ';
            } else {
                $approvals[] = ' (ge.approved = 1 AND ge.glossaryid = :glsid'.$glinstanceid.') ';
            }
            $params['glsid'.$glinstanceid] = $glinstanceid;
        }
    }

    if (count($approvals) == 0) {
        return false;
    }
    $selectsql = 'SELECT ge.id, ge.concept, ge.approved, ge.timemodified, ge.glossaryid,
                                        '.user_picture::fields('u',null,'userid');
    $countsql = 'SELECT COUNT(*)';

    $joins = array(' FROM {glossary_entries} ge ');
    $joins[] = 'JOIN {user} u ON u.id = ge.userid ';
    $fromsql = implode($joins, "\n");

    $params['timestart'] = $timestart;
    $clausesql = ' WHERE ge.timemodified > :timestart ';

    if (count($approvals) > 0) {
        $approvalsql = 'AND ('. implode($approvals, ' OR ') .') ';
    } else {
        $approvalsql = '';
    }
    $ordersql = 'ORDER BY ge.timemodified ASC';
    $entries = $DB->get_records_sql($selectsql.$fromsql.$clausesql.$approvalsql.$ordersql, $params, 0, (GLOSSARY_RECENT_ACTIVITY_LIMIT+1));

    if (empty($entries)) {
        return false;
    }

    echo $OUTPUT->heading(get_string('newentries', 'glossary').':', 3);
    $strftimerecent = get_string('strftimerecent');
    $entrycount = 0;
    foreach ($entries as $entry) {
        if ($entrycount < GLOSSARY_RECENT_ACTIVITY_LIMIT) {
            if ($entry->approved) {
                $dimmed = '';
                $urlparams = array('g' => $entry->glossaryid, 'mode' => 'entry', 'hook' => $entry->id);
            } else {
                $dimmed = ' dimmed_text';
                $urlparams = array('id' => $ids[$entry->glossaryid], 'mode' => 'approval', 'hook' => format_text($entry->concept, true));
            }
            $link = new moodle_url($CFG->wwwroot.'/mod/glossary/view.php' , $urlparams);
            echo '<div class="head'.$dimmed.'">';
            echo '<div class="date">'.userdate($entry->timemodified, $strftimerecent).'</div>';
            echo '<div class="name">'.fullname($entry, $viewfullnames).'</div>';
            echo '</div>';
            echo '<div class="info"><a href="'.$link.'">'.format_string($entry->concept, true).'</a></div>';
            $entrycount += 1;
        } else {
            $numnewentries = $DB->count_records_sql($countsql.$joins[0].$clausesql.$approvalsql, $params);
            echo '<div class="head"><div class="activityhead">'.get_string('andmorenewentries', 'glossary', $numnewentries - GLOSSARY_RECENT_ACTIVITY_LIMIT).'</div></div>';
            break;
        }
    }

    return true;
}


function glossary_log_info($log) {
    global $DB;

    return $DB->get_record_sql("SELECT e.*, u.firstname, u.lastname
                                  FROM {glossary_entries} e, {user} u
                                 WHERE e.id = ? AND u.id = ?", array($log->info, $log->userid));
}


function glossary_cron () {
    return true;
}


function glossary_get_user_grades($glossary, $userid=0) {
    global $CFG;

    require_once($CFG->dirroot.'/rating/lib.php');

    $ratingoptions = new stdClass;

        $ratingoptions->modulename = 'glossary';
    $ratingoptions->moduleid   = $glossary->id;
    $ratingoptions->component  = 'mod_glossary';
    $ratingoptions->ratingarea = 'entry';

    $ratingoptions->userid = $userid;
    $ratingoptions->aggregationmethod = $glossary->assessed;
    $ratingoptions->scaleid = $glossary->scale;
    $ratingoptions->itemtable = 'glossary_entries';
    $ratingoptions->itemtableusercolumn = 'userid';

    $rm = new rating_manager();
    return $rm->get_user_grades($ratingoptions);
}


function glossary_rating_permissions($contextid, $component, $ratingarea) {
    if ($component != 'mod_glossary' || $ratingarea != 'entry') {
                        return null;
    }
    $context = context::instance_by_id($contextid);
    return array(
        'view'    => has_capability('mod/glossary:viewrating', $context),
        'viewany' => has_capability('mod/glossary:viewanyrating', $context),
        'viewall' => has_capability('mod/glossary:viewallratings', $context),
        'rate'    => has_capability('mod/glossary:rate', $context)
    );
}


function glossary_rating_validate($params) {
    global $DB, $USER;

        if ($params['component'] != 'mod_glossary') {
        throw new rating_exception('invalidcomponent');
    }

        if ($params['ratingarea'] != 'entry') {
        throw new rating_exception('invalidratingarea');
    }

        if ($params['rateduserid'] == $USER->id) {
        throw new rating_exception('nopermissiontorate');
    }

    $glossarysql = "SELECT g.id as glossaryid, g.scale, g.course, e.userid as userid, e.approved, e.timecreated, g.assesstimestart, g.assesstimefinish
                      FROM {glossary_entries} e
                      JOIN {glossary} g ON e.glossaryid = g.id
                     WHERE e.id = :itemid";
    $glossaryparams = array('itemid' => $params['itemid']);
    $info = $DB->get_record_sql($glossarysql, $glossaryparams);
    if (!$info) {
                throw new rating_exception('invaliditemid');
    }

    if ($info->scale != $params['scaleid']) {
                throw new rating_exception('invalidscaleid');
    }

    
        if ($params['rating'] < 0  && $params['rating'] != RATING_UNSET_RATING) {
        throw new rating_exception('invalidnum');
    }

        if ($info->scale < 0) {
                $scalerecord = $DB->get_record('scale', array('id' => -$info->scale));
        if ($scalerecord) {
            $scalearray = explode(',', $scalerecord->scale);
            if ($params['rating'] > count($scalearray)) {
                throw new rating_exception('invalidnum');
            }
        } else {
            throw new rating_exception('invalidscaleid');
        }
    } else if ($params['rating'] > $info->scale) {
                throw new rating_exception('invalidnum');
    }

        if (!empty($info->assesstimestart) && !empty($info->assesstimefinish)) {
        if ($info->timecreated < $info->assesstimestart || $info->timecreated > $info->assesstimefinish) {
            throw new rating_exception('notavailable');
        }
    }

    $cm = get_coursemodule_from_instance('glossary', $info->glossaryid, $info->course, false, MUST_EXIST);
    $context = context_module::instance($cm->id, MUST_EXIST);

        if ($context->id != $params['context']->id) {
        throw new rating_exception('invalidcontext');
    }

    return true;
}


function glossary_update_grades($glossary=null, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (!$glossary->assessed) {
        glossary_grade_item_update($glossary);

    } else if ($grades = glossary_get_user_grades($glossary, $userid)) {
        glossary_grade_item_update($glossary, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = NULL;
        glossary_grade_item_update($glossary, $grade);

    } else {
        glossary_grade_item_update($glossary);
    }
}


function glossary_grade_item_update($glossary, $grades=NULL) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array('itemname'=>$glossary->name, 'idnumber'=>$glossary->cmidnumber);

    if (!$glossary->assessed or $glossary->scale == 0) {
        $params['gradetype'] = GRADE_TYPE_NONE;

    } else if ($glossary->scale > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $glossary->scale;
        $params['grademin']  = 0;

    } else if ($glossary->scale < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$glossary->scale;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/glossary', $glossary->course, 'mod', 'glossary', $glossary->id, 0, $grades, $params);
}


function glossary_grade_item_delete($glossary) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/glossary', $glossary->course, 'mod', 'glossary', $glossary->id, 0, NULL, array('deleted'=>1));
}


function glossary_scale_used ($glossaryid,$scaleid) {
    global $DB;

    $return = false;

    $rec = $DB->get_record("glossary", array("id"=>$glossaryid, "scale"=>-$scaleid));

    if (!empty($rec)  && !empty($scaleid)) {
        $return = true;
    }

    return $return;
}


function glossary_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('glossary', array('scale'=>-$scaleid))) {
        return true;
    } else {
        return false;
    }
}



function glossary_get_available_formats() {
    global $CFG, $DB;

        $formats = get_list_of_plugins('mod/glossary/formats', 'TEMPLATE');
    $pluginformats = array();
    foreach ($formats as $format) {
                if (file_exists($CFG->dirroot.'/mod/glossary/formats/'.$format.'/'.$format.'_format.php')) {
            include_once($CFG->dirroot.'/mod/glossary/formats/'.$format.'/'.$format.'_format.php');
                        if (function_exists('glossary_show_entry_'.$format)) {
                                $pluginformats[] = $format;
                                if (!$rec = $DB->get_record('glossary_formats', array('name'=>$format))) {
                                        $gf = new stdClass();
                    $gf->name = $format;
                    $gf->popupformatname = $format;
                    $gf->visible = 1;
                    $id = $DB->insert_record('glossary_formats', $gf);
                    $rec = $DB->get_record('glossary_formats', array('id' => $id));
                }

                if (empty($rec->showtabs)) {
                    glossary_set_default_visible_tabs($rec);
                }
            }
        }
    }

        $formats = $DB->get_records("glossary_formats");
    foreach ($formats as $format) {
        $todelete = false;
                if (!in_array($format->name,$pluginformats)) {
            $todelete = true;
        }

        if ($todelete) {
                        $DB->delete_records('glossary_formats', array('name'=>$format->name));
                        if ($glossaries = $DB->get_records('glossary', array('displayformat'=>$format->name))) {
                foreach($glossaries as $glossary) {
                    $DB->set_field('glossary','displayformat','dictionary', array('id'=>$glossary->id));
                }
            }
        }
    }

        $formats = $DB->get_records("glossary_formats");

    return $formats;
}


function glossary_debug($debug,$text,$br=1) {
    if ( $debug ) {
        echo '<font color="red">' . $text . '</font>';
        if ( $br ) {
            echo '<br />';
        }
    }
}


function glossary_get_entries($glossaryid, $entrylist, $pivot = "") {
    global $DB;
    if ($pivot) {
       $pivot .= ",";
    }

    return $DB->get_records_sql("SELECT $pivot id,userid,concept,definition,format
                                   FROM {glossary_entries}
                                  WHERE glossaryid = ?
                                        AND id IN ($entrylist)", array($glossaryid));
}


function glossary_get_entries_search($concept, $courseid) {
    global $CFG, $DB;

        $bypassadmin = 1;     if (has_capability('moodle/course:viewhiddenactivities', context_system::instance())) {
        $bypassadmin = 0;     }

        $bypassteacher = 1;     if (has_capability('mod/glossary:manageentries', context_course::instance($courseid))) {
        $bypassteacher = 0;     }

    $conceptlower = core_text::strtolower(trim($concept));

    $params = array('courseid1'=>$courseid, 'courseid2'=>$courseid, 'conceptlower'=>$conceptlower, 'concept'=>$concept);

    return $DB->get_records_sql("SELECT e.*, g.name as glossaryname, cm.id as cmid, cm.course as courseid
                                   FROM {glossary_entries} e, {glossary} g,
                                        {course_modules} cm, {modules} m
                                  WHERE m.name = 'glossary' AND
                                        cm.module = m.id AND
                                        (cm.visible = 1 OR  cm.visible = $bypassadmin OR
                                            (cm.course = :courseid1 AND cm.visible = $bypassteacher)) AND
                                        g.id = cm.instance AND
                                        e.glossaryid = g.id  AND
                                        ( (e.casesensitive != 0 AND LOWER(concept) = :conceptlower) OR
                                          (e.casesensitive = 0 and concept = :concept)) AND
                                        (g.course = :courseid2 OR g.globalglossary = 1) AND
                                         e.usedynalink != 0 AND
                                         g.usedynalink != 0", $params);
}


function glossary_print_entry($course, $cm, $glossary, $entry, $mode='',$hook='',$printicons = 1, $displayformat  = -1, $printview = false) {
    global $USER, $CFG;
    $return = false;
    if ( $displayformat < 0 ) {
        $displayformat = $glossary->displayformat;
    }
    if ($entry->approved or ($USER->id == $entry->userid) or ($mode == 'approval' and !$entry->approved) ) {
        $formatfile = $CFG->dirroot.'/mod/glossary/formats/'.$displayformat.'/'.$displayformat.'_format.php';
        if ($printview) {
            $functionname = 'glossary_print_entry_'.$displayformat;
        } else {
            $functionname = 'glossary_show_entry_'.$displayformat;
        }

        if (file_exists($formatfile)) {
            include_once($formatfile);
            if (function_exists($functionname)) {
                $return = $functionname($course, $cm, $glossary, $entry,$mode,$hook,$printicons);
            } else if ($printview) {
                                $return = glossary_print_entry_default($entry, $glossary, $cm);
            }
        }
    }
    return $return;
}


function glossary_print_entry_default ($entry, $glossary, $cm) {
    global $CFG;

    require_once($CFG->libdir . '/filelib.php');

    echo $OUTPUT->heading(strip_tags($entry->concept), 4);

    $definition = $entry->definition;

    $definition = '<span class="nolink">' . strip_tags($definition) . '</span>';

    $context = context_module::instance($cm->id);
    $definition = file_rewrite_pluginfile_urls($definition, 'pluginfile.php', $context->id, 'mod_glossary', 'entry', $entry->id);

    $options = new stdClass();
    $options->para = false;
    $options->trusted = $entry->definitiontrust;
    $options->context = $context;
    $options->overflowdiv = true;
    $definition = format_text($definition, $entry->definitionformat, $options);
    echo ($definition);
    echo '<br /><br />';
}


function  glossary_print_entry_concept($entry, $return=false) {
    global $OUTPUT;

    $text = $OUTPUT->heading(format_string($entry->concept), 4);
    if (!empty($entry->highlight)) {
        $text = highlight($entry->highlight, $text);
    }

    if ($return) {
        return $text;
    } else {
        echo $text;
    }
}


function glossary_print_entry_definition($entry, $glossary, $cm) {
    global $GLOSSARY_EXCLUDEENTRY;

    $definition = $entry->definition;

        $GLOSSARY_EXCLUDEENTRY = $entry->id;

    $context = context_module::instance($cm->id);
    $definition = file_rewrite_pluginfile_urls($definition, 'pluginfile.php', $context->id, 'mod_glossary', 'entry', $entry->id);

    $options = new stdClass();
    $options->para = false;
    $options->trusted = $entry->definitiontrust;
    $options->context = $context;
    $options->overflowdiv = true;

    $text = format_text($definition, $entry->definitionformat, $options);

        unset($GLOSSARY_EXCLUDEENTRY);

    if (!empty($entry->highlight)) {
        $text = highlight($entry->highlight, $text);
    }
    if (isset($entry->footer)) {           $text .= $entry->footer;
    }
    echo $text;
}


function  glossary_print_entry_aliases($course, $cm, $glossary, $entry,$mode='',$hook='', $type = 'print') {
    global $DB;

    $return = '';
    if ( $aliases = $DB->get_records('glossary_alias', array('entryid'=>$entry->id))) {
        foreach ($aliases as $alias) {
            if (trim($alias->alias)) {
                if ($return == '') {
                    $return = '<select id="keyword" style="font-size:8pt">';
                }
                $return .= "<option>$alias->alias</option>";
            }
        }
        if ($return != '') {
            $return .= '</select>';
        }
    }
    if ($type == 'print') {
        echo $return;
    } else {
        return $return;
    }
}


function glossary_print_entry_icons($course, $cm, $glossary, $entry, $mode='',$hook='', $type = 'print') {
    global $USER, $CFG, $DB, $OUTPUT;

    $context = context_module::instance($cm->id);

    $output = false;       $importedentry = ($entry->sourceglossaryid == $glossary->id);
    $ismainglossary = $glossary->mainglossary;


    $return = '<span class="commands">';
        $altsuffix = ': '.strip_tags(format_text($entry->concept));

    if (!$entry->approved) {
        $output = true;
        $return .= html_writer::tag('span', get_string('entryishidden','glossary'),
            array('class' => 'glossary-hidden-note'));
    }

    if (has_capability('mod/glossary:approve', $context) && !$glossary->defaultapproval && $entry->approved) {
        $output = true;
        $return .= '<a class="action-icon" title="' . get_string('disapprove', 'glossary').
                   '" href="approve.php?newstate=0&amp;eid='.$entry->id.'&amp;mode='.$mode.
                   '&amp;hook='.urlencode($hook).'&amp;sesskey='.sesskey().
                   '"><img src="'.$OUTPUT->pix_url('t/block').'" class="smallicon" alt="'.
                   get_string('disapprove','glossary').$altsuffix.'" /></a>';
    }

    $iscurrentuser = ($entry->userid == $USER->id);

    if (has_capability('mod/glossary:manageentries', $context) or (isloggedin() and has_capability('mod/glossary:write', $context) and $iscurrentuser)) {
                if (has_capability('mod/glossary:export', $context) and !$ismainglossary and !$importedentry) {
            $mainglossary = $DB->get_record('glossary', array('mainglossary'=>1,'course'=>$course->id));
            if ( $mainglossary ) {                  $output = true;
                $return .= '<a class="action-icon" title="'.get_string('exporttomainglossary','glossary') . '" href="exportentry.php?id='.$entry->id.'&amp;prevmode='.$mode.'&amp;hook='.urlencode($hook).'"><img src="'.$OUTPUT->pix_url('export', 'glossary').'" class="smallicon" alt="'.get_string('exporttomainglossary','glossary').$altsuffix.'" /></a>';
            }
        }

        if ( $entry->sourceglossaryid ) {
            $icon = $OUTPUT->pix_url('minus', 'glossary');           } else {
            $icon = $OUTPUT->pix_url('t/delete');
        }

                                $ineditperiod = ((time() - $entry->timecreated <  $CFG->maxeditingtime) || $glossary->editalways);
        if ( !$importedentry and (has_capability('mod/glossary:manageentries', $context) or ($entry->userid == $USER->id and ($ineditperiod and has_capability('mod/glossary:write', $context))))) {
            $output = true;
            $return .= "<a class='action-icon' title=\"" . get_string("delete") . "\" href=\"deleteentry.php?id=$cm->id&amp;mode=delete&amp;entry=$entry->id&amp;prevmode=$mode&amp;hook=".urlencode($hook)."\"><img src=\"";
            $return .= $icon;
            $return .= "\" class=\"smallicon\" alt=\"" . get_string("delete") .$altsuffix."\" /></a>";

            $return .= "<a class='action-icon' title=\"" . get_string("edit") . "\" href=\"edit.php?cmid=$cm->id&amp;id=$entry->id&amp;mode=$mode&amp;hook=".urlencode($hook)."\"><img src=\"" . $OUTPUT->pix_url('t/edit') . "\" class=\"smallicon\" alt=\"" . get_string("edit") .$altsuffix. "\" /></a>";
        } elseif ( $importedentry ) {
            $return .= "<font size=\"-1\">" . get_string("exportedentry","glossary") . "</font>";
        }
    }
    if (!empty($CFG->enableportfolios) && (has_capability('mod/glossary:exportentry', $context) || ($iscurrentuser && has_capability('mod/glossary:exportownentry', $context)))) {
        require_once($CFG->libdir . '/portfoliolib.php');
        $button = new portfolio_add_button();
        $button->set_callback_options('glossary_entry_portfolio_caller',  array('id' => $cm->id, 'entryid' => $entry->id), 'mod_glossary');

        $filecontext = $context;
        if ($entry->sourceglossaryid == $cm->instance) {
            if ($maincm = get_coursemodule_from_instance('glossary', $entry->glossaryid)) {
                $filecontext = context_module::instance($maincm->id);
            }
        }
        $fs = get_file_storage();
        if ($files = $fs->get_area_files($filecontext->id, 'mod_glossary', 'attachment', $entry->id, "timemodified", false)
         || $files = $fs->get_area_files($filecontext->id, 'mod_glossary', 'entry', $entry->id, "timemodified", false)) {

            $button->set_formats(PORTFOLIO_FORMAT_RICHHTML);
        } else {
            $button->set_formats(PORTFOLIO_FORMAT_PLAINHTML);
        }

        $return .= $button->to_html(PORTFOLIO_ADD_ICON_LINK);
    }
    $return .= '</span>';

    if (!empty($CFG->usecomments) && has_capability('mod/glossary:comment', $context) and $glossary->allowcomments) {
        require_once($CFG->dirroot . '/comment/lib.php');
        $cmt = new stdClass();
        $cmt->component = 'mod_glossary';
        $cmt->context  = $context;
        $cmt->course   = $course;
        $cmt->cm       = $cm;
        $cmt->area     = 'glossary_entry';
        $cmt->itemid   = $entry->id;
        $cmt->showcount = true;
        $comment = new comment($cmt);
        $return .= '<div>'.$comment->output(true).'</div>';
        $output = true;
    }

        if (!$output) {
        $return = '';
    }
        if ($type == 'print') {
        echo $return;
    } else {
        return $return;
    }
}


function  glossary_print_entry_lower_section($course, $cm, $glossary, $entry, $mode, $hook, $printicons, $aliases=true) {
    if ($aliases) {
        $aliases = glossary_print_entry_aliases($course, $cm, $glossary, $entry, $mode, $hook,'html');
    }
    $icons   = '';
    if ($printicons) {
        $icons   = glossary_print_entry_icons($course, $cm, $glossary, $entry, $mode, $hook,'html');
    }
    if ($aliases || $icons || !empty($entry->rating)) {
        echo '<table>';
        if ( $aliases ) {
            echo '<tr valign="top"><td class="aliases">' .
                 '<label for="keyword">' . get_string('aliases','glossary').': </label>' .
                 $aliases . '</td></tr>';
        }
        if ($icons) {
            echo '<tr valign="top"><td class="icons">'.$icons.'</td></tr>';
        }
        if (!empty($entry->rating)) {
            echo '<tr valign="top"><td class="ratings">';
            glossary_print_entry_ratings($course, $entry);
            echo '</td></tr>';
        }
        echo '</table>';
    }
}


function glossary_print_entry_attachment($entry, $cm, $format = null, $unused1 = null, $unused2 = null) {
            if ($entry->attachment) {
        echo '<div class="attachments">';
        echo glossary_print_attachments($entry, $cm, $format);
        echo '</div>';
    }
    if ($unused1) {
        debugging('The align parameter is deprecated, please use appropriate CSS instead', DEBUG_DEVELOPER);
    }
    if ($unused2 !== null) {
        debugging('The insidetable parameter is deprecated, please use appropriate CSS instead', DEBUG_DEVELOPER);
    }
}


function  glossary_print_entry_approval($cm, $entry, $mode, $align="right", $insidetable=true) {
    global $CFG, $OUTPUT;

    if ($mode == 'approval' and !$entry->approved) {
        if ($insidetable) {
            echo '<table class="glossaryapproval" align="'.$align.'"><tr><td align="'.$align.'">';
        }
        echo $OUTPUT->action_icon(
            new moodle_url('approve.php', array('eid' => $entry->id, 'mode' => $mode, 'sesskey' => sesskey())),
            new pix_icon('t/approve', get_string('approve','glossary'), '',
                array('class' => 'iconsmall', 'align' => $align))
        );
        if ($insidetable) {
            echo '</td></tr></table>';
        }
    }
}


function glossary_search($course, $searchterms, $extended = 0, $glossary = NULL) {
    global $CFG, $DB;

    if ( !$glossary ) {
        if ( $glossaries = $DB->get_records("glossary", array("course"=>$course->id)) ) {
            $glos = "";
            foreach ( $glossaries as $glossary ) {
                $glos .= "$glossary->id,";
            }
            $glos = substr($glos,0,-1);
        }
    } else {
        $glos = $glossary->id;
    }

    if (!has_capability('mod/glossary:manageentries', context_course::instance($glossary->course))) {
        $glossarymodule = $DB->get_record("modules", array("name"=>"glossary"));
        $onlyvisible = " AND g.id = cm.instance AND cm.visible = 1 AND cm.module = $glossarymodule->id";
        $onlyvisibletable = ", {course_modules} cm";
    } else {

        $onlyvisible = "";
        $onlyvisibletable = "";
    }

    if ($DB->sql_regex_supported()) {
        $REGEXP    = $DB->sql_regex(true);
        $NOTREGEXP = $DB->sql_regex(false);
    }

    $searchcond = array();
    $params     = array();
    $i = 0;

    $concat = $DB->sql_concat('e.concept', "' '", 'e.definition');


    foreach ($searchterms as $searchterm) {
        $i++;

        $NOT = false;                    
                if (!$DB->sql_regex_supported()) {
            if (substr($searchterm, 0, 1) == '-') {
                $NOT = true;
            }
            $searchterm = trim($searchterm, '+-');
        }

        
        if (substr($searchterm,0,1) == '+') {
            $searchterm = trim($searchterm, '+-');
            $searchterm = preg_quote($searchterm, '|');
            $searchcond[] = "$concat $REGEXP :ss$i";
            $params['ss'.$i] = "(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)";

        } else if (substr($searchterm,0,1) == "-") {
            $searchterm = trim($searchterm, '+-');
            $searchterm = preg_quote($searchterm, '|');
            $searchcond[] = "$concat $NOTREGEXP :ss$i";
            $params['ss'.$i] = "(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)";

        } else {
            $searchcond[] = $DB->sql_like($concat, ":ss$i", false, true, $NOT);
            $params['ss'.$i] = "%$searchterm%";
        }
    }

    if (empty($searchcond)) {
        $totalcount = 0;
        return array();
    }

    $searchcond = implode(" AND ", $searchcond);

    $sql = "SELECT e.*
              FROM {glossary_entries} e, {glossary} g $onlyvisibletable
             WHERE $searchcond
               AND (e.glossaryid = g.id or e.sourceglossaryid = g.id) $onlyvisible
               AND g.id IN ($glos) AND e.approved <> 0";

    return $DB->get_records_sql($sql, $params);
}


function glossary_search_entries($searchterms, $glossary, $extended) {
    global $DB;

    $course = $DB->get_record("course", array("id"=>$glossary->course));
    return glossary_search($course,$searchterms,$extended,$glossary);
}


function glossary_print_attachments($entry, $cm, $type=NULL, $unused = null) {
    global $CFG, $DB, $OUTPUT;

    if (!$context = context_module::instance($cm->id, IGNORE_MISSING)) {
        return '';
    }

    if ($entry->sourceglossaryid == $cm->instance) {
        if (!$maincm = get_coursemodule_from_instance('glossary', $entry->glossaryid)) {
            return '';
        }
        $filecontext = context_module::instance($maincm->id);

    } else {
        $filecontext = $context;
    }

    $strattachment = get_string('attachment', 'glossary');

    $fs = get_file_storage();

    $imagereturn = '';
    $output = '';

    if ($files = $fs->get_area_files($filecontext->id, 'mod_glossary', 'attachment', $entry->id, "timemodified", false)) {
        foreach ($files as $file) {
            $filename = $file->get_filename();
            $mimetype = $file->get_mimetype();
            $iconimage = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file), 'moodle', array('class' => 'icon'));
            $path = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$context->id.'/mod_glossary/attachment/'.$entry->id.'/'.$filename);

            if ($type == 'html') {
                $output .= "<a href=\"$path\">$iconimage</a> ";
                $output .= "<a href=\"$path\">".s($filename)."</a>";
                $output .= "<br />";

            } else if ($type == 'text') {
                $output .= "$strattachment ".s($filename).":\n$path\n";

            } else {
                if (in_array($mimetype, array('image/gif', 'image/jpeg', 'image/png'))) {
                                        $imagereturn .= "<br /><img src=\"$path\" alt=\"\" />";
                } else {
                    $output .= "<a href=\"$path\">$iconimage</a> ";
                    $output .= format_text("<a href=\"$path\">".s($filename)."</a>", FORMAT_HTML, array('context'=>$context));
                    $output .= '<br />';
                }
            }
        }
    }

    if ($type) {
        return $output;
    } else {
        echo $output;
        return $imagereturn;
    }
}



function glossary_get_file_areas($course, $cm, $context) {
    return array(
        'attachment' => get_string('areaattachment', 'mod_glossary'),
        'entry' => get_string('areaentry', 'mod_glossary'),
    );
}


function glossary_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    if (!isset($areas[$filearea])) {
        return null;
    }

    if (is_null($itemid)) {
        require_once($CFG->dirroot.'/mod/glossary/locallib.php');
        return new glossary_file_info_container($browser, $course, $cm, $context, $areas, $filearea);
    }

    if (!$entry = $DB->get_record('glossary_entries', array('id' => $itemid))) {
        return null;
    }

    if (!$glossary = $DB->get_record('glossary', array('id' => $cm->instance))) {
        return null;
    }

    if ($glossary->defaultapproval and !$entry->approved and !has_capability('mod/glossary:approve', $context)) {
        return null;
    }

        if ($entry->glossaryid == $cm->instance) {
        $filecontext = $context;
    } else if ($entry->sourceglossaryid == $cm->instance) {
        if (!$maincm = get_coursemodule_from_instance('glossary', $entry->glossaryid)) {
            return null;
        }
        $filecontext = context_module::instance($maincm->id);
    } else {
        return null;
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!($storedfile = $fs->get_file($filecontext->id, 'mod_glossary', $filearea, $itemid, $filepath, $filename))) {
        return null;
    }

            if (!has_capability('moodle/course:managefiles', $context) && $storedfile->get_userid() != $USER->id) {
        return null;
    }

    $urlbase = $CFG->wwwroot.'/pluginfile.php';

    return new file_info_stored($browser, $filecontext, $storedfile, $urlbase, s($entry->concept), true, true, false, false);
}


function glossary_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea === 'attachment' or $filearea === 'entry') {
        $entryid = (int)array_shift($args);

        require_course_login($course, true, $cm);

        if (!$entry = $DB->get_record('glossary_entries', array('id'=>$entryid))) {
            return false;
        }

        if (!$glossary = $DB->get_record('glossary', array('id'=>$cm->instance))) {
            return false;
        }

        if ($glossary->defaultapproval and !$entry->approved and !has_capability('mod/glossary:approve', $context)) {
            return false;
        }

        
        if ($entry->glossaryid == $cm->instance) {
            $filecontext = $context;

        } else if ($entry->sourceglossaryid == $cm->instance) {
            if (!$maincm = get_coursemodule_from_instance('glossary', $entry->glossaryid)) {
                return false;
            }
            $filecontext = context_module::instance($maincm->id);

        } else {
            return false;
        }

        $relativepath = implode('/', $args);
        $fullpath = "/$filecontext->id/mod_glossary/$filearea/$entryid/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

                send_stored_file($file, 0, 0, true, $options); 
    } else if ($filearea === 'export') {
        require_login($course, false, $cm);
        require_capability('mod/glossary:export', $context);

        if (!$glossary = $DB->get_record('glossary', array('id'=>$cm->instance))) {
            return false;
        }

        $cat = array_shift($args);
        $cat = clean_param($cat, PARAM_ALPHANUM);

        $filename = clean_filename(strip_tags(format_string($glossary->name)).'.xml');
        $content = glossary_generate_export_file($glossary, NULL, $cat);

        send_file($content, $filename, 0, 0, true, true);
    }

    return false;
}


function glossary_print_tabbed_table_end() {
     echo "</div></div>";
}


function glossary_print_approval_menu($cm, $glossary,$mode, $hook, $sortkey = '', $sortorder = '') {
    if ($glossary->showalphabet) {
        echo '<div class="glossaryexplain">' . get_string("explainalphabet","glossary") . '</div><br />';
    }
    glossary_print_special_links($cm, $glossary, $mode, $hook);

    glossary_print_alphabet_links($cm, $glossary, $mode, $hook,$sortkey, $sortorder);

    glossary_print_all_links($cm, $glossary, $mode, $hook);

    glossary_print_sorting_links($cm, $mode, 'CREATION', 'asc');
}

function glossary_print_import_menu($cm, $glossary, $mode, $hook, $sortkey='', $sortorder = '') {
    echo '<div class="glossaryexplain">' . get_string("explainimport","glossary") . '</div>';
}


function glossary_print_export_menu($cm, $glossary, $mode, $hook, $sortkey='', $sortorder = '') {
    echo '<div class="glossaryexplain">' . get_string("explainexport","glossary") . '</div>';
}

function glossary_print_alphabet_menu($cm, $glossary, $mode, $hook, $sortkey='', $sortorder = '') {
    if ( $mode != 'date' ) {
        if ($glossary->showalphabet) {
            echo '<div class="glossaryexplain">' . get_string("explainalphabet","glossary") . '</div><br />';
        }

        glossary_print_special_links($cm, $glossary, $mode, $hook);

        glossary_print_alphabet_links($cm, $glossary, $mode, $hook, $sortkey, $sortorder);

        glossary_print_all_links($cm, $glossary, $mode, $hook);
    } else {
        glossary_print_sorting_links($cm, $mode, $sortkey,$sortorder);
    }
}


function glossary_print_author_menu($cm, $glossary,$mode, $hook, $sortkey = '', $sortorder = '') {
    if ($glossary->showalphabet) {
        echo '<div class="glossaryexplain">' . get_string("explainalphabet","glossary") . '</div><br />';
    }

    glossary_print_alphabet_links($cm, $glossary, $mode, $hook, $sortkey, $sortorder);
    glossary_print_all_links($cm, $glossary, $mode, $hook);
    glossary_print_sorting_links($cm, $mode, $sortkey,$sortorder);
}


function glossary_print_categories_menu($cm, $glossary, $hook, $category) {
     global $CFG, $DB, $OUTPUT;

     $context = context_module::instance($cm->id);

        $fmtoptions = array(
        'context' => $context);

     echo '<table border="0" width="100%">';
     echo '<tr>';

     echo '<td align="center" style="width:20%">';
     if (has_capability('mod/glossary:managecategories', $context)) {
             $options['id'] = $cm->id;
             $options['mode'] = 'cat';
             $options['hook'] = $hook;
             echo $OUTPUT->single_button(new moodle_url("editcategories.php", $options), get_string("editcategories","glossary"), "get");
     }
     echo '</td>';

     echo '<td align="center" style="width:60%">';
     echo '<b>';

     $menu = array();
     $menu[GLOSSARY_SHOW_ALL_CATEGORIES] = get_string("allcategories","glossary");
     $menu[GLOSSARY_SHOW_NOT_CATEGORISED] = get_string("notcategorised","glossary");

     $categories = $DB->get_records("glossary_categories", array("glossaryid"=>$glossary->id), "name ASC");
     $selected = '';
     if ( $categories ) {
          foreach ($categories as $currentcategory) {
                 $url = $currentcategory->id;
                 if ( $category ) {
                     if ($currentcategory->id == $category->id) {
                         $selected = $url;
                     }
                 }
                 $menu[$url] = format_string($currentcategory->name, true, $fmtoptions);
          }
     }
     if ( !$selected ) {
         $selected = GLOSSARY_SHOW_NOT_CATEGORISED;
     }

     if ( $category ) {
        echo format_string($category->name, true, $fmtoptions);
     } else {
        if ( $hook == GLOSSARY_SHOW_NOT_CATEGORISED ) {

            echo get_string("entrieswithoutcategory","glossary");
            $selected = GLOSSARY_SHOW_NOT_CATEGORISED;

        } elseif ( $hook == GLOSSARY_SHOW_ALL_CATEGORIES ) {

            echo get_string("allcategories","glossary");
            $selected = GLOSSARY_SHOW_ALL_CATEGORIES;

        }
     }
     echo '</b></td>';
     echo '<td align="center" style="width:20%">';

     $select = new single_select(new moodle_url("/mod/glossary/view.php", array('id'=>$cm->id, 'mode'=>'cat')), 'hook', $menu, $selected, null, "catmenu");
     $select->set_label(get_string('categories', 'glossary'), array('class' => 'accesshide'));
     echo $OUTPUT->render($select);

     echo '</td>';
     echo '</tr>';

     echo '</table>';
}


function glossary_print_all_links($cm, $glossary, $mode, $hook) {
global $CFG;
     if ( $glossary->showall) {
         $strallentries       = get_string("allentries", "glossary");
         if ( $hook == 'ALL' ) {
              echo "<b>$strallentries</b>";
         } else {
              $strexplainall = strip_tags(get_string("explainall","glossary"));
              echo "<a title=\"$strexplainall\" href=\"$CFG->wwwroot/mod/glossary/view.php?id=$cm->id&amp;mode=$mode&amp;hook=ALL\">$strallentries</a>";
         }
     }
}


function glossary_print_special_links($cm, $glossary, $mode, $hook) {
global $CFG;
     if ( $glossary->showspecial) {
         $strspecial          = get_string("special", "glossary");
         if ( $hook == 'SPECIAL' ) {
              echo "<b>$strspecial</b> | ";
         } else {
              $strexplainspecial = strip_tags(get_string("explainspecial","glossary"));
              echo "<a title=\"$strexplainspecial\" href=\"$CFG->wwwroot/mod/glossary/view.php?id=$cm->id&amp;mode=$mode&amp;hook=SPECIAL\">$strspecial</a> | ";
         }
     }
}


function glossary_print_alphabet_links($cm, $glossary, $mode, $hook, $sortkey, $sortorder) {
global $CFG;
     if ( $glossary->showalphabet) {
          $alphabet = explode(",", get_string('alphabet', 'langconfig'));
          for ($i = 0; $i < count($alphabet); $i++) {
              if ( $hook == $alphabet[$i] and $hook) {
                   echo "<b>$alphabet[$i]</b>";
              } else {
                   echo "<a href=\"$CFG->wwwroot/mod/glossary/view.php?id=$cm->id&amp;mode=$mode&amp;hook=".urlencode($alphabet[$i])."&amp;sortkey=$sortkey&amp;sortorder=$sortorder\">$alphabet[$i]</a>";
              }
              echo ' | ';
          }
     }
}


function glossary_print_sorting_links($cm, $mode, $sortkey = '',$sortorder = '') {
    global $CFG, $OUTPUT;

    $asc    = get_string("ascending","glossary");
    $desc   = get_string("descending","glossary");
    $bopen  = '<b>';
    $bclose = '</b>';

     $neworder = '';
     $currentorder = '';
     $currentsort = '';
     if ( $sortorder ) {
         if ( $sortorder == 'asc' ) {
             $currentorder = $asc;
             $neworder = '&amp;sortorder=desc';
             $newordertitle = get_string('changeto', 'glossary', $desc);
         } else {
             $currentorder = $desc;
             $neworder = '&amp;sortorder=asc';
             $newordertitle = get_string('changeto', 'glossary', $asc);
         }
         $icon = " <img src=\"".$OUTPUT->pix_url($sortorder, 'glossary')."\" class=\"icon\" alt=\"$newordertitle\" />";
     } else {
         if ( $sortkey != 'CREATION' and $sortkey != 'UPDATE' and
               $sortkey != 'FIRSTNAME' and $sortkey != 'LASTNAME' ) {
             $icon = "";
             $newordertitle = $asc;
         } else {
             $newordertitle = $desc;
             $neworder = '&amp;sortorder=desc';
             $icon = ' <img src="'.$OUTPUT->pix_url('asc', 'glossary').'" class="icon" alt="'.$newordertitle.'" />';
         }
     }
     $ficon     = '';
     $fneworder = '';
     $fbtag     = '';
     $fendbtag  = '';

     $sicon     = '';
     $sneworder = '';

     $sbtag      = '';
     $fbtag      = '';
     $fendbtag      = '';
     $sendbtag      = '';

     $sendbtag  = '';

     if ( $sortkey == 'CREATION' or $sortkey == 'FIRSTNAME' ) {
         $ficon       = $icon;
         $fneworder   = $neworder;
         $fordertitle = $newordertitle;
         $sordertitle = $asc;
         $fbtag       = $bopen;
         $fendbtag    = $bclose;
     } elseif ($sortkey == 'UPDATE' or $sortkey == 'LASTNAME') {
         $sicon = $icon;
         $sneworder   = $neworder;
         $fordertitle = $asc;
         $sordertitle = $newordertitle;
         $sbtag       = $bopen;
         $sendbtag    = $bclose;
     } else {
         $fordertitle = $asc;
         $sordertitle = $asc;
     }

     if ( $sortkey == 'CREATION' or $sortkey == 'UPDATE' ) {
         $forder = 'CREATION';
         $sorder =  'UPDATE';
         $fsort  = get_string("sortbycreation", "glossary");
         $ssort  = get_string("sortbylastupdate", "glossary");

         $currentsort = $fsort;
         if ($sortkey == 'UPDATE') {
             $currentsort = $ssort;
         }
         $sort        = get_string("sortchronogically", "glossary");
     } elseif ( $sortkey == 'FIRSTNAME' or $sortkey == 'LASTNAME') {
         $forder = 'FIRSTNAME';
         $sorder =  'LASTNAME';
         $fsort  = get_string("firstname");
         $ssort  = get_string("lastname");

         $currentsort = $fsort;
         if ($sortkey == 'LASTNAME') {
             $currentsort = $ssort;
         }
         $sort        = get_string("sortby", "glossary");
     }
     $current = '<span class="accesshide">'.get_string('current', 'glossary', "$currentsort $currentorder").'</span>';
     echo "<br />$current $sort: $sbtag<a title=\"$ssort $sordertitle\" href=\"$CFG->wwwroot/mod/glossary/view.php?id=$cm->id&amp;sortkey=$sorder$sneworder&amp;mode=$mode\">$ssort$sicon</a>$sendbtag | ".
                          "$fbtag<a title=\"$fsort $fordertitle\" href=\"$CFG->wwwroot/mod/glossary/view.php?id=$cm->id&amp;sortkey=$forder$fneworder&amp;mode=$mode\">$fsort$ficon</a>$fendbtag<br />";
}


function glossary_sort_entries ( $entry0, $entry1 ) {

    if ( core_text::strtolower(ltrim($entry0->concept)) < core_text::strtolower(ltrim($entry1->concept)) ) {
        return -1;
    } elseif ( core_text::strtolower(ltrim($entry0->concept)) > core_text::strtolower(ltrim($entry1->concept)) ) {
        return 1;
    } else {
        return 0;
    }
}



function  glossary_print_entry_ratings($course, $entry) {
    global $OUTPUT;
    if( !empty($entry->rating) ){
        echo $OUTPUT->render($entry->rating);
    }
}


function glossary_print_dynaentry($courseid, $entries, $displayformat = -1) {
    global $USER,$CFG, $DB;

    echo '<div class="boxaligncenter">';
    echo '<table class="glossarypopup" cellspacing="0"><tr>';
    echo '<td>';
    if ( $entries ) {
        foreach ( $entries as $entry ) {
            if (! $glossary = $DB->get_record('glossary', array('id'=>$entry->glossaryid))) {
                print_error('invalidid', 'glossary');
            }
            if (! $course = $DB->get_record('course', array('id'=>$glossary->course))) {
                print_error('coursemisconf');
            }
            if (!$cm = get_coursemodule_from_instance('glossary', $entry->glossaryid, $glossary->course) ) {
                print_error('invalidid', 'glossary');
            }

                        if ($displayformat < 0) {
                $dp = $glossary->displayformat;
            } else {
                $dp = $displayformat;
            }

                        $format = $DB->get_record('glossary_formats', array('name'=>$dp));
            $displayformat = $format->popupformatname;

                        if (!$displayformat) {
                $displayformat = 'dictionary';
            }

            $formatfile = $CFG->dirroot.'/mod/glossary/formats/'.$displayformat.'/'.$displayformat.'_format.php';
            $functionname = 'glossary_show_entry_'.$displayformat;

            if (file_exists($formatfile)) {
                include_once($formatfile);
                if (function_exists($functionname)) {
                    $functionname($course, $cm, $glossary, $entry,'','','','');
                }
            }
        }
    }
    echo '</td>';
    echo '</tr></table></div>';
}


function glossary_generate_export_csv($entries, $aliases, $categories) {
    global $CFG;
    $csv = '';
    $delimiter = '';
    require_once($CFG->libdir . '/csvlib.class.php');
    $delimiter = csv_import_reader::get_delimiter('comma');
    $csventries = array(0 => array(get_string('concept', 'glossary'), get_string('definition', 'glossary')));
    $csvaliases = array(0 => array());
    $csvcategories = array(0 => array());
    $aliascount = 0;
    $categorycount = 0;

    foreach ($entries as $entry) {
        $thisaliasesentry = array();
        $thiscategoriesentry = array();
        $thiscsventry = array($entry->concept, nl2br($entry->definition));

        if (array_key_exists($entry->id, $aliases) && is_array($aliases[$entry->id])) {
            $thiscount = count($aliases[$entry->id]);
            if ($thiscount > $aliascount) {
                $aliascount = $thiscount;
            }
            foreach ($aliases[$entry->id] as $alias) {
                $thisaliasesentry[] = trim($alias);
            }
        }
        if (array_key_exists($entry->id, $categories) && is_array($categories[$entry->id])) {
            $thiscount = count($categories[$entry->id]);
            if ($thiscount > $categorycount) {
                $categorycount = $thiscount;
            }
            foreach ($categories[$entry->id] as $catentry) {
                $thiscategoriesentry[] = trim($catentry);
            }
        }
        $csventries[$entry->id] = $thiscsventry;
        $csvaliases[$entry->id] = $thisaliasesentry;
        $csvcategories[$entry->id] = $thiscategoriesentry;

    }
    $returnstr = '';
    foreach ($csventries as $id => $row) {
        $aliasstr = '';
        $categorystr = '';
        if ($id == 0) {
            $aliasstr = get_string('alias', 'glossary');
            $categorystr = get_string('category', 'glossary');
        }
        $row = array_merge($row, array_pad($csvaliases[$id], $aliascount, $aliasstr), array_pad($csvcategories[$id], $categorycount, $categorystr));
        $returnstr .= '"' . implode('"' . $delimiter . '"', $row) . '"' . "\n";
    }
    return $returnstr;
}


function glossary_generate_export_file($glossary, $ignored = "", $hook = 0) {
    global $CFG, $DB;

        core_php_time_limit::raise();
    raise_memory_limit(MEMORY_EXTRA);

    $cm = get_coursemodule_from_instance('glossary', $glossary->id, $glossary->course);
    $context = context_module::instance($cm->id);

    $co  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

    $co .= glossary_start_tag("GLOSSARY",0,true);
    $co .= glossary_start_tag("INFO",1,true);
        $co .= glossary_full_tag("NAME",2,false,$glossary->name);
        $co .= glossary_full_tag("INTRO",2,false,$glossary->intro);
        $co .= glossary_full_tag("INTROFORMAT",2,false,$glossary->introformat);
        $co .= glossary_full_tag("ALLOWDUPLICATEDENTRIES",2,false,$glossary->allowduplicatedentries);
        $co .= glossary_full_tag("DISPLAYFORMAT",2,false,$glossary->displayformat);
        $co .= glossary_full_tag("SHOWSPECIAL",2,false,$glossary->showspecial);
        $co .= glossary_full_tag("SHOWALPHABET",2,false,$glossary->showalphabet);
        $co .= glossary_full_tag("SHOWALL",2,false,$glossary->showall);
        $co .= glossary_full_tag("ALLOWCOMMENTS",2,false,$glossary->allowcomments);
        $co .= glossary_full_tag("USEDYNALINK",2,false,$glossary->usedynalink);
        $co .= glossary_full_tag("DEFAULTAPPROVAL",2,false,$glossary->defaultapproval);
        $co .= glossary_full_tag("GLOBALGLOSSARY",2,false,$glossary->globalglossary);
        $co .= glossary_full_tag("ENTBYPAGE",2,false,$glossary->entbypage);
        $co .= glossary_xml_export_files('INTROFILES', 2, $context->id, 'intro', 0);

        if ( $entries = $DB->get_records("glossary_entries", array("glossaryid"=>$glossary->id))) {
            $co .= glossary_start_tag("ENTRIES",2,true);
            foreach ($entries as $entry) {
                $permissiongranted = 1;
                if ( $hook ) {
                    switch ( $hook ) {
                    case "ALL":
                    case "SPECIAL":
                    break;
                    default:
                        $permissiongranted = ($entry->concept[ strlen($hook)-1 ] == $hook);
                    break;
                    }
                }
                if ( $hook ) {
                    switch ( $hook ) {
                    case GLOSSARY_SHOW_ALL_CATEGORIES:
                    break;
                    case GLOSSARY_SHOW_NOT_CATEGORISED:
                        $permissiongranted = !$DB->record_exists("glossary_entries_categories", array("entryid"=>$entry->id));
                    break;
                    default:
                        $permissiongranted = $DB->record_exists("glossary_entries_categories", array("entryid"=>$entry->id, "categoryid"=>$hook));
                    break;
                    }
                }
                if ( $entry->approved and $permissiongranted ) {
                    $co .= glossary_start_tag("ENTRY",3,true);
                    $co .= glossary_full_tag("CONCEPT",4,false,trim($entry->concept));
                    $co .= glossary_full_tag("DEFINITION",4,false,$entry->definition);
                    $co .= glossary_full_tag("FORMAT",4,false,$entry->definitionformat);                     $co .= glossary_full_tag("USEDYNALINK",4,false,$entry->usedynalink);
                    $co .= glossary_full_tag("CASESENSITIVE",4,false,$entry->casesensitive);
                    $co .= glossary_full_tag("FULLMATCH",4,false,$entry->fullmatch);
                    $co .= glossary_full_tag("TEACHERENTRY",4,false,$entry->teacherentry);

                    if ( $aliases = $DB->get_records("glossary_alias", array("entryid"=>$entry->id))) {
                        $co .= glossary_start_tag("ALIASES",4,true);
                        foreach ($aliases as $alias) {
                            $co .= glossary_start_tag("ALIAS",5,true);
                                $co .= glossary_full_tag("NAME",6,false,trim($alias->alias));
                            $co .= glossary_end_tag("ALIAS",5,true);
                        }
                        $co .= glossary_end_tag("ALIASES",4,true);
                    }
                    if ( $catentries = $DB->get_records("glossary_entries_categories", array("entryid"=>$entry->id))) {
                        $co .= glossary_start_tag("CATEGORIES",4,true);
                        foreach ($catentries as $catentry) {
                            $category = $DB->get_record("glossary_categories", array("id"=>$catentry->categoryid));

                            $co .= glossary_start_tag("CATEGORY",5,true);
                                $co .= glossary_full_tag("NAME",6,false,$category->name);
                                $co .= glossary_full_tag("USEDYNALINK",6,false,$category->usedynalink);
                            $co .= glossary_end_tag("CATEGORY",5,true);
                        }
                        $co .= glossary_end_tag("CATEGORIES",4,true);
                    }

                                        $co .= glossary_xml_export_files('ENTRYFILES', 4, $context->id, 'entry', $entry->id);

                                        $co .= glossary_xml_export_files('ATTACHMENTFILES', 4, $context->id, 'attachment', $entry->id);

                    $co .= glossary_end_tag("ENTRY",3,true);
                }
            }
            $co .= glossary_end_tag("ENTRIES",2,true);

        }


    $co .= glossary_end_tag("INFO",1,true);
    $co .= glossary_end_tag("GLOSSARY",0,true);

    return $co;
}


function glossary_read_imported_file($file_content) {
    require_once "../../lib/xmlize.php";
    global $CFG;

    return xmlize($file_content, 0);
}


function glossary_start_tag($tag,$level=0,$endline=false) {
        if ($endline) {
           $endchar = "\n";
        } else {
           $endchar = "";
        }
        return str_repeat(" ",$level*2)."<".strtoupper($tag).">".$endchar;
}


function glossary_end_tag($tag,$level=0,$endline=true) {
        if ($endline) {
           $endchar = "\n";
        } else {
           $endchar = "";
        }
        return str_repeat(" ",$level*2)."</".strtoupper($tag).">".$endchar;
}


function glossary_full_tag($tag,$level=0,$endline=true,$content) {
        global $CFG;

        $st = glossary_start_tag($tag,$level,$endline);
        $co = preg_replace("/\r\n|\r/", "\n", s($content));
        $et = glossary_end_tag($tag,0,true);
        return $st.$co.$et;
}


function glossary_xml_export_files($tag, $taglevel, $contextid, $filearea, $itemid) {
    $co = '';
    $fs = get_file_storage();
    if ($files = $fs->get_area_files(
        $contextid, 'mod_glossary', $filearea, $itemid, 'itemid,filepath,filename', false)) {
        $co .= glossary_start_tag($tag, $taglevel, true);
        foreach ($files as $file) {
            $co .= glossary_start_tag('FILE', $taglevel + 1, true);
            $co .= glossary_full_tag('FILENAME', $taglevel + 2, false, $file->get_filename());
            $co .= glossary_full_tag('FILEPATH', $taglevel + 2, false, $file->get_filepath());
            $co .= glossary_full_tag('CONTENTS', $taglevel + 2, false, base64_encode($file->get_content()));
            $co .= glossary_full_tag('FILEAUTHOR', $taglevel + 2, false, $file->get_author());
            $co .= glossary_full_tag('FILELICENSE', $taglevel + 2, false, $file->get_license());
            $co .= glossary_end_tag('FILE', $taglevel + 1);
        }
        $co .= glossary_end_tag($tag, $taglevel);
    }
    return $co;
}


function glossary_xml_import_files($xmlparent, $tag, $contextid, $filearea, $itemid) {
    global $USER, $CFG;
    $count = 0;
    if (isset($xmlparent[$tag][0]['#']['FILE'])) {
        $fs = get_file_storage();
        $files = $xmlparent[$tag][0]['#']['FILE'];
        foreach ($files as $file) {
            $filerecord = array(
                'contextid' => $contextid,
                'component' => 'mod_glossary',
                'filearea'  => $filearea,
                'itemid'    => $itemid,
                'filepath'  => $file['#']['FILEPATH'][0]['#'],
                'filename'  => $file['#']['FILENAME'][0]['#'],
                'userid'    => $USER->id
            );
            if (array_key_exists('FILEAUTHOR', $file['#'])) {
                $filerecord['author'] = $file['#']['FILEAUTHOR'][0]['#'];
            }
            if (array_key_exists('FILELICENSE', $file['#'])) {
                $license = $file['#']['FILELICENSE'][0]['#'];
                require_once($CFG->libdir . "/licenselib.php");
                if (license_manager::get_license_by_shortname($license)) {
                    $filerecord['license'] = $license;
                }
            }
            $content =  $file['#']['CONTENTS'][0]['#'];
            $fs->create_file_from_string($filerecord, base64_decode($content));
            $count++;
        }
    }
    return $count;
}


function glossary_count_unrated_entries($glossaryid, $userid) {
    global $DB;

    $sql = "SELECT COUNT('x') as num
              FROM {glossary_entries}
             WHERE glossaryid = :glossaryid AND
                   userid <> :userid";
    $params = array('glossaryid' => $glossaryid, 'userid' => $userid);
    $entries = $DB->count_records_sql($sql, $params);

    if ($entries) {
                $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module
                  JOIN {glossary} g ON g.id = cm.instance
                 WHERE ctx.contextlevel = :contextlevel AND
                       m.name = 'glossary' AND
                       g.id = :glossaryid";
        $contextid = $DB->get_field_sql($sql, array('glossaryid' => $glossaryid, 'contextlevel' => CONTEXT_MODULE));

                $sql = "SELECT COUNT('x') AS num
                  FROM {glossary_entries} e
                  JOIN {rating} r ON r.itemid = e.id
                 WHERE e.glossaryid = :glossaryid AND
                       r.userid = :userid AND
                       r.component = 'mod_glossary' AND
                       r.ratingarea = 'entry' AND
                       r.contextid = :contextid";
        $params = array('glossaryid' => $glossaryid, 'userid' => $userid, 'contextid' => $contextid);
        $rated = $DB->count_records_sql($sql, $params);
        if ($rated) {
                                    if ($entries > $rated) {
                return $entries - $rated;
            } else {
                return 0;                }
        } else {
            return (int)$entries;
        }
    } else {
        return 0;
    }
}


function glossary_get_paging_bar($totalcount, $page, $perpage, $baseurl, $maxpageallowed=99999, $maxdisplay=20, $separator="&nbsp;", $specialtext="", $specialvalue=-1, $previousandnext = true) {

    $code = '';

    $showspecial = false;
    $specialselected = false;

        if (!empty($specialtext)) {
        $showspecial = true;
    }
        if ($showspecial && $page == $specialvalue) {
        $specialselected = true;
    }

        if ($totalcount > $perpage) {
        $code .= "<div style=\"text-align:center\">";
        $code .= "<p>".get_string("page").":";

        $maxpage = (int)(($totalcount-1)/$perpage);

                if ($page < 0) {
            $page = 0;
        }
        if ($page > $maxpageallowed) {
            $page = $maxpageallowed;
        }
        if ($page > $maxpage) {
            $page = $maxpage;
        }

                $pagefrom = $page - ((int)($maxdisplay / 2));
        if ($pagefrom < 0) {
            $pagefrom = 0;
        }
        $pageto = $pagefrom + $maxdisplay - 1;
        if ($pageto > $maxpageallowed) {
            $pageto = $maxpageallowed;
        }
        if ($pageto > $maxpage) {
            $pageto = $maxpage;
        }

                if ($pageto - $pagefrom < $maxdisplay - 1) {
            if ($pageto - $maxdisplay + 1 > 0) {
                $pagefrom = $pageto - $maxdisplay + 1;
            }
        }

                $firstpagecode = '';
        $lastpagecode = '';
        if ($pagefrom > 0) {
            $firstpagecode = "$separator<a href=\"{$baseurl}page=0\">1</a>";
            if ($pagefrom > 1) {
                $firstpagecode .= "$separator...";
            }
        }
        if ($pageto < $maxpage) {
            if ($pageto < $maxpage -1) {
                $lastpagecode = "$separator...";
            }
            $lastpagecode .= "$separator<a href=\"{$baseurl}page=$maxpage\">".($maxpage+1)."</a>";
        }

                if ($page > 0 && $previousandnext) {
            $pagenum = $page - 1;
            $code .= "&nbsp;(<a  href=\"{$baseurl}page=$pagenum\">".get_string("previous")."</a>)&nbsp;";
        }

                $code .= $firstpagecode;

        $pagenum = $pagefrom;

                while ($pagenum <= $pageto) {
            $pagetoshow = $pagenum +1;
            if ($pagenum == $page && !$specialselected) {
                $code .= "$separator<b>$pagetoshow</b>";
            } else {
                $code .= "$separator<a href=\"{$baseurl}page=$pagenum\">$pagetoshow</a>";
            }
            $pagenum++;
        }

                $code .= $lastpagecode;

                if ($page < $maxpage && $page < $maxpageallowed && $previousandnext) {
            $pagenum = $page + 1;
            $code .= "$separator(<a href=\"{$baseurl}page=$pagenum\">".get_string("next")."</a>)";
        }

                if ($showspecial) {
            $code .= '<br />';
            if ($specialselected) {
                $code .= "<b>$specialtext</b>";
            } else {
                $code .= "$separator<a href=\"{$baseurl}page=$specialvalue\">$specialtext</a>";
            }
        }

                $code .= "</p>";
        $code .= "</div>";
    }

    return $code;
}


function glossary_get_view_actions() {
    return array('view','view all','view entry');
}


function glossary_get_post_actions() {
    return array('add category','add entry','approve entry','delete category','delete entry','edit category','update entry');
}



function glossary_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'glossaryheader', get_string('modulenameplural', 'glossary'));
    $mform->addElement('checkbox', 'reset_glossary_all', get_string('resetglossariesall','glossary'));

    $mform->addElement('select', 'reset_glossary_types', get_string('resetglossaries', 'glossary'),
                       array('main'=>get_string('mainglossary', 'glossary'), 'secondary'=>get_string('secondaryglossary', 'glossary')), array('multiple' => 'multiple'));
    $mform->setAdvanced('reset_glossary_types');
    $mform->disabledIf('reset_glossary_types', 'reset_glossary_all', 'checked');

    $mform->addElement('checkbox', 'reset_glossary_notenrolled', get_string('deletenotenrolled', 'glossary'));
    $mform->disabledIf('reset_glossary_notenrolled', 'reset_glossary_all', 'checked');

    $mform->addElement('checkbox', 'reset_glossary_ratings', get_string('deleteallratings'));
    $mform->disabledIf('reset_glossary_ratings', 'reset_glossary_all', 'checked');

    $mform->addElement('checkbox', 'reset_glossary_comments', get_string('deleteallcomments'));
    $mform->disabledIf('reset_glossary_comments', 'reset_glossary_all', 'checked');
}


function glossary_reset_course_form_defaults($course) {
    return array('reset_glossary_all'=>0, 'reset_glossary_ratings'=>1, 'reset_glossary_comments'=>1, 'reset_glossary_notenrolled'=>0);
}


function glossary_reset_gradebook($courseid, $type='') {
    global $DB;

    switch ($type) {
        case 'main'      : $type = "AND g.mainglossary=1"; break;
        case 'secondary' : $type = "AND g.mainglossary=0"; break;
        default          : $type = "";     }

    $sql = "SELECT g.*, cm.idnumber as cmidnumber, g.course as courseid
              FROM {glossary} g, {course_modules} cm, {modules} m
             WHERE m.name='glossary' AND m.id=cm.module AND cm.instance=g.id AND g.course=? $type";

    if ($glossarys = $DB->get_records_sql($sql, array($courseid))) {
        foreach ($glossarys as $glossary) {
            glossary_grade_item_update($glossary, 'reset');
        }
    }
}

function glossary_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/rating/lib.php');

    $componentstr = get_string('modulenameplural', 'glossary');
    $status = array();

    $allentriessql = "SELECT e.id
                        FROM {glossary_entries} e
                             JOIN {glossary} g ON e.glossaryid = g.id
                       WHERE g.course = ?";

    $allglossariessql = "SELECT g.id
                           FROM {glossary} g
                          WHERE g.course = ?";

    $params = array($data->courseid);

    $fs = get_file_storage();

    $rm = new rating_manager();
    $ratingdeloptions = new stdClass;
    $ratingdeloptions->component = 'mod_glossary';
    $ratingdeloptions->ratingarea = 'entry';

        if (!empty($data->reset_glossary_all)
         or (!empty($data->reset_glossary_types) and in_array('main', $data->reset_glossary_types) and in_array('secondary', $data->reset_glossary_types))) {

        $params[] = 'glossary_entry';
        $DB->delete_records_select('comments', "itemid IN ($allentriessql) AND commentarea=?", $params);
        $DB->delete_records_select('glossary_alias',    "entryid IN ($allentriessql)", $params);
        $DB->delete_records_select('glossary_entries', "glossaryid IN ($allglossariessql)", $params);

                if ($glossaries = $DB->get_records_sql($allglossariessql, $params)) {
            foreach ($glossaries as $glossaryid=>$unused) {
                if (!$cm = get_coursemodule_from_instance('glossary', $glossaryid)) {
                    continue;
                }
                $context = context_module::instance($cm->id);
                $fs->delete_area_files($context->id, 'mod_glossary', 'attachment');

                                $ratingdeloptions->contextid = $context->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

                if (empty($data->reset_gradebook_grades)) {
            glossary_reset_gradebook($data->courseid);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('resetglossariesall', 'glossary'), 'error'=>false);

    } else if (!empty($data->reset_glossary_types)) {
        $mainentriessql         = "$allentriessql AND g.mainglossary=1";
        $secondaryentriessql    = "$allentriessql AND g.mainglossary=0";

        $mainglossariessql      = "$allglossariessql AND g.mainglossary=1";
        $secondaryglossariessql = "$allglossariessql AND g.mainglossary=0";

        if (in_array('main', $data->reset_glossary_types)) {
            $params[] = 'glossary_entry';
            $DB->delete_records_select('comments', "itemid IN ($mainentriessql) AND commentarea=?", $params);
            $DB->delete_records_select('glossary_entries', "glossaryid IN ($mainglossariessql)", $params);

            if ($glossaries = $DB->get_records_sql($mainglossariessql, $params)) {
                foreach ($glossaries as $glossaryid=>$unused) {
                    if (!$cm = get_coursemodule_from_instance('glossary', $glossaryid)) {
                        continue;
                    }
                    $context = context_module::instance($cm->id);
                    $fs->delete_area_files($context->id, 'mod_glossary', 'attachment');

                                        $ratingdeloptions->contextid = $context->id;
                    $rm->delete_ratings($ratingdeloptions);
                }
            }

                        if (empty($data->reset_gradebook_grades)) {
                glossary_reset_gradebook($data->courseid, 'main');
            }

            $status[] = array('component'=>$componentstr, 'item'=>get_string('resetglossaries', 'glossary').': '.get_string('mainglossary', 'glossary'), 'error'=>false);

        } else if (in_array('secondary', $data->reset_glossary_types)) {
            $params[] = 'glossary_entry';
            $DB->delete_records_select('comments', "itemid IN ($secondaryentriessql) AND commentarea=?", $params);
            $DB->delete_records_select('glossary_entries', "glossaryid IN ($secondaryglossariessql)", $params);
                        $DB->execute("UPDATE {glossary_entries}
                             SET sourceglossaryid=0
                           WHERE glossaryid IN ($mainglossariessql)", $params);

            if ($glossaries = $DB->get_records_sql($secondaryglossariessql, $params)) {
                foreach ($glossaries as $glossaryid=>$unused) {
                    if (!$cm = get_coursemodule_from_instance('glossary', $glossaryid)) {
                        continue;
                    }
                    $context = context_module::instance($cm->id);
                    $fs->delete_area_files($context->id, 'mod_glossary', 'attachment');

                                        $ratingdeloptions->contextid = $context->id;
                    $rm->delete_ratings($ratingdeloptions);
                }
            }

                        if (empty($data->reset_gradebook_grades)) {
                glossary_reset_gradebook($data->courseid, 'secondary');
            }

            $status[] = array('component'=>$componentstr, 'item'=>get_string('resetglossaries', 'glossary').': '.get_string('secondaryglossary', 'glossary'), 'error'=>false);
        }
    }

        if (!empty($data->reset_glossary_notenrolled)) {
        $entriessql = "SELECT e.id, e.userid, e.glossaryid, u.id AS userexists, u.deleted AS userdeleted
                         FROM {glossary_entries} e
                              JOIN {glossary} g ON e.glossaryid = g.id
                              LEFT JOIN {user} u ON e.userid = u.id
                        WHERE g.course = ? AND e.userid > 0";

        $course_context = context_course::instance($data->courseid);
        $notenrolled = array();
        $rs = $DB->get_recordset_sql($entriessql, $params);
        if ($rs->valid()) {
            foreach ($rs as $entry) {
                if (array_key_exists($entry->userid, $notenrolled) or !$entry->userexists or $entry->userdeleted
                  or !is_enrolled($course_context , $entry->userid)) {
                    $DB->delete_records('comments', array('commentarea'=>'glossary_entry', 'itemid'=>$entry->id));
                    $DB->delete_records('glossary_entries', array('id'=>$entry->id));

                    if ($cm = get_coursemodule_from_instance('glossary', $entry->glossaryid)) {
                        $context = context_module::instance($cm->id);
                        $fs->delete_area_files($context->id, 'mod_glossary', 'attachment', $entry->id);

                                                $ratingdeloptions->contextid = $context->id;
                        $rm->delete_ratings($ratingdeloptions);
                    }
                }
            }
            $status[] = array('component'=>$componentstr, 'item'=>get_string('deletenotenrolled', 'glossary'), 'error'=>false);
        }
        $rs->close();
    }

        if (!empty($data->reset_glossary_ratings)) {
                if ($glossaries = $DB->get_records_sql($allglossariessql, $params)) {
            foreach ($glossaries as $glossaryid=>$unused) {
                if (!$cm = get_coursemodule_from_instance('glossary', $glossaryid)) {
                    continue;
                }
                $context = context_module::instance($cm->id);

                                $ratingdeloptions->contextid = $context->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

                if (empty($data->reset_gradebook_grades)) {
            glossary_reset_gradebook($data->courseid);
        }
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallratings'), 'error'=>false);
    }

        if (!empty($data->reset_glossary_comments)) {
        $params[] = 'glossary_entry';
        $DB->delete_records_select('comments', "itemid IN ($allentriessql) AND commentarea= ? ", $params);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallcomments'), 'error'=>false);
    }

        if ($data->timeshift) {
        shift_course_mod_dates('glossary', array('assesstimestart', 'assesstimefinish'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}


function glossary_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames', 'moodle/site:trustcontent', 'moodle/rating:view', 'moodle/rating:viewany', 'moodle/rating:viewall', 'moodle/rating:rate', 'moodle/comment:view', 'moodle/comment:post', 'moodle/comment:delete');
}


function glossary_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_RATE:                    return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


function glossary_get_completion_state($course,$cm,$userid,$type) {
    global $CFG, $DB;

        if (!($glossary=$DB->get_record('glossary',array('id'=>$cm->instance)))) {
        throw new Exception("Can't find glossary {$cm->instance}");
    }

    $result=$type; 
    if ($glossary->completionentries) {
        $value = $glossary->completionentries <=
                 $DB->count_records('glossary_entries',array('glossaryid'=>$glossary->id, 'userid'=>$userid, 'approved'=>1));
        if ($type == COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }

    return $result;
}

function glossary_extend_navigation($navigation, $course, $module, $cm) {
    global $CFG, $DB;

    $displayformat = $DB->get_record('glossary_formats', array('name' => $module->displayformat));
        $showtabs = glossary_get_visible_tabs($displayformat);

    foreach ($showtabs as $showtabkey => $showtabvalue) {

        switch($showtabvalue) {
            case GLOSSARY_STANDARD :
                $navigation->add(get_string('standardview', 'glossary'), new moodle_url('/mod/glossary/view.php',
                        array('id' => $cm->id, 'mode' => 'letter')));
                break;
            case GLOSSARY_CATEGORY :
                $navigation->add(get_string('categoryview', 'glossary'), new moodle_url('/mod/glossary/view.php',
                        array('id' => $cm->id, 'mode' => 'cat')));
                break;
            case GLOSSARY_DATE :
                $navigation->add(get_string('dateview', 'glossary'), new moodle_url('/mod/glossary/view.php',
                        array('id' => $cm->id, 'mode' => 'date')));
                break;
            case GLOSSARY_AUTHOR :
                $navigation->add(get_string('authorview', 'glossary'), new moodle_url('/mod/glossary/view.php',
                        array('id' => $cm->id, 'mode' => 'author')));
                break;
        }
    }
}


function glossary_extend_settings_navigation(settings_navigation $settings, navigation_node $glossarynode) {
    global $PAGE, $DB, $CFG, $USER;

    $mode = optional_param('mode', '', PARAM_ALPHA);
    $hook = optional_param('hook', 'ALL', PARAM_CLEAN);

    if (has_capability('mod/glossary:import', $PAGE->cm->context)) {
        $glossarynode->add(get_string('importentries', 'glossary'), new moodle_url('/mod/glossary/import.php', array('id'=>$PAGE->cm->id)));
    }

    if (has_capability('mod/glossary:export', $PAGE->cm->context)) {
        $glossarynode->add(get_string('exportentries', 'glossary'), new moodle_url('/mod/glossary/export.php', array('id'=>$PAGE->cm->id, 'mode'=>$mode, 'hook'=>$hook)));
    }

    if (has_capability('mod/glossary:approve', $PAGE->cm->context) && ($hiddenentries = $DB->count_records('glossary_entries', array('glossaryid'=>$PAGE->cm->instance, 'approved'=>0)))) {
        $glossarynode->add(get_string('waitingapproval', 'glossary'), new moodle_url('/mod/glossary/view.php', array('id'=>$PAGE->cm->id, 'mode'=>'approval')));
    }

    if (has_capability('mod/glossary:write', $PAGE->cm->context)) {
        $glossarynode->add(get_string('addentry', 'glossary'), new moodle_url('/mod/glossary/edit.php', array('cmid'=>$PAGE->cm->id)));
    }

    $glossary = $DB->get_record('glossary', array("id" => $PAGE->cm->instance));

    if (!empty($CFG->enablerssfeeds) && !empty($CFG->glossary_enablerssfeeds) && $glossary->rsstype && $glossary->rssarticles && has_capability('mod/glossary:view', $PAGE->cm->context)) {
        require_once("$CFG->libdir/rsslib.php");

        $string = get_string('rsstype','forum');

        $url = new moodle_url(rss_get_url($PAGE->cm->context->id, $USER->id, 'mod_glossary', $glossary->id));
        $glossarynode->add($string, $url, settings_navigation::TYPE_SETTING, null, null, new pix_icon('i/rss', ''));
    }
}


function glossary_comment_permissions($comment_param) {
    return array('post'=>true, 'view'=>true);
}


function glossary_comment_validate($comment_param) {
    global $DB;
        if ($comment_param->commentarea != 'glossary_entry') {
        throw new comment_exception('invalidcommentarea');
    }
    if (!$record = $DB->get_record('glossary_entries', array('id'=>$comment_param->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    if ($record->sourceglossaryid && $record->sourceglossaryid == $comment_param->cm->instance) {
        $glossary = $DB->get_record('glossary', array('id'=>$record->sourceglossaryid));
    } else {
        $glossary = $DB->get_record('glossary', array('id'=>$record->glossaryid));
    }
    if (!$glossary) {
        throw new comment_exception('invalidid', 'data');
    }
    if (!$course = $DB->get_record('course', array('id'=>$glossary->course))) {
        throw new comment_exception('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('glossary', $glossary->id, $course->id)) {
        throw new comment_exception('invalidcoursemodule');
    }
    $context = context_module::instance($cm->id);

    if ($glossary->defaultapproval and !$record->approved and !has_capability('mod/glossary:approve', $context)) {
        throw new comment_exception('notapproved', 'glossary');
    }
        if ($context->id != $comment_param->context->id) {
        throw new comment_exception('invalidcontext');
    }
        if (!empty($comment_param->commentid)) {
        if ($comment = $DB->get_record('comments', array('id'=>$comment_param->commentid))) {
            if ($comment->commentarea != 'glossary_entry') {
                throw new comment_exception('invalidcommentarea');
            }
            if ($comment->contextid != $comment_param->context->id) {
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


function glossary_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array(
        'mod-glossary-*'=>get_string('page-mod-glossary-x', 'glossary'),
        'mod-glossary-view'=>get_string('page-mod-glossary-view', 'glossary'),
        'mod-glossary-edit'=>get_string('page-mod-glossary-edit', 'glossary'));
    return $module_pagetype;
}


function glossary_get_all_tabs() {

    return array (
        GLOSSARY_AUTHOR => get_string('authorview', 'glossary'),
        GLOSSARY_CATEGORY => get_string('categoryview', 'glossary'),
        GLOSSARY_DATE => get_string('dateview', 'glossary')
    );
}


function glossary_set_default_visible_tabs($glossaryformat) {
    global $DB;

    switch($glossaryformat->name) {
        case GLOSSARY_CONTINUOUS:
            $showtabs = 'standard,category,date';
            break;
        case GLOSSARY_DICTIONARY:
            $showtabs = 'standard';
                                                if ($DB->record_exists_sql("SELECT 1
                    FROM {glossary} g, {glossary_categories} gc
                    WHERE g.id = gc.glossaryid and g.displayformat = ?",
                    array(GLOSSARY_DICTIONARY))) {
                $showtabs .= ',category';
            }
            break;
        case GLOSSARY_FULLWITHOUTAUTHOR:
            $showtabs = 'standard,category,date';
            break;
        default:
            $showtabs = 'standard,category,date,author';
            break;
    }

    $DB->set_field('glossary_formats', 'showtabs', $showtabs, array('id' => $glossaryformat->id));
    $glossaryformat->showtabs = $showtabs;
}


function glossary_get_visible_tabs($displayformat) {
    if (empty($displayformat->showtabs)) {
        glossary_set_default_visible_tabs($displayformat);
    }
    $showtabs = preg_split('/,/', $displayformat->showtabs, -1, PREG_SPLIT_NO_EMPTY);

    return $showtabs;
}


function glossary_view($glossary, $course, $cm, $context, $mode) {

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);

        $event = \mod_glossary\event\course_module_viewed::create(array(
        'objectid' => $glossary->id,
        'context' => $context,
        'other' => array('mode' => $mode)
    ));
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('glossary', $glossary);
    $event->trigger();
}


function glossary_entry_view($entry, $context) {

        $event = \mod_glossary\event\entry_viewed::create(array(
        'objectid' => $entry->id,
        'context' => $context
    ));
    $event->add_record_snapshot('glossary_entries', $entry);
    $event->trigger();

}


function glossary_get_entries_by_letter($glossary, $context, $letter, $from, $limit, $options = array()) {

    $qb = new mod_glossary_entry_query_builder($glossary);
    if ($letter != 'ALL' && $letter != 'SPECIAL' && core_text::strlen($letter)) {
        $qb->filter_by_concept_letter($letter);
    }
    if ($letter == 'SPECIAL') {
        $qb->filter_by_concept_non_letter();
    }

    if (!empty($options['includenotapproved']) && has_capability('mod/glossary:approve', $context)) {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_ALL);
    } else {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_SELF);
    }

    $qb->add_field('*', 'entries');
    $qb->join_user();
    $qb->add_user_fields();
    $qb->order_by('concept', 'entries');
    $qb->order_by('id', 'entries', 'ASC');     $qb->limit($from, $limit);

        $count = $qb->count_records();
    $entries = $qb->get_records();

    return array($entries, $count);
}


function glossary_get_entries_by_date($glossary, $context, $order, $sort, $from, $limit, $options = array()) {

    $qb = new mod_glossary_entry_query_builder($glossary);
    if (!empty($options['includenotapproved']) && has_capability('mod/glossary:approve', $context)) {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_ALL);
    } else {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_SELF);
    }

    $qb->add_field('*', 'entries');
    $qb->join_user();
    $qb->add_user_fields();
    $qb->limit($from, $limit);

    if ($order == 'CREATION') {
        $qb->order_by('timecreated', 'entries', $sort);
    } else {
        $qb->order_by('timemodified', 'entries', $sort);
    }
    $qb->order_by('id', 'entries', $sort); 
        $count = $qb->count_records();
    $entries = $qb->get_records();

    return array($entries, $count);
}


function glossary_get_entries_by_category($glossary, $context, $categoryid, $from, $limit, $options = array()) {

    $qb = new mod_glossary_entry_query_builder($glossary);
    if (!empty($options['includenotapproved']) && has_capability('mod/glossary:approve', $context)) {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_ALL);
    } else {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_SELF);
    }

    $qb->join_category($categoryid);
    $qb->join_user();

        if ($categoryid === GLOSSARY_SHOW_ALL_CATEGORIES) {
        $qb->add_field('id', 'entries_categories', 'cid');
    }

    $qb->add_field('*', 'entries');
    $qb->add_field('categoryid', 'entries_categories');
    $qb->add_user_fields();

    if ($categoryid === GLOSSARY_SHOW_ALL_CATEGORIES) {
        $qb->add_field('name', 'categories', 'categoryname');
        $qb->order_by('name', 'categories');

    } else if ($categoryid === GLOSSARY_SHOW_NOT_CATEGORISED) {
        $qb->where('categoryid', 'entries_categories', null);
    }

        $qb->order_by('concept', 'entries');
    $qb->order_by('id', 'entries', 'ASC');
    $qb->limit($from, $limit);

        $count = $qb->count_records();
    $entries = $qb->get_records();

    return array($entries, $count);
}


function glossary_get_entries_by_author($glossary, $context, $letter, $field, $sort, $from, $limit, $options = array()) {

    $firstnamefirst = $field === 'FIRSTNAME';
    $qb = new mod_glossary_entry_query_builder($glossary);
    if ($letter != 'ALL' && $letter != 'SPECIAL' && core_text::strlen($letter)) {
        $qb->filter_by_author_letter($letter, $firstnamefirst);
    }
    if ($letter == 'SPECIAL') {
        $qb->filter_by_author_non_letter($firstnamefirst);
    }

    if (!empty($options['includenotapproved']) && has_capability('mod/glossary:approve', $context)) {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_ALL);
    } else {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_SELF);
    }

    $qb->add_field('*', 'entries');
    $qb->join_user(true);
    $qb->add_user_fields();
    $qb->order_by_author($firstnamefirst, $sort);
    $qb->order_by('concept', 'entries');
    $qb->order_by('id', 'entries', 'ASC');     $qb->limit($from, $limit);

        $count = $qb->count_records();
    $entries = $qb->get_records();

    return array($entries, $count);
}


function glossary_get_entries_by_author_id($glossary, $context, $authorid, $order, $sort, $from, $limit, $options = array()) {

    $qb = new mod_glossary_entry_query_builder($glossary);
    if (!empty($options['includenotapproved']) && has_capability('mod/glossary:approve', $context)) {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_ALL);
    } else {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_SELF);
    }

    $qb->add_field('*', 'entries');
    $qb->join_user(true);
    $qb->add_user_fields();
    $qb->where('id', 'user', $authorid);

    if ($order == 'CREATION') {
        $qb->order_by('timecreated', 'entries', $sort);
    } else if ($order == 'UPDATE') {
        $qb->order_by('timemodified', 'entries', $sort);
    } else {
        $qb->order_by('concept', 'entries', $sort);
    }
    $qb->order_by('id', 'entries', $sort); 
    $qb->limit($from, $limit);

        $count = $qb->count_records();
    $entries = $qb->get_records();

    return array($entries, $count);
}


function glossary_get_authors($glossary, $context, $limit, $from, $options = array()) {
    global $DB, $USER;

    $params = array();
    $userfields = user_picture::fields('u', null);

    $approvedsql = '(ge.approved <> 0 OR ge.userid = :myid)';
    $params['myid'] = $USER->id;
    if (!empty($options['includenotapproved']) && has_capability('mod/glossary:approve', $context)) {
        $approvedsql = '1 = 1';
    }

    $sqlselectcount = "SELECT COUNT(DISTINCT(u.id))";
    $sqlselect = "SELECT DISTINCT(u.id) AS userId, $userfields";
    $sql = "  FROM {user} u
              JOIN {glossary_entries} ge
                ON ge.userid = u.id
               AND (ge.glossaryid = :gid1 OR ge.sourceglossaryid = :gid2)
               AND $approvedsql";
    $ordersql = " ORDER BY u.lastname, u.firstname";

    $params['gid1'] = $glossary->id;
    $params['gid2'] = $glossary->id;

    $count = $DB->count_records_sql($sqlselectcount . $sql, $params);
    $users = $DB->get_recordset_sql($sqlselect . $sql . $ordersql, $params, $from, $limit);

    return array($users, $count);
}


function glossary_get_categories($glossary, $from, $limit) {
    global $DB;

    $count = $DB->count_records('glossary_categories', array('glossaryid' => $glossary->id));
    $categories = $DB->get_recordset('glossary_categories', array('glossaryid' => $glossary->id), 'name ASC', '*', $from, $limit);

    return array($categories, $count);
}


function glossary_get_search_terms_sql(array $terms, $fullsearch = true, $glossaryid = null) {
    global $DB;
    static $i = 0;

    if ($DB->sql_regex_supported()) {
        $regexp = $DB->sql_regex(true);
        $notregexp = $DB->sql_regex(false);
    }

    $params = array();
    $conditions = array();

    foreach ($terms as $searchterm) {
        $i++;

        $not = false;                       
        if (empty($fullsearch)) {
                        $concat = $DB->sql_concat('ge.concept', "' '", "COALESCE(al.alias, :emptychar{$i})");
        } else {
                        $concat = $DB->sql_concat('ge.concept', "' '", 'ge.definition', "' '", "COALESCE(al.alias, :emptychar{$i})");
        }
        $params['emptychar' . $i] = '';

                if (!$DB->sql_regex_supported()) {
            if (substr($searchterm, 0, 1) === '-') {
                $not = true;
            }
            $searchterm = trim($searchterm, '+-');
        }

        if (substr($searchterm, 0, 1) === '+') {
            $searchterm = trim($searchterm, '+-');
            $conditions[] = "$concat $regexp :searchterm{$i}";
            $params['searchterm' . $i] = '(^|[^a-zA-Z0-9])' . preg_quote($searchterm, '|') . '([^a-zA-Z0-9]|$)';

        } else if (substr($searchterm, 0, 1) === "-") {
            $searchterm = trim($searchterm, '+-');
            $conditions[] = "$concat $notregexp :searchterm{$i}";
            $params['searchterm' . $i] = '(^|[^a-zA-Z0-9])' . preg_quote($searchterm, '|') . '([^a-zA-Z0-9]|$)';

        } else {
            $conditions[] = $DB->sql_like($concat, ":searchterm{$i}", false, true, $not);
            $params['searchterm' . $i] = '%' . $DB->sql_like_escape($searchterm) . '%';
        }
    }

        if (isset($glossaryid)) {
        $conditions[] = 'ge.glossaryid = :glossaryid';
        $params['glossaryid'] = $glossaryid;
    }

        if (empty($conditions)) {
        $conditions[] = '1 = 2';
    }

    $where = implode(' AND ', $conditions);
    return array($where, $params);
}



function glossary_get_entries_by_search($glossary, $context, $query, $fullsearch, $order, $sort, $from, $limit,
                                        $options = array()) {
    global $DB, $USER;

        $terms = explode(' ', $query);
    foreach ($terms as $key => $term) {
        if (strlen(trim($term, '+-')) < 2) {
            unset($terms[$key]);
        }
    }

    list($searchcond, $params) = glossary_get_search_terms_sql($terms, $fullsearch, $glossary->id);

    $userfields = user_picture::fields('u', null, 'userdataid', 'userdata');

        $sqlwrapheader = 'SELECT ge.*, ge.concept AS glossarypivot, ' . $userfields . '
                        FROM {glossary_entries} ge
                        LEFT JOIN {user} u ON u.id = ge.userid
                        JOIN ( ';
    $sqlwrapfooter = ' ) gei ON (ge.id = gei.id)';
    $sqlselect  = "SELECT DISTINCT ge.id";
    $sqlfrom    = "FROM {glossary_entries} ge
                   LEFT JOIN {glossary_alias} al ON al.entryid = ge.id";

    if (!empty($options['includenotapproved']) && has_capability('mod/glossary:approve', $context)) {
        $approvedsql = '';
    } else {
        $approvedsql = 'AND (ge.approved <> 0 OR ge.userid = :myid)';
        $params['myid'] = $USER->id;
    }

    if ($order == 'CREATION') {
        $sqlorderby = "ORDER BY ge.timecreated $sort";
    } else if ($order == 'UPDATE') {
        $sqlorderby = "ORDER BY ge.timemodified $sort";
    } else {
        $sqlorderby = "ORDER BY ge.concept $sort";
    }
    $sqlorderby .= " , ge.id ASC"; 
    $sqlwhere = "WHERE ($searchcond) $approvedsql";

        $count = $DB->count_records_sql("SELECT COUNT(DISTINCT(ge.id)) $sqlfrom $sqlwhere", $params);

    $query = "$sqlwrapheader $sqlselect $sqlfrom $sqlwhere $sqlwrapfooter $sqlorderby";
    $entries = $DB->get_recordset_sql($query, $params, $from, $limit);

    return array($entries, $count);
}


function glossary_get_entries_by_term($glossary, $context, $term, $from, $limit, $options = array()) {

        $qb = new mod_glossary_entry_query_builder($glossary);
    if (!empty($options['includenotapproved']) && has_capability('mod/glossary:approve', $context)) {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_ALL);
    } else {
        $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_SELF);
    }

    $qb->add_field('*', 'entries');
    $qb->join_alias();
    $qb->join_user();
    $qb->add_user_fields();
    $qb->filter_by_term($term);

    $qb->order_by('concept', 'entries');
    $qb->order_by('id', 'entries');         $qb->limit($from, $limit);

        $count = $qb->count_records();
    $entries = $qb->get_records();

    return array($entries, $count);
}


function glossary_get_entries_to_approve($glossary, $context, $letter, $order, $sort, $from, $limit) {

    $qb = new mod_glossary_entry_query_builder($glossary);
    if ($letter != 'ALL' && $letter != 'SPECIAL' && core_text::strlen($letter)) {
        $qb->filter_by_concept_letter($letter);
    }
    if ($letter == 'SPECIAL') {
        $qb->filter_by_concept_non_letter();
    }

    $qb->add_field('*', 'entries');
    $qb->join_user();
    $qb->add_user_fields();
    $qb->filter_by_non_approved(mod_glossary_entry_query_builder::NON_APPROVED_ONLY);
    if ($order == 'CREATION') {
        $qb->order_by('timecreated', 'entries', $sort);
    } else if ($order == 'UPDATE') {
        $qb->order_by('timemodified', 'entries', $sort);
    } else {
        $qb->order_by('concept', 'entries', $sort);
    }
    $qb->order_by('id', 'entries', $sort);     $qb->limit($from, $limit);

        $count = $qb->count_records();
    $entries = $qb->get_records();

    return array($entries, $count);
}


function glossary_get_entry_by_id($id) {

        $qb = new mod_glossary_entry_query_builder();
    $qb->add_field('*', 'entries');
    $qb->join_user();
    $qb->add_user_fields();
    $qb->where('id', 'entries', $id);

        $entries = $qb->get_records();
    if (empty($entries)) {
        return false;
    }
    return array_pop($entries);
}


function glossary_can_view_entry($entry, $cminfo) {
    global $USER;

    $cm = $cminfo->get_course_module_record();
    $context = \context_module::instance($cm->id);

        if ($cminfo->uservisible === false) {
        return false;
    }

        if (empty($entry->approved) && $entry->userid != $USER->id && !has_capability('mod/glossary:approve', $context)) {
        return false;
    }

    return true;
}
