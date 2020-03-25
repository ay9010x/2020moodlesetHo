<?php


namespace core\task;


class stats_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskstatscron', 'admin');
    }

    
    public function execute() {
        global $CFG;

                if (!empty($CFG->enablestats) and empty($CFG->disablestatsprocessing)) {
            require_once($CFG->dirroot.'/lib/statslib.php');
                        $maxdays = empty($CFG->statsruntimedays) ? 31 : abs($CFG->statsruntimedays);
            if (stats_cron_daily($maxdays)) {
                if (stats_cron_weekly()) {
                    if (stats_cron_monthly()) {
                        stats_clean_old();
                    }
                }
            }
            \core_php_time_limit::raise();
        }
    }
}
