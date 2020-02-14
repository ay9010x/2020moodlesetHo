<?php



namespace logstore_standard\task;

defined('MOODLE_INTERNAL') || die();

class cleanup_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('taskcleanup', 'logstore_standard');
    }

    
    public function execute() {
        global $DB;

        $loglifetime = (int)get_config('logstore_standard', 'loglifetime');

        if (empty($loglifetime) || $loglifetime < 0) {
            return;
        }

        $loglifetime = time() - ($loglifetime * 3600 * 24);         $lifetimep = array($loglifetime);
        $start = time();

        while ($min = $DB->get_field_select("logstore_standard_log", "MIN(timecreated)", "timecreated < ?", $lifetimep)) {
                                                $params = array(min($min + 3600 * 24, $loglifetime));
            $DB->delete_records_select("logstore_standard_log", "timecreated < ?", $params);
            if (time() > $start + 300) {
                                break;
            }
        }

        mtrace(" Deleted old log records from standard store.");
    }
}
