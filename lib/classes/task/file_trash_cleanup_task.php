<?php


namespace core\task;


class file_trash_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskfiletrashcleanup', 'admin');
    }

    
    public function execute() {

                $fs = get_file_storage();
        $fs->cron();
    }

}
