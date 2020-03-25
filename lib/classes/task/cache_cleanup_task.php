<?php


namespace core\task;


class cache_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskcachecleanup', 'admin');
    }

    
    public function execute() {
                gc_cache_flags();

    }

}
