<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/workshop/backup/moodle2/backup_workshop_settingslib.php');
require_once($CFG->dirroot . '/mod/workshop/backup/moodle2/backup_workshop_stepslib.php');


class backup_workshop_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_workshop_activity_structure_step('workshop_structure', 'workshop.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search = "/(" . $base . "\/mod\/workshop\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@WORKSHOPINDEX*$2@$', $content);

                $search = "/(" . $base . "\/mod\/workshop\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@WORKSHOPVIEWBYID*$2@$', $content);

        return $content;
    }
}
