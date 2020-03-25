<?php


namespace core\task;


class automated_backup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskautomatedbackup', 'admin');
    }

    
    public function execute() {
        global $CFG;

                require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot.'/backup/util/helper/backup_cron_helper.class.php');
        \backup_cron_automated_helper::run_automated_backup();
    }

}
