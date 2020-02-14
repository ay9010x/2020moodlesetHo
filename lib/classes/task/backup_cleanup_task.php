<?php


namespace core\task;


class backup_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskbackupcleanup', 'admin');
    }

    
    public function execute() {
        global $DB;

        $timenow = time();

                $loglifetime = get_config('backup', 'loglifetime');
        if (!empty($loglifetime)) {              $loglifetime = $timenow - ($loglifetime * 3600 * 24);
                        $DB->execute("DELETE FROM {backup_logs}
                           WHERE EXISTS (
                               SELECT 'x'
                                 FROM {backup_controllers} bc
                                WHERE bc.backupid = {backup_logs}.backupid
                                  AND bc.timecreated < ?)", array($loglifetime));
                        $DB->execute("DELETE FROM {backup_controllers}
                          WHERE timecreated < ?", array($loglifetime));
        }

    }

}
