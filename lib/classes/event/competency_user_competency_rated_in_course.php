<?php



namespace core\event;

use core\event\base;
use core_competency\user_competency_course;
use context_course;
defined('MOODLE_INTERNAL') || die();


class competency_user_competency_rated_in_course extends base {

    
    public static function create_from_user_competency_course(user_competency_course $usercompetencycourse) {
        if (!$usercompetencycourse->get_id()) {
            throw new \coding_exception('The user competency course ID must be set.');
        }

        $params = array(
            'objectid' => $usercompetencycourse->get_id(),
            'relateduserid' => $usercompetencycourse->get_userid(),
            'other' => array(
                'competencyid' => $usercompetencycourse->get_competencyid(),
                'grade' => $usercompetencycourse->get_grade()
            )
        );
        $coursecontext = context_course::instance($usercompetencycourse->get_courseid());
        $params['contextid'] = $coursecontext->id;
        $params['courseid'] = $usercompetencycourse->get_courseid();

        $event = static::create($params);
        $event->add_record_snapshot(user_competency_course::TABLE, $usercompetencycourse->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' rated the user competency with id '$this->objectid' with "
                . "'" . $this->other['grade'] . "' rating "
                . "in course with id '$this->courseid'";
    }

    
    public static function get_name() {
        return get_string('eventusercompetencyratedincourse', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::user_competency_in_course($this->relateduserid, $this->other['competencyid'],
            $this->courseid);
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = user_competency_course::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

    
    protected function validate_data() {
        if (!isset($this->other) || !isset($this->other['competencyid'])) {
            throw new \coding_exception('The \'competencyid\' value must be set.');
        }

        if (!$this->courseid) {
            throw new \coding_exception('The \'courseid\' value must be set.');
        }

        if (!$this->relateduserid) {
            throw new \coding_exception('The \'relateduserid\' value must be set.');
        }

        if (!isset($this->other['grade'])) {
            throw new \coding_exception('The \'grade\' value must be set.');
        }
    }

}
