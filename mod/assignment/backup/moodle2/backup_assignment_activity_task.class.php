<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/assignment/backup/moodle2/backup_assignment_stepslib.php');


class backup_assignment_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_assignment_activity_structure_step('assignment_structure', 'assignment.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/assignment\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@ASSIGNMENTINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/assignment\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@ASSIGNMENTVIEWBYID*$2@$', $content);

        return $content;
    }
}
