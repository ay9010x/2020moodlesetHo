<?php


namespace core\task;


class blog_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskblogcron', 'admin');
    }

    
    public function execute() {
        global $CFG, $DB;

        $timenow = time();
                if (!empty($CFG->enableblogs) && $CFG->useexternalblogs) {
            require_once($CFG->dirroot . '/blog/lib.php');
            $sql = "timefetched < ? OR timefetched = 0";
            $externalblogs = $DB->get_records_select('blog_external', $sql, array($timenow - $CFG->externalblogcrontime));

            foreach ($externalblogs as $eb) {
                blog_sync_external_entries($eb);
            }
        }
                if (!empty($CFG->enableblogs) && $CFG->useblogassociations) {
            require_once($CFG->dirroot . '/blog/lib.php');
                        $DB->delete_records_select('blog_association', 'contextid NOT IN (SELECT id FROM {context})');
        }

    }

}
