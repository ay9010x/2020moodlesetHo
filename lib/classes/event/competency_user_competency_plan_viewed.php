<?php



namespace core\event;

use core\event\base;
use core_competency\user_competency_plan;

defined('MOODLE_INTERNAL') || die();


class competency_user_competency_plan_viewed extends base {

    
    public static function create_from_user_competency_plan(user_competency_plan $usercompetencyplan) {
        if (!$usercompetencyplan->get_id()) {
            throw new \coding_exception('The user competency plan ID must be set.');
        }
        $event = static::create(array(
            'contextid' => $usercompetencyplan->get_context()->id,
            'objectid' => $usercompetencyplan->get_id(),
            'relateduserid' => $usercompetencyplan->get_userid(),
            'other' => array(
                'planid' => $usercompetencyplan->get_planid(),
                'competencyid' => $usercompetencyplan->get_competencyid()
            )
        ));
        $event->add_record_snapshot(user_competency_plan::TABLE, $usercompetencyplan->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the user competency plan with id '$this->objectid'";
    }

    
    public static function get_name() {
        return get_string('eventusercompetencyplanviewed', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::user_competency_in_plan($this->relateduserid, $this->other['competencyid'],
            $this->other['planid']);
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = user_competency_plan::TABLE;
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
