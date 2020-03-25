<?php



namespace core\task;
defined('MOODLE_INTERNAL') || die();

use core_competency\api;
use core_competency\plan;


class complete_plans_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('completeplanstask', 'core_competency');
    }

    
    public function execute() {
        if (!api::is_enabled()) {
            return;
        }

        $records = plan::get_recordset_for_due_and_incomplete();
        foreach ($records as $record) {
            $plan = new plan(0, $record);
            api::complete_plan($plan);
        }
        $records->close();
    }

}
