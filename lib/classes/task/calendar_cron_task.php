<?php


namespace core\task;


class calendar_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskcalendarcron', 'admin');
    }

    
    public function execute() {
        global $CFG;

                require_once("{$CFG->dirroot}/calendar/lib.php");
        calendar_cron();
    }

}
