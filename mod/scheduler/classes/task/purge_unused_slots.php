<?php



namespace mod_scheduler\task;

require_once(dirname(__FILE__).'/../../model/scheduler_instance.php');

class purge_unused_slots extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('purgeunusedslots', 'mod_scheduler');
    }

    public function execute() {
        \scheduler_instance::free_late_unused_slots();
    }
}