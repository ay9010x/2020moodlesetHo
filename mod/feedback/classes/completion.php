<?php



defined('MOODLE_INTERNAL') || die();


class mod_feedback_completion extends mod_feedback_structure {
    
    protected $completed;
    
    protected $completedtmp = null;
    
    protected $valuestmp = null;
    
    protected $values = null;
    
    protected $iscompleted = false;


    
    public function __construct($feedback, $cm, $courseid, $iscompleted = false, $completedid = null, $userid = null) {
        global $DB;
                if ($feedback->course == SITEID) {
            $courseid = $courseid ?: SITEID;
        } else {
            $courseid = 0;
        }
        parent::__construct($feedback, $cm, $courseid, 0);
        if ($iscompleted) {
                        $this->iscompleted = true;
            $params = array('feedback' => $feedback->id);
            if (!$userid && !$completedid) {
                throw new coding_exception('Either $completedid or $userid must be specified for completed feedbacks');
            }
            if ($completedid) {
                $params['id'] = $completedid;
            }
            if ($userid) {
                                                $params['anonymous_response'] = FEEDBACK_ANONYMOUS_NO;
                $params['userid'] = $userid;
            }
            $this->completed = $DB->get_record('feedback_completed', $params, '*', MUST_EXIST);
            $this->courseid = $this->completed->courseid;
        }
    }

    
    public function get_completed() {
        return $this->completed;
    }

    
    protected function get_current_completed_tmp() {
        global $USER, $DB;
        if ($this->completedtmp === null) {
            $params = array('feedback' => $this->get_feedback()->id);
            if ($courseid = $this->get_courseid()) {
                $params['courseid'] = $courseid;
            }
            if (isloggedin() && !isguestuser()) {
                $params['userid'] = $USER->id;
            } else {
                $params['guestid'] = sesskey();
            }
            $this->completedtmp = $DB->get_record('feedback_completedtmp', $params);
        }
        return $this->completedtmp;
    }

    
    protected function can_see_item($item) {
        if (empty($item->dependitem)) {
            return true;
        }
        if ($this->dependency_has_error($item)) {
            return null;
        }
        $allitems = $this->get_items();
        $ditem = $allitems[$item->dependitem];
        $itemobj = feedback_get_item_class($ditem->typ);
        if ($this->iscompleted) {
            $value = $this->get_values($ditem);
        } else {
            $value = $this->get_values_tmp($ditem);
        }
        if ($value === null) {
            return null;
        }
        return $itemobj->compare_value($ditem, $value, $item->dependvalue) ? true : false;
    }

    
    protected function dependency_has_error($item) {
        if (empty($item->dependitem)) {
                        return false;
        }
        $allitems = $this->get_items();
        if (!array_key_exists($item->dependitem, $allitems)) {
                        return true;
        }
        $itemids = array_keys($allitems);
        $index1 = array_search($item->dependitem, $itemids);
        $index2 = array_search($item->id, $itemids);
        if ($index1 >= $index2) {
                        return true;
        }
        for ($i = $index1 + 1; $i < $index2; $i++) {
            if ($allitems[$itemids[$i]]->typ === 'pagebreak') {
                return false;
            }
        }
                return true;
    }

    
    public function get_item_value($item) {
        if ($this->iscompleted) {
            return $this->get_values($item);
        } else {
            return $this->get_values_tmp($item);
        }
    }

    
    protected function get_values_tmp($item = null) {
        global $DB;
        if ($this->valuestmp === null) {
            $completedtmp = $this->get_current_completed_tmp();
            if ($completedtmp) {
                $this->valuestmp = $DB->get_records_menu('feedback_valuetmp',
                        ['completed' => $completedtmp->id], '', 'item, value');
            } else {
                $this->valuestmp = array();
            }
        }
        if ($item) {
            return array_key_exists($item->id, $this->valuestmp) ? $this->valuestmp[$item->id] : null;
        }
        return $this->valuestmp;
    }

    
    protected function get_values($item = null) {
        global $DB;
        if ($this->values === null) {
            if ($this->completed) {
                $this->values = $DB->get_records_menu('feedback_value',
                        ['completed' => $this->completed->id], '', 'item, value');
            } else {
                $this->values = array();
            }
        }
        if ($item) {
            return array_key_exists($item->id, $this->values) ? $this->values[$item->id] : null;
        }
        return $this->values;
    }

    
    public function get_pages() {
        $pages = [[]];         $items = $this->get_items();
        foreach ($items as $item) {
            if ($item->typ === 'pagebreak') {
                $pages[] = [];
            } else if ($this->can_see_item($item) !== false) {
                $pages[count($pages) - 1][] = $item;
            }
        }
        return $pages;
    }

    
    protected function get_last_completed_page() {
        $completed = [];
        $incompleted = [];
        $pages = $this->get_pages();
        foreach ($pages as $pageidx => $pageitems) {
            foreach ($pageitems as $item) {
                if ($item->hasvalue) {
                    if ($this->get_values_tmp($item) !== null) {
                        $completed[$pageidx] = true;
                    } else {
                        $incompleted[$pageidx] = true;
                    }
                }
            }
        }
        $completed = array_keys($completed);
        $incompleted = array_keys($incompleted);
                $completed = array_diff($completed, $incompleted);
                $firstincompleted = $incompleted ? min($incompleted) : null;
        if ($firstincompleted !== null) {
            $completed = array_filter($completed, function($a) use ($firstincompleted) {
                return $a < $firstincompleted;
            });
        }
        $lastcompleted = $completed ? max($completed) : null;
        return [$lastcompleted, $firstincompleted];
    }

    
    public function get_next_page($gopage, $strictcheck = true) {
        if ($strictcheck) {
            list($lastcompleted, $firstincompleted) = $this->get_last_completed_page();
            if ($firstincompleted !== null && $firstincompleted <= $gopage) {
                return $firstincompleted;
            }
        }
        $pages = $this->get_pages();
        for ($pageidx = $gopage + 1; $pageidx < count($pages); $pageidx++) {
            if (!empty($pages[$pageidx])) {
                return $pageidx;
            }
        }
                return null;
    }

    
    public function get_previous_page($gopage, $strictcheck = true) {
        if (!$gopage) {
                        return null;
        }
        $pages = $this->get_pages();
        $rv = null;
                for ($pageidx = $gopage - 1; $pageidx >= 0; $pageidx--) {
            if (!empty($pages[$pageidx])) {
                $rv = $pageidx;
                break;
            }
        }
        if ($rv === null) {
                        return null;
        }
        if ($rv > 0 && $strictcheck) {
                        list($lastcompleted, $firstincompleted) = $this->get_last_completed_page();
            if ($firstincompleted !== null && $firstincompleted < $rv) {
                return $firstincompleted;
            }
        }
        return $rv;
    }

    
    public function get_resume_page() {
        list($lastcompleted, $firstincompleted) = $this->get_last_completed_page();
        return $lastcompleted === null ? 0 : $this->get_next_page($lastcompleted, false);
    }

    
    protected function create_current_completed_tmp() {
        global $USER, $DB;
        $record = (object)['feedback' => $this->feedback->id];
        if ($this->get_courseid()) {
            $record->courseid = $this->get_courseid();
        }
        if (isloggedin() && !isguestuser()) {
            $record->userid = $USER->id;
        } else {
            $record->guestid = sesskey();
        }
        $record->timemodified = time();
        $record->anonymous_response = $this->feedback->anonymous;
        $id = $DB->insert_record('feedback_completedtmp', $record);
        $this->completedtmp = $DB->get_record('feedback_completedtmp', ['id' => $id]);
        $this->valuestmp = null;
        return $this->completedtmp;
    }

    
    public function save_response_tmp($data) {
        global $DB;
        if (!$completedtmp = $this->get_current_completed_tmp()) {
            $completedtmp = $this->create_current_completed_tmp();
        } else {
            $currentime = time();
            $DB->update_record('feedback_completedtmp',
                    ['id' => $completedtmp->id, 'timemodified' => $currentime]);
            $completedtmp->timemodified = $currentime;
        }

                $existingvalues = $DB->get_records_menu('feedback_valuetmp',
                ['completed' => $completedtmp->id], '', 'item, id');

                $allitems = $this->get_items();
        foreach ($allitems as $item) {
            if (!$item->hasvalue) {
                continue;
            }
            $keyname = $item->typ . '_' . $item->id;
            if (!isset($data->$keyname)) {
                                continue;
            }

            $newvalue = ['item' => $item->id, 'completed' => $completedtmp->id, 'course_id' => $completedtmp->courseid];

                        $itemobj = feedback_get_item_class($item->typ);
            $newvalue['value'] = $itemobj->create_value($data->$keyname);

                        if (array_key_exists($item->id, $existingvalues)) {
                $newvalue['id'] = $existingvalues[$item->id];
                $DB->update_record('feedback_valuetmp', $newvalue);
            } else {
                $DB->insert_record('feedback_valuetmp', $newvalue);
            }
        }

                $this->valuestmp = null;
    }

    
    public function save_response() {
        global $USER, $SESSION, $DB;

        $feedbackcompleted = $this->find_last_completed();
        $feedbackcompletedtmp = $this->get_current_completed_tmp();

        if (feedback_check_is_switchrole()) {
                        $this->delete_completedtmp();
            return;
        }

                $completedid = feedback_save_tmp_values($feedbackcompletedtmp, $feedbackcompleted);
        $this->completed = $DB->get_record('feedback_completed', array('id' => $completedid));

                if ($this->feedback->anonymous == FEEDBACK_ANONYMOUS_NO) {
            feedback_send_email($this->cm, $this->feedback, $this->cm->get_course(), $USER);
        } else {
            feedback_send_email_anonym($this->cm, $this->feedback, $this->cm->get_course());
        }

        unset($SESSION->feedback->is_started);

                $completion = new completion_info($this->cm->get_course());
        if (isloggedin() && !isguestuser() && $completion->is_enabled($this->cm) && $this->feedback->completionsubmit) {
            $completion->update_state($this->cm, COMPLETION_COMPLETE);
        }
    }

    
    protected function delete_completedtmp() {
        global $DB;

        if ($completedtmp = $this->get_current_completed_tmp()) {
            $DB->delete_records('feedback_valuetmp', ['completed' => $completedtmp->id]);
            $DB->delete_records('feedback_completedtmp', ['id' => $completedtmp->id]);
            $this->completedtmp = null;
        }
    }

    
    protected function find_last_completed() {
        global $USER, $DB;
        if (isloggedin() || isguestuser()) {
                        return false;
        }
        if ($this->is_anonymous()) {
                        return false;
        }
        $params = array('feedback' => $this->feedback->id, 'userid' => $USER->id);
        if ($this->get_courseid()) {
            $params['courseid'] = $this->get_courseid();
        }
        $this->completed = $DB->get_record('feedback_completed', $params);
        return $this->completed;
    }

    
    public function can_complete() {
        global $CFG;

        $context = context_module::instance($this->cm->id);
        if (has_capability('mod/feedback:complete', $context)) {
            return true;
        }

        if (!empty($CFG->feedback_allowfullanonymous)
                    AND $this->feedback->course == SITEID
                    AND $this->feedback->anonymous == FEEDBACK_ANONYMOUS_YES
                    AND (!isloggedin() OR isguestuser())) {
                        return true;
        }

        return false;
    }

    
    public function can_submit() {
        if ($this->get_feedback()->multiple_submit == 0 ) {
            if ($this->is_already_submitted()) {
                return false;
            }
        }
        return true;
    }
}