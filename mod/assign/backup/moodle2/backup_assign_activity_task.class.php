<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/backup/moodle2/backup_assign_stepslib.php');


class backup_assign_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
        $this->add_step(new backup_assign_activity_structure_step('assign_structure', 'assign.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        $search="/(".$base."\/mod\/assign\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@ASSIGNINDEX*$2@$', $content);

        $search="/(".$base."\/mod\/assign\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@ASSIGNVIEWBYID*$2@$', $content);

        return $content;
    }

}

