<?php



namespace mod_assign\output;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use renderable;
use templatable;
use stdClass;


class grading_app implements templatable, renderable {

    
    public $userid = 0;

    
    public $groupid = 0;

    
    public $assignment = null;

    
    public function __construct($userid, $groupid, $assignment) {
        $this->userid = $userid;
        $this->groupid = $groupid;
        $this->assignment = $assignment;
        $this->participants = $assignment->list_participants_with_filter_status_and_group($groupid);
        if (!$this->userid && count($this->participants)) {
            $this->userid = reset($this->participants)->id;
        }
    }

    
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $export = new stdClass();
        $export->userid = $this->userid;
        $export->assignmentid = $this->assignment->get_instance()->id;
        $export->cmid = $this->assignment->get_course_module()->id;
        $export->contextid = $this->assignment->get_context()->id;
        $export->groupid = $this->groupid;
        $export->name = $this->assignment->get_instance()->name;
        $export->courseid = $this->assignment->get_course()->id;
        $export->participants = array();
        $num = 1;
        foreach ($this->participants as $idx => $record) {
            $user = new stdClass();
            $user->id = $record->id;
            $user->fullname = fullname($record);
            $user->requiregrading = $record->requiregrading;
            $user->submitted = $record->submitted;
            if (!empty($record->groupid)) {
                $user->groupid = $record->groupid;
                $user->groupname = $record->groupname;
            }
            if ($record->id == $this->userid) {
                $export->index = $num;
                $user->current = true;
            }
            $export->participants[] = $user;
            $num++;
        }

        $feedbackplugins = $this->assignment->get_feedback_plugins();
        $showreview = false;
        foreach ($feedbackplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                if ($plugin->supports_review_panel()) {
                    $showreview = true;
                }
            }
        }

        $export->showreview = $showreview;

        $time = time();
        $export->count = count($export->participants);
        $export->coursename = $this->assignment->get_course_context()->get_context_name();
        $export->caneditsettings = has_capability('mod/assign:addinstance', $this->assignment->get_context());
        $export->duedate = $this->assignment->get_instance()->duedate;
        $export->duedatestr = userdate($this->assignment->get_instance()->duedate);

                $due = '';
        if ($export->duedate - $time <= 0) {
            $due = get_string('assignmentisdue', 'assign');
        } else {
            $due = get_string('timeremainingcolon', 'assign', format_time($export->duedate - $time));
        }
        $export->timeremainingstr = $due;

        if ($export->duedate < $time) {
            $export->cutoffdate = $this->assignment->get_instance()->cutoffdate;
            $cutoffdate = $export->cutoffdate;
            if ($cutoffdate) {
                if ($cutoffdate > $time) {
                    $late = get_string('latesubmissionsaccepted', 'assign', userdate($export->cutoffdate));
                } else {
                    $late = get_string('nomoresubmissionsaccepted', 'assign');
                }
                $export->cutoffdatestr = $late;
            }
        }

        $export->defaultsendnotifications = $this->assignment->get_instance()->sendstudentnotifications;
        $export->rarrow = $output->rarrow();
        $export->larrow = $output->larrow();
                $export->showuseridentity = $CFG->showuseridentity;

        return $export;
    }

}
