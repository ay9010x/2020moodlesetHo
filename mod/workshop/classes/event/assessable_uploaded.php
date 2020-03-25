<?php



namespace mod_workshop\event;

defined('MOODLE_INTERNAL') || die();


class assessable_uploaded extends \core\event\assessable_uploaded {

    
    protected $legacylogdata = null;

    
    public function get_description() {
        return "The user with id '$this->userid' has uploaded the submission with id '$this->objectid' " .
            "to the workshop activity with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename   = 'workshop';
        $eventdata->cmid         = $this->contextinstanceid;
        $eventdata->itemid       = $this->objectid;
        $eventdata->courseid     = $this->courseid;
        $eventdata->userid       = $this->userid;
        $eventdata->content      = $this->other['content'];
        if ($this->other['pathnamehashes']) {
            $eventdata->pathnamehashes = $this->other['pathnamehashes'];
        }
        return $eventdata;
    }

    
    public static function get_legacy_eventname() {
        return 'assessable_content_uploaded';
    }

    
    protected function get_legacy_logdata() {
        return $this->legacylogdata;
    }

    
    public static function get_name() {
        return get_string('eventassessableuploaded', 'mod_workshop');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/workshop/submission.php',
            array('cmid' => $this->contextinstanceid, 'id' => $this->objectid));
    }

    
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'workshop_submissions';
    }

    
    public function set_legacy_logdata($legacylogdata) {
        $this->legacylogdata = $legacylogdata;
    }

    public static function get_objectid_mapping() {
        return array('db' => 'workshop_submissions', 'restore' => 'workshop_submission');
    }
}
