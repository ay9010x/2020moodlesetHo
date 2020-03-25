<?php



namespace assignsubmission_file\event;

defined('MOODLE_INTERNAL') || die();


class assessable_uploaded extends \core\event\assessable_uploaded {

    
    protected $legacyfiles = array();

    
    public function get_description() {
        return "The user with id '$this->userid' has uploaded a file to the submission with id '$this->objectid' " .
            "in the assignment activity with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->contextinstanceid;
        $eventdata->itemid = $this->objectid;
        $eventdata->courseid = $this->courseid;
        $eventdata->userid = $this->userid;
        if (count($this->legacyfiles) > 1) {
            $eventdata->files = $this->legacyfiles;
        }
        $eventdata->file = $this->legacyfiles;
        $eventdata->pathnamehashes = array_keys($this->legacyfiles);
        return $eventdata;
    }

    
    public static function get_legacy_eventname() {
        return 'assessable_file_uploaded';
    }

    
    public static function get_name() {
        return get_string('eventassessableuploaded', 'assignsubmission_file');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/assign/view.php', array('id' => $this->contextinstanceid));
    }

    
    public function set_legacy_files($legacyfiles) {
        $this->legacyfiles = $legacyfiles;
    }

    
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assign_submission';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_submission', 'restore' => 'submission');
    }
}
