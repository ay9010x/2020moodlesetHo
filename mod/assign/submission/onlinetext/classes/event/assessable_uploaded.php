<?php



namespace assignsubmission_onlinetext\event;

defined('MOODLE_INTERNAL') || die();


class assessable_uploaded extends \core\event\assessable_uploaded {

    
    public function get_description() {
        return "The user with id '$this->userid' has saved an online text submission with id '$this->objectid' " .
            "in the assignment activity with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->contextinstanceid;
        $eventdata->itemid = $this->objectid;
        $eventdata->courseid = $this->courseid;
        $eventdata->userid = $this->userid;
        $eventdata->content = $this->other['content'];
        if ($this->other['pathnamehashes']) {
            $eventdata->pathnamehashes = $this->other['pathnamehashes'];
        }
        return $eventdata;
    }

    
    public static function get_legacy_eventname() {
        return 'assessable_content_uploaded';
    }

    
    public static function get_name() {
        return get_string('eventassessableuploaded', 'assignsubmission_onlinetext');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/assign/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assign_submission';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_submission', 'restore' => 'submission');
    }
}
