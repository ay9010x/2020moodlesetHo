<?php



namespace mod_data\event;

defined('MOODLE_INTERNAL') || die();


class record_created extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'data_records';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventrecordcreated', 'mod_data');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the data record with id '$this->objectid' for the data activity " .
            "with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/data/view.php', array('d' => $this->other['dataid'], 'rid' => $this->objectid));
    }

    
    public function get_legacy_logdata() {
        return array($this->courseid, 'data', 'add', 'view.php?d=' . $this->other['dataid'] . '&amp;rid=' . $this->objectid,
            $this->other['dataid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['dataid'])) {
            throw new \coding_exception('The \'dataid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'data_records', 'restore' => 'data_record');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['dataid'] = array('db' => 'data', 'restore' => 'data');

        return $othermapped;
    }
}
