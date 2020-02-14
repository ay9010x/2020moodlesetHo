<?php


namespace core\task;


class search_index_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskglobalsearchindex', 'admin');
    }

    
    public function execute() {
        if (!\core_search\manager::is_global_search_enabled()) {
            return;
        }
        $globalsearch = \core_search\manager::instance();

                $globalsearch->index();
    }
}
