<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/imscp/backup/moodle2/backup_imscp_stepslib.php');


class backup_imscp_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_imscp_activity_structure_step('imscp_structure', 'imscp.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search = "/(" . $base . "\/mod\/imscp\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@IMSCPINDEX*$2@$', $content);

                $search = "/(" . $base . "\/mod\/imscp\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@IMSCPVIEWBYID*$2@$', $content);

        return $content;
    }
}
