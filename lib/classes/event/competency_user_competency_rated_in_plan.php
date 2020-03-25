<?php



namespace core\event;

use core\event\base;
use core_competency\user_competency;
defined('MOODLE_INTERNAL') || die();


class competency_user_competency_rated_in_plan extends base {

    
    public static function create_from_user_competency(user_competency $usercompetency, $planid) {
        if (!$usercompetency->get_id()) {
            throw new \coding_exception('The user competency ID must be set.');
        }

        $params = array(
            'contextid' => $usercompetency->get_context()->id,
            'objectid' => $usercompetency->get_id(),
            'relateduserid' => $usercompetency->get_userid(),
            'other' => array(
                'competencyid' => $usercompetency->get_competencyid(),
                'grade' => $usercompetency->get_grade(),
                'planid' => $planid
            )
        );

        $event = static::create($params);
        $event->add_record_snapshot(user_competency::TABLE, $usercompetency->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' rated the user competency with id '$this->objectid' with "
                . "'" . $this->other['grade'] . "' rating "
                . "in plan with id '" . $this->other['grade'] . "'";
    }

    
    public static function get_name() {
        return get_string('eventusercompetencyratedinplan', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::user_competency_in_plan($this->relateduserid, $this->other['competencyid'],
            $this->other['planid']);
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = user_competency::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

    
    protected function validate_data() {
        if (!isset($this->other) || !isset($this->other['competencyid'])) {
            throw new \coding_exception('The \'competencyid\' value must be set.');
        }

        if (!isset($this->other['planid'])) {
            throw new \coding_exception('The \'planid\' value must be set.');
        }

        if (!$this->relateduserid) {
            throw new \coding_exception('The \'relateduserid\' value must be set.');
        }

        if (!isset($this->other['grade'])) {
            throw new \coding_exception('The \'grade\' value must be set.');
        }
    }

}
