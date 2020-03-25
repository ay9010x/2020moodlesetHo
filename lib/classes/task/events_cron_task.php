<?php


namespace core\task;


class events_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskeventscron', 'admin');
    }

    
    public function execute() {
        events_cron();
    }

}
