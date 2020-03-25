<?php


namespace core\task;


class portfolio_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskportfoliocron', 'admin');
    }

    
    public function execute() {
        global $CFG;

        if ($CFG->enableportfolios) {
            require_once($CFG->libdir . '/portfoliolib.php');
            portfolio_cron();
        }
    }

}
