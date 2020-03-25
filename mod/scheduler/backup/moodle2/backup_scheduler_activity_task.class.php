<?php



require_once($CFG->dirroot . '/mod/scheduler/backup/moodle2/backup_scheduler_stepslib.php');


class backup_scheduler_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new backup_scheduler_activity_structure_step('scheduler_structure', 'scheduler.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search = "/(".$base."\/mod\/scheduler\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@SCHEDULERINDEX*$2@$', $content);

                $search = "/(".$base."\/mod\/scheduler\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@SCHEDULERVIEWBYID*$2@$', $content);

        return $content;
    }
}
