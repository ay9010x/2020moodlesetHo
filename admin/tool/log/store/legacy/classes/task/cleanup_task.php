<?php



namespace logstore_legacy\task;

defined('MOODLE_INTERNAL') || die();

class cleanup_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('taskcleanup', 'logstore_legacy');
    }

    
    public function execute() {
        global $CFG, $DB;

        if (empty($CFG->loglifetime)) {
            return;
        }

        $loglifetime = time() - ($CFG->loglifetime * 3600 * 24);         $lifetimep = array($loglifetime);
        $start = time();

        while ($min = $DB->get_field_select("log", "MIN(time)", "time < ?", $lifetimep)) {
                                                $params = array(min($min + 3600 * 24, $loglifetime));
            $DB->delete_records_select("log", "time < ?", $params);
            if (time() > $start + 300) {
                                break;
            }
        }

        mtrace(" Deleted old legacy log records");
    }
}
