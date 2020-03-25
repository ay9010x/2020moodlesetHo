<?php


namespace tool_langimport\task;


class update_langpacks_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('updatelangs', 'tool_langimport');
    }

    
    public function execute() {
        global $CFG;

        if (!empty($CFG->skiplangupgrade)) {
            mtrace('Langpack update skipped. ($CFG->skiplangupgrade set)');

            return;
        }

        $controller = new \tool_langimport\controller();
        if ($controller->update_all_installed_languages()) {
            foreach ($controller->info as $message) {
                mtrace($message);
            }
            return true;
        } else {
            foreach ($controller->errors as $message) {
                mtrace($message);
            }
            return false;
        }

    }

}
