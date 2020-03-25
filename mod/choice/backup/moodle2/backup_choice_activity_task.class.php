<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/choice/backup/moodle2/backup_choice_stepslib.php');
require_once($CFG->dirroot . '/mod/choice/backup/moodle2/backup_choice_settingslib.php');


class backup_choice_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_choice_activity_structure_step('choice_structure', 'choice.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/choice\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@CHOICEINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/choice\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@CHOICEVIEWBYID*$2@$', $content);

        return $content;
    }
}
