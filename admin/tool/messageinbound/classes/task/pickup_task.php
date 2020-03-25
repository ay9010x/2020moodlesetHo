<?php



namespace tool_messageinbound\task;

defined('MOODLE_INTERNAL') || die();


class pickup_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('taskpickup', 'tool_messageinbound');
    }

    
    public function execute() {
        $manager = new \tool_messageinbound\manager();
        return $manager->pickup_messages();
    }
}
