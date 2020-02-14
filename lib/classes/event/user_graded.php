<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class user_graded extends base {
    
    protected $grade;

    
    public static function create_from_grade(\grade_grade $grade) {
        $event = self::create(array(
            'context'       => \context_course::instance($grade->grade_item->courseid),
            'objectid'      => $grade->id,
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

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'grade_grades';
    }

    
    public static function get_name() {
        return get_string('eventusergraded', 'core_grades');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the grade with id '$this->objectid' for the user with " .
            "id '$this->relateduserid' for the grade item with id '{$this->other['itemid']}'.";
    }

    
    public function get_url() {
        return new \moodle_url('/grade/edit/tree/grade.php', array(
            'courseid' => $this->courseid,
            'itemid'   => $this->other['itemid'],
            'userid'   => $this->relateduserid,
        ));
    }

    
    public function get_legacy_logdata() {
        $user = $this->get_record_snapshot('user', $this->relateduserid);
        $fullname = fullname($user);
        $info = $this->grade->grade_item->itemname . ': ' . $fullname;
        $url = '/report/grader/index.php?id=' . $this->courseid;

        return array($this->courseid, 'grade', 'update', $url, $info);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['itemid'])) {
            throw new \coding_exception('The \'itemid\' value must be set in other.');
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
