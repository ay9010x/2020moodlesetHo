<?php


namespace enrol_imsenterprise\task;


class cron_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('imsenterprisecrontask', 'enrol_imsenterprise');
    }

    
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/imsenterprise/lib.php');
        $ims = new \enrol_imsenterprise_plugin();
        $ims->cron();
    }

}
