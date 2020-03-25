<?php



namespace core\task;

defined('MOODLE_INTERNAL') || die();


class search_optimize_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskglobalsearchoptimize', 'admin');
    }

    
    public function execute() {
        if (!\core_search\manager::is_global_search_enabled()) {
            return;
        }

        $globalsearch = \core_search\manager::instance();

                $globalsearch->optimize_index();
    }
}
