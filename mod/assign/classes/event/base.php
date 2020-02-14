<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


abstract class base extends \core\event\base {

    
    protected $assign;

    
    protected $legacylogdata;

    
    public function set_assign(\assign $assign) {
        if ($this->is_triggered()) {
            throw new \coding_exception('set_assign() must be done before triggerring of event');
        }
        if ($assign->get_context()->id != $this->get_context()->id) {
            throw new \coding_exception('Invalid assign isntance supplied!');
        }
        $this->assign = $assign;
    }

    
    public function get_assign() {
        if ($this->is_restored()) {
            throw new \coding_exception('get_assign() is intended for event observers only');
        }
        if (!isset($this->assign)) {
            debugging('assign property should be initialised in each event', DEBUG_DEVELOPER);
            global $CFG;
            require_once($CFG->dirroot . '/mod/assign/locallib.php');
            $cm = get_coursemodule_from_id('assign', $this->contextinstanceid, 0, false, MUST_EXIST);
            $course = get_course($cm->course);
            $this->assign = new \assign($this->get_context(), $cm, $course);
        }
        return $this->assign;
    }


    
    public function get_url() {
        return new \moodle_url('/mod/assign/view.php', array('id' => $this->contextinstanceid));
    }

    
    public function set_legacy_logdata($action = '', $info = '', $url = '') {
        $fullurl = 'view.php?id=' . $this->contextinstanceid;
        if ($url != '') {
            $fullurl .= '&' . $url;
        }

        $this->legacylogdata = array($this->courseid, 'assign', $action, $fullurl, $info, $this->contextinstanceid);
    }

    
    protected function get_legacy_logdata() {
        if (isset($this->legacylogdata)) {
            return $this->legacylogdata;
        }

        return null;
    }

    
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
