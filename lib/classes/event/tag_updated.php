<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class tag_updated extends base {

    
    private $legacylogdata;

    
    protected function init() {
        $this->data['objecttable'] = 'tag';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventtagupdated', 'tag');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the tag with id '$this->objectid'.";
    }

    
    public function set_legacy_logdata($logdata) {
        $this->legacylogdata = $logdata;
    }

    
    protected function get_legacy_logdata() {
        if (isset($this->legacylogdata)) {
            return $this->legacylogdata;
        }

        return array($this->courseid, 'tag', 'update', 'index.php?id='. $this->objectid, $this->other['name']);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['name'])) {
            throw new \coding_exception('The \'name\' value must be set in other.');
        }

        if (!isset($this->other['rawname'])) {
            throw new \coding_exception('The \'rawname\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'tag', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        return false;
    }

}
