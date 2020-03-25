<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/feedback/backup/moodle2/backup_feedback_stepslib.php');
require_once($CFG->dirroot . '/mod/feedback/backup/moodle2/backup_feedback_settingslib.php');


class backup_feedback_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
                $this->add_step(new backup_feedback_activity_structure_step('feedback structure', 'feedback.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search="/(".$base."\/mod\/feedback\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FEEDBACKINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/feedback\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FEEDBACKVIEWBYID*$2@$', $content);

                $search="/(".$base."\/mod\/feedback\/analysis.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FEEDBACKANALYSISBYID*$2@$', $content);

                $search="/(".$base."\/mod\/feedback\/show_entries.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FEEDBACKSHOWENTRIESBYID*$2@$', $content);

        return $content;
    }
}
