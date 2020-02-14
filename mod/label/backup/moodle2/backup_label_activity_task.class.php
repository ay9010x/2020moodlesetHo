<?php




defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/label/backup/moodle2/backup_label_stepslib.php');


class backup_label_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_label_activity_structure_step('label_structure', 'label.xml'));
    }

    
    static public function encode_content_links($content) {
        return $content;
    }
}
