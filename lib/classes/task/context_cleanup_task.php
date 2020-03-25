<?php



namespace core\task;


class context_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskcontextcleanup', 'admin');
    }

    
    public function execute() {
                \context_helper::cleanup_instances();
        mtrace(' Cleaned up context instances');
        \context_helper::build_all_paths(false);
                    }

}
