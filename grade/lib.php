<?php



require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/export/lib.php');


class graded_users_iterator {

    
    protected $course;

    
    protected $grade_items;

    
    protected $groupid;

    
    protected $users_rs;

    
    protected $grades_rs;

    
    protected $gradestack;

    
    protected $sortfield1;

    
    protected $sortorder1;

    
    protected $sortfield2;

    
    protected $sortorder2;

    
    protected $onlyactive = false;

    
    protected $allowusercustomfields = false;

    
    protected $suspendedusers = array();

    
    public function __construct($course, $grade_items=null, $groupid=0,
                                          $sortfield1='lastname', $sortorder1='ASC',
                                          $sortfield2='firstname', $sortorder2='ASC') {
        $this->course      = $course;
        $this->grade_items = $grade_items;
        $this->groupid     = $groupid;
        $this->sortfield1  = $sortfield1;
        $this->sortorder1  = $sortorder1;
        $this->sortfield2  = $sortfield2;
        $this->sortorder2  = $sortorder2;

        $this->gradestack  = array();
    }

    
    public function init() {
        global $CFG, $DB;

        $this->close();

        export_verify_grades($this->course->id);
        $course_item = grade_item::fetch_course_item($this->course->id);
        if ($course_item->needsupdate) {
                        return false;
        }

        $coursecontext = context_course::instance($this->course->id);

        list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($coursecontext->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');
        list($gradebookroles_sql, $params) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr');
        list($enrolledsql, $enrolledparams) = get_enrolled_sql($coursecontext, '', 0, $this->onlyactive);

        $params = array_merge($params, $enrolledparams, $relatedctxparams);

        if ($this->groupid) {
            $groupsql = "INNER JOIN {groups_members} gm ON gm.userid = u.id";
            $groupwheresql = "AND gm.groupid = :groupid";
                        $params['groupid'] = $this->groupid;
        } else {
            $groupsql = "";
            $groupwheresql = "";
        }

        if (empty($this->sortfield1)) {
                        $ofields = ", u.id AS usrt";
            $order   = "usrt ASC";

        } else {
            $ofields = ", u.$this->sortfield1 AS usrt1";
            $order   = "usrt1 $this->sortorder1";
            if (!empty($this->sortfield2)) {
                $ofields .= ", u.$this->sortfield2 AS usrt2";
                $order   .= ", usrt2 $this->sortorder2";
            }
            if ($this->sortfield1 != 'id' and $this->sortfield2 != 'id') {
                                                $ofields .= ", u.id AS usrt";
                $order   .= ", usrt ASC";
            }
        }

        $userfields = 'u.*';
        $customfieldssql = '';
        if ($this->allowusercustomfields && !empty($CFG->grade_export_customprofilefields)) {
            $customfieldscount = 0;
            $customfieldsarray = grade_helper::get_user_profile_fields($this->course->id, $this->allowusercustomfields);
            foreach ($customfieldsarray as $field) {
                if (!empty($field->customid)) {
                    $customfieldssql .= "
                            LEFT JOIN (SELECT * FROM {user_info_data}
                                WHERE fieldid = :cf$customfieldscount) cf$customfieldscount
                            ON u.id = cf$customfieldscount.userid";
                    $userfields .= ", cf$customfieldscount.data AS customfield_{$field->shortname}";
                    $params['cf'.$customfieldscount] = $field->customid;
                    $customfieldscount++;
                }
            }
        }

        $users_sql = "SELECT $userfields $ofields
                        FROM {user} u
                        JOIN ($enrolledsql) je ON je.id = u.id
                             $groupsql $customfieldssql
                        JOIN (
                                  SELECT DISTINCT ra.userid
                                    FROM {role_assignments} ra
                                   WHERE ra.roleid $gradebookroles_sql
                                     AND ra.contextid $relatedctxsql
                             ) rainner ON rainner.userid = u.id
                         WHERE u.deleted = 0
                             $groupwheresql
                    ORDER BY $order";
        $this->users_rs = $DB->get_recordset_sql($users_sql, $params);

        if (!$this->onlyactive) {
            $context = context_course::instance($this->course->id);
            $this->suspendedusers = get_suspended_userids($context);
        } else {
            $this->suspendedusers = array();
        }

        if (!empty($this->grade_items)) {
            $itemids = array_keys($this->grade_items);
            list($itemidsql, $grades_params) = $DB->get_in_or_equal($itemids, SQL_PARAMS_NAMED, 'items');
            $params = array_merge($params, $grades_params);

            $grades_sql = "SELECT g.* $ofields
                             FROM {grade_grades} g
                             JOIN {user} u ON g.userid = u.id
                             JOIN ($enrolledsql) je ON je.id = u.id
                                  $groupsql
                             JOIN (
                                      SELECT DISTINCT ra.userid
                                        FROM {role_assignments} ra
                                       WHERE ra.roleid $gradebookroles_sql
                                         AND ra.contextid $relatedctxsql
                                  ) rainner ON rainner.userid = u.id
                              WHERE u.deleted = 0
                              AND g.itemid $itemidsql
                              $groupwheresql
                         ORDER BY $order, g.itemid ASC";
            $this->grades_rs = $DB->get_recordset_sql($grades_sql, $params);
        } else {
            $this->grades_rs = false;
        }

        return true;
    }

    
    public function next_user() {
        if (!$this->users_rs) {
            return false;         }

        if (!$this->users_rs->valid()) {
            if ($current = $this->_pop()) {
                            }

            return false;         } else {
            $user = $this->users_rs->current();
            $this->users_rs->next();
        }

                $grade_records = array();
        while (true) {
            if (!$current = $this->_pop()) {
                break;             }

            if (empty($current->userid)) {
                break;
            }

            if ($current->userid != $user->id) {
                                $this->_push($current);
                break;
            }

            $grade_records[$current->itemid] = $current;
        }

        $grades = array();
        $feedbacks = array();

        if (!empty($this->grade_items)) {
            foreach ($this->grade_items as $grade_item) {
                if (!isset($feedbacks[$grade_item->id])) {
                    $feedbacks[$grade_item->id] = new stdClass();
                }
                if (array_key_exists($grade_item->id, $grade_records)) {
                    $feedbacks[$grade_item->id]->feedback       = $grade_records[$grade_item->id]->feedback;
                    $feedbacks[$grade_item->id]->feedbackformat = $grade_records[$grade_item->id]->feedbackformat;
                    unset($grade_records[$grade_item->id]->feedback);
                    unset($grade_records[$grade_item->id]->feedbackformat);
                    $grades[$grade_item->id] = new grade_grade($grade_records[$grade_item->id], false);
                } else {
                    $feedbacks[$grade_item->id]->feedback       = '';
                    $feedbacks[$grade_item->id]->feedbackformat = FORMAT_MOODLE;
                    $grades[$grade_item->id] =
                        new grade_grade(array('userid'=>$user->id, 'itemid'=>$grade_item->id), false);
                }
                $grades[$grade_item->id]->grade_item = $grade_item;
            }
        }

                $user->suspendedenrolment = isset($this->suspendedusers[$user->id]);
        $result = new stdClass();
        $result->user      = $user;
        $result->grades    = $grades;
        $result->feedbacks = $feedbacks;
        return $result;
    }

    
    public function close() {
        if ($this->users_rs) {
            $this->users_rs->close();
            $this->users_rs = null;
        }
        if ($this->grades_rs) {
            $this->grades_rs->close();
            $this->grades_rs = null;
        }
        $this->gradestack = array();
    }

    
    public function require_active_enrolment($onlyactive = true) {
        if (!empty($this->users_rs)) {
            debugging('Calling require_active_enrolment() has no effect unless you call init() again', DEBUG_DEVELOPER);
        }
        $this->onlyactive  = $onlyactive;
    }

    
    public function allow_user_custom_fields($allow = true) {
        if ($allow) {
            $this->allowusercustomfields = true;
        } else {
            $this->allowusercustomfields = false;
        }
    }

    
    private function _push($grade) {
        array_push($this->gradestack, $grade);
    }


    
    private function _pop() {
        global $DB;
        if (empty($this->gradestack)) {
            if (empty($this->grades_rs) || !$this->grades_rs->valid()) {
                return null;             }

            $current = $this->grades_rs->current();

            $this->grades_rs->next();

            return $current;
        } else {
            return array_pop($this->gradestack);
        }
    }
}


function print_graded_users_selector($course, $actionpage, $userid=0, $groupid=0, $includeall=true, $return=false) {
    global $CFG, $USER, $OUTPUT;
    return $OUTPUT->render(grade_get_graded_users_select(substr($actionpage, 0, strpos($actionpage, '/')), $course, $userid, $groupid, $includeall));
}

function grade_get_graded_users_select($report, $course, $userid, $groupid, $includeall) {
    global $USER, $CFG;

    if (is_null($userid)) {
        $userid = $USER->id;
    }
    $coursecontext = context_course::instance($course->id);
    $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
    $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
    $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $coursecontext);
    $menu = array();     $menususpendedusers = array();     $gui = new graded_users_iterator($course, null, $groupid);
    $gui->require_active_enrolment($showonlyactiveenrol);
    $gui->init();
    $label = get_string('selectauser', 'grades');
    if ($includeall) {
        $menu[0] = get_string('allusers', 'grades');
        $label = get_string('selectalloroneuser', 'grades');
    }
    while ($userdata = $gui->next_user()) {
        $user = $userdata->user;
        $userfullname = fullname($user);
        if ($user->suspendedenrolment) {
            $menususpendedusers[$user->id] = $userfullname;
        } else {
            $menu[$user->id] = $userfullname;
        }
    }
    $gui->close();

    if ($includeall) {
        $menu[0] .= " (" . (count($menu) + count($menususpendedusers) - 1) . ")";
    }

    if (!empty($menususpendedusers)) {
        $menu[] = array(get_string('suspendedusers') => $menususpendedusers);
    }
    $select = new single_select(new moodle_url('/grade/report/'.$report.'/index.php', array('id'=>$course->id)), 'userid', $menu, $userid);
    $select->label = $label;
    $select->formid = 'choosegradeuser';
    return $select;
}


function hide_natural_aggregation_upgrade_notice($courseid) {
    unset_config('show_sumofgrades_upgrade_' . $courseid);
}


function grade_hide_min_max_grade_upgrade_notice($courseid) {
    unset_config('show_min_max_grades_changed_' . $courseid);
}


function grade_upgrade_use_min_max_from_grade_grade($courseid) {
    grade_set_setting($courseid, 'minmaxtouse', GRADE_MIN_MAX_FROM_GRADE_GRADE);

    grade_force_full_regrading($courseid);
        grade_regrade_final_grades($courseid);
}


function grade_upgrade_use_min_max_from_grade_item($courseid) {
    grade_set_setting($courseid, 'minmaxtouse', GRADE_MIN_MAX_FROM_GRADE_ITEM);

    grade_force_full_regrading($courseid);
        grade_regrade_final_grades($courseid);
}


function hide_aggregatesubcats_upgrade_notice($courseid) {
    unset_config('show_aggregatesubcats_upgrade_' . $courseid);
}


function hide_gradebook_calculations_freeze_notice($courseid) {
    unset_config('gradebook_calculations_freeze_' . $courseid);
}


function print_natural_aggregation_upgrade_notice($courseid, $context, $thispage, $return=false) {
    global $CFG, $OUTPUT;
    $html = '';

        if (!has_capability('moodle/grade:manage', $context)) {
        return $html;
    }

    $hidesubcatswarning = optional_param('seenaggregatesubcatsupgradedgrades', false, PARAM_BOOL) && confirm_sesskey();
    $showsubcatswarning = get_config('core', 'show_aggregatesubcats_upgrade_' . $courseid);
    $hidenaturalwarning = optional_param('seensumofgradesupgradedgrades', false, PARAM_BOOL) && confirm_sesskey();
    $shownaturalwarning = get_config('core', 'show_sumofgrades_upgrade_' . $courseid);

    $hideminmaxwarning = optional_param('seenminmaxupgradedgrades', false, PARAM_BOOL) && confirm_sesskey();
    $showminmaxwarning = get_config('core', 'show_min_max_grades_changed_' . $courseid);

    $useminmaxfromgradeitem = optional_param('useminmaxfromgradeitem', false, PARAM_BOOL) && confirm_sesskey();
    $useminmaxfromgradegrade = optional_param('useminmaxfromgradegrade', false, PARAM_BOOL) && confirm_sesskey();

    $minmaxtouse = grade_get_setting($courseid, 'minmaxtouse', $CFG->grade_minmaxtouse);

    $gradebookcalculationsfreeze = get_config('core', 'gradebook_calculations_freeze_' . $courseid);
    $acceptgradebookchanges = optional_param('acceptgradebookchanges', false, PARAM_BOOL) && confirm_sesskey();

        if ($hidenaturalwarning) {
        hide_natural_aggregation_upgrade_notice($courseid);
    }
        if ($hidesubcatswarning) {
        hide_aggregatesubcats_upgrade_notice($courseid);
    }

        if ($hideminmaxwarning) {
        grade_hide_min_max_grade_upgrade_notice($courseid);
        $showminmaxwarning = false;
    }

    if ($useminmaxfromgradegrade) {
                grade_upgrade_use_min_max_from_grade_grade($courseid);
        grade_hide_min_max_grade_upgrade_notice($courseid);
        $showminmaxwarning = false;

    } else if ($useminmaxfromgradeitem) {
                grade_upgrade_use_min_max_from_grade_item($courseid);
        grade_hide_min_max_grade_upgrade_notice($courseid);
        $showminmaxwarning = false;
    }


    if (!$hidenaturalwarning && $shownaturalwarning) {
        $message = get_string('sumofgradesupgradedgrades', 'grades');
        $hidemessage = get_string('upgradedgradeshidemessage', 'grades');
        $urlparams = array( 'id' => $courseid,
                            'seensumofgradesupgradedgrades' => true,
                            'sesskey' => sesskey());
        $goawayurl = new moodle_url($thispage, $urlparams);
        $goawaybutton = $OUTPUT->single_button($goawayurl, $hidemessage, 'get');
        $html .= $OUTPUT->notification($message, 'notifysuccess');
        $html .= $goawaybutton;
    }

    if (!$hidesubcatswarning && $showsubcatswarning) {
        $message = get_string('aggregatesubcatsupgradedgrades', 'grades');
        $hidemessage = get_string('upgradedgradeshidemessage', 'grades');
        $urlparams = array( 'id' => $courseid,
                            'seenaggregatesubcatsupgradedgrades' => true,
                            'sesskey' => sesskey());
        $goawayurl = new moodle_url($thispage, $urlparams);
        $goawaybutton = $OUTPUT->single_button($goawayurl, $hidemessage, 'get');
        $html .= $OUTPUT->notification($message, 'notifysuccess');
        $html .= $goawaybutton;
    }

    if ($showminmaxwarning) {
        $hidemessage = get_string('upgradedgradeshidemessage', 'grades');
        $urlparams = array( 'id' => $courseid,
                            'seenminmaxupgradedgrades' => true,
                            'sesskey' => sesskey());

        $goawayurl = new moodle_url($thispage, $urlparams);
        $hideminmaxbutton = $OUTPUT->single_button($goawayurl, $hidemessage, 'get');
        $moreinfo = html_writer::link(get_docs_url(get_string('minmaxtouse_link', 'grades')), get_string('moreinfo'),
            array('target' => '_blank'));

        if ($minmaxtouse == GRADE_MIN_MAX_FROM_GRADE_ITEM) {
                        $message = get_string('minmaxupgradedgrades', 'grades') . ' ' . $moreinfo;

            $revertmessage = get_string('upgradedminmaxrevertmessage', 'grades');
            $urlparams = array('id' => $courseid,
                               'useminmaxfromgradegrade' => true,
                               'sesskey' => sesskey());
            $reverturl = new moodle_url($thispage, $urlparams);
            $revertbutton = $OUTPUT->single_button($reverturl, $revertmessage, 'get');

            $html .= $OUTPUT->notification($message);
            $html .= $revertbutton . $hideminmaxbutton;

        } else if ($minmaxtouse == GRADE_MIN_MAX_FROM_GRADE_GRADE) {
                        $message = get_string('minmaxupgradewarning', 'grades') . ' ' . $moreinfo;

            $fixmessage = get_string('minmaxupgradefixbutton', 'grades');
            $urlparams = array('id' => $courseid,
                               'useminmaxfromgradeitem' => true,
                               'sesskey' => sesskey());
            $fixurl = new moodle_url($thispage, $urlparams);
            $fixbutton = $OUTPUT->single_button($fixurl, $fixmessage, 'get');

            $html .= $OUTPUT->notification($message);
            $html .= $fixbutton . $hideminmaxbutton;
        }
    }

    if ($gradebookcalculationsfreeze) {
        if ($acceptgradebookchanges) {
                        hide_gradebook_calculations_freeze_notice($courseid);
            $courseitem = grade_item::fetch_course_item($courseid);
            $courseitem->force_regrading();
            grade_regrade_final_grades($courseid);

            $html .= $OUTPUT->notification(get_string('gradebookcalculationsuptodate', 'grades'), 'notifysuccess');
        } else {
                        $a = new stdClass();
            $a->gradebookversion = $gradebookcalculationsfreeze;
            if (preg_match('/(\d{8,})/', $CFG->release, $matches)) {
                $a->currentversion = $matches[1];
            } else {
                $a->currentversion = $CFG->release;
            }
            $a->url = get_docs_url('Gradebook_calculation_changes');
            $message = get_string('gradebookcalculationswarning', 'grades', $a);

            $fixmessage = get_string('gradebookcalculationsfixbutton', 'grades');
            $urlparams = array('id' => $courseid,
                'acceptgradebookchanges' => true,
                'sesskey' => sesskey());
            $fixurl = new moodle_url($thispage, $urlparams);
            $fixbutton = $OUTPUT->single_button($fixurl, $fixmessage, 'get');

            $html .= $OUTPUT->notification($message);
            $html .= $fixbutton;
        }
    }

    if (!empty($html)) {
        $html = html_writer::tag('div', $html, array('class' => 'core_grades_notices'));
    }

    if ($return) {
        return $html;
    } else {
        echo $html;
    }
}


function print_grade_plugin_selector($plugin_info, $active_type, $active_plugin, $return=false) {
    global $CFG, $OUTPUT, $PAGE;

    $menu = array();
    $count = 0;
    $active = '';

    foreach ($plugin_info as $plugin_type => $plugins) {
        if ($plugin_type == 'strings') {
            continue;
        }

        $first_plugin = reset($plugins);

        $sectionname = $plugin_info['strings'][$plugin_type];
        $section = array();

        foreach ($plugins as $plugin) {
            $link = $plugin->link->out(false);
            $section[$link] = $plugin->string;
            $count++;
            if ($plugin_type === $active_type and $plugin->id === $active_plugin) {
                $active = $link;
            }
        }

        if ($section) {
            $menu[] = array($sectionname=>$section);
        }
    }

        if ($count > 1) {
        $select = new url_select($menu, $active, null, 'choosepluginreport');
        $select->set_label(get_string('gradereport', 'grades'), array('class' => 'accesshide'));
        if ($return) {
            return $OUTPUT->render($select);
        } else {
            echo $OUTPUT->render($select);
        }
    } else {
                return '';
    }
}


function grade_print_tabs($active_type, $active_plugin, $plugin_info, $return=false) {
    global $CFG, $COURSE;

    if (!isset($currenttab)) {         $currenttab = '';
    }

    $tabs = array();
    $top_row  = array();
    $bottom_row = array();
    $inactive = array($active_plugin);
    $activated = array($active_type);

    $count = 0;
    $active = '';

    foreach ($plugin_info as $plugin_type => $plugins) {
        if ($plugin_type == 'strings') {
            continue;
        }

                if (!empty($plugins->id)) {
            $string = $plugins->string;
            if (!empty($plugin_info[$active_type]->parent)) {
                $string = $plugin_info[$active_type]->parent->string;
            }

            $top_row[] = new tabobject($plugin_type, $plugins->link, $string);
            continue;
        }

        $first_plugin = reset($plugins);
        $url = $first_plugin->link;

        if ($plugin_type == 'report') {
            $url = $CFG->wwwroot.'/grade/report/index.php?id='.$COURSE->id;
        }

        $top_row[] = new tabobject($plugin_type, $url, $plugin_info['strings'][$plugin_type]);

        if ($active_type == $plugin_type) {
            foreach ($plugins as $plugin) {
                $bottom_row[] = new tabobject($plugin->id, $plugin->link, $plugin->string);
                if ($plugin->id == $active_plugin) {
                    $inactive = array($plugin->id);
                }
            }
        }
    }

    $tabs[] = $top_row;
    $tabs[] = $bottom_row;

    if ($return) {
        return print_tabs($tabs, $active_plugin, $inactive, $activated, true);
    } else {
        print_tabs($tabs, $active_plugin, $inactive, $activated);
    }
}


function grade_get_plugin_info($courseid, $active_type, $active_plugin) {
    global $CFG, $SITE;

    $context = context_course::instance($courseid);

    $plugin_info = array();
    $count = 0;
    $active = '';
    $url_prefix = $CFG->wwwroot . '/grade/';

        $plugin_info['strings'] = grade_helper::get_plugin_strings();

    if ($reports = grade_helper::get_plugins_reports($courseid)) {
        $plugin_info['report'] = $reports;
    }

    if ($settings = grade_helper::get_info_manage_settings($courseid)) {
        $plugin_info['settings'] = $settings;
    }

    if ($scale = grade_helper::get_info_scales($courseid)) {
        $plugin_info['scale'] = array('view'=>$scale);
    }

    if ($outcomes = grade_helper::get_info_outcomes($courseid)) {
        $plugin_info['outcome'] = $outcomes;
    }

    if ($letters = grade_helper::get_info_letters($courseid)) {
        $plugin_info['letter'] = $letters;
    }

    if ($imports = grade_helper::get_plugins_import($courseid)) {
        $plugin_info['import'] = $imports;
    }

    if ($exports = grade_helper::get_plugins_export($courseid)) {
        $plugin_info['export'] = $exports;
    }

        foreach ($plugin_info as $plugin_type => $plugins) {
        if (!empty($plugins->id) && $active_plugin == $plugins->id) {
            $plugin_info['strings']['active_plugin_str'] = $plugins->string;
            break;
        }
        foreach ($plugins as $plugin) {
            if (is_a($plugin, 'grade_plugin_info')) {
                if ($active_plugin == $plugin->id) {
                    $plugin_info['strings']['active_plugin_str'] = $plugin->string;
                }
            }
        }
    }

    return $plugin_info;
}


class grade_plugin_info {
    
    public $id;
    
    public $link;
    
    public $string;
    
    public $parent;

    
    public function __construct($id, $link, $string, $parent=null) {
        $this->id = $id;
        $this->link = $link;
        $this->string = $string;
        $this->parent = $parent;
    }
}


function print_grade_page_head($courseid, $active_type, $active_plugin=null,
                               $heading = false, $return=false,
                               $buttons=false, $shownavigation=true, $headerhelpidentifier = null, $headerhelpcomponent = null,
                               $user = null) {
    global $CFG, $OUTPUT, $PAGE;

    if ($active_type === 'preferences') {
                $active_type = 'settings';
    }

    $plugin_info = grade_get_plugin_info($courseid, $active_type, $active_plugin);

        $stractive_plugin = ($active_plugin) ? $plugin_info['strings']['active_plugin_str'] : $heading;
    $stractive_type = $plugin_info['strings'][$active_type];

    if (empty($plugin_info[$active_type]->id) || !empty($plugin_info[$active_type]->parent)) {
        $title = $PAGE->course->fullname.': ' . $stractive_type . ': ' . $stractive_plugin;
    } else {
        $title = $PAGE->course->fullname.': ' . $stractive_plugin;
    }

    if ($active_type == 'report') {
        $PAGE->set_pagelayout('report');
    } else {
        $PAGE->set_pagelayout('admin');
    }
    $PAGE->set_title(get_string('grades') . ': ' . $stractive_type);
    $PAGE->set_heading($title);
    if ($buttons instanceof single_button) {
        $buttons = $OUTPUT->render($buttons);
    }
    $PAGE->set_button($buttons);
    if ($courseid != SITEID) {
        grade_extend_settings($plugin_info, $courseid);
    }

        if ($active_plugin !== null && $reportnav = $PAGE->settingsnav->find($active_plugin, navigation_node::TYPE_SETTING)) {
        $reportnav->make_active();
    }

    $returnval = $OUTPUT->header();

    if (!$return) {
        echo $returnval;
    }

        if (!$heading) {
        $heading = $stractive_plugin;
    }

    if ($shownavigation) {
        $navselector = null;
        if ($courseid != SITEID &&
                ($CFG->grade_navmethod == GRADE_NAVMETHOD_COMBO || $CFG->grade_navmethod == GRADE_NAVMETHOD_DROPDOWN)) {
                        $navselector = print_grade_plugin_selector($plugin_info, $active_type, $active_plugin, true);
            if ($return) {
                $returnval .= $navselector;
            } else if (!isset($user)) {
                echo $navselector;
            }
        }

        $output = '';
                if (isset($headerhelpidentifier)) {
            $output = $OUTPUT->heading_with_help($heading, $headerhelpidentifier, $headerhelpcomponent);
        } else {
            if (isset($user)) {
                $output = $OUTPUT->context_header(
                        array(
                            'heading' => fullname($user),
                            'user' => $user,
                            'usercontext' => context_user::instance($user->id)
                        ), 2
                    ) . $navselector;
            } else {
                $output = $OUTPUT->heading($heading);
            }
        }

        if ($return) {
            $returnval .= $output;
        } else {
            echo $output;
        }

        if ($courseid != SITEID &&
                ($CFG->grade_navmethod == GRADE_NAVMETHOD_COMBO || $CFG->grade_navmethod == GRADE_NAVMETHOD_TABS)) {
            $returnval .= grade_print_tabs($active_type, $active_plugin, $plugin_info, $return);
        }
    }

    $returnval .= print_natural_aggregation_upgrade_notice($courseid,
                                                           context_course::instance($courseid),
                                                           $PAGE->url,
                                                           $return);

    if ($return) {
        return $returnval;
    }
}


class grade_plugin_return {
    public $type;
    public $plugin;
    public $courseid;
    public $userid;
    public $page;

    
    public function __construct($params = null) {
        if (empty($params)) {
            $this->type     = optional_param('gpr_type', null, PARAM_SAFEDIR);
            $this->plugin   = optional_param('gpr_plugin', null, PARAM_PLUGIN);
            $this->courseid = optional_param('gpr_courseid', null, PARAM_INT);
            $this->userid   = optional_param('gpr_userid', null, PARAM_INT);
            $this->page     = optional_param('gpr_page', null, PARAM_INT);

        } else {
            foreach ($params as $key=>$value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    
    public function grade_plugin_return($params = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($params);
    }

    
    public function get_options() {
        if (empty($this->type)) {
            return array();
        }

        $params = array();

        if (!empty($this->plugin)) {
            $params['plugin'] = $this->plugin;
        }

        if (!empty($this->courseid)) {
            $params['id'] = $this->courseid;
        }

        if (!empty($this->userid)) {
            $params['userid'] = $this->userid;
        }

        if (!empty($this->page)) {
            $params['page'] = $this->page;
        }

        return $params;
    }

    
    public function get_return_url($default, $extras=null) {
        global $CFG;

        if (empty($this->type) or empty($this->plugin)) {
            return $default;
        }

        $url = $CFG->wwwroot.'/grade/'.$this->type.'/'.$this->plugin.'/index.php';
        $glue = '?';

        if (!empty($this->courseid)) {
            $url .= $glue.'id='.$this->courseid;
            $glue = '&amp;';
        }

        if (!empty($this->userid)) {
            $url .= $glue.'userid='.$this->userid;
            $glue = '&amp;';
        }

        if (!empty($this->page)) {
            $url .= $glue.'page='.$this->page;
            $glue = '&amp;';
        }

        if (!empty($extras)) {
            foreach ($extras as $key=>$value) {
                $url .= $glue.$key.'='.$value;
                $glue = '&amp;';
            }
        }

        return $url;
    }

    
    public function get_form_fields() {
        if (empty($this->type)) {
            return '';
        }

        $result  = '<input type="hidden" name="gpr_type" value="'.$this->type.'" />';

        if (!empty($this->plugin)) {
            $result .= '<input type="hidden" name="gpr_plugin" value="'.$this->plugin.'" />';
        }

        if (!empty($this->courseid)) {
            $result .= '<input type="hidden" name="gpr_courseid" value="'.$this->courseid.'" />';
        }

        if (!empty($this->userid)) {
            $result .= '<input type="hidden" name="gpr_userid" value="'.$this->userid.'" />';
        }

        if (!empty($this->page)) {
            $result .= '<input type="hidden" name="gpr_page" value="'.$this->page.'" />';
        }
    }

    
    public function add_mform_elements(&$mform) {
        if (empty($this->type)) {
            return;
        }

        $mform->addElement('hidden', 'gpr_type', $this->type);
        $mform->setType('gpr_type', PARAM_SAFEDIR);

        if (!empty($this->plugin)) {
            $mform->addElement('hidden', 'gpr_plugin', $this->plugin);
            $mform->setType('gpr_plugin', PARAM_PLUGIN);
        }

        if (!empty($this->courseid)) {
            $mform->addElement('hidden', 'gpr_courseid', $this->courseid);
            $mform->setType('gpr_courseid', PARAM_INT);
        }

        if (!empty($this->userid)) {
            $mform->addElement('hidden', 'gpr_userid', $this->userid);
            $mform->setType('gpr_userid', PARAM_INT);
        }

        if (!empty($this->page)) {
            $mform->addElement('hidden', 'gpr_page', $this->page);
            $mform->setType('gpr_page', PARAM_INT);
        }
    }

    
    public function add_url_params(moodle_url $url) {
        if (empty($this->type)) {
            return $url;
        }

        $url->param('gpr_type', $this->type);

        if (!empty($this->plugin)) {
            $url->param('gpr_plugin', $this->plugin);
        }

        if (!empty($this->courseid)) {
            $url->param('gpr_courseid' ,$this->courseid);
        }

        if (!empty($this->userid)) {
            $url->param('gpr_userid', $this->userid);
        }

        if (!empty($this->page)) {
            $url->param('gpr_page', $this->page);
        }

        return $url;
    }
}


function grade_build_nav($path, $pagename=null, $id=null) {
    global $CFG, $COURSE, $PAGE;

    $strgrades = get_string('grades', 'grades');

        $dirroot_length = strlen($CFG->dirroot) + 1;     $path = substr($path, $dirroot_length);
    $path = str_replace('\\', '/', $path);

    $path_elements = explode('/', $path);

    $path_elements_count = count($path_elements);

        $PAGE->navbar->add($strgrades, new moodle_url('/grade/index.php', array('id'=>$COURSE->id)));

    $link = null;
    $numberofelements = 3;

        $linkparams = array();
    if (!is_null($id)) {
        if (is_array($id)) {
            foreach ($id as $idkey => $idvalue) {
                $linkparams[$idkey] = $idvalue;
            }
        } else {
            $linkparams['id'] = $id;
        }
    }

    $navlink4 = null;

        foreach ($path_elements as $key => $filename) {
        $path_elements[$key] = str_replace('.php', '', $filename);
    }

        switch ($path_elements[1]) {
        case 'edit':             if ($path_elements[3] != 'index.php') {
                $numberofelements = 4;
            }
            break;
        case 'import':             break;
        case 'export':             break;
        case 'report':
                        if (!is_null($id)) {
                $link = new moodle_url('/grade/report/index.php', $linkparams);
            }

            if ($path_elements[2] == 'grader') {
                $numberofelements = 4;
            }
            break;

        default:
                        debugging("grade_build_nav() doesn't support ". $path_elements[1] .
                    " as the second path element after 'grade'.");
            return false;
    }
    $PAGE->navbar->add(get_string($path_elements[1], 'grades'), $link);

        if (empty($pagename)) {
        $pagename = get_string($path_elements[2], 'grades');
    }

    switch ($numberofelements) {
        case 3:
            $PAGE->navbar->add($pagename, $link);
            break;
        case 4:
            if ($path_elements[2] == 'grader' AND $path_elements[3] != 'index.php') {
                $PAGE->navbar->add(get_string('pluginname', 'gradereport_grader'), new moodle_url('/grade/report/grader/index.php', $linkparams));
            }
            $PAGE->navbar->add($pagename);
            break;
    }

    return '';
}


class grade_structure {
    public $context;

    public $courseid;

    
    public $modinfo;

    
    public $items;

    
    public function get_element_icon(&$element, $spacerifnone=false) {
        global $CFG, $OUTPUT;
        require_once $CFG->libdir.'/filelib.php';

        $outputstr = '';

                $icon = new stdClass();
        $icon->attributes = array(
            'class' => 'icon itemicon'
        );
        $icon->component = 'moodle';

        $none = true;
        switch ($element['type']) {
            case 'item':
            case 'courseitem':
            case 'categoryitem':
                $none = false;

                $is_course   = $element['object']->is_course_item();
                $is_category = $element['object']->is_category_item();
                $is_scale    = $element['object']->gradetype == GRADE_TYPE_SCALE;
                $is_value    = $element['object']->gradetype == GRADE_TYPE_VALUE;
                $is_outcome  = !empty($element['object']->outcomeid);

                if ($element['object']->is_calculated()) {
                    $icon->pix = 'i/calc';
                    $icon->title = s(get_string('calculatedgrade', 'grades'));

                } else if (($is_course or $is_category) and ($is_scale or $is_value)) {
                    if ($category = $element['object']->get_item_category()) {
                        $aggrstrings = grade_helper::get_aggregation_strings();
                        $stragg = $aggrstrings[$category->aggregation];

                        $icon->pix = 'i/calc';
                        $icon->title = s($stragg);

                        switch ($category->aggregation) {
                            case GRADE_AGGREGATE_MEAN:
                            case GRADE_AGGREGATE_MEDIAN:
                            case GRADE_AGGREGATE_WEIGHTED_MEAN:
                            case GRADE_AGGREGATE_WEIGHTED_MEAN2:
                            case GRADE_AGGREGATE_EXTRACREDIT_MEAN:
                                $icon->pix = 'i/agg_mean';
                                break;
                            case GRADE_AGGREGATE_SUM:
                                $icon->pix = 'i/agg_sum';
                                break;
                        }
                    }

                } else if ($element['object']->itemtype == 'mod') {
                                        if ($is_outcome) {
                        $icon->pix = 'i/outcomes';
                        $icon->title = s(get_string('outcome', 'grades'));
                    } else {
                        $modinfo = get_fast_modinfo($element['object']->courseid);
                        $module = $element['object']->itemmodule;
                        $instanceid = $element['object']->iteminstance;
                        if (isset($modinfo->instances[$module][$instanceid])) {
                            $icon->url = $modinfo->instances[$module][$instanceid]->get_icon_url();
                        } else {
                            $icon->pix = 'icon';
                            $icon->component = $element['object']->itemmodule;
                        }
                        $icon->title = s(get_string('modulename', $element['object']->itemmodule));
                    }
                } else if ($element['object']->itemtype == 'manual') {
                    if ($element['object']->is_outcome_item()) {
                        $icon->pix = 'i/outcomes';
                        $icon->title = s(get_string('outcome', 'grades'));
                    } else {
                        $icon->pix = 'i/manual_item';
                        $icon->title = s(get_string('manualitem', 'grades'));
                    }
                }
                break;

            case 'category':
                $none = false;
                $icon->pix = 'i/folder';
                $icon->title = s(get_string('category', 'grades'));
                break;
        }

        if ($none) {
            if ($spacerifnone) {
                $outputstr = $OUTPUT->spacer() . ' ';
            }
        } else if (isset($icon->url)) {
            $outputstr = html_writer::img($icon->url, $icon->title, $icon->attributes);
        } else {
            $outputstr = $OUTPUT->pix_icon($icon->pix, $icon->title, $icon->component, $icon->attributes);
        }

        return $outputstr;
    }

    
    public function get_element_header(&$element, $withlink = false, $icon = true, $spacerifnone = false,
        $withdescription = false, $fulltotal = false) {
        $header = '';

        if ($icon) {
            $header .= $this->get_element_icon($element, $spacerifnone);
        }

        $header .= $element['object']->get_name($fulltotal);

        if ($element['type'] != 'item' and $element['type'] != 'categoryitem' and
            $element['type'] != 'courseitem') {
            return $header;
        }

        if ($withlink && $url = $this->get_activity_link($element)) {
            $a = new stdClass();
            $a->name = get_string('modulename', $element['object']->itemmodule);
            $title = get_string('linktoactivity', 'grades', $a);

            $header = html_writer::link($url, $header, array('title' => $title));
        } else {
            $header = html_writer::span($header);
        }

        if ($withdescription) {
            $desc = $element['object']->get_description();
            if (!empty($desc)) {
                $header .= '<div class="gradeitemdescription">' . s($desc) . '</div><div class="gradeitemdescriptionfiller"></div>';
            }
        }

        return $header;
    }

    private function get_activity_link($element) {
        global $CFG;
        
        static $hasgradephp = array();

        $itemtype = $element['object']->itemtype;
        $itemmodule = $element['object']->itemmodule;
        $iteminstance = $element['object']->iteminstance;
        $itemnumber = $element['object']->itemnumber;

                        if ($itemtype != 'mod' || !$iteminstance || !$itemmodule || !$this->modinfo) {
            return null;
        }

                $instances = $this->modinfo->get_instances();
        if (empty($instances[$itemmodule][$iteminstance])) {
            return null;
        }
        $cm = $instances[$itemmodule][$iteminstance];

                if (!$cm->uservisible) {
            return null;
        }

        if (!array_key_exists($itemmodule, $hasgradephp)) {
            if (file_exists($CFG->dirroot . '/mod/' . $itemmodule . '/grade.php')) {
                $hasgradephp[$itemmodule] = true;
            } else {
                $hasgradephp[$itemmodule] = false;
            }
        }

                if ($hasgradephp[$itemmodule]) {
            $args = array('id' => $cm->id, 'itemnumber' => $itemnumber);
            if (isset($element['userid'])) {
                $args['userid'] = $element['userid'];
            }
            return new moodle_url('/mod/' . $itemmodule . '/grade.php', $args);
        } else {
            return new moodle_url('/mod/' . $itemmodule . '/view.php', array('id' => $cm->id));
        }
    }

    
    public function get_grade_analysis_url(grade_grade $grade) {
        global $CFG;
        
        static $hasgradephp = array();

        if (empty($grade->grade_item) or !($grade->grade_item instanceof grade_item)) {
            throw new coding_exception('Passed grade without the associated grade item');
        }
        $item = $grade->grade_item;

        if (!$item->is_external_item()) {
                        return null;
        }
        if ($item->itemtype !== 'mod') {
            throw new coding_exception('Unknown external itemtype: '.$item->itemtype);
        }
        if (empty($item->iteminstance) or empty($item->itemmodule) or empty($this->modinfo)) {
            return null;
        }

        if (!array_key_exists($item->itemmodule, $hasgradephp)) {
            if (file_exists($CFG->dirroot . '/mod/' . $item->itemmodule . '/grade.php')) {
                $hasgradephp[$item->itemmodule] = true;
            } else {
                $hasgradephp[$item->itemmodule] = false;
            }
        }

        if (!$hasgradephp[$item->itemmodule]) {
            return null;
        }

        $instances = $this->modinfo->get_instances();
        if (empty($instances[$item->itemmodule][$item->iteminstance])) {
            return null;
        }
        $cm = $instances[$item->itemmodule][$item->iteminstance];
        if (!$cm->uservisible) {
            return null;
        }

        $url = new moodle_url('/mod/'.$item->itemmodule.'/grade.php', array(
            'id'         => $cm->id,
            'itemid'     => $item->id,
            'itemnumber' => $item->itemnumber,
            'gradeid'    => $grade->id,
            'userid'     => $grade->userid,
        ));

        return $url;
    }

    
    public function get_grade_analysis_icon(grade_grade $grade) {
        global $OUTPUT;

        $url = $this->get_grade_analysis_url($grade);
        if (is_null($url)) {
            return '';
        }

        return $OUTPUT->action_icon($url, new pix_icon('t/preview',
            get_string('gradeanalysis', 'core_grades')));
    }

    
    public function get_grade_eid($grade_grade) {
        if (empty($grade_grade->id)) {
            return 'n'.$grade_grade->itemid.'u'.$grade_grade->userid;
        } else {
            return 'g'.$grade_grade->id;
        }
    }

    
    public function get_item_eid($grade_item) {
        return 'ig'.$grade_item->id;
    }

    
    public function get_params_for_iconstr($element) {
        $strparams = new stdClass();
        $strparams->category = '';
        $strparams->itemname = '';
        $strparams->itemmodule = '';

        if (!method_exists($element['object'], 'get_name')) {
            return $strparams;
        }

        $strparams->itemname = html_to_text($element['object']->get_name());

                if ($strparams->itemname == get_string('categorytotal', 'grades')) {
            $parent = $element['object']->get_parent_category();
            $strparams->category = $parent->get_name() . ' ';
        } else {
            $strparams->category = '';
        }

        $strparams->itemmodule = null;
        if (isset($element['object']->itemmodule)) {
            $strparams->itemmodule = $element['object']->itemmodule;
        }
        return $strparams;
    }

    
    public function get_reset_icon($element, $gpr, $returnactionmenulink = false) {
        global $CFG, $OUTPUT;

                        if ($element['type'] != 'category' || $element['object']->aggregation != GRADE_AGGREGATE_SUM ||
                !has_capability('moodle/grade:manage', $this->context)) {
            return $returnactionmenulink ? null : '';
        }

        $str = get_string('resetweights', 'grades', $this->get_params_for_iconstr($element));
        $url = new moodle_url('/grade/edit/tree/action.php', array(
            'id' => $this->courseid,
            'action' => 'resetweights',
            'eid' => $element['eid'],
            'sesskey' => sesskey(),
        ));

        if ($returnactionmenulink) {
            return new action_menu_link_secondary($gpr->add_url_params($url), new pix_icon('t/reset', $str),
                get_string('resetweightsshort', 'grades'));
        } else {
            return $OUTPUT->action_icon($gpr->add_url_params($url), new pix_icon('t/reset', $str));
        }
    }

    
    public function get_edit_icon($element, $gpr, $returnactionmenulink = false) {
        global $CFG, $OUTPUT;

        if (!has_capability('moodle/grade:manage', $this->context)) {
            if ($element['type'] == 'grade' and has_capability('moodle/grade:edit', $this->context)) {
                            } else {
                return $returnactionmenulink ? null : '';
            }
        }

        static $strfeedback   = null;
        static $streditgrade = null;
        if (is_null($streditgrade)) {
            $streditgrade = get_string('editgrade', 'grades');
            $strfeedback  = get_string('feedback');
        }

        $strparams = $this->get_params_for_iconstr($element);

        $object = $element['object'];

        switch ($element['type']) {
            case 'item':
            case 'categoryitem':
            case 'courseitem':
                $stredit = get_string('editverbose', 'grades', $strparams);
                if (empty($object->outcomeid) || empty($CFG->enableoutcomes)) {
                    $url = new moodle_url('/grade/edit/tree/item.php',
                            array('courseid' => $this->courseid, 'id' => $object->id));
                } else {
                    $url = new moodle_url('/grade/edit/tree/outcomeitem.php',
                            array('courseid' => $this->courseid, 'id' => $object->id));
                }
                break;

            case 'category':
                $stredit = get_string('editverbose', 'grades', $strparams);
                $url = new moodle_url('/grade/edit/tree/category.php',
                        array('courseid' => $this->courseid, 'id' => $object->id));
                break;

            case 'grade':
                $stredit = $streditgrade;
                if (empty($object->id)) {
                    $url = new moodle_url('/grade/edit/tree/grade.php',
                            array('courseid' => $this->courseid, 'itemid' => $object->itemid, 'userid' => $object->userid));
                } else {
                    $url = new moodle_url('/grade/edit/tree/grade.php',
                            array('courseid' => $this->courseid, 'id' => $object->id));
                }
                if (!empty($object->feedback)) {
                    $feedback = addslashes_js(trim(format_string($object->feedback, $object->feedbackformat)));
                }
                break;

            default:
                $url = null;
        }

        if ($url) {
            if ($returnactionmenulink) {
                return new action_menu_link_secondary($gpr->add_url_params($url),
                    new pix_icon('t/edit', $stredit),
                    get_string('editsettings'));
            } else {
                return $OUTPUT->action_icon($gpr->add_url_params($url), new pix_icon('t/edit', $stredit));
            }

        } else {
            return $returnactionmenulink ? null : '';
        }
    }

    
    public function get_hiding_icon($element, $gpr, $returnactionmenulink = false) {
        global $CFG, $OUTPUT;

        if (!$element['object']->can_control_visibility()) {
            return $returnactionmenulink ? null : '';
        }

        if (!has_capability('moodle/grade:manage', $this->context) and
            !has_capability('moodle/grade:hide', $this->context)) {
            return $returnactionmenulink ? null : '';
        }

        $strparams = $this->get_params_for_iconstr($element);
        $strshow = get_string('showverbose', 'grades', $strparams);
        $strhide = get_string('hideverbose', 'grades', $strparams);

        $url = new moodle_url('/grade/edit/tree/action.php', array('id' => $this->courseid, 'sesskey' => sesskey(), 'eid' => $element['eid']));
        $url = $gpr->add_url_params($url);

        if ($element['object']->is_hidden()) {
            $type = 'show';
            $tooltip = $strshow;

                        if ($element['type'] != 'category' and $element['object']->get_hidden() > 1) {
                $type = 'hiddenuntil';
                $tooltip = get_string('hiddenuntildate', 'grades',
                        userdate($element['object']->get_hidden()));
            }

            $url->param('action', 'show');

            if ($returnactionmenulink) {
                $hideicon = new action_menu_link_secondary($url, new pix_icon('t/'.$type, $tooltip), get_string('show'));
            } else {
                $hideicon = $OUTPUT->action_icon($url, new pix_icon('t/'.$type, $tooltip, 'moodle', array('alt'=>$strshow, 'class'=>'smallicon')));
            }

        } else {
            $url->param('action', 'hide');
            if ($returnactionmenulink) {
                $hideicon = new action_menu_link_secondary($url, new pix_icon('t/hide', $strhide), get_string('hide'));
            } else {
                $hideicon = $OUTPUT->action_icon($url, new pix_icon('t/hide', $strhide));
            }
        }

        return $hideicon;
    }

    
    public function get_locking_icon($element, $gpr) {
        global $CFG, $OUTPUT;

        $strparams = $this->get_params_for_iconstr($element);
        $strunlock = get_string('unlockverbose', 'grades', $strparams);
        $strlock = get_string('lockverbose', 'grades', $strparams);

        $url = new moodle_url('/grade/edit/tree/action.php', array('id' => $this->courseid, 'sesskey' => sesskey(), 'eid' => $element['eid']));
        $url = $gpr->add_url_params($url);

                if ($element['type'] == 'grade' && $element['object']->grade_item->is_locked()) {
            $strparamobj = new stdClass();
            $strparamobj->itemname = $element['object']->grade_item->itemname;
            $strnonunlockable = get_string('nonunlockableverbose', 'grades', $strparamobj);

            $action = html_writer::tag('span', $OUTPUT->pix_icon('t/locked', $strnonunlockable),
                    array('class' => 'action-icon'));

        } else if ($element['object']->is_locked()) {
            $type = 'unlock';
            $tooltip = $strunlock;

                        if ($element['type'] != 'category' and $element['object']->get_locktime() > 1) {
                $type = 'locktime';
                $tooltip = get_string('locktimedate', 'grades',
                        userdate($element['object']->get_locktime()));
            }

            if (!has_capability('moodle/grade:manage', $this->context) and !has_capability('moodle/grade:unlock', $this->context)) {
                $action = '';
            } else {
                $url->param('action', 'unlock');
                $action = $OUTPUT->action_icon($url, new pix_icon('t/'.$type, $tooltip, 'moodle', array('alt'=>$strunlock, 'class'=>'smallicon')));
            }

        } else {
            if (!has_capability('moodle/grade:manage', $this->context) and !has_capability('moodle/grade:lock', $this->context)) {
                $action = '';
            } else {
                $url->param('action', 'lock');
                $action = $OUTPUT->action_icon($url, new pix_icon('t/lock', $strlock));
            }
        }

        return $action;
    }

    
    public function get_calculation_icon($element, $gpr, $returnactionmenulink = false) {
        global $CFG, $OUTPUT;
        if (!has_capability('moodle/grade:manage', $this->context)) {
            return $returnactionmenulink ? null : '';
        }

        $type   = $element['type'];
        $object = $element['object'];

        if ($type == 'item' or $type == 'courseitem' or $type == 'categoryitem') {
            $strparams = $this->get_params_for_iconstr($element);
            $streditcalculation = get_string('editcalculationverbose', 'grades', $strparams);

            $is_scale = $object->gradetype == GRADE_TYPE_SCALE;
            $is_value = $object->gradetype == GRADE_TYPE_VALUE;

                        if (!$object->is_external_item() and ($is_scale or $is_value)) {
                if ($object->is_calculated()) {
                    $icon = 't/calc';
                } else {
                    $icon = 't/calc_off';
                }

                $url = new moodle_url('/grade/edit/tree/calculation.php', array('courseid' => $this->courseid, 'id' => $object->id));
                $url = $gpr->add_url_params($url);
                if ($returnactionmenulink) {
                    return new action_menu_link_secondary($url,
                        new pix_icon($icon, $streditcalculation),
                        get_string('editcalculation', 'grades'));
                } else {
                    return $OUTPUT->action_icon($url, new pix_icon($icon, $streditcalculation));
                }
            }
        }

        return $returnactionmenulink ? null : '';
    }
}


class grade_seq extends grade_structure {

    
    public $elements;

    
    public function __construct($courseid, $category_grade_last=false, $nooutcomes=false) {
        global $USER, $CFG;

        $this->courseid   = $courseid;
        $this->context    = context_course::instance($courseid);

                $top_element = grade_category::fetch_course_tree($courseid, true);

        $this->elements = grade_seq::flatten($top_element, $category_grade_last, $nooutcomes);

        foreach ($this->elements as $key=>$unused) {
            $this->items[$this->elements[$key]['object']->id] =& $this->elements[$key]['object'];
        }
    }

    
    public function grade_seq($courseid, $category_grade_last=false, $nooutcomes=false) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($courseid, $category_grade_last, $nooutcomes);
    }

    
    public function flatten(&$element, $category_grade_last, $nooutcomes) {
        if (empty($element['children'])) {
            return array();
        }
        $children = array();

        foreach ($element['children'] as $sortorder=>$unused) {
            if ($nooutcomes and $element['type'] != 'category' and
                $element['children'][$sortorder]['object']->is_outcome_item()) {
                continue;
            }
            $children[] = $element['children'][$sortorder];
        }
        unset($element['children']);

        if ($category_grade_last and count($children) > 1) {
            $cat_item = array_shift($children);
            array_push($children, $cat_item);
        }

        $result = array();
        foreach ($children as $child) {
            if ($child['type'] == 'category') {
                $result = $result + grade_seq::flatten($child, $category_grade_last, $nooutcomes);
            } else {
                $child['eid'] = 'i'.$child['object']->id;
                $result[$child['object']->id] = $child;
            }
        }

        return $result;
    }

    
    public function locate_element($eid) {
                if (strpos($eid, 'n') === 0) {
            if (!preg_match('/n(\d+)u(\d+)/', $eid, $matches)) {
                return null;
            }

            $itemid = $matches[1];
            $userid = $matches[2];

                        if (!$item_el = $this->locate_element('ig'.$itemid)) {
                return null;
            }

                        $grade = new grade_grade(array('itemid'=>$itemid, 'userid'=>$userid));

            $grade->grade_item =& $item_el['object'];             return array('eid'=>'n'.$itemid.'u'.$userid,'object'=>$grade, 'type'=>'grade');

        } else if (strpos($eid, 'g') === 0) {
            $id = (int) substr($eid, 1);
            if (!$grade = grade_grade::fetch(array('id'=>$id))) {
                return null;
            }
                        if (!$item_el = $this->locate_element('ig'.$grade->itemid)) {
                return null;
            }
            $grade->grade_item =& $item_el['object'];             return array('eid'=>'g'.$id,'object'=>$grade, 'type'=>'grade');
        }

                foreach ($this->elements as $element) {
            if ($element['eid'] == $eid) {
                return $element;
            }
        }

        return null;
    }
}


class grade_tree extends grade_structure {

    
    public $top_element;

    
    public $levels;

    
    public $items;

    
    public function __construct($courseid, $fillers=true, $category_grade_last=false,
                               $collapsed=null, $nooutcomes=false) {
        global $USER, $CFG, $COURSE, $DB;

        $this->courseid   = $courseid;
        $this->levels     = array();
        $this->context    = context_course::instance($courseid);

        if (!empty($COURSE->id) && $COURSE->id == $this->courseid) {
            $course = $COURSE;
        } else {
            $course = $DB->get_record('course', array('id' => $this->courseid));
        }
        $this->modinfo = get_fast_modinfo($course);

                $this->top_element = grade_category::fetch_course_tree($courseid, true);

                if (!empty($collapsed)) {
            grade_tree::category_collapse($this->top_element, $collapsed);
        }

                if (!empty($nooutcomes)) {
            grade_tree::no_outcomes($this->top_element);
        }

                if ($category_grade_last) {
            grade_tree::category_grade_last($this->top_element);
        }

        if ($fillers) {
                        grade_tree::inject_fillers($this->top_element, 0);
                        grade_tree::inject_colspans($this->top_element);
        }

        grade_tree::fill_levels($this->levels, $this->top_element, 0);

    }

    
    public function grade_tree($courseid, $fillers=true, $category_grade_last=false,
                               $collapsed=null, $nooutcomes=false) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($courseid, $fillers, $category_grade_last, $collapsed, $nooutcomes);
    }

    
    public function category_collapse(&$element, $collapsed) {
        if ($element['type'] != 'category') {
            return;
        }
        if (empty($element['children']) or count($element['children']) < 2) {
            return;
        }

        if (in_array($element['object']->id, $collapsed['aggregatesonly'])) {
            $category_item = reset($element['children']);             $element['children'] = array(key($element['children'])=>$category_item);

        } else {
            if (in_array($element['object']->id, $collapsed['gradesonly'])) {                 reset($element['children']);
                $first_key = key($element['children']);
                unset($element['children'][$first_key]);
            }
            foreach ($element['children'] as $sortorder=>$child) {                 grade_tree::category_collapse($element['children'][$sortorder], $collapsed);
            }
        }
    }

    
    public function no_outcomes(&$element) {
        if ($element['type'] != 'category') {
            return;
        }
        foreach ($element['children'] as $sortorder=>$child) {
            if ($element['children'][$sortorder]['type'] == 'item'
              and $element['children'][$sortorder]['object']->is_outcome_item()) {
                unset($element['children'][$sortorder]);

            } else if ($element['children'][$sortorder]['type'] == 'category') {
                grade_tree::no_outcomes($element['children'][$sortorder]);
            }
        }
    }

    
    public function category_grade_last(&$element) {
        if (empty($element['children'])) {
            return;
        }
        if (count($element['children']) < 2) {
            return;
        }
        $first_item = reset($element['children']);
        if ($first_item['type'] == 'categoryitem' or $first_item['type'] == 'courseitem') {
                        $order = key($element['children']);
            unset($element['children'][$order]);
            $element['children'][$order] =& $first_item;
        }
        foreach ($element['children'] as $sortorder => $child) {
            grade_tree::category_grade_last($element['children'][$sortorder]);
        }
    }

    
    public function fill_levels(&$levels, &$element, $depth) {
        if (!array_key_exists($depth, $levels)) {
            $levels[$depth] = array();
        }

                if ($element['type'] == 'category') {
            $element['eid'] = 'cg'.$element['object']->id;
        } else if (in_array($element['type'], array('item', 'courseitem', 'categoryitem'))) {
            $element['eid'] = 'ig'.$element['object']->id;
            $this->items[$element['object']->id] =& $element['object'];
        }

        $levels[$depth][] =& $element;
        $depth++;
        if (empty($element['children'])) {
            return;
        }
        $prev = 0;
        foreach ($element['children'] as $sortorder=>$child) {
            grade_tree::fill_levels($levels, $element['children'][$sortorder], $depth);
            $element['children'][$sortorder]['prev'] = $prev;
            $element['children'][$sortorder]['next'] = 0;
            if ($prev) {
                $element['children'][$prev]['next'] = $sortorder;
            }
            $prev = $sortorder;
        }
    }

    
    public static function can_output_item($element) {
        $canoutput = true;

        if ($element['type'] === 'category') {
            $object = $element['object'];
            $category = grade_category::fetch(array('id' => $object->id));
                        if ($category->get_grade_item()->gradetype != GRADE_TYPE_NONE) {
                return true;
            }

                        if (empty($element['children'])) {
                return false;
            }

            $canoutput = false;
                        foreach ($element['children'] as $child) {
                $canoutput = self::can_output_item($child);
                if ($canoutput) {
                    break;
                }
            }
        }

        return $canoutput;
    }

    
    public function inject_fillers(&$element, $depth) {
        $depth++;

        if (empty($element['children'])) {
            return $depth;
        }
        $chdepths = array();
        $chids = array_keys($element['children']);
        $last_child  = end($chids);
        $first_child = reset($chids);

        foreach ($chids as $chid) {
            $chdepths[$chid] = grade_tree::inject_fillers($element['children'][$chid], $depth);
        }
        arsort($chdepths);

        $maxdepth = reset($chdepths);
        foreach ($chdepths as $chid=>$chd) {
            if ($chd == $maxdepth) {
                continue;
            }
            if (!self::can_output_item($element['children'][$chid])) {
                continue;
            }
            for ($i=0; $i < $maxdepth-$chd; $i++) {
                if ($chid == $first_child) {
                    $type = 'fillerfirst';
                } else if ($chid == $last_child) {
                    $type = 'fillerlast';
                } else {
                    $type = 'filler';
                }
                $oldchild =& $element['children'][$chid];
                $element['children'][$chid] = array('object'=>'filler', 'type'=>$type,
                                                    'eid'=>'', 'depth'=>$element['object']->depth,
                                                    'children'=>array($oldchild));
            }
        }

        return $maxdepth;
    }

    
    public function inject_colspans(&$element) {
        if (empty($element['children'])) {
            return 1;
        }
        $count = 0;
        foreach ($element['children'] as $key=>$child) {
            if (!self::can_output_item($child)) {
                continue;
            }
            $count += grade_tree::inject_colspans($element['children'][$key]);
        }
        $element['colspan'] = $count;
        return $count;
    }

    
    public function locate_element($eid) {
                if (strpos($eid, 'n') === 0) {
            if (!preg_match('/n(\d+)u(\d+)/', $eid, $matches)) {
                return null;
            }

            $itemid = $matches[1];
            $userid = $matches[2];

                        if (!$item_el = $this->locate_element('ig'.$itemid)) {
                return null;
            }

                        $grade = new grade_grade(array('itemid'=>$itemid, 'userid'=>$userid));

            $grade->grade_item =& $item_el['object'];             return array('eid'=>'n'.$itemid.'u'.$userid,'object'=>$grade, 'type'=>'grade');

        } else if (strpos($eid, 'g') === 0) {
            $id = (int) substr($eid, 1);
            if (!$grade = grade_grade::fetch(array('id'=>$id))) {
                return null;
            }
                        if (!$item_el = $this->locate_element('ig'.$grade->itemid)) {
                return null;
            }
            $grade->grade_item =& $item_el['object'];             return array('eid'=>'g'.$id,'object'=>$grade, 'type'=>'grade');
        }

                foreach ($this->levels as $row) {
            foreach ($row as $element) {
                if ($element['type'] == 'filler') {
                    continue;
                }
                if ($element['eid'] == $eid) {
                    return $element;
                }
            }
        }

        return null;
    }

    
    public function exporttoxml($root=null, $tabs="\t") {
        $xml = null;
        $first = false;
        if (is_null($root)) {
            $root = $this->top_element;
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
            $xml .= "<gradetree>\n";
            $first = true;
        }

        $type = 'undefined';
        if (strpos($root['object']->table, 'grade_categories') !== false) {
            $type = 'category';
        } else if (strpos($root['object']->table, 'grade_items') !== false) {
            $type = 'item';
        } else if (strpos($root['object']->table, 'grade_outcomes') !== false) {
            $type = 'outcome';
        }

        $xml .= "$tabs<element type=\"$type\">\n";
        foreach ($root['object'] as $var => $value) {
            if (!is_object($value) && !is_array($value) && !empty($value)) {
                $xml .= "$tabs\t<$var>$value</$var>\n";
            }
        }

        if (!empty($root['children'])) {
            $xml .= "$tabs\t<children>\n";
            foreach ($root['children'] as $sortorder => $child) {
                $xml .= $this->exportToXML($child, $tabs."\t\t");
            }
            $xml .= "$tabs\t</children>\n";
        }

        $xml .= "$tabs</element>\n";

        if ($first) {
            $xml .= "</gradetree>";
        }

        return $xml;
    }

    
    public function exporttojson($root=null, $tabs="\t") {
        $json = null;
        $first = false;
        if (is_null($root)) {
            $root = $this->top_element;
            $first = true;
        }

        $name = '';


        if (strpos($root['object']->table, 'grade_categories') !== false) {
            $name = $root['object']->fullname;
            if ($name == '?') {
                $name = $root['object']->get_name();
            }
        } else if (strpos($root['object']->table, 'grade_items') !== false) {
            $name = $root['object']->itemname;
        } else if (strpos($root['object']->table, 'grade_outcomes') !== false) {
            $name = $root['object']->itemname;
        }

        $json .= "$tabs {\n";
        $json .= "$tabs\t \"type\": \"{$root['type']}\",\n";
        $json .= "$tabs\t \"name\": \"$name\",\n";

        foreach ($root['object'] as $var => $value) {
            if (!is_object($value) && !is_array($value) && !empty($value)) {
                $json .= "$tabs\t \"$var\": \"$value\",\n";
            }
        }

        $json = substr($json, 0, strrpos($json, ','));

        if (!empty($root['children'])) {
            $json .= ",\n$tabs\t\"children\": [\n";
            foreach ($root['children'] as $sortorder => $child) {
                $json .= $this->exportToJSON($child, $tabs."\t\t");
            }
            $json = substr($json, 0, strrpos($json, ','));
            $json .= "\n$tabs\t]\n";
        }

        if ($first) {
            $json .= "\n}";
        } else {
            $json .= "\n$tabs},\n";
        }

        return $json;
    }

    
    public function get_levels() {
        return $this->levels;
    }

    
    public function get_items() {
        return $this->items;
    }

    
    public function get_item($itemid) {
        if (array_key_exists($itemid, $this->items)) {
            return $this->items[$itemid];
        } else {
            return false;
        }
    }
}



class grade_moodleset extends grade_structure {

    
    public $top_element;

    
    public $levels;

    
    public $items;

    
    public function __construct($courseid, $fillers=true, $category_grade_last=false,
                               $collapsed=null, $nooutcomes=false) {
        global $USER, $CFG, $COURSE, $DB;

        $this->courseid   = $courseid;
        $this->levels     = array();
        $this->context    = context_course::instance($courseid);

        if (!empty($COURSE->id) && $COURSE->id == $this->courseid) {
            $course = $COURSE;
        } else {
            $course = $DB->get_record('course', array('id' => $this->courseid));
        }
        $this->modinfo = get_fast_modinfo($course);

                $this->top_element = grade_category::fetch_course_tree($courseid, true);

                if (!empty($collapsed)) {
            grade_moodelset::category_collapse($this->top_element, $collapsed);
        }

                if (!empty($nooutcomes)) {
            grade_moodelset::no_outcomes($this->top_element);
        }

                if ($category_grade_last) {
            grade_moodelset::category_grade_last($this->top_element);
        }

        if ($fillers) {
                        grade_moodelset::inject_fillers($this->top_element, 0);
                        grade_moodelset::inject_colspans($this->top_element);
        }

        grade_moodleset::fill_levels($this->levels, $this->top_element, 0);

    }

    
    public function grade_moodleset($courseid, $fillers=true, $category_grade_last=false,
                               $collapsed=null, $nooutcomes=false) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($courseid, $fillers, $category_grade_last, $collapsed, $nooutcomes);
    }

    
    public function category_collapse(&$element, $collapsed) {
        if ($element['type'] != 'category') {
            return;
        }
        if (empty($element['children']) or count($element['children']) < 2) {
            return;
        }

        if (in_array($element['object']->id, $collapsed['aggregatesonly'])) {
            $category_item = reset($element['children']);             $element['children'] = array(key($element['children'])=>$category_item);

        } else {
            if (in_array($element['object']->id, $collapsed['gradesonly'])) {                 reset($element['children']);
                $first_key = key($element['children']);
                unset($element['children'][$first_key]);
            }
            foreach ($element['children'] as $sortorder=>$child) {                 grade_moodleset::category_collapse($element['children'][$sortorder], $collapsed);
            }
        }
    }

    
    public function no_outcomes(&$element) {
        if ($element['type'] != 'category') {
            return;
        }
        foreach ($element['children'] as $sortorder=>$child) {
            if ($element['children'][$sortorder]['type'] == 'item'
              and $element['children'][$sortorder]['object']->is_outcome_item()) {
                unset($element['children'][$sortorder]);

            } else if ($element['children'][$sortorder]['type'] == 'category') {
                grade_moodleset::no_outcomes($element['children'][$sortorder]);
            }
        }
    }

    
    public function category_grade_last(&$element) {
        if (empty($element['children'])) {
            return;
        }
        if (count($element['children']) < 2) {
            return;
        }
        $first_item = reset($element['children']);
        if ($first_item['type'] == 'categoryitem' or $first_item['type'] == 'courseitem') {
                        $order = key($element['children']);
            unset($element['children'][$order]);
            $element['children'][$order] =& $first_item;
        }
        foreach ($element['children'] as $sortorder => $child) {
            grade_moodleset::category_grade_last($element['children'][$sortorder]);
        }
    }

    
    public function fill_levels(&$levels, &$element, $depth) {
        if (!array_key_exists($depth, $levels)) {
            $levels[$depth] = array();
        }

                if ($element['type'] == 'category') {
            $element['eid'] = 'cg'.$element['object']->id;
        } else if (in_array($element['type'], array('item', 'courseitem', 'categoryitem'))) {
            $element['eid'] = 'ig'.$element['object']->id;
            $this->items[$element['object']->id] =& $element['object'];
        }

        $levels[$depth][] =& $element;
        $depth++;
        if (empty($element['children'])) {
            return;
        }
        $prev = 0;
        foreach ($element['children'] as $sortorder=>$child) {
            grade_moodleset::fill_levels($levels, $element['children'][$sortorder], $depth);
            $element['children'][$sortorder]['prev'] = $prev;
            $element['children'][$sortorder]['next'] = 0;
            if ($prev) {
                $element['children'][$prev]['next'] = $sortorder;
            }
            $prev = $sortorder;
        }
    }

    
    public static function can_output_item($element) {
        $canoutput = true;

        if ($element['type'] === 'category') {
            $object = $element['object'];
            $category = grade_category::fetch(array('id' => $object->id));
                        if ($category->get_grade_item()->gradetype != GRADE_TYPE_NONE) {
                return true;
            }

                        if (empty($element['children'])) {
                return false;
            }

            $canoutput = false;
                        foreach ($element['children'] as $child) {
                $canoutput = self::can_output_item($child);
                if ($canoutput) {
                    break;
                }
            }
        }

        return $canoutput;
    }

    
    public function inject_fillers(&$element, $depth) {
        $depth++;

        if (empty($element['children'])) {
            return $depth;
        }
        $chdepths = array();
        $chids = array_keys($element['children']);
        $last_child  = end($chids);
        $first_child = reset($chids);

        foreach ($chids as $chid) {
            $chdepths[$chid] = grade_moodleset::inject_fillers($element['children'][$chid], $depth);
        }
        arsort($chdepths);

        $maxdepth = reset($chdepths);
        foreach ($chdepths as $chid=>$chd) {
            if ($chd == $maxdepth) {
                continue;
            }
            if (!self::can_output_item($element['children'][$chid])) {
                continue;
            }
            for ($i=0; $i < $maxdepth-$chd; $i++) {
                if ($chid == $first_child) {
                    $type = 'fillerfirst';
                } else if ($chid == $last_child) {
                    $type = 'fillerlast';
                } else {
                    $type = 'filler';
                }
                $oldchild =& $element['children'][$chid];
                $element['children'][$chid] = array('object'=>'filler', 'type'=>$type,
                                                    'eid'=>'', 'depth'=>$element['object']->depth,
                                                    'children'=>array($oldchild));
            }
        }

        return $maxdepth;
    }

    
    public function inject_colspans(&$element) {
        if (empty($element['children'])) {
            return 1;
        }
        $count = 0;
        foreach ($element['children'] as $key=>$child) {
            if (!self::can_output_item($child)) {
                continue;
            }
            $count += grade_moodleset::inject_colspans($element['children'][$key]);
        }
        $element['colspan'] = $count;
        return $count;
    }

    
    public function locate_element($eid) {
                if (strpos($eid, 'n') === 0) {
            if (!preg_match('/n(\d+)u(\d+)/', $eid, $matches)) {
                return null;
            }

            $itemid = $matches[1];
            $userid = $matches[2];

                        if (!$item_el = $this->locate_element('ig'.$itemid)) {
                return null;
            }

                        $grade = new grade_grade(array('itemid'=>$itemid, 'userid'=>$userid));

            $grade->grade_item =& $item_el['object'];             return array('eid'=>'n'.$itemid.'u'.$userid,'object'=>$grade, 'type'=>'grade');

        } else if (strpos($eid, 'g') === 0) {
            $id = (int) substr($eid, 1);
            if (!$grade = grade_grade::fetch(array('id'=>$id))) {
                return null;
            }
                        if (!$item_el = $this->locate_element('ig'.$grade->itemid)) {
                return null;
            }
            $grade->grade_item =& $item_el['object'];             return array('eid'=>'g'.$id,'object'=>$grade, 'type'=>'grade');
        }

                foreach ($this->levels as $row) {
            foreach ($row as $element) {
                if ($element['type'] == 'filler') {
                    continue;
                }
                if ($element['eid'] == $eid) {
                    return $element;
                }
            }
        }

        return null;
    }

    
    public function exporttoxml($root=null, $tabs="\t") {
        $xml = null;
        $first = false;
        if (is_null($root)) {
            $root = $this->top_element;
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
            $xml .= "<gradetree>\n";
            $first = true;
        }

        $type = 'undefined';
        if (strpos($root['object']->table, 'grade_categories') !== false) {
            $type = 'category';
        } else if (strpos($root['object']->table, 'grade_items') !== false) {
            $type = 'item';
        } else if (strpos($root['object']->table, 'grade_outcomes') !== false) {
            $type = 'outcome';
        }

        $xml .= "$tabs<element type=\"$type\">\n";
        foreach ($root['object'] as $var => $value) {
            if (!is_object($value) && !is_array($value) && !empty($value)) {
                $xml .= "$tabs\t<$var>$value</$var>\n";
            }
        }

        if (!empty($root['children'])) {
            $xml .= "$tabs\t<children>\n";
            foreach ($root['children'] as $sortorder => $child) {
                $xml .= $this->exportToXML($child, $tabs."\t\t");
            }
            $xml .= "$tabs\t</children>\n";
        }

        $xml .= "$tabs</element>\n";

        if ($first) {
            $xml .= "</gradetree>";
        }

        return $xml;
    }

    
    public function exporttojson($root=null, $tabs="\t") {
        $json = null;
        $first = false;
        if (is_null($root)) {
            $root = $this->top_element;
            $first = true;
        }

        $name = '';


        if (strpos($root['object']->table, 'grade_categories') !== false) {
            $name = $root['object']->fullname;
            if ($name == '?') {
                $name = $root['object']->get_name();
            }
        } else if (strpos($root['object']->table, 'grade_items') !== false) {
            $name = $root['object']->itemname;
        } else if (strpos($root['object']->table, 'grade_outcomes') !== false) {
            $name = $root['object']->itemname;
        }

        $json .= "$tabs {\n";
        $json .= "$tabs\t \"type\": \"{$root['type']}\",\n";
        $json .= "$tabs\t \"name\": \"$name\",\n";

        foreach ($root['object'] as $var => $value) {
            if (!is_object($value) && !is_array($value) && !empty($value)) {
                $json .= "$tabs\t \"$var\": \"$value\",\n";
            }
        }

        $json = substr($json, 0, strrpos($json, ','));

        if (!empty($root['children'])) {
            $json .= ",\n$tabs\t\"children\": [\n";
            foreach ($root['children'] as $sortorder => $child) {
                $json .= $this->exportToJSON($child, $tabs."\t\t");
            }
            $json = substr($json, 0, strrpos($json, ','));
            $json .= "\n$tabs\t]\n";
        }

        if ($first) {
            $json .= "\n}";
        } else {
            $json .= "\n$tabs},\n";
        }

        return $json;
    }

    
    public function get_levels() {
        return $this->levels;
    }

    
    public function get_items() {
        return $this->items;
    }

    
    public function get_item($itemid) {
        if (array_key_exists($itemid, $this->items)) {
            return $this->items[$itemid];
        } else {
            return false;
        }
    }
}




function grade_button($type, $courseid, $object) {
    global $CFG, $OUTPUT;
    if (preg_match('/grade_(.*)/', get_class($object), $matches)) {
        $objectidstring = $matches[1] . 'id';
    } else {
        throw new coding_exception('grade_button() only accepts grade_* objects as third parameter!');
    }

    $strdelete = get_string('delete');
    $stredit   = get_string('edit');

    if ($type == 'delete') {
        $url = new moodle_url('index.php', array('id' => $courseid, $objectidstring => $object->id, 'action' => 'delete', 'sesskey' => sesskey()));
    } else if ($type == 'edit') {
        $url = new moodle_url('edit.php', array('courseid' => $courseid, 'id' => $object->id));
    }

    return $OUTPUT->action_icon($url, new pix_icon('t/'.$type, ${'str'.$type}, '', array('class' => 'iconsmall')));

}


function grade_extend_settings($plugininfo, $courseid) {
    global $PAGE;

    $gradenode = $PAGE->settingsnav->prepend(get_string('gradeadministration', 'grades'), null, navigation_node::TYPE_CONTAINER);

    $strings = array_shift($plugininfo);

    if ($reports = grade_helper::get_plugins_reports($courseid)) {
        foreach ($reports as $report) {
            $gradenode->add($report->string, $report->link, navigation_node::TYPE_SETTING, null, $report->id, new pix_icon('i/report', ''));
        }
    }

    if ($settings = grade_helper::get_info_manage_settings($courseid)) {
        $settingsnode = $gradenode->add($strings['settings'], null, navigation_node::TYPE_CONTAINER);
        foreach ($settings as $setting) {
            $settingsnode->add($setting->string, $setting->link, navigation_node::TYPE_SETTING, null, $setting->id, new pix_icon('i/settings', ''));
        }
    }

    if ($imports = grade_helper::get_plugins_import($courseid)) {
        $importnode = $gradenode->add($strings['import'], null, navigation_node::TYPE_CONTAINER);
        foreach ($imports as $import) {
            $importnode->add($import->string, $import->link, navigation_node::TYPE_SETTING, null, $import->id, new pix_icon('i/import', ''));
        }
    }

    if ($exports = grade_helper::get_plugins_export($courseid)) {
        $exportnode = $gradenode->add($strings['export'], null, navigation_node::TYPE_CONTAINER);
        foreach ($exports as $export) {
            $exportnode->add($export->string, $export->link, navigation_node::TYPE_SETTING, null, $export->id, new pix_icon('i/export', ''));
        }
    }

    if ($letters = grade_helper::get_info_letters($courseid)) {
        $letters = array_shift($letters);
        $gradenode->add($strings['letter'], $letters->link, navigation_node::TYPE_SETTING, null, $letters->id, new pix_icon('i/settings', ''));
    }

    if ($outcomes = grade_helper::get_info_outcomes($courseid)) {
        $outcomes = array_shift($outcomes);
        $gradenode->add($strings['outcome'], $outcomes->link, navigation_node::TYPE_SETTING, null, $outcomes->id, new pix_icon('i/outcomes', ''));
    }

    if ($scales = grade_helper::get_info_scales($courseid)) {
        $gradenode->add($strings['scale'], $scales->link, navigation_node::TYPE_SETTING, null, $scales->id, new pix_icon('i/scales', ''));
    }

    if ($gradenode->contains_active_node()) {
                        $PAGE->navbar->includesettingsbase = true;

                        if ($coursenode = $PAGE->settingsnav->get('courseadmin', navigation_node::TYPE_COURSE)){
            $coursenode->make_inactive();
            $coursenode->forceopen = false;
        }
    }
}


abstract class grade_helper {
    
    protected static $managesetting = null;
    
    protected static $gradereports = null;
    
    protected static $gradereportpreferences = null;
    
    protected static $scaleinfo = null;
    
    protected static $outcomeinfo = null;
    
    protected static $letterinfo = null;
    
    protected static $importplugins = null;
    
    protected static $exportplugins = null;
    
    protected static $pluginstrings = null;
    
    protected static $aggregationstrings = null;

    
    public static function get_plugin_strings() {
        if (self::$pluginstrings === null) {
            self::$pluginstrings = array(
                'report' => get_string('view'),
                'scale' => get_string('scales'),
                'outcome' => get_string('outcomes', 'grades'),
                'letter' => get_string('letters', 'grades'),
                'export' => get_string('export', 'grades'),
                'import' => get_string('import'),
                'settings' => get_string('edittree', 'grades')
            );
        }
        return self::$pluginstrings;
    }

    
    public static function get_aggregation_strings() {
        if (self::$aggregationstrings === null) {
            self::$aggregationstrings = array(
                GRADE_AGGREGATE_MEAN             => get_string('aggregatemean', 'grades'),
                GRADE_AGGREGATE_WEIGHTED_MEAN    => get_string('aggregateweightedmean', 'grades'),
                GRADE_AGGREGATE_WEIGHTED_MEAN2   => get_string('aggregateweightedmean2', 'grades'),
                GRADE_AGGREGATE_EXTRACREDIT_MEAN => get_string('aggregateextracreditmean', 'grades'),
                GRADE_AGGREGATE_MEDIAN           => get_string('aggregatemedian', 'grades'),
                GRADE_AGGREGATE_MIN              => get_string('aggregatemin', 'grades'),
                GRADE_AGGREGATE_MAX              => get_string('aggregatemax', 'grades'),
                GRADE_AGGREGATE_MODE             => get_string('aggregatemode', 'grades'),
                GRADE_AGGREGATE_SUM              => get_string('aggregatesum', 'grades')
            );
        }
        return self::$aggregationstrings;
    }

    
    public static function get_info_manage_settings($courseid) {
        
        if (self::$managesetting !== null) {
            return self::$managesetting;
        }
        $context = context_course::instance($courseid);
        self::$managesetting = array();

        if ($courseid != SITEID && has_capability('moodle/grade:manage', $context)) {
            self::$managesetting['moodlesetsetup'] = new grade_plugin_info('moodleset',
                new moodle_url('/grade/edit/moodleset/transfer.php', array('id' => $courseid)),
                get_string('moodlesetsetup', 'grades'));    // by YCJ
            self::$managesetting['gradebooksetup'] = new grade_plugin_info('setup',
                new moodle_url('/grade/edit/tree/index.php', array('id' => $courseid)),
                get_string('gradebooksetup', 'grades'));
            self::$managesetting['coursesettings'] = new grade_plugin_info('coursesettings',
                new moodle_url('/grade/edit/settings/index.php', array('id'=>$courseid)),
                get_string('coursegradesettings', 'grades'));
        }
        if (self::$gradereportpreferences === null) {
            self::get_plugins_reports($courseid);
        }
        if (self::$gradereportpreferences) {
            self::$managesetting = array_merge(self::$managesetting, self::$gradereportpreferences);
        }
        return self::$managesetting;
    }
    
    public static function get_plugins_reports($courseid) {
        global $SITE;

        if (self::$gradereports !== null) {
            return self::$gradereports;
        }
        $context = context_course::instance($courseid);
        $gradereports = array();
        $gradepreferences = array();
        foreach (core_component::get_plugin_list('gradereport') as $plugin => $plugindir) {
                        if ($courseid==$SITE->id && ($plugin=='grader' || $plugin=='user')) {
                continue;
            }

                        if (!has_capability('gradereport/'.$plugin.':view', $context)) {
                continue;
            }

                        if ($plugin === 'singleview' && !has_all_capabilities(array('moodle/grade:viewall',
                    'moodle/grade:edit'), $context)) {
                continue;
            }

            $pluginstr = get_string('pluginname', 'gradereport_'.$plugin);
            $url = new moodle_url('/grade/report/'.$plugin.'/index.php', array('id'=>$courseid));
            $gradereports[$plugin] = new grade_plugin_info($plugin, $url, $pluginstr);

                        if (file_exists($plugindir.'/preferences.php')) {
                $url = new moodle_url('/grade/report/'.$plugin.'/preferences.php', array('id'=>$courseid));
                $gradepreferences[$plugin] = new grade_plugin_info($plugin, $url,
                    get_string('preferences', 'grades') . ': ' . $pluginstr);
            }
        }
        if (count($gradereports) == 0) {
            $gradereports = false;
            $gradepreferences = false;
        } else if (count($gradepreferences) == 0) {
            $gradepreferences = false;
            asort($gradereports);
        } else {
            asort($gradereports);
            asort($gradepreferences);
        }
        self::$gradereports = $gradereports;
        self::$gradereportpreferences = $gradepreferences;
        return self::$gradereports;
    }

    
    public static function get_info_scales($courseid) {
        if (self::$scaleinfo !== null) {
            return self::$scaleinfo;
        }
        if (has_capability('moodle/course:managescales', context_course::instance($courseid))) {
            $url = new moodle_url('/grade/edit/scale/index.php', array('id'=>$courseid));
            self::$scaleinfo = new grade_plugin_info('scale', $url, get_string('view'));
        } else {
            self::$scaleinfo = false;
        }
        return self::$scaleinfo;
    }
    
    public static function get_info_outcomes($courseid) {
        global $CFG, $SITE;

        if (self::$outcomeinfo !== null) {
            return self::$outcomeinfo;
        }
        $context = context_course::instance($courseid);
        $canmanage = has_capability('moodle/grade:manage', $context);
        $canupdate = has_capability('moodle/course:update', $context);
        if (!empty($CFG->enableoutcomes) && ($canmanage || $canupdate)) {
            $outcomes = array();
            if ($canupdate) {
                if ($courseid!=$SITE->id) {
                    $url = new moodle_url('/grade/edit/outcome/course.php', array('id'=>$courseid));
                    $outcomes['course'] = new grade_plugin_info('course', $url, get_string('outcomescourse', 'grades'));
                }
                $url = new moodle_url('/grade/edit/outcome/index.php', array('id'=>$courseid));
                $outcomes['edit'] = new grade_plugin_info('edit', $url, get_string('editoutcomes', 'grades'));
                $url = new moodle_url('/grade/edit/outcome/import.php', array('courseid'=>$courseid));
                $outcomes['import'] = new grade_plugin_info('import', $url, get_string('importoutcomes', 'grades'));
            } else {
                if ($courseid!=$SITE->id) {
                    $url = new moodle_url('/grade/edit/outcome/course.php', array('id'=>$courseid));
                    $outcomes['edit'] = new grade_plugin_info('edit', $url, get_string('outcomescourse', 'grades'));
                }
            }
            self::$outcomeinfo = $outcomes;
        } else {
            self::$outcomeinfo = false;
        }
        return self::$outcomeinfo;
    }
    
    public static function get_info_letters($courseid) {
        global $SITE;
        if (self::$letterinfo !== null) {
            return self::$letterinfo;
        }
        $context = context_course::instance($courseid);
        $canmanage = has_capability('moodle/grade:manage', $context);
        $canmanageletters = has_capability('moodle/grade:manageletters', $context);
        if ($canmanage || $canmanageletters) {
                        if ($context->instanceid == $SITE->id) {
                $param = array('edit' => 1);
            } else {
                $param = array('edit' => 1,'id' => $context->id);
            }
            self::$letterinfo = array(
                'view' => new grade_plugin_info('view', new moodle_url('/grade/edit/letter/index.php', array('id'=>$context->id)), get_string('view')),
                'edit' => new grade_plugin_info('edit', new moodle_url('/grade/edit/letter/index.php', $param), get_string('edit'))
            );
        } else {
            self::$letterinfo = false;
        }
        return self::$letterinfo;
    }
    
    public static function get_plugins_import($courseid) {
        global $CFG;

        if (self::$importplugins !== null) {
            return self::$importplugins;
        }
        $importplugins = array();
        $context = context_course::instance($courseid);

        if (has_capability('moodle/grade:import', $context)) {
            foreach (core_component::get_plugin_list('gradeimport') as $plugin => $plugindir) {
                if (!has_capability('gradeimport/'.$plugin.':view', $context)) {
                    continue;
                }
                $pluginstr = get_string('pluginname', 'gradeimport_'.$plugin);
                $url = new moodle_url('/grade/import/'.$plugin.'/index.php', array('id'=>$courseid));
                $importplugins[$plugin] = new grade_plugin_info($plugin, $url, $pluginstr);
            }

                                    if ($CFG->gradepublishing && has_capability('gradeimport/xml:publish', $context)) {
                $url = new moodle_url('/grade/import/keymanager.php', array('id'=>$courseid));
                $importplugins['keymanager'] = new grade_plugin_info('keymanager', $url, get_string('keymanager', 'grades'));
            }
        }

        if (count($importplugins) > 0) {
            asort($importplugins);
            self::$importplugins = $importplugins;
        } else {
            self::$importplugins = false;
        }
        return self::$importplugins;
    }
    
    public static function get_plugins_export($courseid) {
        global $CFG;

        if (self::$exportplugins !== null) {
            return self::$exportplugins;
        }
        $context = context_course::instance($courseid);
        $exportplugins = array();
        $canpublishgrades = 0;
        if (has_capability('moodle/grade:export', $context)) {
            foreach (core_component::get_plugin_list('gradeexport') as $plugin => $plugindir) {
                if (!has_capability('gradeexport/'.$plugin.':view', $context)) {
                    continue;
                }
                                if (has_capability('gradeexport/'.$plugin.':publish', $context)) {
                    $canpublishgrades++;
                }

                $pluginstr = get_string('pluginname', 'gradeexport_'.$plugin);
                $url = new moodle_url('/grade/export/'.$plugin.'/index.php', array('id'=>$courseid));
                $exportplugins[$plugin] = new grade_plugin_info($plugin, $url, $pluginstr);
            }

                        if ($CFG->gradepublishing && $canpublishgrades != 0) {
                $url = new moodle_url('/grade/export/keymanager.php', array('id'=>$courseid));
                $exportplugins['keymanager'] = new grade_plugin_info('keymanager', $url, get_string('keymanager', 'grades'));
            }
        }
        if (count($exportplugins) > 0) {
            asort($exportplugins);
            self::$exportplugins = $exportplugins;
        } else {
            self::$exportplugins = false;
        }
        return self::$exportplugins;
    }

    
    public static function get_user_field_value($user, $field) {
        if (!empty($field->customid)) {
            $fieldname = 'customfield_' . $field->shortname;
            if (!empty($user->{$fieldname}) || is_numeric($user->{$fieldname})) {
                $fieldvalue = $user->{$fieldname};
            } else {
                $fieldvalue = $field->default;
            }
        } else {
            $fieldvalue = $user->{$field->shortname};
        }
        return $fieldvalue;
    }

    
    public static function get_user_profile_fields($courseid, $includecustomfields = false) {
        global $CFG, $DB;

                $hiddenfields = array_map('trim', explode(',', $CFG->hiddenuserfields));
        $context = context_course::instance($courseid);
        $canseehiddenfields = has_capability('moodle/course:viewhiddenuserfields', $context);
        if ($canseehiddenfields) {
            $hiddenfields = array();
        }

        $fields = array();
        require_once($CFG->dirroot.'/user/lib.php');                        require_once($CFG->dirroot.'/user/profile/lib.php');                $userdefaultfields = user_get_default_fields();

                $userprofilefields = array_map('trim', explode(',', $CFG->grade_export_userprofilefields));
        if (!empty($userprofilefields)) {
            foreach ($userprofilefields as $field) {
                $field = trim($field);
                if (in_array($field, $hiddenfields) || !in_array($field, $userdefaultfields)) {
                    continue;
                }
                $obj = new stdClass();
                $obj->customid  = 0;
                $obj->shortname = $field;
                $obj->fullname  = get_string($field);
                $fields[] = $obj;
            }
        }

                $customprofilefields = array_map('trim', explode(',', $CFG->grade_export_customprofilefields));
        if ($includecustomfields && !empty($customprofilefields)) {
            list($wherefields, $whereparams) = $DB->get_in_or_equal($customprofilefields);
            $customfields = $DB->get_records_sql("SELECT f.*
                                                    FROM {user_info_field} f
                                                    JOIN {user_info_category} c ON f.categoryid=c.id
                                                    WHERE f.shortname $wherefields
                                                    ORDER BY c.sortorder ASC, f.sortorder ASC", $whereparams);

            foreach ($customfields as $field) {
                                if (!in_array($field->shortname, $customprofilefields)) {
                    continue;
                } else if (in_array($field->shortname, $hiddenfields)) {
                    continue;
                } else if ($field->visible != PROFILE_VISIBLE_ALL && !$canseehiddenfields) {
                    continue;
                }

                $obj = new stdClass();
                $obj->customid  = $field->id;
                $obj->shortname = $field->shortname;
                $obj->fullname  = format_string($field->name);
                $obj->datatype  = $field->datatype;
                $obj->default   = $field->defaultdata;
                $fields[] = $obj;
            }
        }

        return $fields;
    }

    
    public static function fetch_all_natural_weights_for_course($courseid) {
        global $DB;
        $result = array();

        $records = $DB->get_records('grade_items', array('courseid'=>$courseid), 'id', 'id, aggregationcoef2');
        foreach ($records as $record) {
            $result[$record->id] = $record->aggregationcoef2;
        }
        return $result;
    }
}

