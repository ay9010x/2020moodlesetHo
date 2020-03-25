<?php



namespace tool_monitor\event;

defined('MOODLE_INTERNAL') || die();


class subscription_criteria_met extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventsubcriteriamet', 'tool_monitor');
    }

    
    public function get_description() {
        return "The criteria for the subscription with id '{$this->other['subscriptionid']}' was met.";
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['subscriptionid'])) {
            throw new \coding_exception('The \'subscriptionid\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}
