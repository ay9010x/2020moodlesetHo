<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/scorm/backup/moodle2/backup_scorm_stepslib.php');


class backup_scorm_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_scorm_activity_structure_step('scorm_structure', 'scorm.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search="/(".$base."\/mod\/scorm\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SCORMINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/scorm\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SCORMVIEWBYID*$2@$', $content);

        return $content;
    }
}
