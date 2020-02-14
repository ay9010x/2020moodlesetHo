<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class grade_deleted extends base {

    
    protected $grade;

    
    protected function init() {
        $this->data['objecttable'] = 'grade_grades';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function create_from_grade(\grade_grade $grade) {
        $event = self::create(array(
            'objectid'      => $grade->id,
            'context'       => \context_course::instance($grade->grade_item->courseid),
            'relateduserid' => $grade->userid,
            'other'         => array(
                'itemid'     => $grade->itemid,
                'overridden' => !empty($grade->overridden),
                'finalgrade' => $grade->finalgrade),
        ));
        $event->grade = $grade;
        return $event;
    }

    
    public function get_grade() {
        if ($this->is_restored()) {
            throw new \coding_exception('get_grade() is intended for event observers only');
        }
        return $this->grade;
    }

    
    public static function get_name() {
        return get_string('eventgradedeleted', 'core_grades');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the grade with id '$this->objectid' for the user with " .
            "id '$this->relateduserid' for the grade item with id '{$this->other['itemid']}'.";
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['itemid'])) {
            throw new \coding_exception('The \'itemid\' value must be set in other.');
        }

        if (!isset($this->other['overridden'])) {
            throw new \coding_exception('The \'overridden\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'grade_grades', 'restore' => 'grade_grades');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['itemid'] = array('db' => 'grade_items', 'restore' => 'grade_item');

        return $othermapped;
    }
}
