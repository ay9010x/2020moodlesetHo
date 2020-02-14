<?php



namespace enrol_flatfile\task;

defined('MOODLE_INTERNAL') || die;


class flatfile_sync_task extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('flatfilesync', 'enrol_flatfile');
    }

    
    public function execute() {
        global $CFG;

        require_once($CFG->dirroot . '/enrol/flatfile/lib.php');

        if (!enrol_is_enabled('flatfile')) {
            return;
        }

                $plugin = enrol_get_plugin('flatfile');
        $result = $plugin->sync(new \null_progress_trace());
        return $result;

    }

}
