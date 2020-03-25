<?php



namespace core\event;

use core\event\base;
use core_competency\user_competency;
use context_course;
defined('MOODLE_INTERNAL') || die();


class competency_user_competency_viewed_in_plan extends base {

    
    public static function create_from_user_competency_viewed_in_plan(user_competency $usercompetency, $planid) {
        if (!$usercompetency->get_id()) {
            throw new \coding_exception('The user competency ID must be set.');
        }
        $params = array(
            'contextid' => $usercompetency->get_context()->id,
            'objectid' => $usercompetency->get_id(),
            'relateduserid' => $usercompetency->get_userid(),
            'other' => array(
                'competencyid' => $usercompetency->get_competencyid(),
                'planid' => $planid
            )
        );

        $event = static::create($params);
        $event->add_record_snapshot(user_competency::TABLE, $usercompetency->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the user competency with id '$this->objectid' "
                . "in plan with id '" . $this->other['planid'] . "'";
    }

    
    public static function get_name() {
        return get_string('eventusercompetencyviewedinplan', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::user_competency_in_plan($this->relateduserid, $this->other['competencyid'],
            $this->other['planid']);
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = user_competency::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

    
    protected function validate_data() {
        if ($this->other === null) {
            throw new \coding_exception('The \'competencyid\' and \'planid\' values must be set.');
        }

        if (!isset($this->other['competencyid'])) {
            throw new \coding_exception('The \'competencyid\' value must be set.');
        }

        if (!isset($this->other['planid'])) {
            throw new \coding_exception('The \'planid\' value must be set.');
        }
    }

}
