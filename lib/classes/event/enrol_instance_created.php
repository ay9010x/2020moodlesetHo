<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class enrol_instance_created extends base {

    
    public static final function create_from_record($enrol) {
        $event = static::create(array(
            'context'  => \context_course::instance($enrol->courseid),
            'objectid' => $enrol->id,
            'other'    => array('enrol' => $enrol->enrol)
        ));
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the instance of enrolment method '" .
                $this->other['enrol'] . "' with id '$this->objectid'.";
    }

    
    public static function get_name() {
        return get_string('eventenrolinstancecreated', 'enrol');
    }

    
    public function get_url() {
        return new \moodle_url('/enrol/instances.php', array('id' => $this->courseid));
    }

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'enrol';
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['enrol'])) {
            throw new \coding_exception('The \'enrol\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'enrol', 'restore' => 'enrol');
    }

    public static function get_other_mapping() {
                return false;
    }
}
