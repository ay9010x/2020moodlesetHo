<?php


namespace mod_forum\task;

class cron_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('crontask', 'mod_forum');
    }

    
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/forum/lib.php');
        forum_cron();
    }

}
