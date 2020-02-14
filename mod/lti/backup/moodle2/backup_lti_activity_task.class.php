<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/lti/backup/moodle2/backup_lti_stepslib.php');


class backup_lti_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_lti_activity_structure_step('lti_structure', 'lti.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search = "/(".$base."\/mod\/lti\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@LTIINDEX*$2@$', $content);

                $search = "/(".$base."\/mod\/lti\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@LTIVIEWBYID*$2@$', $content);

        return $content;
    }
}
