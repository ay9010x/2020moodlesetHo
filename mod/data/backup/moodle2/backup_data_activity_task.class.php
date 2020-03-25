<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/data/backup/moodle2/backup_data_stepslib.php');


class backup_data_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_data_activity_structure_step('data_structure', 'data.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/data\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@DATAINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/data\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@DATAVIEWBYID*$2@$', $content);

                $search="/(".$base."\/mod\/data\/view.php\?d\=)([0-9]+)/";
        $content= preg_replace($search,'$@DATAVIEWBYD*$2@$', $content);

                $search="/(".$base."\/mod\/data\/view.php\?d\=)([0-9]+)\&(amp;)rid\=([0-9]+)/";
        $content= preg_replace($search,'$@DATAVIEWRECORD*$2*$4@$', $content);

        return $content;
    }
}
