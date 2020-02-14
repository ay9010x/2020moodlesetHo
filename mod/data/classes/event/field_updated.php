<?php



namespace mod_data\event;

defined('MOODLE_INTERNAL') || die();


class field_updated extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'data_fields';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventfieldupdated', 'mod_data');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the field with id '$this->objectid' in the data activity " .
            "with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/data/field.php', array('d' => $this->other['dataid']));
    }

    
    public function get_legacy_logdata() {
        return array($this->courseid, 'data', 'fields update', 'field.php?d=' . $this->other['dataid'] .
            '&amp;mode=display&amp;fid=' . $this->objectid, $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['fieldname'])) {
            throw new \coding_exception('The \'fieldname\' value must be set in other.');
        }

        if (!isset($this->other['dataid'])) {
            throw new \coding_exception('The \'dataid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'data_fields', 'restore' => 'data_field');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['dataid'] = array('db' => 'data', 'restore' => 'data');

        return $othermapped;
    }
}
