<?php


namespace core\task;


class create_contexts_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskcreatecontexts', 'admin');
    }

    
    public function execute() {
                \context_helper::create_instances();
        mtrace(' Created missing context instances');
    }

}
