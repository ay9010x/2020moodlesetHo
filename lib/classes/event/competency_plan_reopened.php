<?php



namespace core\event;

use core\event\base;
use core_competency\plan;

defined('MOODLE_INTERNAL') || die();


class competency_plan_reopened extends base {

    
    public static final function create_from_plan(plan $plan) {
        if (!$plan->get_id()) {
            throw new \coding_exception('The plan ID must be set.');
        }
        $event = static::create(array(
            'contextid'  => $plan->get_context()->id,
            'objectid' => $plan->get_id(),
            'relateduserid' => $plan->get_userid(),
        ));
        $event->add_record_snapshot(plan::TABLE, $plan->to_record());
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' reopened the learning plan with id '$this->objectid'.";
    }

    
    public static function get_name() {
        return get_string('eventplanreopened', 'core_competency');
    }

    
    public function get_url() {
        return \core_competency\url::plan($this->objectid);
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = plan::TABLE;
    }

    
    public static function get_objectid_mapping() {
        return base::NOT_MAPPED;
    }

}
