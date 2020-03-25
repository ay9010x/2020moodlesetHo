<?php


namespace core\task;


class cache_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskcachecron', 'admin');
    }

    
    public function execute() {
        \cache_helper::cron();
    }

}
