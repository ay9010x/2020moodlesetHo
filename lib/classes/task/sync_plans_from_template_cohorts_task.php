<?php



namespace core\task;
defined('MOODLE_INTERNAL') || die();

use core_competency\api;
use core_competency\template_cohort;


class sync_plans_from_template_cohorts_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('syncplanscohorts', 'core_competency');
    }

    
    public function execute() {
        if (!api::is_enabled()) {
            return;
        }

        $missingplans = template_cohort::get_all_missing_plans(self::get_last_run_time());

        foreach ($missingplans as $missingplan) {
            foreach ($missingplan['userids'] as $userid) {
                try {
                    api::create_plan_from_template($missingplan['template'], $userid);
                } catch (\Exception $e) {
                    debugging(sprintf('Exception caught while creating plan for user %d from template %d. Message: %s',
                        $userid, $missingplan['template']->get_id(), $e->getMessage()), DEBUG_DEVELOPER);
                }
            }
        }
    }
}
