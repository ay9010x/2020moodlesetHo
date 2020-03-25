<?php



defined('MOODLE_INTERNAL') || die();

define('ASSIGN_SUBMISSION_STATUS_NEW', 'new');
define('ASSIGN_SUBMISSION_STATUS_REOPENED', 'reopened');
define('ASSIGN_SUBMISSION_STATUS_DRAFT', 'draft');
define('ASSIGN_SUBMISSION_STATUS_SUBMITTED', 'submitted');

define('ASSIGN_FILTER_SUBMITTED', 'submitted');
define('ASSIGN_FILTER_NOT_SUBMITTED', 'notsubmitted');
define('ASSIGN_FILTER_SINGLE_USER', 'singleuser');
define('ASSIGN_FILTER_REQUIRE_GRADING', 'require_grading');

define('ASSIGN_MARKER_FILTER_NO_MARKER', -1);

define('ASSIGN_ATTEMPT_REOPEN_METHOD_NONE', 'none');
define('ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL', 'manual');
define('ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS', 'untilpass');

define('ASSIGN_UNLIMITED_ATTEMPTS', -1);

define('ASSIGN_GRADING_STATUS_GRADED', 'graded');
define('ASSIGN_GRADING_STATUS_NOT_GRADED', 'notgraded');

define('ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED', 'notmarked');
define('ASSIGN_MARKING_WORKFLOW_STATE_INMARKING', 'inmarking');
define('ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW', 'readyforreview');
define('ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW', 'inreview');
define('ASSIGN_MARKING_WORKFLOW_STATE_READYFORRELEASE', 'readyforrelease');
define('ASSIGN_MARKING_WORKFLOW_STATE_RELEASED', 'released');

define('ASSIGN_INTROATTACHMENT_FILEAREA', 'introattachment');

require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/grading/lib.php');
require_once($CFG->dirroot . '/mod/assign/feedbackplugin.php');
require_once($CFG->dirroot . '/mod/assign/submissionplugin.php');
require_once($CFG->dirroot . '/mod/assign/renderable.php');
require_once($CFG->dirroot . '/mod/assign/gradingtable.php');
require_once($CFG->libdir . '/eventslib.php');
require_once($CFG->libdir . '/portfolio/caller.php');

use \mod_assign\output\grading_app;


class assign {

    
    private $instance;

    
    private $gradeitem;

    
    private $context;

    
    private $course;

    
    private $adminconfig;

    
    private $output;

    
    private $coursemodule;

    
    private $cache;

    
    private $submissionplugins;

    
    private $feedbackplugins;

    
    private $returnaction = 'view';

    
    private $returnparams = array();

    
    private static $modulename = null;

    
    private static $modulenameplural = null;

    
    private $markingworkflowstates = null;

    
    private $showonlyactiveenrol = null;

    
    private $useridlistid = null;

    
    private $participants = array();

    
    private $usersubmissiongroups = array();

    
    private $usergroups = array();

    
    private $sharedgroupmembers = array();

    
    public function __construct($coursemodulecontext, $coursemodule, $course) {
        global $SESSION;

        $this->context = $coursemodulecontext;
        $this->course = $course;

                $this->coursemodule = cm_info::create($coursemodule);

                $this->cache = array();

        $this->submissionplugins = $this->load_plugins('assignsubmission');
        $this->feedbackplugins = $this->load_plugins('assignfeedback');

                $this->useridlistid = clean_param(uniqid('', true), PARAM_ALPHANUM);

        if (!isset($SESSION->mod_assign_useridlist)) {
            $SESSION->mod_assign_useridlist = [];
        }
    }

    
    public function register_return_link($action, $params) {
        global $PAGE;
        $params['action'] = $action;
        $cm = $this->get_course_module();
        if ($cm) {
            $currenturl = new moodle_url('/mod/assign/view.php', array('id' => $cm->id));
        } else {
            $currenturl = new moodle_url('/mod/assign/index.php', array('id' => $this->get_course()->id));
        }

        $currenturl->params($params);
        $PAGE->set_url($currenturl);
    }

    
    public function get_return_action() {
        global $PAGE;

                if (!WS_SERVER) {
            $params = $PAGE->url->params();
        }

        if (!empty($params['action'])) {
            return $params['action'];
        }
        return '';
    }

    
    public function show_intro() {
        if ($this->get_instance()->alwaysshowdescription ||
                time() > $this->get_instance()->allowsubmissionsfromdate) {
            return true;
        }
        return false;
    }

    
    public function get_return_params() {
        global $PAGE;

        $params = $PAGE->url->params();
        unset($params['id']);
        unset($params['action']);
        return $params;
    }

    
    public function set_instance(stdClass $data) {
        $this->instance = $data;
    }

    
    public function set_context(context $context) {
        $this->context = $context;
    }

    
    public function set_course(stdClass $course) {
        $this->course = $course;
    }

    
    public function get_feedback_plugins() {
        return $this->feedbackplugins;
    }

    
    public function get_submission_plugins() {
        return $this->submissionplugins;
    }

    
    public function is_blind_marking() {
        return $this->get_instance()->blindmarking && !$this->get_instance()->revealidentities;
    }

    
    public function has_submissions_or_grades() {
        $allgrades = $this->count_grades();
        $allsubmissions = $this->count_submissions();
        if (($allgrades == 0) && ($allsubmissions == 0)) {
            return false;
        }
        return true;
    }

    
    public function get_plugin_by_type($subtype, $type) {
        $shortsubtype = substr($subtype, strlen('assign'));
        $name = $shortsubtype . 'plugins';
        if ($name != 'feedbackplugins' && $name != 'submissionplugins') {
            return null;
        }
        $pluginlist = $this->$name;
        foreach ($pluginlist as $plugin) {
            if ($plugin->get_type() == $type) {
                return $plugin;
            }
        }
        return null;
    }

    
    public function get_feedback_plugin_by_type($type) {
        return $this->get_plugin_by_type('assignfeedback', $type);
    }

    
    public function get_submission_plugin_by_type($type) {
        return $this->get_plugin_by_type('assignsubmission', $type);
    }

    
    protected function load_plugins($subtype) {
        global $CFG;
        $result = array();

        $names = core_component::get_plugin_list($subtype);

        foreach ($names as $name => $path) {
            if (file_exists($path . '/locallib.php')) {
                require_once($path . '/locallib.php');

                $shortsubtype = substr($subtype, strlen('assign'));
                $pluginclass = 'assign_' . $shortsubtype . '_' . $name;

                $plugin = new $pluginclass($this, $name);

                if ($plugin instanceof assign_plugin) {
                    $idx = $plugin->get_sort_order();
                    while (array_key_exists($idx, $result)) {
                        $idx +=1;
                    }
                    $result[$idx] = $plugin;
                }
            }
        }
        ksort($result);
        return $result;
    }

    
    public function view($action='', $args = array()) {
        global $PAGE;

        $o = '';
        $mform = null;
        $notices = array();
        $nextpageparams = array();

        if (!empty($this->get_course_module()->id)) {
            $nextpageparams['id'] = $this->get_course_module()->id;
        }

                if ($action == 'savesubmission') {
            $action = 'editsubmission';
            if ($this->process_save_submission($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'view';
            }
        } else if ($action == 'editprevioussubmission') {
            $action = 'editsubmission';
            if ($this->process_copy_previous_attempt($notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'editsubmission';
            }
        } else if ($action == 'lock') {
            $this->process_lock_submission();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'addattempt') {
            $this->process_add_attempt(required_param('userid', PARAM_INT));
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'reverttodraft') {
            $this->process_revert_to_draft();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'unlock') {
            $this->process_unlock_submission();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'setbatchmarkingworkflowstate') {
            $this->process_set_batch_marking_workflow_state();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'setbatchmarkingallocation') {
            $this->process_set_batch_marking_allocation();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'confirmsubmit') {
            $action = 'submit';
            if ($this->process_submit_for_grading($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'view';
            } else if ($notices) {
                $action = 'viewsubmitforgradingerror';
            }
        } else if ($action == 'submitotherforgrading') {
            if ($this->process_submit_other_for_grading($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            } else {
                $action = 'viewsubmitforgradingerror';
            }
        } else if ($action == 'gradingbatchoperation') {
            $action = $this->process_grading_batch_operation($mform);
            if ($action == 'grading') {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'submitgrade') {
            if (optional_param('saveandshownext', null, PARAM_RAW)) {
                                $action = 'grade';
                if ($this->process_save_grade($mform)) {
                    $action = 'redirect';
                    $nextpageparams['action'] = 'grade';
                    $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) + 1;
                    $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
                }
            } else if (optional_param('nosaveandprevious', null, PARAM_RAW)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grade';
                $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) - 1;
                $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
            } else if (optional_param('nosaveandnext', null, PARAM_RAW)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grade';
                $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) + 1;
                $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
            } else if (optional_param('savegrade', null, PARAM_RAW)) {
                                $action = 'grade';
                if ($this->process_save_grade($mform)) {
                    $action = 'redirect';
                    $nextpageparams['action'] = 'savegradingresult';
                }
            } else {
                                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'quickgrade') {
            $message = $this->process_save_quick_grades();
            $action = 'quickgradingresult';
        } else if ($action == 'saveoptions') {
            $this->process_save_grading_options();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'saveextension') {
            $action = 'grantextension';
            if ($this->process_save_extension($mform)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'revealidentitiesconfirm') {
            $this->process_reveal_identities();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        }

        $returnparams = array('rownum'=>optional_param('rownum', 0, PARAM_INT),
                              'useridlistid' => optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM));
        $this->register_return_link($action, $returnparams);

                if (!empty($action)) {
            $PAGE->set_pagetype('mod-assign-' . $action);
        }
                if ($action == 'redirect') {
            $nextpageurl = new moodle_url('/mod/assign/view.php', $nextpageparams);
            redirect($nextpageurl);
            return;
        } else if ($action == 'savegradingresult') {
            $message = get_string('gradingchangessaved', 'assign');
            $o .= $this->view_savegrading_result($message);
        } else if ($action == 'quickgradingresult') {
            $mform = null;
            $o .= $this->view_quickgrading_result($message);
        } else if ($action == 'gradingpanel') {
            $o .= $this->view_single_grading_panel($args);
        } else if ($action == 'grade') {
            $o .= $this->view_single_grade_page($mform);
        } else if ($action == 'viewpluginassignfeedback') {
            $o .= $this->view_plugin_content('assignfeedback');
        } else if ($action == 'viewpluginassignsubmission') {
            $o .= $this->view_plugin_content('assignsubmission');
        } else if ($action == 'editsubmission') {
            $o .= $this->view_edit_submission_page($mform, $notices);
        } else if ($action == 'grader') {
            $o .= $this->view_grader();
        } else if ($action == 'grading') {
            $o .= $this->view_grading_page();
        } else if ($action == 'downloadall') {
            $o .= $this->download_submissions();
        } else if ($action == 'submit') {
            $o .= $this->check_submit_for_grading($mform);
        } else if ($action == 'grantextension') {
            $o .= $this->view_grant_extension($mform);
        } else if ($action == 'revealidentities') {
            $o .= $this->view_reveal_identities_confirm($mform);
        } else if ($action == 'plugingradingbatchoperation') {
            $o .= $this->view_plugin_grading_batch_operation($mform);
        } else if ($action == 'viewpluginpage') {
             $o .= $this->view_plugin_page();
        } else if ($action == 'viewcourseindex') {
             $o .= $this->view_course_index();
        } else if ($action == 'viewbatchsetmarkingworkflowstate') {
             $o .= $this->view_batch_set_workflow_state($mform);
        } else if ($action == 'viewbatchmarkingallocation') {
            $o .= $this->view_batch_markingallocation($mform);
        } else if ($action == 'viewsubmitforgradingerror') {
            $o .= $this->view_error_page(get_string('submitforgrading', 'assign'), $notices);
        } else {
            $o .= $this->view_submission_page();
        }

        return $o;
    }

    
    public function add_instance(stdClass $formdata, $callplugins) {
        global $DB;
        $adminconfig = $this->get_admin_config();

        $err = '';

                $update = new stdClass();
        $update->name = $formdata->name;
        $update->timemodified = time();
        $update->timecreated = time();
        $update->course = $formdata->course;
        $update->courseid = $formdata->course;
        $update->intro = $formdata->intro;
        $update->introformat = $formdata->introformat;
        $update->alwaysshowdescription = !empty($formdata->alwaysshowdescription);
        $update->submissiondrafts = $formdata->submissiondrafts;
        $update->requiresubmissionstatement = $formdata->requiresubmissionstatement;
        $update->sendnotifications = $formdata->sendnotifications;
        $update->sendlatenotifications = $formdata->sendlatenotifications;
        $update->sendstudentnotifications = $adminconfig->sendstudentnotifications;
        if (isset($formdata->sendstudentnotifications)) {
            $update->sendstudentnotifications = $formdata->sendstudentnotifications;
        }
        $update->duedate = $formdata->duedate;
        $update->cutoffdate = $formdata->cutoffdate;
        $update->allowsubmissionsfromdate = $formdata->allowsubmissionsfromdate;
        $update->grade = $formdata->grade;
        $update->completionsubmit = !empty($formdata->completionsubmit);
        $update->teamsubmission = $formdata->teamsubmission;
        $update->requireallteammemberssubmit = $formdata->requireallteammemberssubmit;
        if (isset($formdata->teamsubmissiongroupingid)) {
            $update->teamsubmissiongroupingid = $formdata->teamsubmissiongroupingid;
        }
        $update->blindmarking = $formdata->blindmarking;
        $update->attemptreopenmethod = ASSIGN_ATTEMPT_REOPEN_METHOD_NONE;
        if (!empty($formdata->attemptreopenmethod)) {
            $update->attemptreopenmethod = $formdata->attemptreopenmethod;
        }
        if (!empty($formdata->maxattempts)) {
            $update->maxattempts = $formdata->maxattempts;
        }
        if (isset($formdata->preventsubmissionnotingroup)) {
            $update->preventsubmissionnotingroup = $formdata->preventsubmissionnotingroup;
        }
        $update->markingworkflow = $formdata->markingworkflow;
        $update->markingallocation = $formdata->markingallocation;
        if (empty($update->markingworkflow)) {             $update->markingallocation = 0;
        }

        $returnid = $DB->insert_record('assign', $update);
        $this->instance = $DB->get_record('assign', array('id'=>$returnid), '*', MUST_EXIST);
                $this->course = $DB->get_record('course', array('id'=>$formdata->course), '*', MUST_EXIST);

        $this->save_intro_draft_files($formdata);

        if ($callplugins) {
                        foreach ($this->submissionplugins as $plugin) {
                if (!$this->update_plugin_instance($plugin, $formdata)) {
                    print_error($plugin->get_error());
                    return false;
                }
            }
            foreach ($this->feedbackplugins as $plugin) {
                if (!$this->update_plugin_instance($plugin, $formdata)) {
                    print_error($plugin->get_error());
                    return false;
                }
            }

                                    $this->update_calendar($formdata->coursemodule);
            $this->update_gradebook(false, $formdata->coursemodule);

        }

        $update = new stdClass();
        $update->id = $this->get_instance()->id;
        $update->nosubmissions = (!$this->is_any_submission_plugin_enabled()) ? 1: 0;
        $DB->update_record('assign', $update);

        return $returnid;
    }

    
    protected function delete_grades() {
        global $CFG;

        $result = grade_update('mod/assign',
                               $this->get_course()->id,
                               'mod',
                               'assign',
                               $this->get_instance()->id,
                               0,
                               null,
                               array('deleted'=>1));
        return $result == GRADE_UPDATE_OK;
    }

    
    public function delete_instance() {
        global $DB;
        $result = true;

        foreach ($this->submissionplugins as $plugin) {
            if (!$plugin->delete_instance()) {
                print_error($plugin->get_error());
                $result = false;
            }
        }
        foreach ($this->feedbackplugins as $plugin) {
            if (!$plugin->delete_instance()) {
                print_error($plugin->get_error());
                $result = false;
            }
        }

                $fs = get_file_storage();
        if (! $fs->delete_area_files($this->context->id) ) {
            $result = false;
        }

                $DB->delete_records('assign_submission', array('assignment' => $this->get_instance()->id));
        $DB->delete_records('assign_grades', array('assignment' => $this->get_instance()->id));
        $DB->delete_records('assign_plugin_config', array('assignment' => $this->get_instance()->id));
        $DB->delete_records('assign_user_flags', array('assignment' => $this->get_instance()->id));
        $DB->delete_records('assign_user_mapping', array('assignment' => $this->get_instance()->id));

                if (! $this->delete_grades()) {
            $result = false;
        }

                $DB->delete_records('assign', array('id'=>$this->get_instance()->id));

        return $result;
    }

    
    public function reset_userdata($data) {
        global $CFG, $DB;

        $componentstr = get_string('modulenameplural', 'assign');
        $status = array();

        $fs = get_file_storage();
        if (!empty($data->reset_assign_submissions)) {
                        foreach ($this->submissionplugins as $plugin) {
                $fileareas = array();
                $plugincomponent = $plugin->get_subtype() . '_' . $plugin->get_type();
                $fileareas = $plugin->get_file_areas();
                foreach ($fileareas as $filearea => $notused) {
                    $fs->delete_area_files($this->context->id, $plugincomponent, $filearea);
                }

                if (!$plugin->delete_instance()) {
                    $status[] = array('component'=>$componentstr,
                                      'item'=>get_string('deleteallsubmissions', 'assign'),
                                      'error'=>$plugin->get_error());
                }
            }

            foreach ($this->feedbackplugins as $plugin) {
                $fileareas = array();
                $plugincomponent = $plugin->get_subtype() . '_' . $plugin->get_type();
                $fileareas = $plugin->get_file_areas();
                foreach ($fileareas as $filearea => $notused) {
                    $fs->delete_area_files($this->context->id, $plugincomponent, $filearea);
                }

                if (!$plugin->delete_instance()) {
                    $status[] = array('component'=>$componentstr,
                                      'item'=>get_string('deleteallsubmissions', 'assign'),
                                      'error'=>$plugin->get_error());
                }
            }

            $assignids = $DB->get_records('assign', array('course' => $data->courseid), '', 'id');
            list($sql, $params) = $DB->get_in_or_equal(array_keys($assignids));

            $DB->delete_records_select('assign_submission', "assignment $sql", $params);
            $DB->delete_records_select('assign_user_flags', "assignment $sql", $params);

            $status[] = array('component'=>$componentstr,
                              'item'=>get_string('deleteallsubmissions', 'assign'),
                              'error'=>false);

            if (!empty($data->reset_gradebook_grades)) {
                $DB->delete_records_select('assign_grades', "assignment $sql", $params);
                                require_once($CFG->dirroot.'/mod/assign/lib.php');
                assign_reset_gradebook($data->courseid);

                                if ($this->get_instance()->blindmarking && $this->get_instance()->revealidentities) {
                    $DB->set_field('assign', 'revealidentities', 0, array('id' => $this->get_instance()->id));
                }
            }
        }
                if ($data->timeshift) {
            shift_course_mod_dates('assign',
                                    array('duedate', 'allowsubmissionsfromdate', 'cutoffdate'),
                                    $data->timeshift,
                                    $data->courseid, $this->get_instance()->id);
            $status[] = array('component'=>$componentstr,
                              'item'=>get_string('datechanged'),
                              'error'=>false);
        }

        return $status;
    }

    
    protected function update_plugin_instance(assign_plugin $plugin, stdClass $formdata) {
        if ($plugin->is_visible()) {
            $enabledname = $plugin->get_subtype() . '_' . $plugin->get_type() . '_enabled';
            if (!empty($formdata->$enabledname)) {
                $plugin->enable();
                if (!$plugin->save_settings($formdata)) {
                    print_error($plugin->get_error());
                    return false;
                }
            } else {
                $plugin->disable();
            }
        }
        return true;
    }

    
    public function update_gradebook($reset, $coursemoduleid) {
        global $CFG;

        require_once($CFG->dirroot.'/mod/assign/lib.php');
        $assign = clone $this->get_instance();
        $assign->cmidnumber = $coursemoduleid;

                $assign->gradefeedbackenabled = $this->is_gradebook_feedback_enabled();

        $param = null;
        if ($reset) {
            $param = 'reset';
        }

        return assign_grade_item_update($assign, $param);
    }

    
    public function get_assign_perpage() {
        $perpage = (int) get_user_preferences('assign_perpage', 10);
        $adminconfig = $this->get_admin_config();
        $maxperpage = -1;
        if (isset($adminconfig->maxperpage)) {
            $maxperpage = $adminconfig->maxperpage;
        }
        if (isset($maxperpage) &&
            $maxperpage != -1 &&
            ($perpage == -1 || $perpage > $maxperpage)) {
            $perpage = $maxperpage;
        }
        return $perpage;
    }

    
    public function get_admin_config() {
        if ($this->adminconfig) {
            return $this->adminconfig;
        }
        $this->adminconfig = get_config('assign');
        return $this->adminconfig;
    }

    
    public function update_calendar($coursemoduleid) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/calendar/lib.php');

                $instance = $this->get_instance();

        $eventtype = 'due';

        if ($instance->duedate) {
            $event = new stdClass();

            $params = array('modulename' => 'assign', 'instance' => $instance->id, 'eventtype' => $eventtype);
            $event->id = $DB->get_field('event', 'id', $params);
            $event->name = $instance->name;
            $event->timestart = $instance->duedate;

                                    $intro = $instance->intro;
            if ($draftid = file_get_submitted_draft_itemid('introeditor')) {
                $intro = file_rewrite_urls_to_pluginfile($intro, $draftid);
            }

                                    $intro = strip_pluginfile_content($intro);
            if ($this->show_intro()) {
                $event->description = array(
                    'text' => $intro,
                    'format' => $instance->introformat
                );
            } else {
                $event->description = array(
                    'text' => '',
                    'format' => $instance->introformat
                );
            }

            if ($event->id) {
                $calendarevent = calendar_event::load($event->id);
                $calendarevent->update($event);
            } else {
                unset($event->id);
                $event->courseid    = $instance->course;
                $event->groupid     = 0;
                $event->userid      = 0;
                $event->modulename  = 'assign';
                $event->instance    = $instance->id;
                $event->eventtype   = $eventtype;
                $event->timeduration = 0;
                calendar_event::create($event);
            }
        } else {
            $DB->delete_records('event', array('modulename' => 'assign', 'instance' => $instance->id, 'eventtype' => $eventtype));
        }
    }


    
    public function update_instance($formdata) {
        global $DB;
        $adminconfig = $this->get_admin_config();

        $update = new stdClass();
        $update->id = $formdata->instance;
        $update->name = $formdata->name;
        $update->timemodified = time();
        $update->course = $formdata->course;
        $update->intro = $formdata->intro;
        $update->introformat = $formdata->introformat;
        $update->alwaysshowdescription = !empty($formdata->alwaysshowdescription);
        $update->submissiondrafts = $formdata->submissiondrafts;
        $update->requiresubmissionstatement = $formdata->requiresubmissionstatement;
        $update->sendnotifications = $formdata->sendnotifications;
        $update->sendlatenotifications = $formdata->sendlatenotifications;
        $update->sendstudentnotifications = $adminconfig->sendstudentnotifications;
        if (isset($formdata->sendstudentnotifications)) {
            $update->sendstudentnotifications = $formdata->sendstudentnotifications;
        }
        $update->duedate = $formdata->duedate;
        $update->cutoffdate = $formdata->cutoffdate;
        $update->allowsubmissionsfromdate = $formdata->allowsubmissionsfromdate;
        $update->grade = $formdata->grade;
        if (!empty($formdata->completionunlocked)) {
            $update->completionsubmit = !empty($formdata->completionsubmit);
        }
        $update->teamsubmission = $formdata->teamsubmission;
        $update->requireallteammemberssubmit = $formdata->requireallteammemberssubmit;
        if (isset($formdata->teamsubmissiongroupingid)) {
            $update->teamsubmissiongroupingid = $formdata->teamsubmissiongroupingid;
        }
        $update->blindmarking = $formdata->blindmarking;
        $update->attemptreopenmethod = ASSIGN_ATTEMPT_REOPEN_METHOD_NONE;
        if (!empty($formdata->attemptreopenmethod)) {
            $update->attemptreopenmethod = $formdata->attemptreopenmethod;
        }
        if (!empty($formdata->maxattempts)) {
            $update->maxattempts = $formdata->maxattempts;
        }
        if (isset($formdata->preventsubmissionnotingroup)) {
            $update->preventsubmissionnotingroup = $formdata->preventsubmissionnotingroup;
        }
        $update->markingworkflow = $formdata->markingworkflow;
        $update->markingallocation = $formdata->markingallocation;
        if (empty($update->markingworkflow)) {             $update->markingallocation = 0;
        }

        $result = $DB->update_record('assign', $update);
        $this->instance = $DB->get_record('assign', array('id'=>$update->id), '*', MUST_EXIST);

        $this->save_intro_draft_files($formdata);

        
                foreach ($this->submissionplugins as $plugin) {
            if (!$this->update_plugin_instance($plugin, $formdata)) {
                print_error($plugin->get_error());
                return false;
            }
        }
        foreach ($this->feedbackplugins as $plugin) {
            if (!$this->update_plugin_instance($plugin, $formdata)) {
                print_error($plugin->get_error());
                return false;
            }
        }

        $this->update_calendar($this->get_course_module()->id);
        $this->update_gradebook(false, $this->get_course_module()->id);

        $update = new stdClass();
        $update->id = $this->get_instance()->id;
        $update->nosubmissions = (!$this->is_any_submission_plugin_enabled()) ? 1: 0;
        $DB->update_record('assign', $update);

        return $result;
    }

    
    protected function save_intro_draft_files($formdata) {
        if (isset($formdata->introattachments)) {
            file_save_draft_area_files($formdata->introattachments, $this->get_context()->id,
                                       'mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA, 0);
        }
    }

    
    protected function add_plugin_grade_elements($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
        foreach ($this->feedbackplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $plugin->get_form_elements_for_user($grade, $mform, $data, $userid);
            }
        }
    }



    
    protected function add_plugin_settings(assign_plugin $plugin, MoodleQuickForm $mform, & $pluginsenabled) {
        global $CFG;
        if ($plugin->is_visible() && !$plugin->is_configurable() && $plugin->is_enabled()) {
            $name = $plugin->get_subtype() . '_' . $plugin->get_type() . '_enabled';
            $pluginsenabled[] = $mform->createElement('hidden', $name, 1);
            $mform->setType($name, PARAM_BOOL);
            $plugin->get_settings($mform);
        } else if ($plugin->is_visible() && $plugin->is_configurable()) {
            $name = $plugin->get_subtype() . '_' . $plugin->get_type() . '_enabled';
            $label = $plugin->get_name();
            $label .= ' ' . $this->get_renderer()->help_icon('enabled', $plugin->get_subtype() . '_' . $plugin->get_type());
            $pluginsenabled[] = $mform->createElement('checkbox', $name, '', $label);

            $default = get_config($plugin->get_subtype() . '_' . $plugin->get_type(), 'default');
            if ($plugin->get_config('enabled') !== false) {
                $default = $plugin->is_enabled();
            }
            $mform->setDefault($plugin->get_subtype() . '_' . $plugin->get_type() . '_enabled', $default);

            $plugin->get_settings($mform);

        }
    }

    
    public function add_all_plugin_settings(MoodleQuickForm $mform) {
        $mform->addElement('header', 'submissiontypes', get_string('submissiontypes', 'assign'));

        $submissionpluginsenabled = array();
        $group = $mform->addGroup(array(), 'submissionplugins', get_string('submissiontypes', 'assign'), array(' '), false);
        foreach ($this->submissionplugins as $plugin) {
            $this->add_plugin_settings($plugin, $mform, $submissionpluginsenabled);
        }
        $group->setElements($submissionpluginsenabled);

        $mform->addElement('header', 'feedbacktypes', get_string('feedbacktypes', 'assign'));
        $feedbackpluginsenabled = array();
        $group = $mform->addGroup(array(), 'feedbackplugins', get_string('feedbacktypes', 'assign'), array(' '), false);
        foreach ($this->feedbackplugins as $plugin) {
            $this->add_plugin_settings($plugin, $mform, $feedbackpluginsenabled);
        }
        $group->setElements($feedbackpluginsenabled);
        $mform->setExpanded('submissiontypes');
    }

    
    public function plugin_data_preprocessing(&$defaultvalues) {
        foreach ($this->submissionplugins as $plugin) {
            if ($plugin->is_visible()) {
                $plugin->data_preprocessing($defaultvalues);
            }
        }
        foreach ($this->feedbackplugins as $plugin) {
            if ($plugin->is_visible()) {
                $plugin->data_preprocessing($defaultvalues);
            }
        }
    }

    
    protected function get_module_name() {
        if (isset(self::$modulename)) {
            return self::$modulename;
        }
        self::$modulename = get_string('modulename', 'assign');
        return self::$modulename;
    }

    
    protected function get_module_name_plural() {
        if (isset(self::$modulenameplural)) {
            return self::$modulenameplural;
        }
        self::$modulenameplural = get_string('modulenameplural', 'assign');
        return self::$modulenameplural;
    }

    
    public function has_instance() {
        return $this->instance || $this->get_course_module();
    }

    
    public function get_instance() {
        global $DB;
        if ($this->instance) {
            return $this->instance;
        }
        if ($this->get_course_module()) {
            $params = array('id' => $this->get_course_module()->instance);
            $this->instance = $DB->get_record('assign', $params, '*', MUST_EXIST);
        }
        if (!$this->instance) {
            throw new coding_exception('Improper use of the assignment class. ' .
                                       'Cannot load the assignment record.');
        }
        return $this->instance;
    }

    
    public function get_grade_item() {
        if ($this->gradeitem) {
            return $this->gradeitem;
        }
        $instance = $this->get_instance();
        $params = array('itemtype' => 'mod',
                        'itemmodule' => 'assign',
                        'iteminstance' => $instance->id,
                        'courseid' => $instance->course,
                        'itemnumber' => 0);
        $this->gradeitem = grade_item::fetch($params);
        if (!$this->gradeitem) {
            throw new coding_exception('Improper use of the assignment class. ' .
                                       'Cannot load the grade item.');
        }
        return $this->gradeitem;
    }

    
    public function get_course_context() {
        if (!$this->context && !$this->course) {
            throw new coding_exception('Improper use of the assignment class. ' .
                                       'Cannot load the course context.');
        }
        if ($this->context) {
            return $this->context->get_course_context();
        } else {
            return context_course::instance($this->course->id);
        }
    }


    
    public function get_course_module() {
        if ($this->coursemodule) {
            return $this->coursemodule;
        }
        if (!$this->context) {
            return null;
        }

        if ($this->context->contextlevel == CONTEXT_MODULE) {
            $modinfo = get_fast_modinfo($this->get_course());
            $this->coursemodule = $modinfo->get_cm($this->context->instanceid);
            return $this->coursemodule;
        }
        return null;
    }

    
    public function get_context() {
        return $this->context;
    }

    
    public function get_course() {
        global $DB;

        if ($this->course) {
            return $this->course;
        }

        if (!$this->context) {
            return null;
        }
        $params = array('id' => $this->get_course_context()->instanceid);
        $this->course = $DB->get_record('course', $params, '*', MUST_EXIST);

        return $this->course;
    }

    
    protected function count_attachments() {

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->get_context()->id, 'mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA,
                        0, 'id', false);

        return count($files);
    }

    
    protected function has_visible_attachments() {
        return ($this->count_attachments() > 0);
    }

    
    public function display_grade($grade, $editing, $userid=0, $modified=0) {
        global $DB;

        static $scalegrades = array();

        $o = '';

        if ($this->get_instance()->grade >= 0) {
                        if ($editing && $this->get_instance()->grade > 0) {
                if ($grade < 0) {
                    $displaygrade = '';
                } else {
                    $displaygrade = format_float($grade, $this->get_grade_item()->get_decimals());
                }
                $o .= '<label class="accesshide" for="quickgrade_' . $userid . '">' .
                       get_string('usergrade', 'assign') .
                       '</label>';
                $o .= '<input type="text"
                              id="quickgrade_' . $userid . '"
                              name="quickgrade_' . $userid . '"
                              value="' .  $displaygrade . '"
                              size="6"
                              maxlength="10"
                              class="quickgrade"/>';
                $o .= '&nbsp;/&nbsp;' . format_float($this->get_instance()->grade, $this->get_grade_item()->get_decimals());
                return $o;
            } else {
                if ($grade == -1 || $grade === null) {
                    $o .= '-';
                } else {
                    $item = $this->get_grade_item();
                    $o .= grade_format_gradevalue($grade, $item);
                    if ($item->get_displaytype() == GRADE_DISPLAY_TYPE_REAL) {
                                                $o .= '&nbsp;/&nbsp;' . format_float($this->get_instance()->grade, $item->get_decimals());
                    }
                }
                return $o;
            }

        } else {
                        if (empty($this->cache['scale'])) {
                if ($scale = $DB->get_record('scale', array('id'=>-($this->get_instance()->grade)))) {
                    $this->cache['scale'] = make_menu_from_list($scale->scale);
                } else {
                    $o .= '-';
                    return $o;
                }
            }
            if ($editing) {
                $o .= '<label class="accesshide"
                              for="quickgrade_' . $userid . '">' .
                      get_string('usergrade', 'assign') .
                      '</label>';
                $o .= '<select name="quickgrade_' . $userid . '" class="quickgrade">';
                $o .= '<option value="-1">' . get_string('nograde') . '</option>';
                foreach ($this->cache['scale'] as $optionid => $option) {
                    $selected = '';
                    if ($grade == $optionid) {
                        $selected = 'selected="selected"';
                    }
                    $o .= '<option value="' . $optionid . '" ' . $selected . '>' . $option . '</option>';
                }
                $o .= '</select>';
                return $o;
            } else {
                $scaleid = (int)$grade;
                if (isset($this->cache['scale'][$scaleid])) {
                    $o .= $this->cache['scale'][$scaleid];
                    return $o;
                }
                $o .= '-';
                return $o;
            }
        }
    }

    
    private function get_submission_info_for_participants($participants) {
        global $DB;

        if (empty($participants)) {
            return $participants;
        }

        list($insql, $params) = $DB->get_in_or_equal(array_keys($participants), SQL_PARAMS_NAMED);

        $assignid = $this->get_instance()->id;
        $params['assignmentid1'] = $assignid;
        $params['assignmentid2'] = $assignid;

        $fields = 'SELECT u.id, s.status, s.timemodified AS stime, g.timemodified AS gtime, g.grade';
        $from = ' FROM {user} u
                         LEFT JOIN {assign_submission} s
                                ON u.id = s.userid
                               AND s.assignment = :assignmentid1
                               AND s.latest = 1
                         LEFT JOIN {assign_grades} g
                                ON u.id = g.userid
                               AND g.assignment = :assignmentid2
                               AND g.attemptnumber = s.attemptnumber
            ';
        $where = ' WHERE u.id ' . $insql;

        if (!empty($this->get_instance()->blindmarking)) {
            $from .= 'LEFT JOIN {assign_user_mapping} um
                             ON u.id = um.userid
                            AND um.assignment = :assignmentid3 ';
            $params['assignmentid3'] = $assignid;
            $fields .= ', um.id as recordid ';
        }

        $sql = "$fields $from $where";

        $records = $DB->get_records_sql($sql, $params);

        if ($this->get_instance()->teamsubmission) {
                        $allgroups = groups_get_all_groups($this->get_course()->id,
                                               array_keys($participants),
                                               $this->get_instance()->teamsubmissiongroupingid,
                                               'DISTINCT g.id, g.name');

        }
        foreach ($participants as $userid => $participant) {
            $participants[$userid]->fullname = $this->fullname($participant);
            $participants[$userid]->submitted = false;
            $participants[$userid]->requiregrading = false;
        }

        foreach ($records as $userid => $submissioninfo) {
                        $submitted = false;
            $requiregrading = false;

            if (!empty($submissioninfo->stime) && $submissioninfo->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                $submitted = true;
            }

            if ($submitted && ($submissioninfo->stime >= $submissioninfo->gtime ||
                    empty($submissioninfo->gtime) ||
                    $submissioninfo->grade === null)) {
                $requiregrading = true;
            }

            $participants[$userid]->submitted = $submitted;
            $participants[$userid]->requiregrading = $requiregrading;
            if ($this->get_instance()->teamsubmission) {
                $group = $this->get_submission_group($userid);
                if ($group) {
                    $participants[$userid]->groupid = $group->id;
                    $participants[$userid]->groupname = $group->name;
                }
            }
        }
        return $participants;
    }

    
    public function list_participants_with_filter_status_and_group($currentgroup) {
        $participants = $this->list_participants($currentgroup, false);

        if (empty($participants)) {
            return $participants;
        } else {
            return $this->get_submission_info_for_participants($participants);
        }
    }

    
    public function list_participants($currentgroup, $idsonly) {

        if (empty($currentgroup)) {
            $currentgroup = 0;
        }

        $key = $this->context->id . '-' . $currentgroup . '-' . $this->show_only_active_users();
        if (!isset($this->participants[$key])) {
            $order = 'u.lastname, u.firstname, u.id';
            if ($this->is_blind_marking()) {
                $order = 'u.id';
            }
            $users = get_enrolled_users($this->context, 'mod/assign:submit', $currentgroup, 'u.*', $order, null, null,
                    $this->show_only_active_users());

            $cm = $this->get_course_module();
            $info = new \core_availability\info_module($cm);
            $users = $info->filter_user_list($users);

            $this->participants[$key] = $users;
        }

        if ($idsonly) {
            $idslist = array();
            foreach ($this->participants[$key] as $id => $user) {
                $idslist[$id] = new stdClass();
                $idslist[$id]->id = $id;
            }
            return $idslist;
        }
        return $this->participants[$key];
    }

    
    public function get_participant($userid) {
        global $DB;

        $participant = $DB->get_record('user', array('id' => $userid));
        if (!$participant) {
            return null;
        }

        if (!is_enrolled($this->context, $participant, 'mod/assign:submit', $this->show_only_active_users())) {
            return null;
        }

        $result = $this->get_submission_info_for_participants(array($participant->id => $participant));
        return $result[$participant->id];
    }

    
    public function count_teams($activitygroup = 0) {

        $count = 0;

        $participants = $this->list_participants($activitygroup, true);

                                if ($this->get_instance()->teamsubmissiongroupingid) {

                        $groups = groups_get_all_groups($this->get_course()->id,
                                            array_keys($participants),
                                            $this->get_instance()->teamsubmissiongroupingid,
                                            'DISTINCT g.id, g.name');

            $count = count($groups);

                        if ($activitygroup == 0) {
                if (empty($this->get_instance()->preventsubmissionnotingroup)) {
                                        $defaultusers = $this->get_submission_group_members(0, true);
                    if (count($defaultusers) > 0) {
                        $count += 1;
                    }
                }
            } else if ($activitygroup != 0 && empty($groups)) {
                                                $count = 1;
            }
        } else {
                        $groups = array();
            foreach ($participants as $participant) {
                if ($group = $this->get_submission_group($participant->id)) {
                    $groups[$group->id] = true;
                } else if (empty($this->get_instance()->preventsubmissionnotingroup)) {
                    $groups[0] = true;
                }
            }

            $count = count($groups);
        }

        return $count;
    }

    
    public function count_participants($currentgroup) {
        return count($this->list_participants($currentgroup, true));
    }

    
    public function count_submissions_need_grading() {
        global $DB;

        if ($this->get_instance()->teamsubmission) {
                        return 0;
        }

        $currentgroup = groups_get_activity_group($this->get_course_module(), true);
        list($esql, $params) = get_enrolled_sql($this->get_context(), 'mod/assign:submit', $currentgroup, true);

        $params['assignid'] = $this->get_instance()->id;
        $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

        $sql = 'SELECT COUNT(s.userid)
                   FROM {assign_submission} s
                   LEFT JOIN {assign_grades} g ON
                        s.assignment = g.assignment AND
                        s.userid = g.userid AND
                        g.attemptnumber = s.attemptnumber
                   JOIN(' . $esql . ') e ON e.id = s.userid
                   WHERE
                        s.latest = 1 AND
                        s.assignment = :assignid AND
                        s.timemodified IS NOT NULL AND
                        s.status = :submitted AND
                        (s.timemodified >= g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL)';

        return $DB->count_records_sql($sql, $params);
    }

    
    public function count_grades() {
        global $DB;

        if (!$this->has_instance()) {
            return 0;
        }

        $currentgroup = groups_get_activity_group($this->get_course_module(), true);
        list($esql, $params) = get_enrolled_sql($this->get_context(), 'mod/assign:submit', $currentgroup, true);

        $params['assignid'] = $this->get_instance()->id;

        $sql = 'SELECT COUNT(g.userid)
                   FROM {assign_grades} g
                   JOIN(' . $esql . ') e ON e.id = g.userid
                   WHERE g.assignment = :assignid';

        return $DB->count_records_sql($sql, $params);
    }

    
    public function count_submissions($includenew = false) {
        global $DB;

        if (!$this->has_instance()) {
            return 0;
        }

        $params = array();
        $sqlnew = '';

        if (!$includenew) {
            $sqlnew = ' AND s.status <> :status ';
            $params['status'] = ASSIGN_SUBMISSION_STATUS_NEW;
        }

        if ($this->get_instance()->teamsubmission) {
                        $sql = 'SELECT COUNT(DISTINCT s.groupid)
                        FROM {assign_submission} s
                        WHERE
                            s.assignment = :assignid AND
                            s.timemodified IS NOT NULL AND
                            s.userid = :groupuserid' .
                            $sqlnew;

            $params['assignid'] = $this->get_instance()->id;
            $params['groupuserid'] = 0;
        } else {
            $currentgroup = groups_get_activity_group($this->get_course_module(), true);
            list($esql, $enrolparams) = get_enrolled_sql($this->get_context(), 'mod/assign:submit', $currentgroup, true);

            $params = array_merge($params, $enrolparams);
            $params['assignid'] = $this->get_instance()->id;

            $sql = 'SELECT COUNT(DISTINCT s.userid)
                       FROM {assign_submission} s
                       JOIN(' . $esql . ') e ON e.id = s.userid
                       WHERE
                            s.assignment = :assignid AND
                            s.timemodified IS NOT NULL ' .
                            $sqlnew;

        }

        return $DB->count_records_sql($sql, $params);
    }

    
    public function count_submissions_with_status($status) {
        global $DB;

        $currentgroup = groups_get_activity_group($this->get_course_module(), true);
        list($esql, $params) = get_enrolled_sql($this->get_context(), 'mod/assign:submit', $currentgroup, true);

        $params['assignid'] = $this->get_instance()->id;
        $params['assignid2'] = $this->get_instance()->id;
        $params['submissionstatus'] = $status;

        if ($this->get_instance()->teamsubmission) {

            $groupsstr = '';
            if ($currentgroup != 0) {
                                $participants = $this->list_participants($currentgroup, true);
                $groups = groups_get_all_groups($this->get_course()->id,
                                                array_keys($participants),
                                                $this->get_instance()->teamsubmissiongroupingid,
                                                'DISTINCT g.id, g.name');
                if (empty($groups)) {
                                                                                $groups = [true];
                }
                list($groupssql, $groupsparams) = $DB->get_in_or_equal(array_keys($groups), SQL_PARAMS_NAMED);
                $groupsstr = 's.groupid ' . $groupssql . ' AND';
                $params = $params + $groupsparams;
            }
            $sql = 'SELECT COUNT(s.groupid)
                        FROM {assign_submission} s
                        WHERE
                            s.latest = 1 AND
                            s.assignment = :assignid AND
                            s.timemodified IS NOT NULL AND
                            s.userid = :groupuserid AND '
                            . $groupsstr . '
                            s.status = :submissionstatus';
            $params['groupuserid'] = 0;
        } else {
            $sql = 'SELECT COUNT(s.userid)
                        FROM {assign_submission} s
                        JOIN(' . $esql . ') e ON e.id = s.userid
                        WHERE
                            s.latest = 1 AND
                            s.assignment = :assignid AND
                            s.timemodified IS NOT NULL AND
                            s.status = :submissionstatus';

        }

        return $DB->count_records_sql($sql, $params);
    }

    
    protected function get_grading_userid_list() {
        $filter = get_user_preferences('assign_filter', '');
        $table = new assign_grading_table($this, 0, $filter, 0, false);

        $useridlist = $table->get_column_data('userid');

        return $useridlist;
    }

    
    protected function pack_files($filesforzipping) {
        global $CFG;
                $tempzip = tempnam($CFG->tempdir . '/', 'assignment_');
                $zipper = new zip_packer();
        if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
            return $tempzip;
        }
        return false;
    }

    
    public static function cron() {
        global $DB;

                $yesterday = time() - (24 * 3600);
        $timenow   = time();
        $lastcron = $DB->get_field('modules', 'lastcron', array('name' => 'assign'));

                                                        $sql = "SELECT g.id as gradeid, a.course, a.name, a.blindmarking, a.revealidentities,
                       g.*, g.timemodified as lastmodified, cm.id as cmid, um.id as recordid
                 FROM {assign} a
                 JOIN {assign_grades} g ON g.assignment = a.id
            LEFT JOIN {assign_user_flags} uf ON uf.assignment = a.id AND uf.userid = g.userid
                 JOIN {course_modules} cm ON cm.course = a.course AND cm.instance = a.id
                 JOIN {modules} md ON md.id = cm.module AND md.name = 'assign'
                 JOIN {grade_items} gri ON gri.iteminstance = a.id AND gri.courseid = a.course AND gri.itemmodule = md.name
            LEFT JOIN {assign_user_mapping} um ON g.id = um.userid AND um.assignment = a.id
                 WHERE ((a.markingworkflow = 0 AND g.timemodified >= :yesterday AND g.timemodified <= :today) OR
                        (a.markingworkflow = 1 AND uf.workflowstate = :wfreleased)) AND
                       uf.mailed = 0 AND gri.hidden = 0
              ORDER BY a.course, cm.id";

        $params = array(
            'yesterday' => $yesterday,
            'today' => $timenow,
            'wfreleased' => ASSIGN_MARKING_WORKFLOW_STATE_RELEASED,
        );
        $submissions = $DB->get_records_sql($sql, $params);

        if (!empty($submissions)) {

            mtrace('Processing ' . count($submissions) . ' assignment submissions ...');

                        $courseids = array();
            foreach ($submissions as $submission) {
                $courseids[] = $submission->course;
            }

                        $courseids = array_unique($courseids);
            $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
            list($courseidsql, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $sql = 'SELECT c.*, ' . $ctxselect .
                      ' FROM {course} c
                 LEFT JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
                     WHERE c.id ' . $courseidsql;

            $params['contextlevel'] = CONTEXT_COURSE;
            $courses = $DB->get_records_sql($sql, $params);

                        unset($courseids);
            unset($ctxselect);
            unset($courseidsql);
            unset($params);

                        foreach ($submissions as $submission) {

                mtrace("Processing assignment submission $submission->id ...");

                                if (!$user = $DB->get_record('user', array('id'=>$submission->userid))) {
                    mtrace('Could not find user ' . $submission->userid);
                    continue;
                }

                                if (!array_key_exists($submission->course, $courses)) {
                    mtrace('Could not find course ' . $submission->course);
                    continue;
                }
                $course = $courses[$submission->course];
                if (isset($course->ctxid)) {
                                        context_helper::preload_from_record($course);
                }

                                                cron_setup_user($user, $course);

                                $coursecontext = context_course::instance($course->id);
                if (!is_enrolled($coursecontext, $user->id)) {
                    $courseshortname = format_string($course->shortname,
                                                     true,
                                                     array('context' => $coursecontext));
                    mtrace(fullname($user) . ' not an active participant in ' . $courseshortname);
                    continue;
                }

                if (!$grader = $DB->get_record('user', array('id'=>$submission->grader))) {
                    mtrace('Could not find grader ' . $submission->grader);
                    continue;
                }

                $modinfo = get_fast_modinfo($course, $user->id);
                $cm = $modinfo->get_cm($submission->cmid);
                                $contextmodule = context_module::instance($cm->id);

                if (!$cm->uservisible) {
                                        continue;
                }

                                $messagetype = 'feedbackavailable';
                $eventtype = 'assign_notification';
                $updatetime = $submission->lastmodified;
                $modulename = get_string('modulename', 'assign');

                $uniqueid = 0;
                if ($submission->blindmarking && !$submission->revealidentities) {
                    if (empty($submission->recordid)) {
                        $uniqueid = self::get_uniqueid_for_user_static($submission->assignment, $user->id);
                    } else {
                        $uniqueid = $submission->recordid;
                    }
                }
                $showusers = $submission->blindmarking && !$submission->revealidentities;
                self::send_assignment_notification($grader,
                                                   $user,
                                                   $messagetype,
                                                   $eventtype,
                                                   $updatetime,
                                                   $cm,
                                                   $contextmodule,
                                                   $course,
                                                   $modulename,
                                                   $submission->name,
                                                   $showusers,
                                                   $uniqueid);

                $flags = $DB->get_record('assign_user_flags', array('userid'=>$user->id, 'assignment'=>$submission->assignment));
                if ($flags) {
                    $flags->mailed = 1;
                    $DB->update_record('assign_user_flags', $flags);
                } else {
                    $flags = new stdClass();
                    $flags->userid = $user->id;
                    $flags->assignment = $submission->assignment;
                    $flags->mailed = 1;
                    $DB->insert_record('assign_user_flags', $flags);
                }

                mtrace('Done');
            }
            mtrace('Done processing ' . count($submissions) . ' assignment submissions');

            cron_setup_user();

                        unset($courses);
        }

                $sql = 'SELECT id
                    FROM {assign}
                    WHERE
                        allowsubmissionsfromdate >= :lastcron AND
                        allowsubmissionsfromdate <= :timenow AND
                        alwaysshowdescription = 0';
        $params = array('lastcron' => $lastcron, 'timenow' => $timenow);
        $newlyavailable = $DB->get_records_sql($sql, $params);
        foreach ($newlyavailable as $record) {
            $cm = get_coursemodule_from_instance('assign', $record->id, 0, false, MUST_EXIST);
            $context = context_module::instance($cm->id);

            $assignment = new assign($context, null, null);
            $assignment->update_calendar($cm->id);
        }

        return true;
    }

    
    public function notify_grade_modified($grade, $mailedoverride = false) {
        global $DB;

        $flags = $this->get_user_flags($grade->userid, true);
        if ($flags->mailed != 1 || $mailedoverride) {
            $flags->mailed = 0;
        }

        return $this->update_user_flags($flags);
    }

    
    public function update_user_flags($flags) {
        global $DB;
        if ($flags->userid <= 0 || $flags->assignment <= 0 || $flags->id <= 0) {
            return false;
        }

        $result = $DB->update_record('assign_user_flags', $flags);
        return $result;
    }

    
    public function update_grade($grade, $reopenattempt = false) {
        global $DB;

        $grade->timemodified = time();

        if (!empty($grade->workflowstate)) {
            $validstates = $this->get_marking_workflow_states_for_current_user();
            if (!array_key_exists($grade->workflowstate, $validstates)) {
                return false;
            }
        }

        if ($grade->grade && $grade->grade != -1) {
            if ($this->get_instance()->grade > 0) {
                if (!is_numeric($grade->grade)) {
                    return false;
                } else if ($grade->grade > $this->get_instance()->grade) {
                    return false;
                } else if ($grade->grade < 0) {
                    return false;
                }
            } else {
                                if ($scale = $DB->get_record('scale', array('id' => -($this->get_instance()->grade)))) {
                    $scaleoptions = make_menu_from_list($scale->scale);
                    if (!array_key_exists((int) $grade->grade, $scaleoptions)) {
                        return false;
                    }
                }
            }
        }

        if (empty($grade->attemptnumber)) {
                        $grade->attemptnumber = 0;
        }
        $DB->update_record('assign_grades', $grade);

        $submission = null;
        if ($this->get_instance()->teamsubmission) {
            $submission = $this->get_group_submission($grade->userid, 0, false);
        } else {
            $submission = $this->get_user_submission($grade->userid, false);
        }

                        if ($submission && $submission->attemptnumber != $grade->attemptnumber) {
            return true;
        }

        if ($this->gradebook_item_update(null, $grade)) {
            \mod_assign\event\submission_graded::create_from_grade($this, $grade)->trigger();
        }

                if ($submission) {
            $this->reopen_submission_if_required($grade->userid,
                    $submission,
                    $reopenattempt);
        }

        return true;
    }

    
    protected function view_grant_extension($mform) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/extensionform.php');

        $o = '';

        $data = new stdClass();
        $data->id = $this->get_course_module()->id;

        $formparams = array(
            'instance' => $this->get_instance(),
            'assign' => $this
        );

        $users = optional_param('userid', 0, PARAM_INT);
        if (!$users) {
            $users = required_param('selectedusers', PARAM_SEQUENCE);
        }
        $userlist = explode(',', $users);

        $formparams['userlist'] = $userlist;

        $data->selectedusers = $users;
        $data->userid = 0;

        if (empty($mform)) {
            $mform = new mod_assign_extension_form(null, $formparams);
        }
        $mform->set_data($data);
        $header = new assign_header($this->get_instance(),
                                    $this->get_context(),
                                    $this->show_intro(),
                                    $this->get_course_module()->id,
                                    get_string('grantextension', 'assign'));
        $o .= $this->get_renderer()->render($header);
        $o .= $this->get_renderer()->render(new assign_form('extensionform', $mform));
        $o .= $this->view_footer();
        return $o;
    }

    
    public function get_submission_group_members($groupid, $onlyids, $excludesuspended = false) {
        $members = array();
        if ($groupid != 0) {
            $allusers = $this->list_participants($groupid, $onlyids);
            foreach ($allusers as $user) {
                if ($this->get_submission_group($user->id)) {
                    $members[] = $user;
                }
            }
        } else {
            $allusers = $this->list_participants(null, $onlyids);
            foreach ($allusers as $user) {
                if ($this->get_submission_group($user->id) == null) {
                    $members[] = $user;
                }
            }
        }
                if ($excludesuspended || !has_capability('moodle/course:viewsuspendedusers', $this->context)) {
            foreach ($members as $key => $member) {
                if (!$this->is_active_user($member->id)) {
                    unset($members[$key]);
                }
            }
        }

        return $members;
    }

    
    public function get_submission_group_members_who_have_not_submitted($groupid, $onlyids) {
        $instance = $this->get_instance();
        if (!$instance->teamsubmission || !$instance->requireallteammemberssubmit) {
            return array();
        }
        $members = $this->get_submission_group_members($groupid, $onlyids);

        foreach ($members as $id => $member) {
            $submission = $this->get_user_submission($member->id, false);
            if ($submission && $submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                unset($members[$id]);
            } else {
                if ($this->is_blind_marking()) {
                    $members[$id]->alias = get_string('hiddenuser', 'assign') .
                                           $this->get_uniqueid_for_user($id);
                }
            }
        }
        return $members;
    }

    
    public function get_group_submission($userid, $groupid, $create, $attemptnumber=-1) {
        global $DB;

        if ($groupid == 0) {
            $group = $this->get_submission_group($userid);
            if ($group) {
                $groupid = $group->id;
            }
        }

                $params = array('assignment'=>$this->get_instance()->id, 'groupid'=>$groupid, 'userid'=>0);
        if ($attemptnumber >= 0) {
            $params['attemptnumber'] = $attemptnumber;
        }

                $submission = null;
        $submissions = $DB->get_records('assign_submission', $params, 'attemptnumber DESC', '*', 0, 1);
        if ($submissions) {
            $submission = reset($submissions);
        }

        if ($submission) {
            return $submission;
        }
        if ($create) {
            $submission = new stdClass();
            $submission->assignment = $this->get_instance()->id;
            $submission->userid = 0;
            $submission->groupid = $groupid;
            $submission->timecreated = time();
            $submission->timemodified = $submission->timecreated;
            if ($attemptnumber >= 0) {
                $submission->attemptnumber = $attemptnumber;
            } else {
                $submission->attemptnumber = 0;
            }
                        $submission->latest = 0;
            $params = array('assignment'=>$this->get_instance()->id, 'groupid'=>$groupid, 'userid'=>0);
            if ($attemptnumber == -1) {
                                $submission->latest = 1;
            } else {
                                $result = $DB->get_records('assign_submission', $params, 'attemptnumber DESC', 'attemptnumber', 0, 1);
                if ($result) {
                    $latestsubmission = reset($result);
                }
                if (!$latestsubmission || ($attemptnumber == $latestsubmission->attemptnumber)) {
                    $submission->latest = 1;
                }
            }
            if ($submission->latest) {
                                $DB->set_field('assign_submission', 'latest', 0, $params);
            }
            $submission->status = ASSIGN_SUBMISSION_STATUS_NEW;
            $sid = $DB->insert_record('assign_submission', $submission);
            return $DB->get_record('assign_submission', array('id' => $sid));
        }
        return false;
    }

    
    private function view_course_index() {
        global $USER;

        $o = '';

        $course = $this->get_course();
        $strplural = get_string('modulenameplural', 'assign');

        if (!$cms = get_coursemodules_in_course('assign', $course->id, 'm.duedate')) {
            $o .= $this->get_renderer()->notification(get_string('thereareno', 'moodle', $strplural));
            $o .= $this->get_renderer()->continue_button(new moodle_url('/course/view.php', array('id' => $course->id)));
            return $o;
        }

        $strsectionname = '';
        $usesections = course_format_uses_sections($course->format);
        $modinfo = get_fast_modinfo($course);

        if ($usesections) {
            $strsectionname = get_string('sectionname', 'format_'.$course->format);
            $sections = $modinfo->get_section_info_all();
        }
        $courseindexsummary = new assign_course_index_summary($usesections, $strsectionname);

        $timenow = time();

        $currentsection = '';
        foreach ($modinfo->instances['assign'] as $cm) {
            if (!$cm->uservisible) {
                continue;
            }

            $timedue = $cms[$cm->id]->duedate;

            $sectionname = '';
            if ($usesections && $cm->sectionnum) {
                $sectionname = get_section_name($course, $sections[$cm->sectionnum]);
            }

            $submitted = '';
            $context = context_module::instance($cm->id);

            $assignment = new assign($context, $cm, $course);

            if (has_capability('mod/assign:grade', $context)) {
                $submitted = $assignment->count_submissions_with_status(ASSIGN_SUBMISSION_STATUS_SUBMITTED);

            } else if (has_capability('mod/assign:submit', $context)) {
                $usersubmission = $assignment->get_user_submission($USER->id, false);

                if (!empty($usersubmission->status)) {
                    $submitted = get_string('submissionstatus_' . $usersubmission->status, 'assign');
                } else {
                    $submitted = get_string('submissionstatus_', 'assign');
                }
            }
            $gradinginfo = grade_get_grades($course->id, 'mod', 'assign', $cm->instance, $USER->id);
            if (isset($gradinginfo->items[0]->grades[$USER->id]) &&
                    !$gradinginfo->items[0]->grades[$USER->id]->hidden ) {
                $grade = $gradinginfo->items[0]->grades[$USER->id]->str_grade;
            } else {
                $grade = '-';
            }

            $courseindexsummary->add_assign_info($cm->id, $cm->get_formatted_name(), $sectionname, $timedue, $submitted, $grade);

        }

        $o .= $this->get_renderer()->render($courseindexsummary);
        $o .= $this->view_footer();

        return $o;
    }

    
    protected function view_plugin_page() {
        global $USER;

        $o = '';

        $pluginsubtype = required_param('pluginsubtype', PARAM_ALPHA);
        $plugintype = required_param('plugin', PARAM_PLUGIN);
        $pluginaction = required_param('pluginaction', PARAM_ALPHA);

        $plugin = $this->get_plugin_by_type($pluginsubtype, $plugintype);
        if (!$plugin) {
            print_error('invalidformdata', '');
            return;
        }

        $o .= $plugin->view_page($pluginaction);

        return $o;
    }


    
    public function get_submission_group($userid) {

        if (isset($this->usersubmissiongroups[$userid])) {
            return $this->usersubmissiongroups[$userid];
        }

        $groups = $this->get_all_groups($userid);
        if (count($groups) != 1) {
            $return = false;
        } else {
            $return = array_pop($groups);
        }

                $this->usersubmissiongroups[$userid] = $return;

        return $return;
    }

    
    public function get_all_groups($userid) {
        if (isset($this->usergroups[$userid])) {
            return $this->usergroups[$userid];
        }

        $grouping = $this->get_instance()->teamsubmissiongroupingid;
        $return = groups_get_all_groups($this->get_course()->id, $userid, $grouping);

        $this->usergroups[$userid] = $return;

        return $return;
    }


    
    protected function view_plugin_content($pluginsubtype) {
        $o = '';

        $submissionid = optional_param('sid', 0, PARAM_INT);
        $gradeid = optional_param('gid', 0, PARAM_INT);
        $plugintype = required_param('plugin', PARAM_PLUGIN);
        $item = null;
        if ($pluginsubtype == 'assignsubmission') {
            $plugin = $this->get_submission_plugin_by_type($plugintype);
            if ($submissionid <= 0) {
                throw new coding_exception('Submission id should not be 0');
            }
            $item = $this->get_submission($submissionid);

                        $this->require_view_submission($item->userid);
            $o .= $this->get_renderer()->render(new assign_header($this->get_instance(),
                                                              $this->get_context(),
                                                              $this->show_intro(),
                                                              $this->get_course_module()->id,
                                                              $plugin->get_name()));
            $o .= $this->get_renderer()->render(new assign_submission_plugin_submission($plugin,
                                                              $item,
                                                              assign_submission_plugin_submission::FULL,
                                                              $this->get_course_module()->id,
                                                              $this->get_return_action(),
                                                              $this->get_return_params()));

                        \mod_assign\event\submission_viewed::create_from_submission($this, $item)->trigger();

        } else {
            $plugin = $this->get_feedback_plugin_by_type($plugintype);
            if ($gradeid <= 0) {
                throw new coding_exception('Grade id should not be 0');
            }
            $item = $this->get_grade($gradeid);
                        $this->require_view_submission($item->userid);
            $o .= $this->get_renderer()->render(new assign_header($this->get_instance(),
                                                              $this->get_context(),
                                                              $this->show_intro(),
                                                              $this->get_course_module()->id,
                                                              $plugin->get_name()));
            $o .= $this->get_renderer()->render(new assign_feedback_plugin_feedback($plugin,
                                                              $item,
                                                              assign_feedback_plugin_feedback::FULL,
                                                              $this->get_course_module()->id,
                                                              $this->get_return_action(),
                                                              $this->get_return_params()));

                        \mod_assign\event\feedback_viewed::create_from_grade($this, $item)->trigger();
        }

        $o .= $this->view_return_links();

        $o .= $this->view_footer();

        return $o;
    }

    
    public function download_rewrite_pluginfile_urls($text, $user, $plugin) {
        $groupmode = groups_get_activity_groupmode($this->get_course_module());
        $groupname = '';
        if ($groupmode) {
            $groupid = groups_get_activity_group($this->get_course_module(), true);
            $groupname = groups_get_group_name($groupid).'-';
        }

        if ($this->is_blind_marking()) {
            $prefix = $groupname . get_string('participant', 'assign');
            $prefix = str_replace('_', ' ', $prefix);
            $prefix = clean_filename($prefix . '_' . $this->get_uniqueid_for_user($user->id) . '_');
        } else {
            $prefix = $groupname . fullname($user);
            $prefix = str_replace('_', ' ', $prefix);
            $prefix = clean_filename($prefix . '_' . $this->get_uniqueid_for_user($user->id) . '_');
        }

        $subtype = $plugin->get_subtype();
        $type = $plugin->get_type();
        $prefix = $prefix . $subtype . '_' . $type . '_';

        $result = str_replace('@@PLUGINFILE@@/', $prefix, $text);

        return $result;
    }

    
    public function render_editor_content($filearea, $submissionid, $plugintype, $editor, $component) {
        global $CFG;

        $result = '';

        $plugin = $this->get_submission_plugin_by_type($plugintype);

        $text = $plugin->get_editor_text($editor, $submissionid);
        $format = $plugin->get_editor_format($editor, $submissionid);

        $finaltext = file_rewrite_pluginfile_urls($text,
                                                  'pluginfile.php',
                                                  $this->get_context()->id,
                                                  $component,
                                                  $filearea,
                                                  $submissionid);
        $params = array('overflowdiv' => true, 'context' => $this->get_context());
        $result .= format_text($finaltext, $format, $params);

        if ($CFG->enableportfolios && has_capability('mod/assign:exportownsubmission', $this->context)) {
            require_once($CFG->libdir . '/portfoliolib.php');

            $button = new portfolio_add_button();
            $portfolioparams = array('cmid' => $this->get_course_module()->id,
                                     'sid' => $submissionid,
                                     'plugin' => $plugintype,
                                     'editor' => $editor,
                                     'area'=>$filearea);
            $button->set_callback_options('assign_portfolio_caller', $portfolioparams, 'mod_assign');
            $fs = get_file_storage();

            if ($files = $fs->get_area_files($this->context->id,
                                             $component,
                                             $filearea,
                                             $submissionid,
                                             'timemodified',
                                             false)) {
                $button->set_formats(PORTFOLIO_FORMAT_RICHHTML);
            } else {
                $button->set_formats(PORTFOLIO_FORMAT_PLAINHTML);
            }
            $result .= $button->to_html();
        }
        return $result;
    }

    
    protected function view_savegrading_result($message) {
        $o = '';
        $o .= $this->get_renderer()->render(new assign_header($this->get_instance(),
                                                      $this->get_context(),
                                                      $this->show_intro(),
                                                      $this->get_course_module()->id,
                                                      get_string('savegradingresult', 'assign')));
        $gradingresult = new assign_gradingmessage(get_string('savegradingresult', 'assign'),
                                                   $message,
                                                   $this->get_course_module()->id);
        $o .= $this->get_renderer()->render($gradingresult);
        $o .= $this->view_footer();
        return $o;
    }
    
    protected function view_quickgrading_result($message) {
        $o = '';
        $o .= $this->get_renderer()->render(new assign_header($this->get_instance(),
                                                      $this->get_context(),
                                                      $this->show_intro(),
                                                      $this->get_course_module()->id,
                                                      get_string('quickgradingresult', 'assign')));
        $lastpage = optional_param('lastpage', null, PARAM_INT);
        $gradingresult = new assign_gradingmessage(get_string('quickgradingresult', 'assign'),
                                                   $message,
                                                   $this->get_course_module()->id,
                                                   false,
                                                   $lastpage);
        $o .= $this->get_renderer()->render($gradingresult);
        $o .= $this->view_footer();
        return $o;
    }

    
    protected function view_footer() {
                if (!PHPUNIT_TEST) {
            return $this->get_renderer()->render_footer();
        }

        return '';
    }

    
    public function require_view_submission($userid) {
        if (!$this->can_view_submission($userid)) {
            throw new required_capability_exception($this->context, 'mod/assign:viewgrades', 'nopermission', '');
        }
    }

    
    public function require_view_grades() {
        if (!$this->can_view_grades()) {
            throw new required_capability_exception($this->context, 'mod/assign:viewgrades', 'nopermission', '');
        }
    }

    
    public function can_view_grades() {
                if (!has_any_capability(array('mod/assign:viewgrades', 'mod/assign:grade'), $this->context)) {
            return false;
        }

        return true;
    }

    
    public function can_grade() {
                if (!has_capability('mod/assign:grade', $this->context)) {
            return false;
        }

        return true;
    }

    
    protected function download_submissions($userids = false) {
        global $CFG, $DB;

                require_once($CFG->libdir.'/filelib.php');

                core_php_time_limit::raise();

        $this->require_view_grades();

                $students = get_enrolled_users($this->context, "mod/assign:submit", null, 'u.*', null, null, null,
                        $this->show_only_active_users());

                $filesforzipping = array();
        $fs = get_file_storage();

        $groupmode = groups_get_activity_groupmode($this->get_course_module());
                $groupid = 0;
        $groupname = '';
        if ($groupmode) {
            $groupid = groups_get_activity_group($this->get_course_module(), true);
            $groupname = groups_get_group_name($groupid).'-';
        }

                $filename = clean_filename($this->get_course()->shortname . '-' .
                                   $this->get_instance()->name . '-' .
                                   $groupname.$this->get_course_module()->id . '.zip');

                foreach ($students as $student) {
            $userid = $student->id;
                        if ($userids and !in_array($userid, $userids)) {
                continue;
            }

            if ((groups_is_member($groupid, $userid) or !$groupmode or !$groupid)) {
                
                $submissiongroup = false;
                $groupname = '';
                if ($this->get_instance()->teamsubmission) {
                    $submission = $this->get_group_submission($userid, 0, false);
                    $submissiongroup = $this->get_submission_group($userid);
                    if ($submissiongroup) {
                        $groupname = $submissiongroup->name . '-';
                    } else {
                        $groupname = get_string('defaultteam', 'assign') . '-';
                    }
                } else {
                    $submission = $this->get_user_submission($userid, false);
                }

                if ($this->is_blind_marking()) {
                    $prefix = str_replace('_', ' ', $groupname . get_string('participant', 'assign'));
                    $prefix = clean_filename($prefix . '_' . $this->get_uniqueid_for_user($userid));
                } else {
                    $prefix = str_replace('_', ' ', $groupname . fullname($student));
                    $prefix = clean_filename($prefix . '_' . $this->get_uniqueid_for_user($userid));
                }

                if ($submission) {
                    $downloadasfolders = get_user_preferences('assign_downloadasfolders', 1);
                    foreach ($this->submissionplugins as $plugin) {
                        if ($plugin->is_enabled() && $plugin->is_visible()) {
                            if ($downloadasfolders) {
                                                                                                $submission->exportfullpath = true;
                                $pluginfiles = $plugin->get_files($submission, $student);
                                foreach ($pluginfiles as $zipfilepath => $file) {
                                    $subtype = $plugin->get_subtype();
                                    $type = $plugin->get_type();
                                    $zipfilename = basename($zipfilepath);
                                    $prefixedfilename = clean_filename($prefix .
                                                                       '_' .
                                                                       $subtype .
                                                                       '_' .
                                                                       $type .
                                                                       '_');
                                    if ($type == 'file') {
                                        $pathfilename = $prefixedfilename . $file->get_filepath() . $zipfilename;
                                    } else if ($type == 'onlinetext') {
                                        $pathfilename = $prefixedfilename . '/' . $zipfilename;
                                    } else {
                                        $pathfilename = $prefixedfilename . '/' . $zipfilename;
                                    }
                                    $pathfilename = clean_param($pathfilename, PARAM_PATH);
                                    $filesforzipping[$pathfilename] = $file;
                                }
                            } else {
                                                                                                $submission->exportfullpath = false;
                                $pluginfiles = $plugin->get_files($submission, $student);
                                foreach ($pluginfiles as $zipfilename => $file) {
                                    $subtype = $plugin->get_subtype();
                                    $type = $plugin->get_type();
                                    $prefixedfilename = clean_filename($prefix .
                                                                       '_' .
                                                                       $subtype .
                                                                       '_' .
                                                                       $type .
                                                                       '_' .
                                                                       $zipfilename);
                                    $filesforzipping[$prefixedfilename] = $file;
                                }
                            }
                        }
                    }
                }
            }
        }
        $result = '';
        if (count($filesforzipping) == 0) {
            $header = new assign_header($this->get_instance(),
                                        $this->get_context(),
                                        '',
                                        $this->get_course_module()->id,
                                        get_string('downloadall', 'assign'));
            $result .= $this->get_renderer()->render($header);
            $result .= $this->get_renderer()->notification(get_string('nosubmission', 'assign'));
            $url = new moodle_url('/mod/assign/view.php', array('id'=>$this->get_course_module()->id,
                                                                    'action'=>'grading'));
            $result .= $this->get_renderer()->continue_button($url);
            $result .= $this->view_footer();
        } else if ($zipfile = $this->pack_files($filesforzipping)) {
            \mod_assign\event\all_submissions_downloaded::create_from_assign($this)->trigger();
                        send_temp_file($zipfile, $filename);
                    }
        return $result;
    }

    
    public function add_to_log($action = '', $info = '', $url='', $return = false) {
        global $USER;

        $fullurl = 'view.php?id=' . $this->get_course_module()->id;
        if ($url != '') {
            $fullurl .= '&' . $url;
        }

        $args = array(
            $this->get_course()->id,
            'assign',
            $action,
            $fullurl,
            $info,
            $this->get_course_module()->id
        );

        if ($return) {
                                    debugging('The mod_assign add_to_log() function is now deprecated.', DEBUG_DEVELOPER);
            return $args;
        }
        call_user_func_array('add_to_log', $args);
    }

    
    public function get_renderer() {
        global $PAGE;
        if ($this->output) {
            return $this->output;
        }
        $this->output = $PAGE->get_renderer('mod_assign', null, RENDERER_TARGET_GENERAL);
        return $this->output;
    }

    
    public function get_user_submission($userid, $create, $attemptnumber=-1) {
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }
                $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid, 'groupid'=>0);
        if ($attemptnumber >= 0) {
            $params['attemptnumber'] = $attemptnumber;
        }

                $submission = null;
        $submissions = $DB->get_records('assign_submission', $params, 'attemptnumber DESC', '*', 0, 1);
        if ($submissions) {
            $submission = reset($submissions);
        }

        if ($submission) {
            return $submission;
        }
        if ($create) {
            $submission = new stdClass();
            $submission->assignment   = $this->get_instance()->id;
            $submission->userid       = $userid;
            $submission->timecreated = time();
            $submission->timemodified = $submission->timecreated;
            $submission->status = ASSIGN_SUBMISSION_STATUS_NEW;
            if ($attemptnumber >= 0) {
                $submission->attemptnumber = $attemptnumber;
            } else {
                $submission->attemptnumber = 0;
            }
                        $submission->latest = 0;
            $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid, 'groupid'=>0);
            if ($attemptnumber == -1) {
                                $submission->latest = 1;
            } else {
                                $result = $DB->get_records('assign_submission', $params, 'attemptnumber DESC', 'attemptnumber', 0, 1);
                $latestsubmission = null;
                if ($result) {
                    $latestsubmission = reset($result);
                }
                if (empty($latestsubmission) || ($attemptnumber > $latestsubmission->attemptnumber)) {
                    $submission->latest = 1;
                }
            }
            if ($submission->latest) {
                                $DB->set_field('assign_submission', 'latest', 0, $params);
            }
            $sid = $DB->insert_record('assign_submission', $submission);
            return $DB->get_record('assign_submission', array('id' => $sid));
        }
        return false;
    }

    
    protected function get_submission($submissionid) {
        global $DB;

        $params = array('assignment'=>$this->get_instance()->id, 'id'=>$submissionid);
        return $DB->get_record('assign_submission', $params, '*', MUST_EXIST);
    }

    
    public function get_user_flags($userid, $create) {
        global $DB, $USER;

                if (!$userid) {
            $userid = $USER->id;
        }

        $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid);

        $flags = $DB->get_record('assign_user_flags', $params);

        if ($flags) {
            return $flags;
        }
        if ($create) {
            $flags = new stdClass();
            $flags->assignment = $this->get_instance()->id;
            $flags->userid = $userid;
            $flags->locked = 0;
            $flags->extensionduedate = 0;
            $flags->workflowstate = '';
            $flags->allocatedmarker = 0;

                                    $flags->mailed = 2;

            $fid = $DB->insert_record('assign_user_flags', $flags);
            $flags->id = $fid;
            return $flags;
        }
        return false;
    }

    
    public function get_user_grade($userid, $create, $attemptnumber=-1) {
        global $DB, $USER;

                if (!$userid) {
            $userid = $USER->id;
        }
        $submission = null;

        $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid);
        if ($attemptnumber < 0 || $create) {
                        if ($this->get_instance()->teamsubmission) {
                $submission = $this->get_group_submission($userid, 0, true, $attemptnumber);
            } else {
                $submission = $this->get_user_submission($userid, true, $attemptnumber);
            }
            if ($submission) {
                $attemptnumber = $submission->attemptnumber;
            }
        }

        if ($attemptnumber >= 0) {
            $params['attemptnumber'] = $attemptnumber;
        }

        $grades = $DB->get_records('assign_grades', $params, 'attemptnumber DESC', '*', 0, 1);

        if ($grades) {
            return reset($grades);
        }
        if ($create) {
            $grade = new stdClass();
            $grade->assignment   = $this->get_instance()->id;
            $grade->userid       = $userid;
            $grade->timecreated = time();
                                                if ($submission) {
                $grade->timemodified = $submission->timemodified;
            } else {
                $grade->timemodified = $grade->timecreated;
            }
            $grade->grade = -1;
            $grade->grader = $USER->id;
            if ($attemptnumber >= 0) {
                $grade->attemptnumber = $attemptnumber;
            }

            $gid = $DB->insert_record('assign_grades', $grade);
            $grade->id = $gid;
            return $grade;
        }
        return false;
    }

    
    protected function get_grade($gradeid) {
        global $DB;

        $params = array('assignment'=>$this->get_instance()->id, 'id'=>$gradeid);
        return $DB->get_record('assign_grades', $params, '*', MUST_EXIST);
    }

    
    protected function view_single_grading_panel($args) {
        global $DB, $CFG, $SESSION, $PAGE;

        $o = '';
        $instance = $this->get_instance();

        require_once($CFG->dirroot . '/mod/assign/gradeform.php');

                require_capability('mod/assign:grade', $this->context);

                $userid = $args['userid'];
        $attemptnumber = $args['attemptnumber'];

        $rownum = 0;
        $useridlist = array($userid);

        $last = true;
                        $returnparams = array('userid' => $userid, 'rownum' => 0, 'useridlistid' => 0);
        $this->register_return_link('grade', $returnparams);

        $user = $DB->get_record('user', array('id' => $userid));
        $submission = $this->get_user_submission($userid, false, $attemptnumber);
        $submissiongroup = null;
        $teamsubmission = null;
        $notsubmitted = array();
        if ($instance->teamsubmission) {
            $teamsubmission = $this->get_group_submission($userid, 0, false, $attemptnumber);
            $submissiongroup = $this->get_submission_group($userid);
            $groupid = 0;
            if ($submissiongroup) {
                $groupid = $submissiongroup->id;
            }
            $notsubmitted = $this->get_submission_group_members_who_have_not_submitted($groupid, false);

        }

                $grade = $this->get_user_grade($userid, false, $attemptnumber);
        $flags = $this->get_user_flags($userid, false);
        if ($this->can_view_submission($userid)) {
            $gradelocked = ($flags && $flags->locked) || $this->grading_disabled($userid);
            $extensionduedate = null;
            if ($flags) {
                $extensionduedate = $flags->extensionduedate;
            }
            $showedit = $this->submissions_open($userid) && ($this->is_any_submission_plugin_enabled());
            $viewfullnames = has_capability('moodle/site:viewfullnames', $this->get_course_context());
            $usergroups = $this->get_all_groups($user->id);

            $submissionstatus = new assign_submission_status_compact($instance->allowsubmissionsfromdate,
                                                                     $instance->alwaysshowdescription,
                                                                     $submission,
                                                                     $instance->teamsubmission,
                                                                     $teamsubmission,
                                                                     $submissiongroup,
                                                                     $notsubmitted,
                                                                     $this->is_any_submission_plugin_enabled(),
                                                                     $gradelocked,
                                                                     $this->is_graded($userid),
                                                                     $instance->duedate,
                                                                     $instance->cutoffdate,
                                                                     $this->get_submission_plugins(),
                                                                     $this->get_return_action(),
                                                                     $this->get_return_params(),
                                                                     $this->get_course_module()->id,
                                                                     $this->get_course()->id,
                                                                     assign_submission_status::GRADER_VIEW,
                                                                     $showedit,
                                                                     false,
                                                                     $viewfullnames,
                                                                     $extensionduedate,
                                                                     $this->get_context(),
                                                                     $this->is_blind_marking(),
                                                                     '',
                                                                     $instance->attemptreopenmethod,
                                                                     $instance->maxattempts,
                                                                     $this->get_grading_status($userid),
                                                                     $instance->preventsubmissionnotingroup,
                                                                     $usergroups);
            $o .= $this->get_renderer()->render($submissionstatus);
        }

        if ($grade) {
            $data = new stdClass();
            if ($grade->grade !== null && $grade->grade >= 0) {
                $data->grade = format_float($grade->grade, $this->get_grade_item()->get_decimals());
            }
        } else {
            $data = new stdClass();
            $data->grade = '';
        }

        if (!empty($flags->workflowstate)) {
            $data->workflowstate = $flags->workflowstate;
        }
        if (!empty($flags->allocatedmarker)) {
            $data->allocatedmarker = $flags->allocatedmarker;
        }

                $allsubmissions = $this->get_all_submissions($userid);

        if ($attemptnumber != -1 && ($attemptnumber + 1) != count($allsubmissions)) {
            $params = array('attemptnumber' => $attemptnumber + 1,
                            'totalattempts' => count($allsubmissions));
            $message = get_string('editingpreviousfeedbackwarning', 'assign', $params);
            $o .= $this->get_renderer()->notification($message);
        }

        $pagination = array('rownum' => $rownum,
                            'useridlistid' => 0,
                            'last' => $last,
                            'userid' => $userid,
                            'attemptnumber' => $attemptnumber,
                            'gradingpanel' => true);

        if (!empty($args['formdata'])) {
            $data = (array) $data;
            $data = (object) array_merge($data, $args['formdata']);
        }
        $formparams = array($this, $data, $pagination);
        $mform = new mod_assign_grade_form(null,
                                           $formparams,
                                           'post',
                                           '',
                                           array('class' => 'gradeform'));

        if (!empty($args['formdata'])) {
                                    $mform->is_validated();
        }
        $o .= $this->get_renderer()->heading(get_string('grade'), 3);
        $o .= $this->get_renderer()->render(new assign_form('gradingform', $mform));

        if (count($allsubmissions) > 1) {
            $allgrades = $this->get_all_grades($userid);
            $history = new assign_attempt_history_chooser($allsubmissions,
                                                          $allgrades,
                                                          $this->get_course_module()->id,
                                                          $userid);

            $o .= $this->get_renderer()->render($history);
        }

        \mod_assign\event\grading_form_viewed::create_from_user($this, $user)->trigger();

        return $o;
    }

    
    protected function view_single_grade_page($mform) {
        global $DB, $CFG, $SESSION;

        $o = '';
        $instance = $this->get_instance();

        require_once($CFG->dirroot . '/mod/assign/gradeform.php');

                require_capability('mod/assign:grade', $this->context);

        $header = new assign_header($instance,
                                    $this->get_context(),
                                    false,
                                    $this->get_course_module()->id,
                                    get_string('grading', 'assign'));
        $o .= $this->get_renderer()->render($header);

                $rownum = optional_param('rownum', 0, PARAM_INT);
        $useridlistid = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
        $userid = optional_param('userid', 0, PARAM_INT);
        $attemptnumber = optional_param('attemptnumber', -1, PARAM_INT);

        if (!$userid) {
            $useridlistkey = $this->get_useridlist_key($useridlistid);
            if (empty($SESSION->mod_assign_useridlist[$useridlistkey])) {
                $SESSION->mod_assign_useridlist[$useridlistkey] = $this->get_grading_userid_list();
            }
            $useridlist = $SESSION->mod_assign_useridlist[$useridlistkey];
        } else {
            $rownum = 0;
            $useridlistid = 0;
            $useridlist = array($userid);
        }

        if ($rownum < 0 || $rownum > count($useridlist)) {
            throw new coding_exception('Row is out of bounds for the current grading table: ' . $rownum);
        }

        $last = false;
        $userid = $useridlist[$rownum];
        if ($rownum == count($useridlist) - 1) {
            $last = true;
        }
                        $returnparams = array('userid' => $userid, 'rownum' => 0, 'useridlistid' => 0);
        $this->register_return_link('grade', $returnparams);

        $user = $DB->get_record('user', array('id' => $userid));
        if ($user) {
            $viewfullnames = has_capability('moodle/site:viewfullnames', $this->get_course_context());
            $usersummary = new assign_user_summary($user,
                                                   $this->get_course()->id,
                                                   $viewfullnames,
                                                   $this->is_blind_marking(),
                                                   $this->get_uniqueid_for_user($user->id),
                                                   get_extra_user_fields($this->get_context()),
                                                   !$this->is_active_user($userid));
            $o .= $this->get_renderer()->render($usersummary);
        }
        $submission = $this->get_user_submission($userid, false, $attemptnumber);
        $submissiongroup = null;
        $teamsubmission = null;
        $notsubmitted = array();
        if ($instance->teamsubmission) {
            $teamsubmission = $this->get_group_submission($userid, 0, false, $attemptnumber);
            $submissiongroup = $this->get_submission_group($userid);
            $groupid = 0;
            if ($submissiongroup) {
                $groupid = $submissiongroup->id;
            }
            $notsubmitted = $this->get_submission_group_members_who_have_not_submitted($groupid, false);

        }

                $grade = $this->get_user_grade($userid, false, $attemptnumber);
        $flags = $this->get_user_flags($userid, false);
        if ($this->can_view_submission($userid)) {
            $gradelocked = ($flags && $flags->locked) || $this->grading_disabled($userid);
            $extensionduedate = null;
            if ($flags) {
                $extensionduedate = $flags->extensionduedate;
            }
            $showedit = $this->submissions_open($userid) && ($this->is_any_submission_plugin_enabled());
            $viewfullnames = has_capability('moodle/site:viewfullnames', $this->get_course_context());
            $usergroups = $this->get_all_groups($user->id);

            $submissionstatus = new assign_submission_status($instance->allowsubmissionsfromdate,
                                                             $instance->alwaysshowdescription,
                                                             $submission,
                                                             $instance->teamsubmission,
                                                             $teamsubmission,
                                                             $submissiongroup,
                                                             $notsubmitted,
                                                             $this->is_any_submission_plugin_enabled(),
                                                             $gradelocked,
                                                             $this->is_graded($userid),
                                                             $instance->duedate,
                                                             $instance->cutoffdate,
                                                             $this->get_submission_plugins(),
                                                             $this->get_return_action(),
                                                             $this->get_return_params(),
                                                             $this->get_course_module()->id,
                                                             $this->get_course()->id,
                                                             assign_submission_status::GRADER_VIEW,
                                                             $showedit,
                                                             false,
                                                             $viewfullnames,
                                                             $extensionduedate,
                                                             $this->get_context(),
                                                             $this->is_blind_marking(),
                                                             '',
                                                             $instance->attemptreopenmethod,
                                                             $instance->maxattempts,
                                                             $this->get_grading_status($userid),
                                                             $instance->preventsubmissionnotingroup,
                                                             $usergroups);
            $o .= $this->get_renderer()->render($submissionstatus);
        }

        if ($grade) {
            $data = new stdClass();
            if ($grade->grade !== null && $grade->grade >= 0) {
                $data->grade = format_float($grade->grade, $this->get_grade_item()->get_decimals());
            }
        } else {
            $data = new stdClass();
            $data->grade = '';
        }

        if (!empty($flags->workflowstate)) {
            $data->workflowstate = $flags->workflowstate;
        }
        if (!empty($flags->allocatedmarker)) {
            $data->allocatedmarker = $flags->allocatedmarker;
        }

                $allsubmissions = $this->get_all_submissions($userid);

        if ($attemptnumber != -1 && ($attemptnumber + 1) != count($allsubmissions)) {
            $params = array('attemptnumber'=>$attemptnumber + 1,
                            'totalattempts'=>count($allsubmissions));
            $message = get_string('editingpreviousfeedbackwarning', 'assign', $params);
            $o .= $this->get_renderer()->notification($message);
        }

                if (!$mform) {
            $pagination = array('rownum' => $rownum,
                                'useridlistid' => $useridlistid,
                                'last' => $last,
                                'userid' => $userid,
                                'attemptnumber' => $attemptnumber);
            $formparams = array($this, $data, $pagination);
            $mform = new mod_assign_grade_form(null,
                                               $formparams,
                                               'post',
                                               '',
                                               array('class'=>'gradeform'));
        }
        $o .= $this->get_renderer()->heading(get_string('grade'), 3);
        $o .= $this->get_renderer()->render(new assign_form('gradingform', $mform));

        if (count($allsubmissions) > 1 && $attemptnumber == -1) {
            $allgrades = $this->get_all_grades($userid);
            $history = new assign_attempt_history($allsubmissions,
                                                  $allgrades,
                                                  $this->get_submission_plugins(),
                                                  $this->get_feedback_plugins(),
                                                  $this->get_course_module()->id,
                                                  $this->get_return_action(),
                                                  $this->get_return_params(),
                                                  true,
                                                  $useridlistid,
                                                  $rownum);

            $o .= $this->get_renderer()->render($history);
        }

        \mod_assign\event\grading_form_viewed::create_from_user($this, $user)->trigger();

        $o .= $this->view_footer();
        return $o;
    }

    
    protected function view_reveal_identities_confirm() {
        require_capability('mod/assign:revealidentities', $this->get_context());

        $o = '';
        $header = new assign_header($this->get_instance(),
                                    $this->get_context(),
                                    false,
                                    $this->get_course_module()->id);
        $o .= $this->get_renderer()->render($header);

        $urlparams = array('id'=>$this->get_course_module()->id,
                           'action'=>'revealidentitiesconfirm',
                           'sesskey'=>sesskey());
        $confirmurl = new moodle_url('/mod/assign/view.php', $urlparams);

        $urlparams = array('id'=>$this->get_course_module()->id,
                           'action'=>'grading');
        $cancelurl = new moodle_url('/mod/assign/view.php', $urlparams);

        $o .= $this->get_renderer()->confirm(get_string('revealidentitiesconfirm', 'assign'),
                                             $confirmurl,
                                             $cancelurl);
        $o .= $this->view_footer();

        \mod_assign\event\reveal_identities_confirmation_page_viewed::create_from_assign($this)->trigger();

        return $o;
    }

    
    protected function view_return_links() {
        $returnaction = optional_param('returnaction', '', PARAM_ALPHA);
        $returnparams = optional_param('returnparams', '', PARAM_TEXT);

        $params = array();
        $returnparams = str_replace('&amp;', '&', $returnparams);
        parse_str($returnparams, $params);
        $newparams = array('id' => $this->get_course_module()->id, 'action' => $returnaction);
        $params = array_merge($newparams, $params);

        $url = new moodle_url('/mod/assign/view.php', $params);
        return $this->get_renderer()->single_button($url, get_string('back'), 'get');
    }

    
    protected function view_grading_table() {
        global $USER, $CFG, $SESSION;

                require_once($CFG->dirroot . '/mod/assign/gradingoptionsform.php');
        require_once($CFG->dirroot . '/mod/assign/quickgradingform.php');
        require_once($CFG->dirroot . '/mod/assign/gradingbatchoperationsform.php');
        $o = '';
        $cmid = $this->get_course_module()->id;

        $links = array();
        if (has_capability('gradereport/grader:view', $this->get_course_context()) &&
                has_capability('moodle/grade:viewall', $this->get_course_context())) {
            $gradebookurl = '/grade/report/grader/index.php?id=' . $this->get_course()->id;
            $links[$gradebookurl] = get_string('viewgradebook', 'assign');
        }
        if ($this->is_any_submission_plugin_enabled() && $this->count_submissions()) {
            $downloadurl = '/mod/assign/view.php?id=' . $cmid . '&action=downloadall';
            $links[$downloadurl] = get_string('downloadall', 'assign');
        }
        if ($this->is_blind_marking() &&
                has_capability('mod/assign:revealidentities', $this->get_context())) {
            $revealidentitiesurl = '/mod/assign/view.php?id=' . $cmid . '&action=revealidentities';
            $links[$revealidentitiesurl] = get_string('revealidentities', 'assign');
        }
        foreach ($this->get_feedback_plugins() as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                foreach ($plugin->get_grading_actions() as $action => $description) {
                    $url = '/mod/assign/view.php' .
                           '?id=' .  $cmid .
                           '&plugin=' . $plugin->get_type() .
                           '&pluginsubtype=assignfeedback' .
                           '&action=viewpluginpage&pluginaction=' . $action;
                    $links[$url] = $description;
                }
            }
        }

                core_collator::asort($links);

        $gradingactions = new url_select($links);
        $gradingactions->set_label(get_string('choosegradingaction', 'assign'));

        $gradingmanager = get_grading_manager($this->get_context(), 'mod_assign', 'submissions');

        $perpage = $this->get_assign_perpage();
        $filter = get_user_preferences('assign_filter', '');
        $markerfilter = get_user_preferences('assign_markerfilter', '');
        $workflowfilter = get_user_preferences('assign_workflowfilter', '');
        $controller = $gradingmanager->get_active_controller();
        $showquickgrading = empty($controller) && $this->can_grade();
        $quickgrading = get_user_preferences('assign_quickgrading', false);
        $showonlyactiveenrolopt = has_capability('moodle/course:viewsuspendedusers', $this->context);
        $downloadasfolders = get_user_preferences('assign_downloadasfolders', 1);

        $markingallocation = $this->get_instance()->markingworkflow &&
            $this->get_instance()->markingallocation &&
            has_capability('mod/assign:manageallocations', $this->context);
                $markingallocationoptions = array();
        if ($markingallocation) {
            list($sort, $params) = users_order_by_sql();
            $markers = get_users_by_capability($this->context, 'mod/assign:grade', '', $sort);
            $markingallocationoptions[''] = get_string('filternone', 'assign');
            $markingallocationoptions[ASSIGN_MARKER_FILTER_NO_MARKER] = get_string('markerfilternomarker', 'assign');
            foreach ($markers as $marker) {
                $markingallocationoptions[$marker->id] = fullname($marker);
            }
        }

        $markingworkflow = $this->get_instance()->markingworkflow;
                $markingworkflowoptions = array();
        if ($markingworkflow) {
            $notmarked = get_string('markingworkflowstatenotmarked', 'assign');
            $markingworkflowoptions[''] = get_string('filternone', 'assign');
            $markingworkflowoptions[ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED] = $notmarked;
            $markingworkflowoptions = array_merge($markingworkflowoptions, $this->get_marking_workflow_states_for_current_user());
        }

                $gradingoptionsformparams = array('cm'=>$cmid,
                                          'contextid'=>$this->context->id,
                                          'userid'=>$USER->id,
                                          'submissionsenabled'=>$this->is_any_submission_plugin_enabled(),
                                          'showquickgrading'=>$showquickgrading,
                                          'quickgrading'=>$quickgrading,
                                          'markingworkflowopt'=>$markingworkflowoptions,
                                          'markingallocationopt'=>$markingallocationoptions,
                                          'showonlyactiveenrolopt'=>$showonlyactiveenrolopt,
                                          'showonlyactiveenrol' => $this->show_only_active_users(),
                                          'downloadasfolders' => $downloadasfolders);

        $classoptions = array('class'=>'gradingoptionsform');
        $gradingoptionsform = new mod_assign_grading_options_form(null,
                                                                  $gradingoptionsformparams,
                                                                  'post',
                                                                  '',
                                                                  $classoptions);

        $batchformparams = array('cm'=>$cmid,
                                 'submissiondrafts'=>$this->get_instance()->submissiondrafts,
                                 'duedate'=>$this->get_instance()->duedate,
                                 'attemptreopenmethod'=>$this->get_instance()->attemptreopenmethod,
                                 'feedbackplugins'=>$this->get_feedback_plugins(),
                                 'context'=>$this->get_context(),
                                 'markingworkflow'=>$markingworkflow,
                                 'markingallocation'=>$markingallocation);
        $classoptions = array('class'=>'gradingbatchoperationsform');

        $gradingbatchoperationsform = new mod_assign_grading_batch_operations_form(null,
                                                                                   $batchformparams,
                                                                                   'post',
                                                                                   '',
                                                                                   $classoptions);

        $gradingoptionsdata = new stdClass();
        $gradingoptionsdata->perpage = $perpage;
        $gradingoptionsdata->filter = $filter;
        $gradingoptionsdata->markerfilter = $markerfilter;
        $gradingoptionsdata->workflowfilter = $workflowfilter;
        $gradingoptionsform->set_data($gradingoptionsdata);

        $actionformtext = $this->get_renderer()->render($gradingactions);
        $header = new assign_header($this->get_instance(),
                                    $this->get_context(),
                                    false,
                                    $this->get_course_module()->id,
                                    get_string('grading', 'assign'),
                                    $actionformtext);
        $o .= $this->get_renderer()->render($header);

        $currenturl = $CFG->wwwroot .
                      '/mod/assign/view.php?id=' .
                      $this->get_course_module()->id .
                      '&action=grading';

        $o .= groups_print_activity_menu($this->get_course_module(), $currenturl, true);

                if (!empty($CFG->enableplagiarism)) {
            require_once($CFG->libdir . '/plagiarismlib.php');
            $o .= plagiarism_update_status($this->get_course(), $this->get_course_module());
        }

        if ($this->is_blind_marking() && has_capability('mod/assign:viewblinddetails', $this->get_context())) {
            $o .= $this->get_renderer()->notification(get_string('blindmarkingenabledwarning', 'assign'), 'notifymessage');
        }

                if ($showquickgrading && $quickgrading) {
            $gradingtable = new assign_grading_table($this, $perpage, $filter, 0, true);
            $table = $this->get_renderer()->render($gradingtable);
            $page = optional_param('page', null, PARAM_INT);
            $quickformparams = array('cm'=>$this->get_course_module()->id,
                                     'gradingtable'=>$table,
                                     'sendstudentnotifications' => $this->get_instance()->sendstudentnotifications,
                                     'page' => $page);
            $quickgradingform = new mod_assign_quick_grading_form(null, $quickformparams);

            $o .= $this->get_renderer()->render(new assign_form('quickgradingform', $quickgradingform));
        } else {
            $gradingtable = new assign_grading_table($this, $perpage, $filter, 0, false);
            $o .= $this->get_renderer()->render($gradingtable);
        }

        if ($this->can_grade()) {
                                    $useridlist = $gradingtable->get_column_data('userid');
            $SESSION->mod_assign_useridlist[$this->get_useridlist_key()] = $useridlist;
        }

        $currentgroup = groups_get_activity_group($this->get_course_module(), true);
        $users = array_keys($this->list_participants($currentgroup, true));
        if (count($users) != 0 && $this->can_grade()) {
                        $assignform = new assign_form('gradingbatchoperationsform', $gradingbatchoperationsform);
            $o .= $this->get_renderer()->render($assignform);
        }
        $assignform = new assign_form('gradingoptionsform',
                                      $gradingoptionsform,
                                      'M.mod_assign.init_grading_options');
        $o .= $this->get_renderer()->render($assignform);
        return $o;
    }

    
    protected function view_grader() {
        global $USER, $PAGE;

        $o = '';
                $this->require_view_grades();

        $PAGE->set_pagelayout('embedded');

        $PAGE->set_title($this->get_context()->get_context_name());

        $o .= $this->get_renderer()->header();

        $userid = optional_param('userid', 0, PARAM_INT);
        $blindid = optional_param('blindid', 0, PARAM_INT);

        if (!$userid && $blindid) {
            $userid = $this->get_user_id_for_uniqueid($blindid);
        }

        $currentgroup = groups_get_activity_group($this->get_course_module(), true);
        $framegrader = new grading_app($userid, $currentgroup, $this);

        $o .= $this->get_renderer()->render($framegrader);

        $o .= $this->view_footer();

        \mod_assign\event\grading_table_viewed::create_from_assign($this)->trigger();

        return $o;
    }
    
    protected function view_grading_page() {
        global $CFG;

        $o = '';
                $this->require_view_grades();
        require_once($CFG->dirroot . '/mod/assign/gradeform.php');

                $o .= $this->view_grading_table();

        $o .= $this->view_footer();

        \mod_assign\event\grading_table_viewed::create_from_assign($this)->trigger();

        return $o;
    }

    
    protected function plagiarism_print_disclosure() {
        global $CFG;
        $o = '';

        if (!empty($CFG->enableplagiarism)) {
            require_once($CFG->libdir . '/plagiarismlib.php');

            $o .= plagiarism_print_disclosure($this->get_course_module()->id);
        }

        return $o;
    }

    
    protected function view_notices($title, $notices) {
        global $CFG;

        $o = '';

        $header = new assign_header($this->get_instance(),
                                    $this->get_context(),
                                    $this->show_intro(),
                                    $this->get_course_module()->id,
                                    $title);
        $o .= $this->get_renderer()->render($header);

        foreach ($notices as $notice) {
            $o .= $this->get_renderer()->notification($notice);
        }

        $url = new moodle_url('/mod/assign/view.php', array('id'=>$this->get_course_module()->id, 'action'=>'view'));
        $o .= $this->get_renderer()->continue_button($url);

        $o .= $this->view_footer();

        return $o;
    }

    
    public function fullname($user) {
        if ($this->is_blind_marking()) {
            $hasviewblind = has_capability('mod/assign:viewblinddetails', $this->get_context());
            if (empty($user->recordid)) {
                $uniqueid = $this->get_uniqueid_for_user($user->id);
            } else {
                $uniqueid = $user->recordid;
            }
            if ($hasviewblind) {
                return get_string('participant', 'assign') . ' ' . $uniqueid . ' (' . fullname($user) . ')';
            } else {
                return get_string('participant', 'assign') . ' ' . $uniqueid;
            }
        } else {
            return fullname($user);
        }
    }

    
    protected function view_edit_submission_page($mform, $notices) {
        global $CFG, $USER, $DB;

        $o = '';
        require_once($CFG->dirroot . '/mod/assign/submission_form.php');
                $userid = optional_param('userid', $USER->id, PARAM_INT);
        $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);

                        $returnparams = array('userid' => $userid, 'rownum' => 0, 'useridlistid' => 0);
        $this->register_return_link('editsubmission', $returnparams);

        if ($userid == $USER->id) {
            if (!$this->can_edit_submission($userid, $USER->id)) {
                print_error('nopermission');
            }
                        require_capability('mod/assign:submit', $this->context);
            $title = get_string('editsubmission', 'assign');
        } else {
                        if (!$this->can_edit_submission($userid, $USER->id)) {
                print_error('nopermission');
            }

            $name = $this->fullname($user);
            $title = get_string('editsubmissionother', 'assign', $name);
        }

        if (!$this->submissions_open($userid)) {
            $message = array(get_string('submissionsclosed', 'assign'));
            return $this->view_notices($title, $message);
        }

        $o .= $this->get_renderer()->render(new assign_header($this->get_instance(),
                                                      $this->get_context(),
                                                      $this->show_intro(),
                                                      $this->get_course_module()->id,
                                                      $title));
        if ($userid == $USER->id) {
                        $o .= $this->plagiarism_print_disclosure();
        }
        $data = new stdClass();
        $data->userid = $userid;
        if (!$mform) {
            $mform = new mod_assign_submission_form(null, array($this, $data));
        }

        foreach ($notices as $notice) {
            $o .= $this->get_renderer()->notification($notice);
        }

        $o .= $this->get_renderer()->render(new assign_form('editsubmissionform', $mform));

        $o .= $this->view_footer();

        \mod_assign\event\submission_form_viewed::create_from_user($this, $user)->trigger();

        return $o;
    }

    
    protected function is_graded($userid) {
        $grade = $this->get_user_grade($userid, false);
        if ($grade) {
            return ($grade->grade !== null && $grade->grade >= 0);
        }
        return false;
    }

    
    public function can_view_group_submission($groupid) {
        global $USER;

        $members = $this->get_submission_group_members($groupid, true);
        foreach ($members as $member) {
                        if ($this->can_view_submission($member->id)) {
                return true;
            }
        }
        return false;
    }

    
    public function can_view_submission($userid) {
        global $USER;

        if (!$this->is_active_user($userid) && !has_capability('moodle/course:viewsuspendedusers', $this->context)) {
            return false;
        }
        if (!is_enrolled($this->get_course_context(), $userid)) {
            return false;
        }
        if (has_any_capability(array('mod/assign:viewgrades', 'mod/assign:grade'), $this->context)) {
            return true;
        }
        if ($userid == $USER->id && has_capability('mod/assign:submit', $this->context)) {
            return true;
        }
        return false;
    }

    
    protected function view_plugin_grading_batch_operation($mform) {
        require_capability('mod/assign:grade', $this->context);
        $prefix = 'plugingradingbatchoperation_';

        if ($data = $mform->get_data()) {
            $tail = substr($data->operation, strlen($prefix));
            list($plugintype, $action) = explode('_', $tail, 2);

            $plugin = $this->get_feedback_plugin_by_type($plugintype);
            if ($plugin) {
                $users = $data->selectedusers;
                $userlist = explode(',', $users);
                echo $plugin->grading_batch_operation($action, $userlist);
                return;
            }
        }
        print_error('invalidformdata', '');
    }

    
    protected function process_grading_batch_operation(& $mform) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/gradingbatchoperationsform.php');
        require_sesskey();

        $markingallocation = $this->get_instance()->markingworkflow &&
            $this->get_instance()->markingallocation &&
            has_capability('mod/assign:manageallocations', $this->context);

        $batchformparams = array('cm'=>$this->get_course_module()->id,
                                 'submissiondrafts'=>$this->get_instance()->submissiondrafts,
                                 'duedate'=>$this->get_instance()->duedate,
                                 'attemptreopenmethod'=>$this->get_instance()->attemptreopenmethod,
                                 'feedbackplugins'=>$this->get_feedback_plugins(),
                                 'context'=>$this->get_context(),
                                 'markingworkflow'=>$this->get_instance()->markingworkflow,
                                 'markingallocation'=>$markingallocation);
        $formclasses = array('class'=>'gradingbatchoperationsform');
        $mform = new mod_assign_grading_batch_operations_form(null,
                                                              $batchformparams,
                                                              'post',
                                                              '',
                                                              $formclasses);

        if ($data = $mform->get_data()) {
                        $users = $data->selectedusers;
            $userlist = explode(',', $users);

            $prefix = 'plugingradingbatchoperation_';

            if ($data->operation == 'grantextension') {
                                $mform = null;
                return 'grantextension';
            } else if ($data->operation == 'setmarkingworkflowstate') {
                return 'viewbatchsetmarkingworkflowstate';
            } else if ($data->operation == 'setmarkingallocation') {
                return 'viewbatchmarkingallocation';
            } else if (strpos($data->operation, $prefix) === 0) {
                $tail = substr($data->operation, strlen($prefix));
                list($plugintype, $action) = explode('_', $tail, 2);

                $plugin = $this->get_feedback_plugin_by_type($plugintype);
                if ($plugin) {
                    return 'plugingradingbatchoperation';
                }
            }

            if ($data->operation == 'downloadselected') {
                $this->download_submissions($userlist);
            } else {
                $notice = '';
                foreach ($userlist as $userid) {
                    if ($data->operation == 'lock') {
                        $this->process_lock_submission($userid);
                    } else if ($data->operation == 'unlock') {
                        $this->process_unlock_submission($userid);
                    } else if ($data->operation == 'reverttodraft') {
                        $this->process_revert_to_draft($userid);
                    } else if ($data->operation == 'addattempt') {
                        if (!$this->get_instance()->teamsubmission) {
                            $this->process_add_attempt($userid);
                        }
                    } else if($data->operation == 'setpattern'){
                        
                        global $DB, $cm;
                        require_once($CFG->dirroot.'/mod/folder/lib.php');
                        $modplugins = get_plugin_list('mod');
                        if (array_key_exists("folder", $modplugins)){
                            $folder = $DB->get_record('folder', array('assign'=>$this->instance->id));
                                                        if(!$folder){                                $folder = new stdClass();
                                $folder->assign = $this->instance->id;
                                $folder->name = $this->get_instance()->name.'_'.get_string('pattern','assign');
                                $folder->course = $this->get_course()->id;
                                $folder->section = $cm->section;
                                $folder->intro = $this->get_instance()->name.'_'.get_string('pattern','assign');
                                $folder->introformat = FORMAT_HTML;
                                $folder->revision = 1;
                                $folder->files = null;
                                $folder->visible = 1;
                                
                                $folder->modulename = 'folder';
                                $modules_id = $DB->get_field('modules', 'id', array('name'=>'folder'));
                                $folder->module = $modules_id;
                                $folder->indent = 1;                                 $folder->idnumber = 'assign_'.$this->instance->id;
                                $folder->coursemodule = add_course_module($folder);
                                
                                $addinstancefunction    = "folder_add_instance";
                                try {
                                    $returnfromfunc = $addinstancefunction($folder);
                                } catch (moodle_exception $e) {
                                    $returnfromfunc = $e;
                                }
                                if (!$returnfromfunc or !is_number($returnfromfunc)) {
                                    $modcontext = context_module::instance($folder->coursemodule);
                                    delete_context(CONTEXT_MODULE, $folder->coursemodule);
                                    $DB->delete_records('course_modules', array('id'=>$folder->coursemodule));
                                    if ($e instanceof moodle_exception) {
                                        throw $e;
                                    } else if (!is_number($returnfromfunc)) {
                                        print_error('invalidfunction', '', course_get_url($this->get_course(), $cm->section));
                                    } else {
                                        print_error('cannotaddnewmodule', '', course_get_url($this->get_course(), $cm->section), $folder->modulename);
                                    }
                                }
                                
                                $folder->instance = $returnfromfunc;
                                $section_id = $DB->get_field('course_sections', 'section', array('id'=>$cm->section));
                                $sectionid = course_add_cm_to_section($this->get_course(), $folder->coursemodule, $section_id,null,$cm->id);
                                $cmid = $folder->coursemodule;
                            }else{
                            
                                $sql = "SELECT cm.id FROM {folder} f
                                        JOIN {course_modules} cm ON f.id = cm.instance AND cm.course =:course_id
                                        JOIN {modules} m ON m.name ='folder' AND m.id = cm.module
                                        WHERE f.id =:folder_id";
                                $cmid = $DB->get_field_sql($sql, array('folder_id'=>$folder->id, 'course_id'=>$this->get_course()->id));
                            }
                            
                            $notice .= $this->process_pattern_submission($userid,$cmid);
                        }
                    } else if($data->operation == 'cancelpattern'){
                        global $DB;
                        require_once($CFG->dirroot.'/mod/folder/lib.php');
                        $modplugins = get_plugin_list('mod');
                        if (array_key_exists("folder", $modplugins)){
                            $folder = $DB->get_record('folder', array('assign'=>$this->instance->id));
                            if(!empty($folder)){
                                $sql = "SELECT cm.id FROM {folder} f
                                            JOIN {course_modules} cm ON f.id = cm.instance AND cm.course =:course_id
                                            JOIN {modules} m ON m.name ='folder' AND m.id = cm.module
                                            WHERE f.id =:folder_id";
                                $cmid = $DB->get_field_sql($sql, array('folder_id'=>$folder->id, 'course_id'=>$this->get_course()->id));
                                $notice .=  $this->process_pattern_cancel($userid, $cmid);
                            }
                        }
                    }
                }
            }
            
            if(!empty($notice)){
                notice($notice,"view.php?action=grading&id=".$this->context->instanceid);
            }
            if ($this->get_instance()->teamsubmission && $data->operation == 'addattempt') {
                                $this->process_add_attempt_group($userlist);
            }
        }

        return 'grading';
    }

    
    protected function view_batch_set_workflow_state($mform) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/assign/batchsetmarkingworkflowstateform.php');

        $o = '';

        $submitteddata = $mform->get_data();
        $users = $submitteddata->selectedusers;
        $userlist = explode(',', $users);

        $formdata = array('id' => $this->get_course_module()->id,
                          'selectedusers' => $users);

        $usershtml = '';

        $usercount = 0;
        $extrauserfields = get_extra_user_fields($this->get_context());
        foreach ($userlist as $userid) {
            if ($usercount >= 5) {
                $usershtml .= get_string('moreusers', 'assign', count($userlist) - 5);
                break;
            }
            $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);

            $usershtml .= $this->get_renderer()->render(new assign_user_summary($user,
                                                                $this->get_course()->id,
                                                                has_capability('moodle/site:viewfullnames',
                                                                $this->get_course_context()),
                                                                $this->is_blind_marking(),
                                                                $this->get_uniqueid_for_user($user->id),
                                                                $extrauserfields,
                                                                !$this->is_active_user($userid)));
            $usercount += 1;
        }

        $formparams = array(
            'userscount' => count($userlist),
            'usershtml' => $usershtml,
            'markingworkflowstates' => $this->get_marking_workflow_states_for_current_user()
        );

        $mform = new mod_assign_batch_set_marking_workflow_state_form(null, $formparams);
        $mform->set_data($formdata);            $header = new assign_header($this->get_instance(),
            $this->get_context(),
            $this->show_intro(),
            $this->get_course_module()->id,
            get_string('setmarkingworkflowstate', 'assign'));
        $o .= $this->get_renderer()->render($header);
        $o .= $this->get_renderer()->render(new assign_form('setworkflowstate', $mform));
        $o .= $this->view_footer();

        \mod_assign\event\batch_set_workflow_state_viewed::create_from_assign($this)->trigger();

        return $o;
    }

    
    public function view_batch_markingallocation($mform) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/assign/batchsetallocatedmarkerform.php');

        $o = '';

        $submitteddata = $mform->get_data();
        $users = $submitteddata->selectedusers;
        $userlist = explode(',', $users);

        $formdata = array('id' => $this->get_course_module()->id,
                          'selectedusers' => $users);

        $usershtml = '';

        $usercount = 0;
        $extrauserfields = get_extra_user_fields($this->get_context());
        foreach ($userlist as $userid) {
            if ($usercount >= 5) {
                $usershtml .= get_string('moreusers', 'assign', count($userlist) - 5);
                break;
            }
            $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);

            $usershtml .= $this->get_renderer()->render(new assign_user_summary($user,
                $this->get_course()->id,
                has_capability('moodle/site:viewfullnames',
                $this->get_course_context()),
                $this->is_blind_marking(),
                $this->get_uniqueid_for_user($user->id),
                $extrauserfields,
                !$this->is_active_user($userid)));
            $usercount += 1;
        }

        $formparams = array(
            'userscount' => count($userlist),
            'usershtml' => $usershtml,
        );

        list($sort, $params) = users_order_by_sql();
        $markers = get_users_by_capability($this->get_context(), 'mod/assign:grade', '', $sort);
        $markerlist = array();
        foreach ($markers as $marker) {
            $markerlist[$marker->id] = fullname($marker);
        }

        $formparams['markers'] = $markerlist;

        $mform = new mod_assign_batch_set_allocatedmarker_form(null, $formparams);
        $mform->set_data($formdata);            $header = new assign_header($this->get_instance(),
            $this->get_context(),
            $this->show_intro(),
            $this->get_course_module()->id,
            get_string('setmarkingallocation', 'assign'));
        $o .= $this->get_renderer()->render($header);
        $o .= $this->get_renderer()->render(new assign_form('setworkflowstate', $mform));
        $o .= $this->view_footer();

        \mod_assign\event\batch_set_marker_allocation_viewed::create_from_assign($this)->trigger();

        return $o;
    }

    
    protected function check_submit_for_grading($mform) {
        global $USER, $CFG;

        require_once($CFG->dirroot . '/mod/assign/submissionconfirmform.php');

                $notifications = array();
        $submission = $this->get_user_submission($USER->id, false);
        $plugins = $this->get_submission_plugins();
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $check = $plugin->precheck_submission($submission);
                if ($check !== true) {
                    $notifications[] = $check;
                }
            }
        }

        $data = new stdClass();
        $adminconfig = $this->get_admin_config();
        $requiresubmissionstatement = $this->get_instance()->requiresubmissionstatement &&
                                       !empty($adminconfig->submissionstatement);

        $submissionstatement = '';
        if (!empty($adminconfig->submissionstatement)) {
                                    $options = array(
                'context' => $this->get_context(),
                'para' => false
            );
            $submissionstatement = format_text($adminconfig->submissionstatement, FORMAT_MOODLE, $options);
        }

        if ($mform == null) {
            $mform = new mod_assign_confirm_submission_form(null, array($requiresubmissionstatement,
                                                                        $submissionstatement,
                                                                        $this->get_course_module()->id,
                                                                        $data));
        }
        $o = '';
        $o .= $this->get_renderer()->header();
        $submitforgradingpage = new assign_submit_for_grading_page($notifications,
                                                                   $this->get_course_module()->id,
                                                                   $mform);
        $o .= $this->get_renderer()->render($submitforgradingpage);
        $o .= $this->view_footer();

        \mod_assign\event\submission_confirmation_form_viewed::create_from_assign($this)->trigger();

        return $o;
    }

    
    public function get_assign_submission_status_renderable($user, $showlinks) {
        global $PAGE;

        $instance = $this->get_instance();
        $flags = $this->get_user_flags($user->id, false);
        $submission = $this->get_user_submission($user->id, false);

        $teamsubmission = null;
        $submissiongroup = null;
        $notsubmitted = array();
        if ($instance->teamsubmission) {
            $teamsubmission = $this->get_group_submission($user->id, 0, false);
            $submissiongroup = $this->get_submission_group($user->id);
            $groupid = 0;
            if ($submissiongroup) {
                $groupid = $submissiongroup->id;
            }
            $notsubmitted = $this->get_submission_group_members_who_have_not_submitted($groupid, false);
        }

        $showedit = $showlinks &&
                    ($this->is_any_submission_plugin_enabled()) &&
                    $this->can_edit_submission($user->id);

        $gradelocked = ($flags && $flags->locked) || $this->grading_disabled($user->id, false);

                $gradingmanager = get_grading_manager($this->context, 'mod_assign', 'submissions');
        $gradingcontrollerpreview = '';
        if ($gradingmethod = $gradingmanager->get_active_method()) {
            $controller = $gradingmanager->get_controller($gradingmethod);
            if ($controller->is_form_defined()) {
                $gradingcontrollerpreview = $controller->render_preview($PAGE);
            }
        }

        $showsubmit = ($showlinks && $this->submissions_open($user->id));
        $showsubmit = ($showsubmit && $this->show_submit_button($submission, $teamsubmission, $user->id));

        $extensionduedate = null;
        if ($flags) {
            $extensionduedate = $flags->extensionduedate;
        }
        $viewfullnames = has_capability('moodle/site:viewfullnames', $this->get_course_context());

        $gradingstatus = $this->get_grading_status($user->id);
        $usergroups = $this->get_all_groups($user->id);
        $submissionstatus = new assign_submission_status($instance->allowsubmissionsfromdate,
                                                          $instance->alwaysshowdescription,
                                                          $submission,
                                                          $instance->teamsubmission,
                                                          $teamsubmission,
                                                          $submissiongroup,
                                                          $notsubmitted,
                                                          $this->is_any_submission_plugin_enabled(),
                                                          $gradelocked,
                                                          $this->is_graded($user->id),
                                                          $instance->duedate,
                                                          $instance->cutoffdate,
                                                          $this->get_submission_plugins(),
                                                          $this->get_return_action(),
                                                          $this->get_return_params(),
                                                          $this->get_course_module()->id,
                                                          $this->get_course()->id,
                                                          assign_submission_status::STUDENT_VIEW,
                                                          $showedit,
                                                          $showsubmit,
                                                          $viewfullnames,
                                                          $extensionduedate,
                                                          $this->get_context(),
                                                          $this->is_blind_marking(),
                                                          $gradingcontrollerpreview,
                                                          $instance->attemptreopenmethod,
                                                          $instance->maxattempts,
                                                          $gradingstatus,
                                                          $instance->preventsubmissionnotingroup,
                                                          $usergroups);
        return $submissionstatus;
    }


    
    public function get_assign_feedback_status_renderable($user) {
        global $CFG, $DB, $PAGE;

        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->dirroot.'/grade/grading/lib.php');

        $instance = $this->get_instance();
        $grade = $this->get_user_grade($user->id, false);
        $gradingstatus = $this->get_grading_status($user->id);

        $gradinginfo = grade_get_grades($this->get_course()->id,
                                    'mod',
                                    'assign',
                                    $instance->id,
                                    $user->id);

        $gradingitem = null;
        $gradebookgrade = null;
        if (isset($gradinginfo->items[0])) {
            $gradingitem = $gradinginfo->items[0];
            $gradebookgrade = $gradingitem->grades[$user->id];
        }

                $emptyplugins = true;
        if ($grade) {
            foreach ($this->get_feedback_plugins() as $plugin) {
                if ($plugin->is_visible() && $plugin->is_enabled()) {
                    if (!$plugin->is_empty($grade)) {
                        $emptyplugins = false;
                    }
                }
            }
        }

        if ($this->get_instance()->markingworkflow && $gradingstatus != ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
            $emptyplugins = true;         }

        $cangrade = has_capability('mod/assign:grade', $this->get_context());
                if (!is_null($gradebookgrade) && (!is_null($gradebookgrade->grade) || !$emptyplugins)
                && ($cangrade || !$gradebookgrade->hidden)) {

            $gradefordisplay = null;
            $gradeddate = null;
            $grader = null;
            $gradingmanager = get_grading_manager($this->get_context(), 'mod_assign', 'submissions');

                        if (!is_null($gradebookgrade->grade) && ($cangrade || !$gradebookgrade->hidden)) {
                if ($controller = $gradingmanager->get_active_controller()) {
                    $menu = make_grades_menu($this->get_instance()->grade);
                    $controller->set_grade_range($menu, $this->get_instance()->grade > 0);
                    $gradefordisplay = $controller->render_grade($PAGE,
                                                                 $grade->id,
                                                                 $gradingitem,
                                                                 $gradebookgrade->str_long_grade,
                                                                 $cangrade);
                } else {
                    $gradefordisplay = $this->display_grade($gradebookgrade->grade, false);
                }
                $gradeddate = $gradebookgrade->dategraded;
                if (isset($grade->grader)) {
                    $grader = $DB->get_record('user', array('id' => $grade->grader));
                }
            }

            $feedbackstatus = new assign_feedback_status($gradefordisplay,
                                                  $gradeddate,
                                                  $grader,
                                                  $this->get_feedback_plugins(),
                                                  $grade,
                                                  $this->get_course_module()->id,
                                                  $this->get_return_action(),
                                                  $this->get_return_params());
            return $feedbackstatus;
        }
        return;
    }

    
    public function get_assign_attempt_history_renderable($user) {

        $allsubmissions = $this->get_all_submissions($user->id);
        $allgrades = $this->get_all_grades($user->id);

        $history = new assign_attempt_history($allsubmissions,
                                              $allgrades,
                                              $this->get_submission_plugins(),
                                              $this->get_feedback_plugins(),
                                              $this->get_course_module()->id,
                                              $this->get_return_action(),
                                              $this->get_return_params(),
                                              false,
                                              0,
                                              0);
        return $history;
    }

    
    public function view_student_summary($user, $showlinks) {

        $o = '';

        if ($this->can_view_submission($user->id)) {

            if (has_capability('mod/assign:submit', $this->get_context(), $user, false)) {
                $submissionstatus = $this->get_assign_submission_status_renderable($user, $showlinks);
                $o .= $this->get_renderer()->render($submissionstatus);
            }

                        $feedbackstatus = $this->get_assign_feedback_status_renderable($user);
            if ($feedbackstatus) {
                $o .= $this->get_renderer()->render($feedbackstatus);
            }

                        $history = $this->get_assign_attempt_history_renderable($user);
            if (count($history->submissions) > 1) {
                $o .= $this->get_renderer()->render($history);
            }
        }
        return $o;
    }

    
    protected function show_submit_button($submission = null, $teamsubmission = null, $userid = null) {
        if ($teamsubmission) {
            if ($teamsubmission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                                return false;
            } else if ($this->submission_empty($teamsubmission)) {
                                return false;
            } else if ($submission && $submission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                                return false;
            } else if (
                !empty($this->get_instance()->preventsubmissionnotingroup)
                && $this->get_submission_group($userid) == false
            ) {
                return false;
            }
        } else if ($submission) {
            if ($submission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                                return false;
            } else if ($this->submission_empty($submission)) {
                                return false;
            }
        } else {
                        return false;
        }
                return $this->get_instance()->submissiondrafts;
    }

    
    protected function get_all_grades($userid) {
        global $DB, $USER, $PAGE;

                if (!$userid) {
            $userid = $USER->id;
        }

        $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid);

        $grades = $DB->get_records('assign_grades', $params, 'attemptnumber ASC');

        $gradercache = array();
        $cangrade = has_capability('mod/assign:grade', $this->get_context());

                $gradingmanager = get_grading_manager($this->get_context(), 'mod_assign', 'submissions');
        $controller = $gradingmanager->get_active_controller();

        $gradinginfo = grade_get_grades($this->get_course()->id,
                                        'mod',
                                        'assign',
                                        $this->get_instance()->id,
                                        $userid);

        $gradingitem = null;
        if (isset($gradinginfo->items[0])) {
            $gradingitem = $gradinginfo->items[0];
        }

        foreach ($grades as $grade) {
                        if (isset($gradercache[$grade->grader])) {
                $grade->grader = $gradercache[$grade->grader];
            } else {
                                $grade->grader = $DB->get_record('user', array('id'=>$grade->grader));
                $gradercache[$grade->grader->id] = $grade->grader;
            }

                        if ($controller) {
                $controller->set_grade_range(make_grades_menu($this->get_instance()->grade), $this->get_instance()->grade > 0);
                $grade->gradefordisplay = $controller->render_grade($PAGE,
                                                                     $grade->id,
                                                                     $gradingitem,
                                                                     $grade->grade,
                                                                     $cangrade);
            } else {
                $grade->gradefordisplay = $this->display_grade($grade->grade, false);
            }

        }

        return $grades;
    }

    
    protected function get_all_submissions($userid) {
        global $DB, $USER;

                if (!$userid) {
            $userid = $USER->id;
        }

        $params = array();

        if ($this->get_instance()->teamsubmission) {
            $groupid = 0;
            $group = $this->get_submission_group($userid);
            if ($group) {
                $groupid = $group->id;
            }

                        $params = array('assignment'=>$this->get_instance()->id, 'groupid'=>$groupid, 'userid'=>0);
        } else {
                        $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid);
        }

                $submissions = $DB->get_records('assign_submission', $params, 'attemptnumber ASC');

        return $submissions;
    }

    
    public function get_assign_grading_summary_renderable() {

        $instance = $this->get_instance();

        $draft = ASSIGN_SUBMISSION_STATUS_DRAFT;
        $submitted = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

        $activitygroup = groups_get_activity_group($this->get_course_module());

        if ($instance->teamsubmission) {
            $defaultteammembers = $this->get_submission_group_members(0, true);
            $warnofungroupedusers = (count($defaultteammembers) > 0 && $instance->preventsubmissionnotingroup);

            $summary = new assign_grading_summary($this->count_teams($activitygroup),
                                                  $instance->submissiondrafts,
                                                  $this->count_submissions_with_status($draft),
                                                  $this->is_any_submission_plugin_enabled(),
                                                  $this->count_submissions_with_status($submitted),
                                                  $instance->cutoffdate,
                                                  $instance->duedate,
                                                  $this->get_course_module()->id,
                                                  $this->count_submissions_need_grading(),
                                                  $instance->teamsubmission,
                                                  $warnofungroupedusers);
        } else {
                        $countparticipants = $this->count_participants($activitygroup);
            $summary = new assign_grading_summary($countparticipants,
                                                  $instance->submissiondrafts,
                                                  $this->count_submissions_with_status($draft),
                                                  $this->is_any_submission_plugin_enabled(),
                                                  $this->count_submissions_with_status($submitted),
                                                  $instance->cutoffdate,
                                                  $instance->duedate,
                                                  $this->get_course_module()->id,
                                                  $this->count_submissions_need_grading(),
                                                  $instance->teamsubmission,
                                                  false);

        }

        return $summary;
    }

    
    protected function view_submission_page() {
        global $CFG, $DB, $USER, $PAGE;

        $instance = $this->get_instance();

        $o = '';

        $postfix = '';
        if ($this->has_visible_attachments()) {
            $postfix = $this->render_area_files('mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA, 0);
        }
        $o .= $this->get_renderer()->render(new assign_header($instance,
                                                      $this->get_context(),
                                                      $this->show_intro(),
                                                      $this->get_course_module()->id,
                                                      '', '', $postfix));

                $plugins = array_merge($this->get_submission_plugins(), $this->get_feedback_plugins());
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $o .= $this->get_renderer()->render(new assign_plugin_header($plugin));
            }
        }

        if ($this->can_view_grades()) {
                        $currenturl = new moodle_url('/mod/assign/view.php', array('id' => $this->get_course_module()->id));
            $o .= groups_print_activity_menu($this->get_course_module(), $currenturl->out(), true);

            $summary = $this->get_assign_grading_summary_renderable();
            $o .= $this->get_renderer()->render($summary);
        }
        $grade = $this->get_user_grade($USER->id, false);
        $submission = $this->get_user_submission($USER->id, false);

        if ($this->can_view_submission($USER->id)) {
            $o .= $this->view_student_summary($USER, true);
        }

        $o .= $this->view_footer();

        \mod_assign\event\submission_status_viewed::create_from_assign($this)->trigger();

        return $o;
    }

    
    protected function convert_grade_for_gradebook(stdClass $grade) {
        $gradebookgrade = array();
        if ($grade->grade >= 0) {
            $gradebookgrade['rawgrade'] = $grade->grade;
        }
                if ($grade->grade == -1) {
            $gradebookgrade['rawgrade'] = NULL;
        }
        $gradebookgrade['userid'] = $grade->userid;
        $gradebookgrade['usermodified'] = $grade->grader;
        $gradebookgrade['datesubmitted'] = null;
        $gradebookgrade['dategraded'] = $grade->timemodified;
        if (isset($grade->feedbackformat)) {
            $gradebookgrade['feedbackformat'] = $grade->feedbackformat;
        }
        if (isset($grade->feedbacktext)) {
            $gradebookgrade['feedback'] = $grade->feedbacktext;
        }

        return $gradebookgrade;
    }

    
    protected function convert_submission_for_gradebook(stdClass $submission) {
        $gradebookgrade = array();

        $gradebookgrade['userid'] = $submission->userid;
        $gradebookgrade['usermodified'] = $submission->userid;
        $gradebookgrade['datesubmitted'] = $submission->timemodified;

        return $gradebookgrade;
    }

    
    protected function gradebook_item_update($submission=null, $grade=null) {
        global $CFG;

        require_once($CFG->dirroot.'/mod/assign/lib.php');
                        if ($this->is_blind_marking()) {
            return false;
        }

                if ($this->get_instance()->markingworkflow && !empty($grade) &&
                $this->get_grading_status($grade->userid) != ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                        $grade->grade = -1;
            $grade->feedbacktext = '';
        }

        if ($submission != null) {
            if ($submission->userid == 0) {
                                $team = groups_get_members($submission->groupid, 'u.id');

                foreach ($team as $member) {
                    $membersubmission = clone $submission;
                    $membersubmission->groupid = 0;
                    $membersubmission->userid = $member->id;
                    $this->gradebook_item_update($membersubmission, null);
                }
                return;
            }

            $gradebookgrade = $this->convert_submission_for_gradebook($submission);

        } else {
            $gradebookgrade = $this->convert_grade_for_gradebook($grade);
        }
                if ($this->grading_disabled($gradebookgrade['userid'])) {
            return false;
        }
        $assign = clone $this->get_instance();
        $assign->cmidnumber = $this->get_course_module()->idnumber;
                $assign->gradefeedbackenabled = $this->is_gradebook_feedback_enabled();
        return assign_grade_item_update($assign, $gradebookgrade) == GRADE_UPDATE_OK;
    }

    
    protected function update_team_submission(stdClass $submission, $userid, $updatetime) {
        global $DB;

        if ($updatetime) {
            $submission->timemodified = time();
        }

                $mysubmission = $this->get_user_submission($userid, true, $submission->attemptnumber);
        $mysubmission->status = $submission->status;

        $this->update_submission($mysubmission, 0, $updatetime, false);

                $team = $this->get_submission_group_members($submission->groupid, true);

        $allsubmitted = true;
        $anysubmitted = false;
        $result = true;
        if ($submission->status != ASSIGN_SUBMISSION_STATUS_REOPENED) {
            foreach ($team as $member) {
                $membersubmission = $this->get_user_submission($member->id, false, $submission->attemptnumber);

                                if (!$membersubmission || $membersubmission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED
                        && ($this->is_active_user($member->id))) {
                    $allsubmitted = false;
                    if ($anysubmitted) {
                        break;
                    }
                } else {
                    $anysubmitted = true;
                }
            }
            if ($this->get_instance()->requireallteammemberssubmit) {
                if ($allsubmitted) {
                    $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
                } else {
                    $submission->status = ASSIGN_SUBMISSION_STATUS_DRAFT;
                }
                $result = $DB->update_record('assign_submission', $submission);
            } else {
                if ($anysubmitted) {
                    $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
                } else {
                    $submission->status = ASSIGN_SUBMISSION_STATUS_DRAFT;
                }
                $result = $DB->update_record('assign_submission', $submission);
            }
        } else {
                        foreach ($team as $member) {
                $membersubmission = $this->get_user_submission($member->id, true, $submission->attemptnumber);
                $membersubmission->status = ASSIGN_SUBMISSION_STATUS_REOPENED;
                $result = $DB->update_record('assign_submission', $membersubmission) && $result;
            }
            $result = $DB->update_record('assign_submission', $submission) && $result;
        }

        $this->gradebook_item_update($submission);
        return $result;
    }

    
    protected function update_submission(stdClass $submission, $userid, $updatetime, $teamsubmission) {
        global $DB;

        if ($teamsubmission) {
            return $this->update_team_submission($submission, $userid, $updatetime);
        }

        if ($updatetime) {
            $submission->timemodified = time();
        }
        $result= $DB->update_record('assign_submission', $submission);
        if ($result) {
            $this->gradebook_item_update($submission);
        }
        return $result;
    }

    
    public function submissions_open($userid = 0,
                                     $skipenrolled = false,
                                     $submission = false,
                                     $flags = false,
                                     $gradinginfo = false) {
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $time = time();
        $dateopen = true;
        $finaldate = false;
        if ($this->get_instance()->cutoffdate) {
            $finaldate = $this->get_instance()->cutoffdate;
        }

        if ($flags === false) {
            $flags = $this->get_user_flags($userid, false);
        }
        if ($flags && $flags->locked) {
            return false;
        }

                if ($finaldate) {
            if ($flags && $flags->extensionduedate) {
                                if ($flags->extensionduedate > $finaldate) {
                    $finaldate = $flags->extensionduedate;
                }
            }
        }

        if ($finaldate) {
            $dateopen = ($this->get_instance()->allowsubmissionsfromdate <= $time && $time <= $finaldate);
        } else {
            $dateopen = ($this->get_instance()->allowsubmissionsfromdate <= $time);
        }

        if (!$dateopen) {
            return false;
        }

                if (!$skipenrolled && !is_enrolled($this->get_course_context(), $userid)) {
            return false;
        }
                if ($submission === false) {
            if ($this->get_instance()->teamsubmission) {
                $submission = $this->get_group_submission($userid, 0, false);
            } else {
                $submission = $this->get_user_submission($userid, false);
            }
        }
        if ($submission) {

            if ($this->get_instance()->submissiondrafts && $submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                                return false;
            }
        }

                if ($gradinginfo === false) {
            $gradinginfo = grade_get_grades($this->get_course()->id,
                                            'mod',
                                            'assign',
                                            $this->get_instance()->id,
                                            array($userid));
        }
        if ($gradinginfo &&
                isset($gradinginfo->items[0]->grades[$userid]) &&
                $gradinginfo->items[0]->grades[$userid]->locked) {
            return false;
        }

        return true;
    }

    
    public function render_area_files($component, $area, $submissionid) {
        global $USER;

        return $this->get_renderer()->assign_files($this->context, $submissionid, $area, $component);

    }

    
    public function can_edit_submission($userid, $graderid = 0) {
        global $USER;

        if (empty($graderid)) {
            $graderid = $USER->id;
        }

        $instance = $this->get_instance();
        if ($userid == $graderid &&
            $instance->teamsubmission &&
            $instance->preventsubmissionnotingroup &&
            $this->get_submission_group($userid) == false) {
            return false;
        }

        if ($userid == $graderid &&
                $this->submissions_open($userid) &&
                has_capability('mod/assign:submit', $this->context, $graderid)) {
                        return true;
        }

        if (!has_capability('mod/assign:editothersubmission', $this->context, $graderid)) {
            return false;
        }

        $cm = $this->get_course_module();
        if (groups_get_activity_groupmode($cm) == SEPARATEGROUPS) {
            $sharedgroupmembers = $this->get_shared_group_members($cm, $graderid);
            return in_array($userid, $sharedgroupmembers);
        }
        return true;
    }

    
    public function get_shared_group_members($cm, $userid) {
        if (!isset($this->sharedgroupmembers[$userid])) {
            $this->sharedgroupmembers[$userid] = array();
            $groupsids = array_keys(groups_get_activity_allowed_groups($cm, $userid));
            foreach ($groupsids as $groupid) {
                $members = array_keys(groups_get_members($groupid, 'u.id'));
                $this->sharedgroupmembers[$userid] = array_merge($this->sharedgroupmembers[$userid], $members);
            }
        }

        return $this->sharedgroupmembers[$userid];
    }

    
    protected function get_graders($userid) {
                $potentialgraders = get_enrolled_users($this->context, "mod/assign:grade", null, 'u.*', null, null, null, true);

        $graders = array();
        if (groups_get_activity_groupmode($this->get_course_module()) == SEPARATEGROUPS) {
            if ($groups = groups_get_all_groups($this->get_course()->id, $userid, $this->get_course_module()->groupingid)) {
                foreach ($groups as $group) {
                    foreach ($potentialgraders as $grader) {
                        if ($grader->id == $userid) {
                                                        continue;
                        }
                        if (groups_is_member($group->id, $grader->id)) {
                            $graders[$grader->id] = $grader;
                        }
                    }
                }
            } else {
                                foreach ($potentialgraders as $grader) {
                    if ($grader->id == $userid) {
                                                continue;
                    }
                    if (!groups_has_membership($this->get_course_module(), $grader->id)) {
                        $graders[$grader->id] = $grader;
                    }
                }
            }
        } else {
            foreach ($potentialgraders as $grader) {
                if ($grader->id == $userid) {
                                        continue;
                }
                                if (is_enrolled($this->get_course_context(), $grader->id)) {
                    $graders[$grader->id] = $grader;
                }
            }
        }
        return $graders;
    }

    
    protected function get_notifiable_users($userid) {
                $potentialusers = get_enrolled_users($this->context, "mod/assign:receivegradernotifications",
                                             null, 'u.*', null, null, null, true);

        $notifiableusers = array();
        if (groups_get_activity_groupmode($this->get_course_module()) == SEPARATEGROUPS) {
            if ($groups = groups_get_all_groups($this->get_course()->id, $userid, $this->get_course_module()->groupingid)) {
                foreach ($groups as $group) {
                    foreach ($potentialusers as $potentialuser) {
                        if ($potentialuser->id == $userid) {
                                                        continue;
                        }
                        if (groups_is_member($group->id, $potentialuser->id)) {
                            $notifiableusers[$potentialuser->id] = $potentialuser;
                        }
                    }
                }
            } else {
                                foreach ($potentialusers as $potentialuser) {
                    if ($potentialuser->id == $userid) {
                                                continue;
                    }
                    if (!groups_has_membership($this->get_course_module(), $potentialuser->id)) {
                        $notifiableusers[$potentialuser->id] = $potentialuser;
                    }
                }
            }
        } else {
            foreach ($potentialusers as $potentialuser) {
                if ($potentialuser->id == $userid) {
                                        continue;
                }
                                if (is_enrolled($this->get_course_context(), $potentialuser->id)) {
                    $notifiableusers[$potentialuser->id] = $potentialuser;
                }
            }
        }
        return $notifiableusers;
    }

    
    protected static function format_notification_message_text($messagetype,
                                                             $info,
                                                             $course,
                                                             $context,
                                                             $modulename,
                                                             $assignmentname) {
        $formatparams = array('context' => $context->get_course_context());
        $posttext  = format_string($course->shortname, true, $formatparams) .
                     ' -> ' .
                     $modulename .
                     ' -> ' .
                     format_string($assignmentname, true, $formatparams) . "\n";
        $posttext .= '---------------------------------------------------------------------' . "\n";
        $posttext .= get_string($messagetype . 'text', 'assign', $info)."\n";
        $posttext .= "\n---------------------------------------------------------------------\n";
        return $posttext;
    }

    
    protected static function format_notification_message_html($messagetype,
                                                             $info,
                                                             $course,
                                                             $context,
                                                             $modulename,
                                                             $coursemodule,
                                                             $assignmentname) {
        global $CFG;
        $formatparams = array('context' => $context->get_course_context());
        $posthtml  = '<p><font face="sans-serif">' .
                     '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' .
                     format_string($course->shortname, true, $formatparams) .
                     '</a> ->' .
                     '<a href="' . $CFG->wwwroot . '/mod/assign/index.php?id=' . $course->id . '">' .
                     $modulename .
                     '</a> ->' .
                     '<a href="' . $CFG->wwwroot . '/mod/assign/view.php?id=' . $coursemodule->id . '">' .
                     format_string($assignmentname, true, $formatparams) .
                     '</a></font></p>';
        $posthtml .= '<hr /><font face="sans-serif">';
        $posthtml .= '<p>' . get_string($messagetype . 'html', 'assign', $info) . '</p>';
        $posthtml .= '</font><hr />';
        return $posthtml;
    }

    
    public static function send_assignment_notification($userfrom,
                                                        $userto,
                                                        $messagetype,
                                                        $eventtype,
                                                        $updatetime,
                                                        $coursemodule,
                                                        $context,
                                                        $course,
                                                        $modulename,
                                                        $assignmentname,
                                                        $blindmarking,
                                                        $uniqueidforuser) {
        global $CFG;

        $info = new stdClass();
        if ($blindmarking) {
            $userfrom = clone($userfrom);
            $info->username = get_string('participant', 'assign') . ' ' . $uniqueidforuser;
            $userfrom->firstname = get_string('participant', 'assign');
            $userfrom->lastname = $uniqueidforuser;
            $userfrom->email = $CFG->noreplyaddress;
        } else {
            $info->username = fullname($userfrom, true);
        }
        $info->assignment = format_string($assignmentname, true, array('context'=>$context));
        $info->url = $CFG->wwwroot.'/mod/assign/view.php?id='.$coursemodule->id;
        $info->timeupdated = userdate($updatetime, get_string('strftimerecentfull'));

        $postsubject = get_string($messagetype . 'small', 'assign', $info);
        $posttext = self::format_notification_message_text($messagetype,
                                                           $info,
                                                           $course,
                                                           $context,
                                                           $modulename,
                                                           $assignmentname);
        $posthtml = '';
        if ($userto->mailformat == 1) {
            $posthtml = self::format_notification_message_html($messagetype,
                                                               $info,
                                                               $course,
                                                               $context,
                                                               $modulename,
                                                               $coursemodule,
                                                               $assignmentname);
        }

        $eventdata = new stdClass();
        $eventdata->modulename       = 'assign';
        $eventdata->userfrom         = $userfrom;
        $eventdata->userto           = $userto;
        $eventdata->subject          = $postsubject;
        $eventdata->fullmessage      = $posttext;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml  = $posthtml;
        $eventdata->smallmessage     = $postsubject;

        $eventdata->name            = $eventtype;
        $eventdata->component       = 'mod_assign';
        $eventdata->notification    = 1;
        $eventdata->contexturl      = $info->url;
        $eventdata->contexturlname  = $info->assignment;

        message_send($eventdata);
    }

    
    public function send_notification($userfrom, $userto, $messagetype, $eventtype, $updatetime) {
        global $USER;
        $userid = core_user::is_real_user($userfrom->id) ? $userfrom->id : $USER->id;
        $uniqueid = $this->get_uniqueid_for_user($userid);
        self::send_assignment_notification($userfrom,
                                           $userto,
                                           $messagetype,
                                           $eventtype,
                                           $updatetime,
                                           $this->get_course_module(),
                                           $this->get_context(),
                                           $this->get_course(),
                                           $this->get_module_name(),
                                           $this->get_instance()->name,
                                           $this->is_blind_marking(),
                                           $uniqueid);
    }

    
    protected function notify_student_submission_copied(stdClass $submission) {
        global $DB, $USER;

        $adminconfig = $this->get_admin_config();
                if (empty($adminconfig->submissionreceipts)) {
                        return;
        }
        if ($submission->userid) {
            $user = $DB->get_record('user', array('id'=>$submission->userid), '*', MUST_EXIST);
        } else {
            $user = $USER;
        }
        $this->send_notification($user,
                                 $user,
                                 'submissioncopied',
                                 'assign_notification',
                                 $submission->timemodified);
    }
    
    protected function notify_student_submission_receipt(stdClass $submission) {
        global $DB, $USER;

        $adminconfig = $this->get_admin_config();
        if (empty($adminconfig->submissionreceipts)) {
                        return;
        }
        if ($submission->userid) {
            $user = $DB->get_record('user', array('id'=>$submission->userid), '*', MUST_EXIST);
        } else {
            $user = $USER;
        }
        if ($submission->userid == $USER->id) {
            $this->send_notification(core_user::get_noreply_user(),
                                     $user,
                                     'submissionreceipt',
                                     'assign_notification',
                                     $submission->timemodified);
        } else {
            $this->send_notification($USER,
                                     $user,
                                     'submissionreceiptother',
                                     'assign_notification',
                                     $submission->timemodified);
        }
    }

    
    protected function notify_graders(stdClass $submission) {
        global $DB, $USER;

        $instance = $this->get_instance();

        $late = $instance->duedate && ($instance->duedate < time());

        if (!$instance->sendnotifications && !($late && $instance->sendlatenotifications)) {
                        return;
        }

        if ($submission->userid) {
            $user = $DB->get_record('user', array('id'=>$submission->userid), '*', MUST_EXIST);
        } else {
            $user = $USER;
        }

        if ($notifyusers = $this->get_notifiable_users($user->id)) {
            foreach ($notifyusers as $notifyuser) {
                $this->send_notification($user,
                                         $notifyuser,
                                         'gradersubmissionupdated',
                                         'assign_notification',
                                         $submission->timemodified);
            }
        }
    }

    
    public function submit_for_grading($data, $notices) {
        global $USER;

        $userid = $USER->id;
        if (!empty($data->userid)) {
            $userid = $data->userid;
        }
                if ($userid == $USER->id) {
            require_capability('mod/assign:submit', $this->context);
        } else {
            if (!$this->can_edit_submission($userid, $USER->id)) {
                print_error('nopermission');
            }
        }

        $instance = $this->get_instance();

        if ($instance->teamsubmission) {
            $submission = $this->get_group_submission($userid, 0, true);
        } else {
            $submission = $this->get_user_submission($userid, true);
        }

        if (!$this->submissions_open($userid)) {
            $notices[] = get_string('submissionsclosed', 'assign');
            return false;
        }

        if ($instance->requiresubmissionstatement && empty($data->submissionstatement) && $USER->id == $userid) {
            return false;
        }

        if ($submission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                        $plugins = $this->get_submission_plugins();
            foreach ($plugins as $plugin) {
                if ($plugin->is_enabled() && $plugin->is_visible()) {
                    $plugin->submit_for_grading($submission);
                }
            }

            $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
            $this->update_submission($submission, $userid, true, $instance->teamsubmission);
            $completion = new completion_info($this->get_course());
            if ($completion->is_enabled($this->get_course_module()) && $instance->completionsubmit) {
                $this->update_activity_completion_records($instance->teamsubmission,
                                                          $instance->requireallteammemberssubmit,
                                                          $submission,
                                                          $userid,
                                                          COMPLETION_COMPLETE,
                                                          $completion);
            }

            if (!empty($data->submissionstatement) && $USER->id == $userid) {
                \mod_assign\event\statement_accepted::create_from_submission($this, $submission)->trigger();
            }
            $this->notify_graders($submission);
            $this->notify_student_submission_receipt($submission);

            \mod_assign\event\assessable_submitted::create_from_submission($this, $submission, false)->trigger();

            return true;
        }
        $notices[] = get_string('submissionsclosed', 'assign');
        return false;
    }

    
    protected function process_submit_other_for_grading($mform, $notices) {
        global $USER, $CFG;

        require_sesskey();

        $userid = optional_param('userid', $USER->id, PARAM_INT);

        if (!$this->submissions_open($userid)) {
            $notices[] = get_string('submissionsclosed', 'assign');
            return false;
        }
        $data = new stdClass();
        $data->userid = $userid;
        return $this->submit_for_grading($data, $notices);
    }

    
    protected function process_submit_for_grading($mform, $notices) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/submissionconfirmform.php');
        require_sesskey();

        if (!$this->submissions_open()) {
            $notices[] = get_string('submissionsclosed', 'assign');
            return false;
        }
        $instance = $this->get_instance();
        $data = new stdClass();
        $adminconfig = $this->get_admin_config();
        $requiresubmissionstatement = $instance->requiresubmissionstatement &&
                                       !empty($adminconfig->submissionstatement);

        $submissionstatement = '';
        if (!empty($adminconfig->submissionstatement)) {
                                    $options = array(
                'context' => $this->get_context(),
                'para' => false
            );
            $submissionstatement = format_text($adminconfig->submissionstatement, FORMAT_MOODLE, $options);
        }

        if ($mform == null) {
            $mform = new mod_assign_confirm_submission_form(null, array($requiresubmissionstatement,
                                                                    $submissionstatement,
                                                                    $this->get_course_module()->id,
                                                                    $data));
        }

        $data = $mform->get_data();
        if (!$mform->is_cancelled()) {
            if ($mform->get_data() == false) {
                return false;
            }
            return $this->submit_for_grading($data, $notices);
        }
        return true;
    }

    
    public function save_user_extension($userid, $extensionduedate) {
        global $DB;

                require_capability('mod/assign:grantextension', $this->context);

        if (!is_enrolled($this->get_course_context(), $userid)) {
            return false;
        }
        if (!has_capability('mod/assign:submit', $this->context, $userid)) {
            return false;
        }

        if ($this->get_instance()->duedate && $extensionduedate) {
            if ($this->get_instance()->duedate > $extensionduedate) {
                return false;
            }
        }
        if ($this->get_instance()->allowsubmissionsfromdate && $extensionduedate) {
            if ($this->get_instance()->allowsubmissionsfromdate > $extensionduedate) {
                return false;
            }
        }

        $flags = $this->get_user_flags($userid, true);
        $flags->extensionduedate = $extensionduedate;

        $result = $this->update_user_flags($flags);

        if ($result) {
            \mod_assign\event\extension_granted::create_from_assign($this, $userid)->trigger();
        }
        return $result;
    }

    
    protected function process_save_extension(& $mform) {
        global $DB, $CFG;

                require_once($CFG->dirroot . '/mod/assign/extensionform.php');
        require_sesskey();

        $users = optional_param('userid', 0, PARAM_INT);
        if (!$users) {
            $users = required_param('selectedusers', PARAM_SEQUENCE);
        }
        $userlist = explode(',', $users);

        $formparams = array(
            'instance' => $this->get_instance(),
            'assign' => $this,
            'userlist' => $userlist
        );

        $mform = new mod_assign_extension_form(null, $formparams);

        if ($mform->is_cancelled()) {
            return true;
        }

        if ($formdata = $mform->get_data()) {
            if (!empty($formdata->selectedusers)) {
                $users = explode(',', $formdata->selectedusers);
                $result = true;
                foreach ($users as $userid) {
                    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                    $result = $this->save_user_extension($user->id, $formdata->extensionduedate) && $result;
                }
                return $result;
            }
            if (!empty($formdata->userid)) {
                $user = $DB->get_record('user', array('id' => $formdata->userid), '*', MUST_EXIST);
                return $this->save_user_extension($user->id, $formdata->extensionduedate);
            }
        }

        return false;
    }


    
    protected function process_save_quick_grades() {
        global $USER, $DB, $CFG;

                require_capability('mod/assign:grade', $this->context);
        require_sesskey();

                $gradingmanager = get_grading_manager($this->get_context(), 'mod_assign', 'submissions');
        $controller = $gradingmanager->get_active_controller();
        if (!empty($controller)) {
            return get_string('errorquickgradingvsadvancedgrading', 'assign');
        }

        $users = array();
                $currentgroup = groups_get_activity_group($this->get_course_module(), true);
        $participants = $this->list_participants($currentgroup, true);

                foreach ($participants as $userid => $unused) {
            $modified = optional_param('grademodified_' . $userid, -1, PARAM_INT);
            $attemptnumber = optional_param('gradeattempt_' . $userid, -1, PARAM_INT);
                        $record = new stdClass();
            $record->userid = $userid;
            if ($modified >= 0) {
                $record->grade = unformat_float(optional_param('quickgrade_' . $record->userid, -1, PARAM_TEXT));
                $record->workflowstate = optional_param('quickgrade_' . $record->userid.'_workflowstate', false, PARAM_ALPHA);
                $record->allocatedmarker = optional_param('quickgrade_' . $record->userid.'_allocatedmarker', false, PARAM_INT);
            } else {
                                continue;
            }
            $record->attemptnumber = $attemptnumber;
            $record->lastmodified = $modified;
            $record->gradinginfo = grade_get_grades($this->get_course()->id,
                                                    'mod',
                                                    'assign',
                                                    $this->get_instance()->id,
                                                    array($userid));
            $users[$userid] = $record;
        }

        if (empty($users)) {
            return get_string('nousersselected', 'assign');
        }

        list($userids, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED);
        $params['assignid1'] = $this->get_instance()->id;
        $params['assignid2'] = $this->get_instance()->id;

                $grademaxattempt = 'SELECT s.userid, s.attemptnumber AS maxattempt
                              FROM {assign_submission} s
                             WHERE s.assignment = :assignid1 AND s.latest = 1';

        $sql = 'SELECT u.id AS userid, g.grade AS grade, g.timemodified AS lastmodified,
                       uf.workflowstate, uf.allocatedmarker, gmx.maxattempt AS attemptnumber
                  FROM {user} u
             LEFT JOIN ( ' . $grademaxattempt . ' ) gmx ON u.id = gmx.userid
             LEFT JOIN {assign_grades} g ON
                       u.id = g.userid AND
                       g.assignment = :assignid2 AND
                       g.attemptnumber = gmx.maxattempt
             LEFT JOIN {assign_user_flags} uf ON uf.assignment = g.assignment AND uf.userid = g.userid
                 WHERE u.id ' . $userids;
        $currentgrades = $DB->get_recordset_sql($sql, $params);

        $modifiedusers = array();
        foreach ($currentgrades as $current) {
            $modified = $users[(int)$current->userid];
            $grade = $this->get_user_grade($modified->userid, false);
                        $gradecolpresent = optional_param('quickgrade_' . $modified->userid, false, PARAM_INT) !== false;

                        if ($CFG->enableoutcomes) {
                foreach ($modified->gradinginfo->outcomes as $outcomeid => $outcome) {
                    $oldoutcome = $outcome->grades[$modified->userid]->grade;
                    $paramname = 'outcome_' . $outcomeid . '_' . $modified->userid;
                    $newoutcome = optional_param($paramname, -1, PARAM_FLOAT);
                                        $outcomecolpresent = optional_param($paramname, false, PARAM_FLOAT) !== false;
                    if ($outcomecolpresent && ($oldoutcome != $newoutcome)) {
                                                $modifiedusers[$modified->userid] = $modified;
                        continue;
                    }
                }
            }

                        foreach ($this->feedbackplugins as $plugin) {
                if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->supports_quickgrading()) {
                                                            if ($plugin->is_quickgrading_modified($modified->userid, $grade)) {
                        if ((int)$current->lastmodified > (int)$modified->lastmodified) {
                            return get_string('errorrecordmodified', 'assign');
                        } else {
                            $modifiedusers[$modified->userid] = $modified;
                            continue;
                        }
                    }
                }
            }

            if (($current->grade < 0 || $current->grade === null) &&
                ($modified->grade < 0 || $modified->grade === null)) {
                                $modified->grade = $current->grade;             }
                        if ($current->grade !== null) {
                $current->grade = floatval($current->grade);
            }
            $gradechanged = $gradecolpresent && grade_floats_different($current->grade, $modified->grade);
            $markingallocationchanged = $this->get_instance()->markingworkflow &&
                                        $this->get_instance()->markingallocation &&
                                            ($modified->allocatedmarker !== false) &&
                                            ($current->allocatedmarker != $modified->allocatedmarker);
            $workflowstatechanged = $this->get_instance()->markingworkflow &&
                                            ($modified->workflowstate !== false) &&
                                            ($current->workflowstate != $modified->workflowstate);
            if ($gradechanged || $markingallocationchanged || $workflowstatechanged) {
                                if ($this->grading_disabled($modified->userid)) {
                    continue;
                }
                $badmodified = (int)$current->lastmodified > (int)$modified->lastmodified;
                $badattempt = (int)$current->attemptnumber != (int)$modified->attemptnumber;
                if ($badmodified || $badattempt) {
                                        return get_string('errorrecordmodified', 'assign');
                } else {
                    $modifiedusers[$modified->userid] = $modified;
                }
            }

        }
        $currentgrades->close();

        $adminconfig = $this->get_admin_config();
        $gradebookplugin = $adminconfig->feedback_plugin_for_gradebook;

                foreach ($modifiedusers as $userid => $modified) {
            $grade = $this->get_user_grade($userid, true);
            $flags = $this->get_user_flags($userid, true);
            $grade->grade= grade_floatval(unformat_float($modified->grade));
            $grade->grader= $USER->id;
            $gradecolpresent = optional_param('quickgrade_' . $userid, false, PARAM_INT) !== false;

                        foreach ($this->feedbackplugins as $plugin) {
                if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->supports_quickgrading()) {
                    $plugin->save_quickgrading_changes($userid, $grade);
                    if (('assignfeedback_' . $plugin->get_type()) == $gradebookplugin) {
                                                $grade->feedbacktext = $plugin->text_for_gradebook($grade);
                        $grade->feedbackformat = $plugin->format_for_gradebook($grade);
                    }
                }
            }

                                    $workflowstatemodified = ($modified->workflowstate !== false) &&
                                        ($flags->workflowstate != $modified->workflowstate);

            $allocatedmarkermodified = ($modified->allocatedmarker !== false) &&
                                        ($flags->allocatedmarker != $modified->allocatedmarker);

            if ($workflowstatemodified) {
                $flags->workflowstate = $modified->workflowstate;
            }
            if ($allocatedmarkermodified) {
                $flags->allocatedmarker = $modified->allocatedmarker;
            }
            if ($workflowstatemodified || $allocatedmarkermodified) {
                if ($this->update_user_flags($flags) && $workflowstatemodified) {
                    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                    \mod_assign\event\workflow_state_updated::create_from_user($this, $user, $flags->workflowstate)->trigger();
                }
            }
            $this->update_grade($grade);

                        if (optional_param('sendstudentnotifications', true, PARAM_BOOL)) {
                $this->notify_grade_modified($grade, true);
            }

                        if ($CFG->enableoutcomes) {
                $data = array();
                foreach ($modified->gradinginfo->outcomes as $outcomeid => $outcome) {
                    $oldoutcome = $outcome->grades[$modified->userid]->grade;
                    $paramname = 'outcome_' . $outcomeid . '_' . $modified->userid;
                                                            $newoutcome = optional_param($paramname, false, PARAM_INT);
                    if ($newoutcome !== false && ($oldoutcome != $newoutcome)) {
                        $data[$outcomeid] = $newoutcome;
                    }
                }
                if (count($data) > 0) {
                    grade_update_outcomes('mod/assign',
                                          $this->course->id,
                                          'mod',
                                          'assign',
                                          $this->get_instance()->id,
                                          $userid,
                                          $data);
                }
            }
        }

        return get_string('quickgradingchangessaved', 'assign');
    }

    
    public function reveal_identities() {
        global $DB;

        require_capability('mod/assign:revealidentities', $this->context);

        if ($this->get_instance()->revealidentities || empty($this->get_instance()->blindmarking)) {
            return false;
        }

                $update = new stdClass();
        $update->id = $this->get_instance()->id;
        $update->revealidentities = 1;
        $DB->update_record('assign', $update);

                $this->instance = null;

                        $this->update_gradebook(false, $this->get_course_module()->id);

        
        $adminconfig = $this->get_admin_config();
        $gradebookplugin = $adminconfig->feedback_plugin_for_gradebook;
        $gradebookplugin = str_replace('assignfeedback_', '', $gradebookplugin);
        $grades = $DB->get_records('assign_grades', array('assignment'=>$this->get_instance()->id));

        $plugin = $this->get_feedback_plugin_by_type($gradebookplugin);

        foreach ($grades as $grade) {
                        if ($plugin && $plugin->is_enabled() && $plugin->is_visible()) {
                $grade->feedbacktext = $plugin->text_for_gradebook($grade);
                $grade->feedbackformat = $plugin->format_for_gradebook($grade);
            }
            $this->gradebook_item_update(null, $grade);
        }

        \mod_assign\event\identities_revealed::create_from_assign($this)->trigger();
    }

    
    protected function process_reveal_identities() {

        if (!confirm_sesskey()) {
            return false;
        }

        return $this->reveal_identities();
    }


    
    protected function process_save_grading_options() {
        global $USER, $CFG;

                require_once($CFG->dirroot . '/mod/assign/gradingoptionsform.php');

                $this->require_view_grades();
        require_sesskey();

                $gradingmanager = get_grading_manager($this->get_context(), 'mod_assign', 'submissions');
        $controller = $gradingmanager->get_active_controller();
        $showquickgrading = empty($controller);
        if (!is_null($this->context)) {
            $showonlyactiveenrolopt = has_capability('moodle/course:viewsuspendedusers', $this->context);
        } else {
            $showonlyactiveenrolopt = false;
        }

        $markingallocation = $this->get_instance()->markingworkflow &&
            $this->get_instance()->markingallocation &&
            has_capability('mod/assign:manageallocations', $this->context);
                $markingallocationoptions = array();
        if ($markingallocation) {
            $markingallocationoptions[''] = get_string('filternone', 'assign');
            $markingallocationoptions[ASSIGN_MARKER_FILTER_NO_MARKER] = get_string('markerfilternomarker', 'assign');
            list($sort, $params) = users_order_by_sql();
            $markers = get_users_by_capability($this->context, 'mod/assign:grade', '', $sort);
            foreach ($markers as $marker) {
                $markingallocationoptions[$marker->id] = fullname($marker);
            }
        }

                $markingworkflowoptions = array();
        if ($this->get_instance()->markingworkflow) {
            $notmarked = get_string('markingworkflowstatenotmarked', 'assign');
            $markingworkflowoptions[''] = get_string('filternone', 'assign');
            $markingworkflowoptions[ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED] = $notmarked;
            $markingworkflowoptions = array_merge($markingworkflowoptions, $this->get_marking_workflow_states_for_current_user());
        }

        $gradingoptionsparams = array('cm'=>$this->get_course_module()->id,
                                      'contextid'=>$this->context->id,
                                      'userid'=>$USER->id,
                                      'submissionsenabled'=>$this->is_any_submission_plugin_enabled(),
                                      'showquickgrading'=>$showquickgrading,
                                      'quickgrading'=>false,
                                      'markingworkflowopt' => $markingworkflowoptions,
                                      'markingallocationopt' => $markingallocationoptions,
                                      'showonlyactiveenrolopt'=>$showonlyactiveenrolopt,
                                      'showonlyactiveenrol' => $this->show_only_active_users(),
                                      'downloadasfolders' => get_user_preferences('assign_downloadasfolders', 1));
        $mform = new mod_assign_grading_options_form(null, $gradingoptionsparams);
        if ($formdata = $mform->get_data()) {
            set_user_preference('assign_perpage', $formdata->perpage);
            if (isset($formdata->filter)) {
                set_user_preference('assign_filter', $formdata->filter);
            }
            if (isset($formdata->markerfilter)) {
                set_user_preference('assign_markerfilter', $formdata->markerfilter);
            }
            if (isset($formdata->workflowfilter)) {
                set_user_preference('assign_workflowfilter', $formdata->workflowfilter);
            }
            if ($showquickgrading) {
                set_user_preference('assign_quickgrading', isset($formdata->quickgrading));
            }
            if (isset($formdata->downloadasfolders)) {
                set_user_preference('assign_downloadasfolders', 1);             } else {
                set_user_preference('assign_downloadasfolders', 0);             }
            if (!empty($showonlyactiveenrolopt)) {
                $showonlyactiveenrol = isset($formdata->showonlyactiveenrol);
                set_user_preference('grade_report_showonlyactiveenrol', $showonlyactiveenrol);
                $this->showonlyactiveenrol = $showonlyactiveenrol;
            }
        }
    }

    
    public function format_grade_for_log(stdClass $grade) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $grade->userid), '*', MUST_EXIST);

        $info = get_string('gradestudent', 'assign', array('id'=>$user->id, 'fullname'=>fullname($user)));
        if ($grade->grade != '') {
            $info .= get_string('grade') . ': ' . $this->display_grade($grade->grade, false) . '. ';
        } else {
            $info .= get_string('nograde', 'assign');
        }
        return $info;
    }

    
    public function format_submission_for_log(stdClass $submission) {
        global $DB;

        $info = '';
        if ($submission->userid) {
            $user = $DB->get_record('user', array('id' => $submission->userid), '*', MUST_EXIST);
            $name = fullname($user);
        } else {
            $group = $this->get_submission_group($submission->userid);
            if ($group) {
                $name = $group->name;
            } else {
                $name = get_string('defaultteam', 'assign');
            }
        }
        $status = get_string('submissionstatus_' . $submission->status, 'assign');
        $params = array('id'=>$submission->userid, 'fullname'=>$name, 'status'=>$status);
        $info .= get_string('submissionlog', 'assign', $params) . ' <br>';

        foreach ($this->submissionplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $info .= '<br>' . $plugin->format_for_log($submission);
            }
        }

        return $info;
    }

    
    protected function process_copy_previous_attempt(&$notices) {
        require_sesskey();

        return $this->copy_previous_attempt($notices);
    }

    
    public function copy_previous_attempt(&$notices) {
        global $USER, $CFG;

        require_capability('mod/assign:submit', $this->context);

        $instance = $this->get_instance();
        if ($instance->teamsubmission) {
            $submission = $this->get_group_submission($USER->id, 0, true);
        } else {
            $submission = $this->get_user_submission($USER->id, true);
        }
        if (!$submission || $submission->status != ASSIGN_SUBMISSION_STATUS_REOPENED) {
            $notices[] = get_string('submissionnotcopiedinvalidstatus', 'assign');
            return false;
        }
        $flags = $this->get_user_flags($USER->id, false);

                if ($flags && $flags->locked) {
            $notices[] = get_string('submissionslocked', 'assign');
            return false;
        }
        if ($instance->submissiondrafts) {
            $submission->status = ASSIGN_SUBMISSION_STATUS_DRAFT;
        } else {
            $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
        }
        $this->update_submission($submission, $USER->id, true, $instance->teamsubmission);

                if ($instance->teamsubmission) {
            $previoussubmission = $this->get_group_submission($USER->id, 0, true, $submission->attemptnumber - 1);
        } else {
            $previoussubmission = $this->get_user_submission($USER->id, true, $submission->attemptnumber - 1);
        }

        if (!$previoussubmission) {
                        return true;
        }

        $pluginerror = false;
        foreach ($this->get_submission_plugins() as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled()) {
                if (!$plugin->copy_submission($previoussubmission, $submission)) {
                    $notices[] = $plugin->get_error();
                    $pluginerror = true;
                }
            }
        }
        if ($pluginerror) {
            return false;
        }

        \mod_assign\event\submission_duplicated::create_from_submission($this, $submission)->trigger();

        $complete = COMPLETION_INCOMPLETE;
        if ($submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
            $complete = COMPLETION_COMPLETE;
        }
        $completion = new completion_info($this->get_course());
        if ($completion->is_enabled($this->get_course_module()) && $instance->completionsubmit) {
            $this->update_activity_completion_records($instance->teamsubmission,
                                                      $instance->requireallteammemberssubmit,
                                                      $submission,
                                                      $USER->id,
                                                      $complete,
                                                      $completion);
        }

        if (!$instance->submissiondrafts) {
                                                $this->notify_student_submission_copied($submission);
            $this->notify_graders($submission);

                                                \mod_assign\event\assessable_submitted::create_from_submission($this, $submission, true)->trigger();
        }
        return true;
    }

    
    public function submission_empty($submission) {
        $allempty = true;

        foreach ($this->submissionplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                if (!$allempty || !$plugin->is_empty($submission)) {
                    $allempty = false;
                }
            }
        }
        return $allempty;
    }

    
    public function save_submission(stdClass $data, & $notices) {
        global $CFG, $USER, $DB;

        $userid = $USER->id;
        if (!empty($data->userid)) {
            $userid = $data->userid;
        }

        $user = clone($USER);
        if ($userid == $USER->id) {
            require_capability('mod/assign:submit', $this->context);
        } else {
            $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
            if (!$this->can_edit_submission($userid, $USER->id)) {
                print_error('nopermission');
            }
        }
        $instance = $this->get_instance();

        if ($instance->teamsubmission) {
            $submission = $this->get_group_submission($userid, 0, true);
        } else {
            $submission = $this->get_user_submission($userid, true);
        }

                if (isset($data->lastmodified) && ($submission->timemodified > $data->lastmodified)) {
                        if ($submission->status !== ASSIGN_SUBMISSION_STATUS_NEW) {
                $notices[] = $instance->teamsubmission ? get_string('submissionmodifiedgroup', 'mod_assign')
                                                       : get_string('submissionmodified', 'mod_assign');
                return false;
            }
        }

        if ($instance->submissiondrafts) {
            $submission->status = ASSIGN_SUBMISSION_STATUS_DRAFT;
        } else {
            $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
        }

        $flags = $this->get_user_flags($userid, false);

                if ($flags && $flags->locked) {
            print_error('submissionslocked', 'assign');
            return true;
        }

        $pluginerror = false;
        foreach ($this->submissionplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                if (!$plugin->save($submission, $data)) {
                    $notices[] = $plugin->get_error();
                    $pluginerror = true;
                }
            }
        }

        $allempty = $this->submission_empty($submission);
        if ($pluginerror || $allempty) {
            if ($allempty) {
                $notices[] = get_string('submissionempty', 'mod_assign');
            }
            return false;
        }

        $this->update_submission($submission, $userid, true, $instance->teamsubmission);

        if ($instance->teamsubmission && !$instance->requireallteammemberssubmit) {
            $team = $this->get_submission_group_members($submission->groupid, true);

            foreach ($team as $member) {
                if ($member->id != $userid) {
                    $membersubmission = clone($submission);
                    $this->update_submission($membersubmission, $member->id, true, $instance->teamsubmission);
                }
            }
        }

                if (isset($data->submissionstatement) && ($userid == $USER->id)) {
            \mod_assign\event\statement_accepted::create_from_submission($this, $submission)->trigger();
        }

        $complete = COMPLETION_INCOMPLETE;
        if ($submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
            $complete = COMPLETION_COMPLETE;
        }
        $completion = new completion_info($this->get_course());
        if ($completion->is_enabled($this->get_course_module()) && $instance->completionsubmit) {
            $completion->update_state($this->get_course_module(), $complete, $userid);
        }

        if (!$instance->submissiondrafts) {
            $this->notify_student_submission_receipt($submission);
            $this->notify_graders($submission);
            \mod_assign\event\assessable_submitted::create_from_submission($this, $submission, true)->trigger();
        }
        return true;
    }

    
    protected function process_save_submission(&$mform, &$notices) {
        global $CFG, $USER;

                require_once($CFG->dirroot . '/mod/assign/submission_form.php');

        $userid = optional_param('userid', $USER->id, PARAM_INT);
                require_sesskey();
        if (!$this->submissions_open($userid)) {
            $notices[] = get_string('duedatereached', 'assign');
            return false;
        }
        $instance = $this->get_instance();

        $data = new stdClass();
        $data->userid = $userid;
        $mform = new mod_assign_submission_form(null, array($this, $data));
        if ($mform->is_cancelled()) {
            return true;
        }
        if ($data = $mform->get_data()) {
            return $this->save_submission($data, $notices);
        }
        return false;
    }


    
    public function grading_disabled($userid, $checkworkflow=true) {
        global $CFG;
        if ($checkworkflow && $this->get_instance()->markingworkflow) {
            $grade = $this->get_user_grade($userid, false);
            $validstates = $this->get_marking_workflow_states_for_current_user();
            if (!empty($grade) && !empty($grade->workflowstate) && !array_key_exists($grade->workflowstate, $validstates)) {
                return true;
            }
        }
        $gradinginfo = grade_get_grades($this->get_course()->id,
                                        'mod',
                                        'assign',
                                        $this->get_instance()->id,
                                        array($userid));
        if (!$gradinginfo) {
            return false;
        }

        if (!isset($gradinginfo->items[0]->grades[$userid])) {
            return false;
        }
        $gradingdisabled = $gradinginfo->items[0]->grades[$userid]->locked ||
                           $gradinginfo->items[0]->grades[$userid]->overridden;
        return $gradingdisabled;
    }


    
    protected function get_grading_instance($userid, $grade, $gradingdisabled) {
        global $CFG, $USER;

        $grademenu = make_grades_menu($this->get_instance()->grade);
        $allowgradedecimals = $this->get_instance()->grade > 0;

        $advancedgradingwarning = false;
        $gradingmanager = get_grading_manager($this->context, 'mod_assign', 'submissions');
        $gradinginstance = null;
        if ($gradingmethod = $gradingmanager->get_active_method()) {
            $controller = $gradingmanager->get_controller($gradingmethod);
            if ($controller->is_form_available()) {
                $itemid = null;
                if ($grade) {
                    $itemid = $grade->id;
                }
                if ($gradingdisabled && $itemid) {
                    $gradinginstance = $controller->get_current_instance($USER->id, $itemid);
                } else if (!$gradingdisabled) {
                    $instanceid = optional_param('advancedgradinginstanceid', 0, PARAM_INT);
                    $gradinginstance = $controller->get_or_create_instance($instanceid,
                                                                           $USER->id,
                                                                           $itemid);
                }
            } else {
                $advancedgradingwarning = $controller->form_unavailable_notification();
            }
        }
        if ($gradinginstance) {
            $gradinginstance->get_controller()->set_grade_range($grademenu, $allowgradedecimals);
        }
        return $gradinginstance;
    }

    
    public function add_grade_form_elements(MoodleQuickForm $mform, stdClass $data, $params) {
        global $USER, $CFG, $SESSION;
        $settings = $this->get_instance();

        $rownum = isset($params['rownum']) ? $params['rownum'] : 0;
        $last = isset($params['last']) ? $params['last'] : true;
        $useridlistid = isset($params['useridlistid']) ? $params['useridlistid'] : 0;
        $userid = isset($params['userid']) ? $params['userid'] : 0;
        $attemptnumber = isset($params['attemptnumber']) ? $params['attemptnumber'] : 0;
        $gradingpanel = !empty($params['gradingpanel']);
        $bothids = ($userid && $useridlistid);

        if (!$userid || $bothids) {
            $useridlistkey = $this->get_useridlist_key($useridlistid);
            if (empty($SESSION->mod_assign_useridlist[$useridlistkey])) {
                $SESSION->mod_assign_useridlist[$useridlistkey] = $this->get_grading_userid_list();
            }
            $useridlist = $SESSION->mod_assign_useridlist[$useridlistkey];
        } else {
            $useridlist = array($userid);
            $rownum = 0;
            $useridlistid = '';
        }

        $userid = $useridlist[$rownum];
                        $grade = $this->get_user_grade($userid, true, $attemptnumber);

        $submission = null;
        if ($this->get_instance()->teamsubmission) {
            $submission = $this->get_group_submission($userid, 0, false, $attemptnumber);
        } else {
            $submission = $this->get_user_submission($userid, false, $attemptnumber);
        }

                $gradingdisabled = $this->grading_disabled($userid);
        $gradinginstance = $this->get_grading_instance($userid, $grade, $gradingdisabled);

        $mform->addElement('header', 'gradeheader', get_string('grade'));
        if ($gradinginstance) {
            $gradingelement = $mform->addElement('grading',
                                                 'advancedgrading',
                                                 get_string('grade').':',
                                                 array('gradinginstance' => $gradinginstance));
            if ($gradingdisabled) {
                $gradingelement->freeze();
            } else {
                $mform->addElement('hidden', 'advancedgradinginstanceid', $gradinginstance->get_id());
                $mform->setType('advancedgradinginstanceid', PARAM_INT);
            }
        } else {
                        if ($this->get_instance()->grade > 0) {
                $name = get_string('gradeoutof', 'assign', $this->get_instance()->grade);
                if (!$gradingdisabled) {
                    $gradingelement = $mform->addElement('text', 'grade', $name);
                    $mform->addHelpButton('grade', 'gradeoutofhelp', 'assign');
                    $mform->setType('grade', PARAM_RAW);
                } else {
                    $mform->addElement('hidden', 'grade', $name);
                    $mform->hardFreeze('grade');
                    $mform->setType('grade', PARAM_RAW);
                    $strgradelocked = get_string('gradelocked', 'assign');
                    $mform->addElement('static', 'gradedisabled', $name, $strgradelocked);
                    $mform->addHelpButton('gradedisabled', 'gradeoutofhelp', 'assign');
                }
            } else {
                $grademenu = array(-1 => get_string("nograde")) + make_grades_menu($this->get_instance()->grade);
                if (count($grademenu) > 1) {
                    $gradingelement = $mform->addElement('select', 'grade', get_string('grade') . ':', $grademenu);

                                        if (!empty($data->grade)) {
                        $data->grade = (int)unformat_float($data->grade);
                    }
                    $mform->setType('grade', PARAM_INT);
                    if ($gradingdisabled) {
                        $gradingelement->freeze();
                    }
                }
            }
        }

        $gradinginfo = grade_get_grades($this->get_course()->id,
                                        'mod',
                                        'assign',
                                        $this->get_instance()->id,
                                        $userid);
        if (!empty($CFG->enableoutcomes)) {
            foreach ($gradinginfo->outcomes as $index => $outcome) {
                $options = make_grades_menu(-$outcome->scaleid);
                $options[0] = get_string('nooutcome', 'grades');
                if ($outcome->grades[$userid]->locked) {
                    $mform->addElement('static',
                                       'outcome_' . $index . '[' . $userid . ']',
                                       $outcome->name . ':',
                                       $options[$outcome->grades[$userid]->grade]);
                } else {
                    $attributes = array('id' => 'menuoutcome_' . $index );
                    $mform->addElement('select',
                                       'outcome_' . $index . '[' . $userid . ']',
                                       $outcome->name.':',
                                       $options,
                                       $attributes);
                    $mform->setType('outcome_' . $index . '[' . $userid . ']', PARAM_INT);
                    $mform->setDefault('outcome_' . $index . '[' . $userid . ']',
                                       $outcome->grades[$userid]->grade);
                }
            }
        }

        $capabilitylist = array('gradereport/grader:view', 'moodle/grade:viewall');
        if (has_all_capabilities($capabilitylist, $this->get_course_context())) {
            $urlparams = array('id'=>$this->get_course()->id);
            $url = new moodle_url('/grade/report/grader/index.php', $urlparams);
            $usergrade = '-';
            if (isset($gradinginfo->items[0]->grades[$userid]->str_grade)) {
                $usergrade = $gradinginfo->items[0]->grades[$userid]->str_grade;
            }
            $gradestring = $this->get_renderer()->action_link($url, $usergrade);
        } else {
            $usergrade = '-';
            if (isset($gradinginfo->items[0]->grades[$userid]) &&
                    !$gradinginfo->items[0]->grades[$userid]->hidden) {
                $usergrade = $gradinginfo->items[0]->grades[$userid]->str_grade;
            }
            $gradestring = $usergrade;
        }

        if ($this->get_instance()->markingworkflow) {
            $states = $this->get_marking_workflow_states_for_current_user();
            $options = array('' => get_string('markingworkflowstatenotmarked', 'assign')) + $states;
            $mform->addElement('select', 'workflowstate', get_string('markingworkflowstate', 'assign'), $options);
            $mform->addHelpButton('workflowstate', 'markingworkflowstate', 'assign');
        }

        if ($this->get_instance()->markingworkflow &&
            $this->get_instance()->markingallocation &&
            has_capability('mod/assign:manageallocations', $this->context)) {

            list($sort, $params) = users_order_by_sql();
            $markers = get_users_by_capability($this->context, 'mod/assign:grade', '', $sort);
            $markerlist = array('' =>  get_string('choosemarker', 'assign'));
            foreach ($markers as $marker) {
                $markerlist[$marker->id] = fullname($marker);
            }
            $mform->addElement('select', 'allocatedmarker', get_string('allocatedmarker', 'assign'), $markerlist);
            $mform->addHelpButton('allocatedmarker', 'allocatedmarker', 'assign');
            $mform->disabledIf('allocatedmarker', 'workflowstate', 'eq', ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW);
            $mform->disabledIf('allocatedmarker', 'workflowstate', 'eq', ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW);
            $mform->disabledIf('allocatedmarker', 'workflowstate', 'eq', ASSIGN_MARKING_WORKFLOW_STATE_READYFORRELEASE);
            $mform->disabledIf('allocatedmarker', 'workflowstate', 'eq', ASSIGN_MARKING_WORKFLOW_STATE_RELEASED);
        }
        $gradestring = '<span class="currentgrade">' . $gradestring . '</span>';
        $mform->addElement('static', 'currentgrade', get_string('currentgrade', 'assign'), $gradestring);

        if (count($useridlist) > 1) {
            $strparams = array('current'=>$rownum+1, 'total'=>count($useridlist));
            $name = get_string('outof', 'assign', $strparams);
            $mform->addElement('static', 'gradingstudent', get_string('gradingstudent', 'assign'), $name);
        }

                $this->add_plugin_grade_elements($grade, $mform, $data, $userid);

                $mform->addElement('hidden', 'id', $this->get_course_module()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'rownum', $rownum);
        $mform->setType('rownum', PARAM_INT);
        $mform->setConstant('rownum', $rownum);
        $mform->addElement('hidden', 'useridlistid', $useridlistid);
        $mform->setType('useridlistid', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'attemptnumber', $attemptnumber);
        $mform->setType('attemptnumber', PARAM_INT);
        $mform->addElement('hidden', 'ajax', optional_param('ajax', 0, PARAM_INT));
        $mform->setType('ajax', PARAM_INT);
        $mform->addElement('hidden', 'userid', optional_param('userid', 0, PARAM_INT));
        $mform->setType('userid', PARAM_INT);

        if ($this->get_instance()->teamsubmission) {
            $mform->addElement('header', 'groupsubmissionsettings', get_string('groupsubmissionsettings', 'assign'));
            $mform->addElement('selectyesno', 'applytoall', get_string('applytoteam', 'assign'));
            $mform->setDefault('applytoall', 1);
        }

                if ($attemptnumber == -1 && $this->get_instance()->attemptreopenmethod != ASSIGN_ATTEMPT_REOPEN_METHOD_NONE) {
            $mform->addElement('header', 'attemptsettings', get_string('attemptsettings', 'assign'));
            $attemptreopenmethod = get_string('attemptreopenmethod_' . $this->get_instance()->attemptreopenmethod, 'assign');
            $mform->addElement('static', 'attemptreopenmethod', get_string('attemptreopenmethod', 'assign'), $attemptreopenmethod);

            $attemptnumber = 0;
            if ($submission) {
                $attemptnumber = $submission->attemptnumber;
            }
            $maxattempts = $this->get_instance()->maxattempts;
            if ($maxattempts == ASSIGN_UNLIMITED_ATTEMPTS) {
                $maxattempts = get_string('unlimitedattempts', 'assign');
            }
            $mform->addelement('static', 'maxattemptslabel', get_string('maxattempts', 'assign'), $maxattempts);
            $mform->addelement('static', 'attemptnumberlabel', get_string('attemptnumber', 'assign'), $attemptnumber + 1);

            $ismanual = $this->get_instance()->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL;
            $issubmission = !empty($submission);
            $isunlimited = $this->get_instance()->maxattempts == ASSIGN_UNLIMITED_ATTEMPTS;
            $islessthanmaxattempts = $issubmission && ($submission->attemptnumber < ($this->get_instance()->maxattempts-1));

            if ($ismanual && (!$issubmission || $isunlimited || $islessthanmaxattempts)) {
                $mform->addElement('selectyesno', 'addattempt', get_string('addattempt', 'assign'));
                $mform->setDefault('addattempt', 0);
            }
        }
        if (!$gradingpanel) {
            $mform->addElement('selectyesno', 'sendstudentnotifications', get_string('sendstudentnotifications', 'assign'));
        } else {
            $mform->addElement('hidden', 'sendstudentnotifications', get_string('sendstudentnotifications', 'assign'));
            $mform->setType('sendstudentnotifications', PARAM_BOOL);
        }
                $modinfo = get_fast_modinfo($settings->course, $userid);
        $cm = $modinfo->get_cm($this->get_course_module()->id);

                        if (!$cm->uservisible) {
            $mform->setDefault('sendstudentnotifications', 0);
            $mform->freeze('sendstudentnotifications');
        } else if ($this->get_instance()->markingworkflow) {
            $mform->setDefault('sendstudentnotifications', 0);
            if (!$gradingpanel) {
                $mform->disabledIf('sendstudentnotifications', 'workflowstate', 'neq', ASSIGN_MARKING_WORKFLOW_STATE_RELEASED);
            }
        } else {
            $mform->setDefault('sendstudentnotifications', $this->get_instance()->sendstudentnotifications);
        }

        $mform->addElement('hidden', 'action', 'submitgrade');
        $mform->setType('action', PARAM_ALPHA);

        if (!$gradingpanel) {

            $buttonarray = array();
            $name = get_string('savechanges', 'assign');
            $buttonarray[] = $mform->createElement('submit', 'savegrade', $name);
            if (!$last) {
                $name = get_string('savenext', 'assign');
                $buttonarray[] = $mform->createElement('submit', 'saveandshownext', $name);
            }
            $buttonarray[] = $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
            $buttonarray = array();

            if ($rownum > 0) {
                $name = get_string('previous', 'assign');
                $buttonarray[] = $mform->createElement('submit', 'nosaveandprevious', $name);
            }

            if (!$last) {
                $name = get_string('nosavebutnext', 'assign');
                $buttonarray[] = $mform->createElement('submit', 'nosaveandnext', $name);
            }
            if (!empty($buttonarray)) {
                $mform->addGroup($buttonarray, 'navar', '', array(' '), false);
            }
        }
                $mform->setDisableShortforms();
    }

    
    protected function add_plugin_submission_elements($submission,
                                                    MoodleQuickForm $mform,
                                                    stdClass $data,
                                                    $userid) {
        foreach ($this->submissionplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible() && $plugin->allow_submissions()) {
                $plugin->get_form_elements_for_user($submission, $mform, $data, $userid);
            }
        }
    }

    
    public function is_any_feedback_plugin_enabled() {
        if (!isset($this->cache['any_feedback_plugin_enabled'])) {
            $this->cache['any_feedback_plugin_enabled'] = false;
            foreach ($this->feedbackplugins as $plugin) {
                if ($plugin->is_enabled() && $plugin->is_visible()) {
                    $this->cache['any_feedback_plugin_enabled'] = true;
                    break;
                }
            }
        }

        return $this->cache['any_feedback_plugin_enabled'];

    }

    
    public function is_any_submission_plugin_enabled() {
        if (!isset($this->cache['any_submission_plugin_enabled'])) {
            $this->cache['any_submission_plugin_enabled'] = false;
            foreach ($this->submissionplugins as $plugin) {
                if ($plugin->is_enabled() && $plugin->is_visible() && $plugin->allow_submissions()) {
                    $this->cache['any_submission_plugin_enabled'] = true;
                    break;
                }
            }
        }

        return $this->cache['any_submission_plugin_enabled'];

    }

    
    public function add_submission_form_elements(MoodleQuickForm $mform, stdClass $data) {
        global $USER;

        $userid = $data->userid;
                if ($this->get_instance()->teamsubmission) {
            $submission = $this->get_group_submission($userid, 0, false);
        } else {
            $submission = $this->get_user_submission($userid, false);
        }

                $adminconfig = $this->get_admin_config();

        $requiresubmissionstatement = $this->get_instance()->requiresubmissionstatement &&
                                       !empty($adminconfig->submissionstatement);

        $draftsenabled = $this->get_instance()->submissiondrafts;

                if ($requiresubmissionstatement && !$draftsenabled && $userid == $USER->id) {

            $submissionstatement = '';
            if (!empty($adminconfig->submissionstatement)) {
                                                $options = array(
                    'context' => $this->get_context(),
                    'para' => false
                );
                $submissionstatement = format_text($adminconfig->submissionstatement, FORMAT_MOODLE, $options);
            }
            $mform->addElement('checkbox', 'submissionstatement', '', $submissionstatement);
            $mform->addRule('submissionstatement', get_string('required'), 'required', null, 'client');
        }

        $this->add_plugin_submission_elements($submission, $mform, $data, $userid);

                $mform->addElement('hidden', 'id', $this->get_course_module()->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'action', 'savesubmission');
        $mform->setType('action', PARAM_ALPHA);
    }

    
    public function revert_to_draft($userid) {
        global $DB, $USER;

                require_capability('mod/assign:grade', $this->context);

        if ($this->get_instance()->teamsubmission) {
            $submission = $this->get_group_submission($userid, 0, false);
        } else {
            $submission = $this->get_user_submission($userid, false);
        }

        if (!$submission) {
            return false;
        }
        $submission->status = ASSIGN_SUBMISSION_STATUS_DRAFT;
        $this->update_submission($submission, $userid, true, $this->get_instance()->teamsubmission);

                $plugins = $this->get_submission_plugins();
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $plugin->revert_to_draft($submission);
            }
        }
                $grade = $this->get_user_grade($userid, true);
        $grade->grader = $USER->id;
        $this->update_grade($grade);

        $completion = new completion_info($this->get_course());
        if ($completion->is_enabled($this->get_course_module()) &&
                $this->get_instance()->completionsubmit) {
            $completion->update_state($this->get_course_module(), COMPLETION_INCOMPLETE, $userid);
        }
        \mod_assign\event\submission_status_updated::create_from_submission($this, $submission)->trigger();
        return true;
    }

    
    protected function process_revert_to_draft($userid = 0) {
        require_sesskey();

        if (!$userid) {
            $userid = required_param('userid', PARAM_INT);
        }

        return $this->revert_to_draft($userid);
    }

    
    public function lock_submission($userid) {
        global $USER, $DB;
                require_capability('mod/assign:grade', $this->context);

                $plugins = $this->get_submission_plugins();
        $submission = $this->get_user_submission($userid, false);

        $flags = $this->get_user_flags($userid, true);
        $flags->locked = 1;
        $this->update_user_flags($flags);

        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $plugin->lock($submission, $flags);
            }
        }

        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        \mod_assign\event\submission_locked::create_from_user($this, $user)->trigger();
        return true;
    }


    
    protected function process_set_batch_marking_workflow_state() {
        global $CFG, $DB;

                require_once($CFG->dirroot . '/mod/assign/batchsetmarkingworkflowstateform.php');

        $formparams = array(
            'userscount' => 0,              'usershtml' => '',              'markingworkflowstates' => $this->get_marking_workflow_states_for_current_user()
        );

        $mform = new mod_assign_batch_set_marking_workflow_state_form(null, $formparams);

        if ($mform->is_cancelled()) {
            return true;
        }

        if ($formdata = $mform->get_data()) {
            $useridlist = explode(',', $formdata->selectedusers);
            $state = $formdata->markingworkflowstate;

            foreach ($useridlist as $userid) {
                $flags = $this->get_user_flags($userid, true);

                $flags->workflowstate = $state;

                                                                $modinfo = get_fast_modinfo($this->course, $userid);
                $cm = $modinfo->get_cm($this->get_course_module()->id);
                if ($formdata->sendstudentnotifications && $cm->uservisible &&
                        $state == ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                    $flags->mailed = 0;
                }

                $gradingdisabled = $this->grading_disabled($userid);

                                if (!$gradingdisabled && $this->update_user_flags($flags)) {
                    if ($state == ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                                                $assign = clone $this->get_instance();
                        $assign->cmidnumber = $this->get_course_module()->idnumber;
                                                $assign->gradefeedbackenabled = $this->is_gradebook_feedback_enabled();
                        assign_update_grades($assign, $userid);
                    }

                    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                    \mod_assign\event\workflow_state_updated::create_from_user($this, $user, $state)->trigger();
                }
            }
        }
    }

    
    protected function process_set_batch_marking_allocation() {
        global $CFG, $DB;

                require_once($CFG->dirroot . '/mod/assign/batchsetallocatedmarkerform.php');

        $formparams = array(
            'userscount' => 0,              'usershtml' => ''           );

        list($sort, $params) = users_order_by_sql();
        $markers = get_users_by_capability($this->get_context(), 'mod/assign:grade', '', $sort);
        $markerlist = array();
        foreach ($markers as $marker) {
            $markerlist[$marker->id] = fullname($marker);
        }

        $formparams['markers'] = $markerlist;

        $mform = new mod_assign_batch_set_allocatedmarker_form(null, $formparams);

        if ($mform->is_cancelled()) {
            return true;
        }

        if ($formdata = $mform->get_data()) {
            $useridlist = explode(',', $formdata->selectedusers);
            $marker = $DB->get_record('user', array('id' => $formdata->allocatedmarker), '*', MUST_EXIST);

            foreach ($useridlist as $userid) {
                $flags = $this->get_user_flags($userid, true);
                if ($flags->workflowstate == ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW ||
                    $flags->workflowstate == ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW ||
                    $flags->workflowstate == ASSIGN_MARKING_WORKFLOW_STATE_READYFORRELEASE ||
                    $flags->workflowstate == ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {

                    continue;                 }

                $flags->allocatedmarker = $marker->id;

                if ($this->update_user_flags($flags)) {
                    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                    \mod_assign\event\marker_updated::create_from_marker($this, $user, $marker)->trigger();
                }
            }
        }
    }


    
    protected function process_lock_submission($userid = 0) {

        require_sesskey();

        if (!$userid) {
            $userid = required_param('userid', PARAM_INT);
        }

        return $this->lock_submission($userid);
    }

    
    public function unlock_submission($userid) {
        global $USER, $DB;

                require_capability('mod/assign:grade', $this->context);

                $plugins = $this->get_submission_plugins();
        $submission = $this->get_user_submission($userid, false);

        $flags = $this->get_user_flags($userid, true);
        $flags->locked = 0;
        $this->update_user_flags($flags);

        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $plugin->unlock($submission, $flags);
            }
        }

        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        \mod_assign\event\submission_unlocked::create_from_user($this, $user)->trigger();
        return true;
    }

    
    protected function process_unlock_submission($userid = 0) {

        require_sesskey();

        if (!$userid) {
            $userid = required_param('userid', PARAM_INT);
        }

        return $this->unlock_submission($userid);
    }

    
    protected function apply_grade_to_user($formdata, $userid, $attemptnumber) {
        global $USER, $CFG, $DB;

        $grade = $this->get_user_grade($userid, true, $attemptnumber);
        $originalgrade = $grade->grade;
        $gradingdisabled = $this->grading_disabled($userid);
        $gradinginstance = $this->get_grading_instance($userid, $grade, $gradingdisabled);
        if (!$gradingdisabled) {
            if ($gradinginstance) {
                $grade->grade = $gradinginstance->submit_and_get_grade($formdata->advancedgrading,
                                                                       $grade->id);
            } else {
                                if (isset($formdata->grade)) {
                    $grade->grade = grade_floatval(unformat_float($formdata->grade));
                }
            }
            if (isset($formdata->workflowstate) || isset($formdata->allocatedmarker)) {
                $flags = $this->get_user_flags($userid, true);
                $oldworkflowstate = $flags->workflowstate;
                $flags->workflowstate = isset($formdata->workflowstate) ? $formdata->workflowstate : $flags->workflowstate;
                $flags->allocatedmarker = isset($formdata->allocatedmarker) ? $formdata->allocatedmarker : $flags->allocatedmarker;
                if ($this->update_user_flags($flags) &&
                        isset($formdata->workflowstate) &&
                        $formdata->workflowstate !== $oldworkflowstate) {
                    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                    \mod_assign\event\workflow_state_updated::create_from_user($this, $user, $formdata->workflowstate)->trigger();
                }
            }
        }
        $grade->grader= $USER->id;

        $adminconfig = $this->get_admin_config();
        $gradebookplugin = $adminconfig->feedback_plugin_for_gradebook;

        $feedbackmodified = false;

                foreach ($this->feedbackplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $gradingmodified = $plugin->is_feedback_modified($grade, $formdata);
                if ($gradingmodified) {
                    if (!$plugin->save($grade, $formdata)) {
                        $result = false;
                        print_error($plugin->get_error());
                    }
                                        $feedbackmodified = $feedbackmodified || $gradingmodified;
                }
                if (('assignfeedback_' . $plugin->get_type()) == $gradebookplugin) {
                                        $grade->feedbacktext = $plugin->text_for_gradebook($grade);
                    $grade->feedbackformat = $plugin->format_for_gradebook($grade);
                }
            }
        }

                if (!empty($formdata->addattempt) ||
                ($originalgrade !== null && $originalgrade != -1) ||
                ($grade->grade !== null && $grade->grade != -1) ||
                $feedbackmodified) {
            $this->update_grade($grade, !empty($formdata->addattempt));
        }
                        if (!isset($formdata->sendstudentnotifications) || $formdata->sendstudentnotifications) {
            $this->notify_grade_modified($grade, true);
        }
    }


    
    protected function process_outcomes($userid, $formdata, $sourceuserid = null) {
        global $CFG, $USER;

        if (empty($CFG->enableoutcomes)) {
            return;
        }
        if ($this->grading_disabled($userid)) {
            return;
        }

        require_once($CFG->libdir.'/gradelib.php');

        $data = array();
        $gradinginfo = grade_get_grades($this->get_course()->id,
                                        'mod',
                                        'assign',
                                        $this->get_instance()->id,
                                        $userid);

        if (!empty($gradinginfo->outcomes)) {
            foreach ($gradinginfo->outcomes as $index => $oldoutcome) {
                $name = 'outcome_'.$index;
                $sourceuserid = $sourceuserid !== null ? $sourceuserid : $userid;
                if (isset($formdata->{$name}[$sourceuserid]) &&
                        $oldoutcome->grades[$userid]->grade != $formdata->{$name}[$sourceuserid]) {
                    $data[$index] = $formdata->{$name}[$sourceuserid];
                }
            }
        }
        if (count($data) > 0) {
            grade_update_outcomes('mod/assign',
                                  $this->course->id,
                                  'mod',
                                  'assign',
                                  $this->get_instance()->id,
                                  $userid,
                                  $data);
        }
    }

    
    protected function process_pattern_submission($userid = 0, $cmid = null) {
        require_sesskey();

        if (!$userid) {
            $userid = required_param('userid', PARAM_INT);
        }

        return $this->pattern_submission($userid, $cmid);
    }
    
    protected function process_pattern_cancel($userid = 0, $cmid = 0 ) {
        require_sesskey();

        if (!$userid) {
            $userid = required_param('userid', PARAM_INT);
        }
        if (!$cmid) {
            $cmid = required_param('cmid', PARAM_INT);
        }
                
        return $this->pattern_cancel($userid, $cmid);
    }
    
    public function pattern_submission($userid, $cmid = null) {
        global $DB;
        require_capability('mod/assign:grade', $this->context);
        $submission = $this->get_user_submission($userid, false);
        $user = $DB->get_record('user',array('id'=>$userid));
        $notice = "";
        $hassubmission = false;
        if ($submission) {
            foreach ($this->submissionplugins as $plugin) {
                if ($plugin->is_enabled() && $plugin->is_visible() && $plugin->get_type() =='file') {
                    $pluginfiles = $plugin->get_files($submission, $user);
                    foreach ($pluginfiles as $source => $file) {
                        $context = context_module::instance($cmid);
                        $filename = $file->get_filename();
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        $filename = get_string('pattern','assign').'_'.$user->username.'.'.$ext;
                        $fs = get_file_storage();
                        $fullpath = "/$context->id/mod_folder/content/0/$filename";
                        if (!$fs->get_file_by_hash(sha1($fullpath))) {                             $filerecord = (object)array(
                                        'contextid' => $context->id,
                                        'component' => 'mod_folder',
                                        'filearea'  => 'content',
                                        'itemid'    => 0,
                                        'filepath'  => '/',
                                        'filename'  => $filename,
                                                                                'userid'    => $userid
                                        );
                            $new_file = $fs->create_file_from_storedfile($filerecord, $file);
                            
                            $flags = $DB->get_record('assign_user_flags', array('userid'=>$userid, 'assignment'=>$submission->assignment));
                            if($flags) {
                                $flags->patternstate = 1;
                                $this->update_user_flags($flags);
                            }else{
                                $flags = new stdClass();
                                $flags->userid = $userid;
                                $flags->patternstate = 1;
                                $flags->assignment = $submission->assignment;
                                $DB->insert_record('assign_user_flags', $flags);
                            }
                        }
                        $hassubmission = true;
                    }
                }
            }
            if (!$hassubmission) {
                $notice .= get_string('pattern_notice_onlinetext', 'assign', fullname($user)).'<br />';
            }
        }else{
                        $notice .= get_string('pattern_notice_nofile', 'assign', fullname($user)).'<br />';
        }
        return $notice;
    }
    
    public function pattern_cancel($userid, $cmid = null) {
        global $DB;
        require_capability('mod/assign:grade', $this->context);
        $submission = $this->get_user_submission($userid, false);

        $notice = "";
        $context = context_module::instance($cmid);
        
        if ($submission) {
            $fs = get_file_storage();
            $rs = $DB->get_records('files', array('contextid'=>$context->id, 'userid'=>$userid,
             'component'=>'mod_folder', 'filearea'=>'content', 'itemid'=>0));
            foreach ($rs as $orphan) {
                $file = $fs->get_file_instance($orphan);
                if (!$file->is_directory()) {
                    $file->delete();
                }
            }
            $flags = $DB->get_record('assign_user_flags', array('userid'=>$userid, 'assignment'=>$submission->assignment,'patternstate'=>1));
            if($flags){
                $flags->patternstate = 0;
                $this->update_user_flags($flags);
            }else{
                $user = $DB->get_record('user',array('id'=>$userid));
                $notice .= get_string('pattern_notice_cancel', 'assign', fullname($user)).'<br />';
            }
        }
        return $notice;
    }
    
    protected function reopen_submission_if_required($userid, $submission, $addattempt) {
        $instance = $this->get_instance();
        $maxattemptsreached = !empty($submission) &&
                              $submission->attemptnumber >= ($instance->maxattempts - 1) &&
                              $instance->maxattempts != ASSIGN_UNLIMITED_ATTEMPTS;
        $shouldreopen = false;
        if ($instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS) {
                        $gradeitem = $this->get_grade_item();
            if ($gradeitem) {
                $gradegrade = grade_grade::fetch(array('userid' => $userid, 'itemid' => $gradeitem->id));

                                if ($gradegrade && ($gradegrade->is_passed() === false)) {
                    $shouldreopen = true;
                }
            }
        }
        if ($instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL &&
                !empty($addattempt)) {
            $shouldreopen = true;
        }
        if ($shouldreopen && !$maxattemptsreached) {
            $this->add_attempt($userid);
            return true;
        }
        return false;
    }

    
    public function save_grade($userid, $data) {

                require_capability('mod/assign:grade', $this->context);

        $instance = $this->get_instance();
        $submission = null;
        if ($instance->teamsubmission) {
            $submission = $this->get_group_submission($userid, 0, false, $data->attemptnumber);
        } else {
            $submission = $this->get_user_submission($userid, false, $data->attemptnumber);
        }
        if ($instance->teamsubmission && !empty($data->applytoall)) {
            $groupid = 0;
            if ($this->get_submission_group($userid)) {
                $group = $this->get_submission_group($userid);
                if ($group) {
                    $groupid = $group->id;
                }
            }
            $members = $this->get_submission_group_members($groupid, true, $this->show_only_active_users());
            foreach ($members as $member) {
                                $this->apply_grade_to_user($data, $member->id, $data->attemptnumber);
                $this->process_outcomes($member->id, $data, $userid);
            }
        } else {
            $this->apply_grade_to_user($data, $userid, $data->attemptnumber);

            $this->process_outcomes($userid, $data);
        }

        return true;
    }

    
    protected function process_save_grade(&$mform) {
        global $CFG, $SESSION;
                require_once($CFG->dirroot . '/mod/assign/gradeform.php');

        require_sesskey();

        $instance = $this->get_instance();
        $rownum = required_param('rownum', PARAM_INT);
        $attemptnumber = optional_param('attemptnumber', -1, PARAM_INT);
        $useridlistid = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
        $userid = optional_param('userid', 0, PARAM_INT);
        if (!$userid) {
            if (empty($SESSION->mod_assign_useridlist[$this->get_useridlist_key($useridlistid)])) {
                                                $url = new moodle_url('/mod/assign/view.php', array('id' => $this->get_course_module()->id));
                throw new moodle_exception('useridlistnotcached', 'mod_assign', $url);
            }
            $useridlist = $SESSION->mod_assign_useridlist[$this->get_useridlist_key($useridlistid)];
        } else {
            $useridlist = array($userid);
            $rownum = 0;
        }

        $last = false;
        $userid = $useridlist[$rownum];
        if ($rownum == count($useridlist) - 1) {
            $last = true;
        }

        $data = new stdClass();

        $gradeformparams = array('rownum' => $rownum,
                                 'useridlistid' => $useridlistid,
                                 'last' => $last,
                                 'attemptnumber' => $attemptnumber,
                                 'userid' => $userid);
        $mform = new mod_assign_grade_form(null,
                                           array($this, $data, $gradeformparams),
                                           'post',
                                           '',
                                           array('class'=>'gradeform'));

        if ($formdata = $mform->get_data()) {
            return $this->save_grade($userid, $formdata);
        } else {
            return false;
        }
    }

    
    public static function can_upgrade_assignment($type, $version) {
        $assignment = new assign(null, null, null);
        return $assignment->can_upgrade($type, $version);
    }

    
    public function can_upgrade($type, $version) {
        if ($type == 'offline' && $version >= 2011112900) {
            return true;
        }
        foreach ($this->submissionplugins as $plugin) {
            if ($plugin->can_upgrade($type, $version)) {
                return true;
            }
        }
        foreach ($this->feedbackplugins as $plugin) {
            if ($plugin->can_upgrade($type, $version)) {
                return true;
            }
        }
        return false;
    }

    
    public function copy_area_files_for_upgrade($oldcontextid,
                                                $oldcomponent,
                                                $oldfilearea,
                                                $olditemid,
                                                $newcontextid,
                                                $newcomponent,
                                                $newfilearea,
                                                $newitemid) {
                        $count = 0;

        $fs = get_file_storage();

        $oldfiles = $fs->get_area_files($oldcontextid,
                                        $oldcomponent,
                                        $oldfilearea,
                                        $olditemid,
                                        'id',
                                        false);
        foreach ($oldfiles as $oldfile) {
            $filerecord = new stdClass();
            $filerecord->contextid = $newcontextid;
            $filerecord->component = $newcomponent;
            $filerecord->filearea = $newfilearea;
            $filerecord->itemid = $newitemid;
            $fs->create_file_from_storedfile($filerecord, $oldfile);
            $count += 1;
        }

        return $count;
    }

    
    protected function process_add_attempt_group($useridlist) {
        $groupsprocessed = array();
        $result = true;

        foreach ($useridlist as $userid) {
            $groupid = 0;
            $group = $this->get_submission_group($userid);
            if ($group) {
                $groupid = $group->id;
            }

            if (empty($groupsprocessed[$groupid])) {
                $result = $this->process_add_attempt($userid) && $result;
                $groupsprocessed[$groupid] = true;
            }
        }
        return $result;
    }

    
    protected function process_add_attempt($userid) {
        require_sesskey();

        return $this->add_attempt($userid);
    }

    
    protected function add_attempt($userid) {
        require_capability('mod/assign:grade', $this->context);

        if ($this->get_instance()->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_NONE) {
            return false;
        }

        if ($this->get_instance()->teamsubmission) {
            $oldsubmission = $this->get_group_submission($userid, 0, false);
        } else {
            $oldsubmission = $this->get_user_submission($userid, false);
        }

        if (!$oldsubmission) {
            return false;
        }

                if ($this->get_instance()->maxattempts != ASSIGN_UNLIMITED_ATTEMPTS &&
            $oldsubmission->attemptnumber >= ($this->get_instance()->maxattempts - 1)) {
            return false;
        }

                if ($this->get_instance()->teamsubmission) {
            $newsubmission = $this->get_group_submission($userid, 0, true, $oldsubmission->attemptnumber + 1);
        } else {
            $newsubmission = $this->get_user_submission($userid, true, $oldsubmission->attemptnumber + 1);
        }

                $newsubmission->status = ASSIGN_SUBMISSION_STATUS_REOPENED;

                $plugins = $this->get_submission_plugins();
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $plugin->add_attempt($oldsubmission, $newsubmission);
            }
        }

        $this->update_submission($newsubmission, $userid, false, $this->get_instance()->teamsubmission);
        $flags = $this->get_user_flags($userid, false);
        if (isset($flags->locked) && $flags->locked) {             $this->process_unlock_submission($userid);
        }
        return true;
    }

    
    public function get_user_grades_for_gradebook($userid) {
        global $DB, $CFG;
        $grades = array();
        $assignmentid = $this->get_instance()->id;

        $adminconfig = $this->get_admin_config();
        $gradebookpluginname = $adminconfig->feedback_plugin_for_gradebook;
        $gradebookplugin = null;

                foreach ($this->feedbackplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                if (('assignfeedback_' . $plugin->get_type()) == $gradebookpluginname) {
                    $gradebookplugin = $plugin;
                }
            }
        }
        if ($userid) {
            $where = ' WHERE u.id = :userid ';
        } else {
            $where = ' WHERE u.id != :userid ';
        }

                $params = array('assignid1'=>$assignmentid,
                        'assignid2'=>$assignmentid,
                        'userid'=>$userid);
        $graderesults = $DB->get_recordset_sql('SELECT
                                                    u.id as userid,
                                                    s.timemodified as datesubmitted,
                                                    g.grade as rawgrade,
                                                    g.timemodified as dategraded,
                                                    g.grader as usermodified
                                                FROM {user} u
                                                LEFT JOIN {assign_submission} s
                                                    ON u.id = s.userid and s.assignment = :assignid1 AND
                                                    s.latest = 1
                                                JOIN {assign_grades} g
                                                    ON u.id = g.userid and g.assignment = :assignid2 AND
                                                    g.attemptnumber = s.attemptnumber' .
                                                $where, $params);

        foreach ($graderesults as $result) {
            $gradingstatus = $this->get_grading_status($result->userid);
            if (!$this->get_instance()->markingworkflow || $gradingstatus == ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                $gradebookgrade = clone $result;
                                if ($gradebookplugin) {
                    $grade = $this->get_user_grade($result->userid, false);
                    if ($grade) {
                        $gradebookgrade->feedback = $gradebookplugin->text_for_gradebook($grade);
                        $gradebookgrade->feedbackformat = $gradebookplugin->format_for_gradebook($grade);
                    }
                }
                $grades[$gradebookgrade->userid] = $gradebookgrade;
            }
        }

        $graderesults->close();
        return $grades;
    }

    
    public function get_uniqueid_for_user($userid) {
        return self::get_uniqueid_for_user_static($this->get_instance()->id, $userid);
    }

    
    public static function allocate_unique_ids($assignid) {
        global $DB;

        $cm = get_coursemodule_from_instance('assign', $assignid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);

        $currentgroup = groups_get_activity_group($cm, true);
        $users = get_enrolled_users($context, "mod/assign:submit", $currentgroup, 'u.id');

                shuffle($users);

        foreach ($users as $user) {
            $record = $DB->get_record('assign_user_mapping',
                                      array('assignment'=>$assignid, 'userid'=>$user->id),
                                     'id');
            if (!$record) {
                $record = new stdClass();
                $record->assignment = $assignid;
                $record->userid = $user->id;
                $DB->insert_record('assign_user_mapping', $record);
            }
        }
    }

    
    public static function get_uniqueid_for_user_static($assignid, $userid) {
        global $DB;

                $params = array('assignment'=>$assignid, 'userid'=>$userid);
        if ($record = $DB->get_record('assign_user_mapping', $params, 'id')) {
            return $record->id;
        }

                                self::allocate_unique_ids($assignid);

                if ($record = $DB->get_record('assign_user_mapping', $params, 'id')) {
            return $record->id;
        }

                $record = new stdClass();
        $record->assignment = $assignid;
        $record->userid = $userid;

        return $DB->insert_record('assign_user_mapping', $record);
    }

    
    public function get_user_id_for_uniqueid($uniqueid) {
        return self::get_user_id_for_uniqueid_static($this->get_instance()->id, $uniqueid);
    }

    
    public static function get_user_id_for_uniqueid_static($assignid, $uniqueid) {
        global $DB;

                if ($record = $DB->get_record('assign_user_mapping',
                                      array('assignment'=>$assignid, 'id'=>$uniqueid),
                                      'userid',
                                      IGNORE_MISSING)) {
            return $record->userid;
        }

        return false;
    }

    
    public function get_marking_workflow_states_for_current_user() {
        if (!empty($this->markingworkflowstates)) {
            return $this->markingworkflowstates;
        }
        $states = array();
        if (has_capability('mod/assign:grade', $this->context)) {
            $states[ASSIGN_MARKING_WORKFLOW_STATE_INMARKING] = get_string('markingworkflowstateinmarking', 'assign');
            $states[ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW] = get_string('markingworkflowstatereadyforreview', 'assign');
        }
        if (has_any_capability(array('mod/assign:reviewgrades',
                                     'mod/assign:managegrades'), $this->context)) {
            $states[ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW] = get_string('markingworkflowstateinreview', 'assign');
            $states[ASSIGN_MARKING_WORKFLOW_STATE_READYFORRELEASE] = get_string('markingworkflowstatereadyforrelease', 'assign');
        }
        if (has_any_capability(array('mod/assign:releasegrades',
                                     'mod/assign:managegrades'), $this->context)) {
            $states[ASSIGN_MARKING_WORKFLOW_STATE_RELEASED] = get_string('markingworkflowstatereleased', 'assign');
        }
        $this->markingworkflowstates = $states;
        return $this->markingworkflowstates;
    }

    
    public function show_only_active_users() {
        global $CFG;

        if (is_null($this->showonlyactiveenrol)) {
            $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $this->showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);

            if (!is_null($this->context)) {
                $this->showonlyactiveenrol = $this->showonlyactiveenrol ||
                            !has_capability('moodle/course:viewsuspendedusers', $this->context);
            }
        }
        return $this->showonlyactiveenrol;
    }

    
    public function is_active_user($userid) {
        return !in_array($userid, get_suspended_userids($this->context, true));
    }

    
    public function is_gradebook_feedback_enabled() {
                $adminconfig = $this->get_admin_config();
        $gradebookplugin = $adminconfig->feedback_plugin_for_gradebook;
        $gradebookplugin = str_replace('assignfeedback_', '', $gradebookplugin);

                $gradebookfeedbackplugin = $this->get_feedback_plugin_by_type($gradebookplugin);

        if (empty($gradebookfeedbackplugin)) {
            return false;
        }

        if ($gradebookfeedbackplugin->is_visible() && $gradebookfeedbackplugin->is_enabled()) {
            return true;
        }

                return false;
    }

    
    public function get_grading_status($userid) {
        if ($this->get_instance()->markingworkflow) {
            $flags = $this->get_user_flags($userid, false);
            if (!empty($flags->workflowstate)) {
                return $flags->workflowstate;
            }
            return ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED;
        } else {
            $attemptnumber = optional_param('attemptnumber', -1, PARAM_INT);
            $grade = $this->get_user_grade($userid, false, $attemptnumber);

            if (!empty($grade) && $grade->grade !== null && $grade->grade >= 0) {
                return ASSIGN_GRADING_STATUS_GRADED;
            } else {
                return ASSIGN_GRADING_STATUS_NOT_GRADED;
            }
        }
    }

    
    public function get_useridlist_key_id() {
        return $this->useridlistid;
    }

    
    public function get_useridlist_key($id = null) {
        if ($id === null) {
            $id = $this->get_useridlist_key_id();
        }
        return $this->get_course_module()->id . '_' . $id;
    }

    
    protected function update_activity_completion_records($teamsubmission,
                                                          $requireallteammemberssubmit,
                                                          $submission,
                                                          $userid,
                                                          $complete,
                                                          $completion) {

        if (($teamsubmission && $submission->groupid > 0 && !$requireallteammemberssubmit) ||
            ($teamsubmission && $submission->groupid > 0 && $requireallteammemberssubmit &&
             $submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED)) {

            $members = groups_get_members($submission->groupid);

            foreach ($members as $member) {
                $completion->update_state($this->get_course_module(), $complete, $member->id);
            }
        } else {
            $completion->update_state($this->get_course_module(), $complete, $userid);
        }

        return;
    }

}


class assign_portfolio_caller extends portfolio_module_caller_base {

    
    protected $sid;

    
    protected $component;

    
    protected $area;

    
    protected $fileid;

    
    protected $cmid;

    
    protected $plugin;

    
    protected $editor;

    
    public static function expected_callbackargs() {
        return array(
            'cmid' => true,
            'sid' => false,
            'area' => false,
            'component' => false,
            'fileid' => false,
            'plugin' => false,
            'editor' => false,
        );
    }

    
    public function __construct($callbackargs) {
        parent::__construct($callbackargs);
        $this->cm = get_coursemodule_from_id('assign', $this->cmid, 0, false, MUST_EXIST);
    }

    
    public function load_data() {

        $context = context_module::instance($this->cmid);

        if (empty($this->fileid)) {
            if (empty($this->sid) || empty($this->area)) {
                throw new portfolio_caller_exception('invalidfileandsubmissionid', 'mod_assign');
            }

        }

                                $this->set_file_and_format_data($this->fileid,
                                        $context->id,
                                        $this->component,
                                        $this->area,
                                        $this->sid,
                                        'timemodified',
                                        false);

    }

    
    public function prepare_package() {

        if ($this->plugin && $this->editor) {
            $options = portfolio_format_text_options();
            $context = context_module::instance($this->cmid);
            $options->context = $context;

            $plugin = $this->get_submission_plugin();

            $text = $plugin->get_editor_text($this->editor, $this->sid);
            $format = $plugin->get_editor_format($this->editor, $this->sid);

            $html = format_text($text, $format, $options);
            $html = portfolio_rewrite_pluginfile_urls($html,
                                                      $context->id,
                                                      'mod_assign',
                                                      $this->area,
                                                      $this->sid,
                                                      $this->exporter->get('format'));

            $exporterclass = $this->exporter->get('formatclass');
            if (in_array($exporterclass, array(PORTFOLIO_FORMAT_PLAINHTML, PORTFOLIO_FORMAT_RICHHTML))) {
                if ($files = $this->exporter->get('caller')->get('multifiles')) {
                    foreach ($files as $file) {
                        $this->exporter->copy_existing_file($file);
                    }
                }
                return $this->exporter->write_new_file($html, 'assignment.html', !empty($files));
            } else if ($this->exporter->get('formatclass') == PORTFOLIO_FORMAT_LEAP2A) {
                $leapwriter = $this->exporter->get('format')->leap2a_writer();
                $entry = new portfolio_format_leap2a_entry($this->area . $this->cmid,
                                                           $context->get_context_name(),
                                                           'resource',
                                                           $html);

                $entry->add_category('web', 'resource_type');
                $entry->author = $this->user;
                $leapwriter->add_entry($entry);
                if ($files = $this->exporter->get('caller')->get('multifiles')) {
                    $leapwriter->link_files($entry, $files, $this->area . $this->cmid . 'file');
                    foreach ($files as $file) {
                        $this->exporter->copy_existing_file($file);
                    }
                }
                return $this->exporter->write_new_file($leapwriter->to_xml(),
                                                       $this->exporter->get('format')->manifest_name(),
                                                       true);
            } else {
                debugging('invalid format class: ' . $this->exporter->get('formatclass'));
            }

        }

        if ($this->exporter->get('formatclass') == PORTFOLIO_FORMAT_LEAP2A) {
            $leapwriter = $this->exporter->get('format')->leap2a_writer();
            $files = array();
            if ($this->singlefile) {
                $files[] = $this->singlefile;
            } else if ($this->multifiles) {
                $files = $this->multifiles;
            } else {
                throw new portfolio_caller_exception('invalidpreparepackagefile',
                                                     'portfolio',
                                                     $this->get_return_url());
            }

            $entryids = array();
            foreach ($files as $file) {
                $entry = new portfolio_format_leap2a_file($file->get_filename(), $file);
                $entry->author = $this->user;
                $leapwriter->add_entry($entry);
                $this->exporter->copy_existing_file($file);
                $entryids[] = $entry->id;
            }
            if (count($files) > 1) {
                $baseid = 'assign' . $this->cmid . $this->area;
                $context = context_module::instance($this->cmid);

                                $entry = new portfolio_format_leap2a_entry($baseid . 'group',
                                                           $context->get_context_name(),
                                                           'selection');
                $leapwriter->add_entry($entry);
                $leapwriter->make_selection($entry, $entryids, 'Folder');
            }
            return $this->exporter->write_new_file($leapwriter->to_xml(),
                                                   $this->exporter->get('format')->manifest_name(),
                                                   true);
        }
        return $this->prepare_package_file();
    }

    
    protected function get_submission_plugin() {
        global $CFG;
        if (!$this->plugin || !$this->cmid) {
            return null;
        }

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $context = context_module::instance($this->cmid);

        $assignment = new assign($context, null, null);
        return $assignment->get_submission_plugin_by_type($this->plugin);
    }

    
    public function get_sha1() {

        if ($this->plugin && $this->editor) {
            $plugin = $this->get_submission_plugin();
            $options = portfolio_format_text_options();
            $options->context = context_module::instance($this->cmid);

            $text = format_text($plugin->get_editor_text($this->editor, $this->sid),
                                $plugin->get_editor_format($this->editor, $this->sid),
                                $options);
            $textsha1 = sha1($text);
            $filesha1 = '';
            try {
                $filesha1 = $this->get_sha1_file();
            } catch (portfolio_caller_exception $e) {
                            }
            return sha1($textsha1 . $filesha1);
        }
        return $this->get_sha1_file();
    }

    
    public function expected_time() {
        return $this->expected_time_file();
    }

    
    public function check_permissions() {
        $context = context_module::instance($this->cmid);
        return has_capability('mod/assign:exportownsubmission', $context);
    }

    
    public static function display_name() {
        return get_string('modulename', 'assign');
    }

    
    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_FILE, PORTFOLIO_FORMAT_LEAP2A);
    }
}
