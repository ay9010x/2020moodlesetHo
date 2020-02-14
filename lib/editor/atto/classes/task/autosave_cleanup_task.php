<?php


namespace editor_atto\task;

use \core\task\scheduled_task;


class autosave_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskautosavecleanup', 'editor_atto');
    }

    
    public function execute() {
        global $DB;

        $now = time();
                                $before = $now - 60*60*24*4;

        $DB->delete_records_select('editor_atto_autosave', 'timemodified < :before', array('before' => $before));
    }

}
