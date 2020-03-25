<?php



namespace tool_messageinbound\task;

defined('MOODLE_INTERNAL') || die();


class cleanup_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('taskcleanup', 'tool_messageinbound');
    }

    
    public function execute() {
        $manager = new \tool_messageinbound\manager();
        return $manager->tidy_old_messages();
    }
}
