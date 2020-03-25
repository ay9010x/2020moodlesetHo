<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/survey/backup/moodle2/backup_survey_stepslib.php');


class backup_survey_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_survey_activity_structure_step('survey_structure', 'survey.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/survey\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SURVEYINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/survey\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SURVEYVIEWBYID*$2@$', $content);

        return $content;
    }
}
